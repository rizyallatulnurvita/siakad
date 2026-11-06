<?php

namespace App\Services;

use App\Models\KelasKuliah;
use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\Mahasiswa;
use App\Models\Nilai;
use App\Models\Pengaturan;
use App\Models\TahunAkademik;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class KrsService
{
    public const EDITABLE = ['draft', 'ditolak'];

    public function __construct(private GradeService $grade)
    {
    }

    /** Dua kumpulan jadwal bentrok bila hari sama dan rentang jam beririsan. */
    public static function jadwalBentrok(iterable $a, iterable $b): bool
    {
        foreach ($a as $x) {
            foreach ($b as $y) {
                if (data_get($x, 'hari') !== data_get($y, 'hari')) {
                    continue;
                }
                $xm = substr((string) data_get($x, 'jam_mulai'), 0, 5);
                $xs = substr((string) data_get($x, 'jam_selesai'), 0, 5);
                $ym = substr((string) data_get($y, 'jam_mulai'), 0, 5);
                $ys = substr((string) data_get($y, 'jam_selesai'), 0, 5);
                if ($xm < $ys && $ym < $xs) {
                    return true;
                }
            }
        }

        return false;
    }

    public function maksSks(Mahasiswa $m, TahunAkademik $ta): int
    {
        $default = (int) Pengaturan::get('maks_sks_default', 20);
        $tinggi = (int) Pengaturan::get('maks_sks_tinggi', 24);
        $batas = (float) Pengaturan::get('ips_batas_tinggi', 3.0);

        $mulai = $ta->tanggal_mulai->toDateString();
        $sebelumnya = $this->grade->rows($m->id)
            ->filter(fn ($r) => (string) $r->tanggal_mulai < $mulai)
            ->groupBy('ta_id');
        if ($sebelumnya->isEmpty()) {
            return $default;
        }

        return $this->grade->ringkas($sebelumnya->last())['ip'] >= $batas ? $tinggi : $default;
    }

    public function terisi(int $kelasId, ?int $kecualiKrsId = null, array $status = ['diajukan', 'disetujui']): int
    {
        return KrsDetail::where('kelas_kuliah_id', $kelasId)
            ->whereHas('krs', function ($q) use ($status, $kecualiKrsId) {
                $q->whereIn('status', $status);
                if ($kecualiKrsId) {
                    $q->where('id', '!=', $kecualiKrsId);
                }
            })->count();
    }

    private function pastikanPeriode(TahunAkademik $ta): void
    {
        if (!$ta->periodeKrsTerbuka()) {
            throw new DomainException('Periode pengisian KRS belum dibuka atau sudah ditutup.');
        }
    }

    public function tambah(Mahasiswa $m, int $kelasId): Krs
    {
        return DB::transaction(function () use ($m, $kelasId) {
            $ta = TahunAkademik::aktif() ?? throw new DomainException('Belum ada semester aktif.');
            $this->pastikanPeriode($ta);
            if ($m->status !== 'aktif') {
                throw new DomainException('Hanya mahasiswa berstatus aktif yang dapat mengisi KRS.');
            }

            $kelas = KelasKuliah::with(['mataKuliah', 'jadwal'])->findOrFail($kelasId);
            if ($kelas->tahun_akademik_id !== $ta->id) {
                throw new DomainException('Kelas tidak berada pada semester aktif.');
            }
            if ($kelas->mataKuliah->program_studi_id !== $m->program_studi_id) {
                throw new DomainException('Kelas bukan dari program studi Anda.');
            }

            $krs = Krs::where('mahasiswa_id', $m->id)->where('tahun_akademik_id', $ta->id)->lockForUpdate()->first()
                ?? Krs::create(['mahasiswa_id' => $m->id, 'tahun_akademik_id' => $ta->id, 'status' => 'draft']);

            if (!in_array($krs->status, self::EDITABLE, true)) {
                throw new DomainException('KRS sudah diajukan/disetujui dan tidak dapat diubah.');
            }

            $krs->load('details.kelas.mataKuliah', 'details.kelas.jadwal');

            if ($krs->details->contains('kelas_kuliah_id', $kelas->id)) {
                throw new DomainException('Kelas ini sudah ada di KRS Anda.');
            }
            if ($krs->details->contains(fn ($d) => $d->kelas->mata_kuliah_id === $kelas->mata_kuliah_id)) {
                throw new DomainException('Mata kuliah yang sama sudah dipilih pada kelas lain.');
            }

            $sks = $krs->details->sum(fn ($d) => $d->kelas->mataKuliah->sks) + $kelas->mataKuliah->sks;
            $maks = $this->maksSks($m, $ta);
            if ($sks > $maks) {
                throw new DomainException("Total SKS ($sks) melebihi batas maksimum $maks SKS.");
            }

            foreach ($krs->details as $d) {
                if (self::jadwalBentrok($d->kelas->jadwal, $kelas->jadwal)) {
                    throw new DomainException('Jadwal bentrok dengan mata kuliah ' . $d->kelas->mataKuliah->nama . '.');
                }
            }

            if ($this->terisi($kelas->id, $krs->id) >= $kelas->kapasitas) {
                throw new DomainException('Kapasitas kelas sudah penuh.');
            }

            $krs->details()->create(['kelas_kuliah_id' => $kelas->id]);
            $krs->update(['total_sks' => $sks, 'status' => 'draft']);

            return $krs;
        });
    }

    public function hapus(Mahasiswa $m, int $detailId): void
    {
        DB::transaction(function () use ($m, $detailId) {
            $detail = KrsDetail::with('krs')->findOrFail($detailId);
            $krs = $detail->krs;
            abort_unless($krs->mahasiswa_id === $m->id, 403);
            if (!in_array($krs->status, self::EDITABLE, true)) {
                throw new DomainException('KRS sudah diajukan/disetujui dan tidak dapat diubah.');
            }
            $detail->delete();
            $krs->update(['total_sks' => $this->hitungSks($krs)]);
        });
    }

    private function hitungSks(Krs $krs): int
    {
        return (int) KrsDetail::where('krs_id', $krs->id)
            ->join('kelas_kuliah', 'kelas_kuliah.id', '=', 'krs_detail.kelas_kuliah_id')
            ->join('mata_kuliah', 'mata_kuliah.id', '=', 'kelas_kuliah.mata_kuliah_id')
            ->sum('mata_kuliah.sks');
    }

    public function ajukan(Mahasiswa $m): Krs
    {
        return DB::transaction(function () use ($m) {
            $ta = TahunAkademik::aktif() ?? throw new DomainException('Belum ada semester aktif.');
            $this->pastikanPeriode($ta);

            $krs = Krs::where('mahasiswa_id', $m->id)->where('tahun_akademik_id', $ta->id)->lockForUpdate()->first()
                ?? throw new DomainException('Anda belum memilih kelas.');
            if (!in_array($krs->status, self::EDITABLE, true)) {
                throw new DomainException('KRS sudah diajukan atau disetujui.');
            }

            $krs->load('details.kelas.mataKuliah', 'details.kelas.jadwal');
            if ($krs->details->isEmpty()) {
                throw new DomainException('Pilih minimal satu kelas sebelum mengajukan KRS.');
            }

            $sks = $krs->details->sum(fn ($d) => $d->kelas->mataKuliah->sks);
            $maks = $this->maksSks($m, $ta);
            if ($sks > $maks) {
                throw new DomainException("Total SKS ($sks) melebihi batas maksimum $maks SKS.");
            }

            $list = $krs->details->values();
            foreach ($list as $i => $a) {
                foreach ($list->slice($i + 1) as $b) {
                    if (self::jadwalBentrok($a->kelas->jadwal, $b->kelas->jadwal)) {
                        throw new DomainException('Jadwal bentrok: ' . $a->kelas->mataKuliah->nama . ' dan ' . $b->kelas->mataKuliah->nama . '.');
                    }
                }
                if ($this->terisi($a->kelas_kuliah_id, $krs->id) >= $a->kelas->kapasitas) {
                    throw new DomainException('Kapasitas kelas ' . $a->kelas->mataKuliah->nama . ' sudah penuh.');
                }
            }

            $krs->update([
                'status' => 'diajukan', 'total_sks' => $sks,
                'diajukan_at' => now(), 'catatan' => null,
            ]);

            return $krs;
        });
    }

    public function setujui(Krs $krs, User $oleh, ?string $catatan = null): void
    {
        DB::transaction(function () use ($krs, $oleh, $catatan) {
            $krs = Krs::lockForUpdate()->findOrFail($krs->id);
            if ($krs->status !== 'diajukan') {
                throw new DomainException('Hanya KRS berstatus "diajukan" yang dapat disetujui.');
            }
            $krs->load('details.kelas.mataKuliah');

            foreach ($krs->details as $d) {
                if ($this->terisi($d->kelas_kuliah_id, $krs->id, ['disetujui']) >= $d->kelas->kapasitas) {
                    throw new DomainException('Kapasitas kelas ' . $d->kelas->mataKuliah->nama . ' sudah penuh.');
                }
            }

            $krs->update([
                'status' => 'disetujui', 'catatan' => $catatan,
                'diproses_at' => now(), 'diproses_oleh' => $oleh->id,
            ]);

            foreach ($krs->details as $d) {
                Nilai::firstOrCreate(
                    ['krs_detail_id' => $d->id],
                    ['kelas_kuliah_id' => $d->kelas_kuliah_id, 'mahasiswa_id' => $krs->mahasiswa_id]
                );
            }
        });
    }

    public function tolak(Krs $krs, User $oleh, string $catatan): void
    {
        DB::transaction(function () use ($krs, $oleh, $catatan) {
            $krs = Krs::lockForUpdate()->findOrFail($krs->id);
            if ($krs->status !== 'diajukan') {
                throw new DomainException('Hanya KRS berstatus "diajukan" yang dapat ditolak.');
            }
            $krs->update([
                'status' => 'ditolak', 'catatan' => $catatan,
                'diproses_at' => now(), 'diproses_oleh' => $oleh->id,
            ]);
        });
    }
}
