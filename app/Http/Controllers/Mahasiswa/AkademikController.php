<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Admin\Concerns\ResolvesProfile;
use App\Http\Controllers\Controller;
use App\Models\JadwalKuliah;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use App\http\Services\GradeService;
use Illuminate\Http\Request;

class AkademikController extends Controller
{
    use ResolvesProfile;

    public function mataKuliah(Request $request)
    {
        $m = $this->mahasiswaSaya();
        $s = trim((string) $request->input('q'));
        $rows = MataKuliah::where('program_studi_id', $m->program_studi_id)
            ->when($s !== '', function ($q) use ($s) {
                $like = '%' . addcslashes($s, '%_\\') . '%';
                $q->where(fn ($w) => $w->where('nama', 'like', $like)->orWhere('kode', 'like', $like));
            })
            ->when($request->filled('semester'), fn ($q) => $q->where('semester', (int) $request->semester))
            ->orderBy('semester')->orderBy('kode')->paginate(12)->withQueryString();

        return view('mahasiswa.matakuliah', ['rows' => $rows]);
    }

    public function jadwal()
    {
        $m = $this->mahasiswaSaya();
        $ta = TahunAkademik::aktif();

        $jadwal = collect();
        if ($ta) {
            $jadwal = JadwalKuliah::with(['kelas.mataKuliah', 'kelas.dosen'])
                ->whereHas('kelas.krsDetail.krs', fn ($k) => $k->where('mahasiswa_id', $m->id)
                    ->where('tahun_akademik_id', $ta->id)->whereIn('status', ['diajukan', 'disetujui']))
                ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")->orderBy('jam_mulai')->get();
        }

        return view('mahasiswa.jadwal', ['jadwal' => $jadwal, 'ta' => $ta]);
    }

    public function khs(Request $request, GradeService $g)
    {
        $m = $this->mahasiswaSaya();
        $all = $g->rows($m->id);
        $semester = $all->unique('ta_id')->map(fn ($r) => ['id' => $r->ta_id, 'label' => $r->tahun . ' ' . ucfirst($r->semester)])->values();

        $pilih = (int) ($request->input('ta') ?: ($semester->last()['id'] ?? 0));
        abort_if($pilih && !$semester->contains('id', $pilih), 404);

        $rows = $all->where('ta_id', $pilih)->values();

        return view('mahasiswa.khs', [
            'mhs' => $m->load('programStudi'), 'semester' => $semester, 'pilih' => $pilih, 'rows' => $rows,
            'ips' => $g->ringkas($rows), 'ipk' => $g->ringkasKumulatif($all),
        ]);
    }

    public function nilai(GradeService $g)
    {
        $m = $this->mahasiswaSaya();
        $all = $g->rows($m->id);

        return view('mahasiswa.nilai', [
            'mhs' => $m->load('programStudi'),
            'perSemester' => $all->groupBy('ta_id'),
            'ipk' => $g->ringkasKumulatif($all),
            'g' => $g,
        ]);
    }
}
