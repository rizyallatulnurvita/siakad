<?php

namespace App\http\Services;

use App\Models\Pengaturan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Perhitungan nilai akhir, konversi huruf, IP semester dan IPK.
 * Semua aturan dibaca dari tabel `pengaturan` (bisa diubah Admin).
 */
class GradeService
{
    public const DEFAULT_WEIGHTS = ['tugas' => 30, 'uts' => 30, 'uas' => 40];


    public const DEFAULT_SCALE = [
        ['huruf' => 'A',  'min' => 85, 'bobot' => 4.0],
        ['huruf' => 'A-', 'min' => 80, 'bobot' => 3.7],
        ['huruf' => 'B+', 'min' => 75, 'bobot' => 3.3],
        ['huruf' => 'B',  'min' => 70, 'bobot' => 3.0],
        ['huruf' => 'B-', 'min' => 65, 'bobot' => 2.7],
        ['huruf' => 'C+', 'min' => 60, 'bobot' => 2.3],
        ['huruf' => 'C',  'min' => 55, 'bobot' => 2.0],
        ['huruf' => 'D',  'min' => 40, 'bobot' => 1.0],
        ['huruf' => 'E',  'min' => 0,  'bobot' => 0.0],
    ];

    private array $weights;
    private array $scale;
    private bool $nilaiTerbaik;

    public function __construct(?array $weights = null, ?array $scale = null, ?bool $nilaiTerbaik = null)
    {
        $this->weights = $weights ?? Pengaturan::get('bobot_nilai', self::DEFAULT_WEIGHTS);
        $scale = $scale ?? Pengaturan::get('skala_nilai', self::DEFAULT_SCALE);
        usort($scale, fn ($a, $b) => $b['min'] <=> $a['min']);
        $this->scale = $scale;
        $this->nilaiTerbaik = $nilaiTerbaik ?? (bool) Pengaturan::get('ipk_nilai_terbaik', true);
    }

    public function weights(): array
    {
        return $this->weights;
    }

    public function scale(): array
    {
        return $this->scale;
    }

    /** Nilai akhir; null bila salah satu komponen belum diisi. */
    public function nilaiAkhir(?float $tugas, ?float $uts, ?float $uas): ?float
    {
        if ($tugas === null || $uts === null || $uas === null) {
            return null;
        }
        $total = array_sum($this->weights);
        if ($total <= 0) {
            return null;
        }
        $nilai = ($tugas * $this->weights['tugas'] + $uts * $this->weights['uts'] + $uas * $this->weights['uas']) / $total;

        return round($nilai, 2);
    }

    /** @return array{huruf:string,bobot:float}|null */
    public function konversi(?float $nilai): ?array
    {
        if ($nilai === null) {
            return null;
        }
        foreach ($this->scale as $row) {
            if ($nilai >= $row['min']) {
                return ['huruf' => $row['huruf'], 'bobot' => (float) $row['bobot']];
            }
        }

        return null;
    }

    /**
     * @param iterable $rows item dengan sks, bobot, (opsional) mk_id; array atau objek
     * @return array{sks:int,mutu:float,ip:float,sks_lulus:int}
     */
    public function ringkas(iterable $rows): array
    {
        $sks = 0;
        $mutu = 0.0;
        $lulus = 0;
        foreach ($rows as $r) {
            $bobot = data_get($r, 'bobot');
            if ($bobot === null) {
                continue;
            }
            $s = (int) data_get($r, 'sks');
            $sks += $s;
            $mutu += $s * (float) $bobot;
            if ((float) $bobot > 0) {
                $lulus += $s;
            }
        }

        return [
            'sks' => $sks,
            'mutu' => round($mutu, 2),
            'ip' => $sks > 0 ? round($mutu / $sks, 2) : 0.0,
            'sks_lulus' => $lulus,
        ];
    }

    /** IPK; bila "nilai terbaik" aktif, mata kuliah yang diulang hanya dihitung nilai tertingginya. */
    public function ringkasKumulatif(iterable $rows): array
    {
        $rows = collect($rows);
        if ($this->nilaiTerbaik) {
            $rows = $rows->filter(fn ($r) => data_get($r, 'bobot') !== null)
                ->groupBy(fn ($r) => data_get($r, 'mk_id'))
                ->map(fn ($g) => $g->sortByDesc(fn ($r) => (float) data_get($r, 'bobot'))->first())
                ->values();
        }

        return $this->ringkas($rows);
    }

    /** Baris nilai yang sudah memiliki bobot, diurutkan menurut semester. */
    public function rows(array|int $mahasiswaIds): Collection
    {
        return DB::table('nilai')
            ->whereIn('nilai.mahasiswa_id', (array) $mahasiswaIds)
            ->whereNotNull('nilai.bobot')
            ->join('kelas_kuliah', 'kelas_kuliah.id', '=', 'nilai.kelas_kuliah_id')
            ->join('mata_kuliah', 'mata_kuliah.id', '=', 'kelas_kuliah.mata_kuliah_id')
            ->join('tahun_akademik', 'tahun_akademik.id', '=', 'kelas_kuliah.tahun_akademik_id')
            ->orderBy('tahun_akademik.tanggal_mulai')
            ->orderBy('mata_kuliah.kode')
            ->get([
                'nilai.mahasiswa_id', 'nilai.tugas', 'nilai.uts', 'nilai.uas', 'nilai.nilai_akhir', 'nilai.huruf', 'nilai.bobot',
                'mata_kuliah.id as mk_id', 'mata_kuliah.kode', 'mata_kuliah.nama as mk_nama', 'mata_kuliah.sks',
                'tahun_akademik.id as ta_id', 'tahun_akademik.tahun', 'tahun_akademik.semester',
                'tahun_akademik.tanggal_mulai',
            ]);
    }

    public function ips(int $mahasiswaId, int $taId): array
    {
        return $this->ringkas($this->rows($mahasiswaId)->where('ta_id', $taId));
    }

    public function ipk(int $mahasiswaId): array
    {
        return $this->ringkasKumulatif($this->rows($mahasiswaId));
    }

    /** @return array<string,float> label semester => IP */
    public function ipPerSemester(int $mahasiswaId): array
    {
        $out = [];
        foreach ($this->rows($mahasiswaId)->groupBy('ta_id') as $group) {
            $first = $group->first();
            $out[$first->tahun . ' ' . ucfirst($first->semester)] = $this->ringkas($group)['ip'];
        }

        return $out;
    }
}
