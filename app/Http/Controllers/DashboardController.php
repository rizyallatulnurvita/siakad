<?php

namespace App\Http\Controllers;

use App\Models\{Absensi, Dosen, Kelas, Krs, Mahasiswa, MataKuliah, Pertemuan, Prodi};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private const HURUF = ['A', 'B', 'C', 'D', 'E'];
    private const BOBOT = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'E' => 0];

    /** Arahkan ke dashboard sesuai role setelah login. */
    public function index(Request $request)
    {
        return redirect()->route($request->user()->role . '.dashboard');
    }

    /** Jumlah nilai huruf A-E dari sebuah query Krs (urutan tetap A..E). */
    private function distribusi($query): array
    {
        $raw = $query->whereNotNull('nilai_huruf')
            ->selectRaw('nilai_huruf, count(*) as total')
            ->groupBy('nilai_huruf')
            ->pluck('total', 'nilai_huruf');

        return collect(self::HURUF)->map(fn ($h) => (int) ($raw[$h] ?? 0))->all();
    }

    // ================= ADMIN =================
    public function admin()
    {
        $kelas = Kelas::with(['mataKuliah', 'dosen'])
            ->withCount(['krs' => fn ($q) => $q->where('status', 'disetujui')])
            ->withAvg('krs', 'nilai_akhir')
            ->orderBy('id')
            ->get();

        $prodi = Prodi::withCount('mahasiswa')->get();

        return view('admin.dashboard', [
            'totalMahasiswa' => Mahasiswa::count(),
            'totalDosen'     => Dosen::count(),
            'totalKelas'     => $kelas->count(),
            'kelasTerkunci'  => $kelas->where('nilai_terkunci', true)->count(),
            'totalMk'        => MataKuliah::count(),
            'distribusi'     => $this->distribusi(Krs::query()),
            'prodiLabels'    => $prodi->pluck('nama')->values()->all(),
            'prodiData'      => $prodi->pluck('mahasiswa_count')->values()->all(),
            'kelas'          => $kelas,
        ]);
    }

    // ================= DOSEN =================
    public function dosen(Request $request)
    {
        $dosen = Dosen::where('user_id', $request->user()->id)->firstOrFail();

        $kelas = $dosen->kelas()
            ->with('mataKuliah')
            ->withCount(['krs' => fn ($q) => $q->where('status', 'disetujui')])
            ->withAvg(['krs as rata_nilai' => fn ($q) => $q->where('status', 'disetujui')], 'nilai_akhir')
            ->get();

        return view('dosen.dashboard', [
            'dosen'         => $dosen,
            'kelas'         => $kelas,
            'totalKelas'    => $kelas->count(),
            'totalPeserta'  => $kelas->sum('krs_count'),
            'belumKunci'    => $kelas->where('nilai_terkunci', false)->count(),
            'distribusi'    => $this->distribusi(Krs::whereIn('kelas_id', $kelas->pluck('id'))),
            'rataLabels'    => $kelas->map(fn ($k) => $k->mataKuliah->kode . '-' . $k->nama)->values()->all(),
            'rataData'      => $kelas->map(fn ($k) => round((float) $k->rata_nilai, 1))->values()->all(),
        ]);
    }

    // ================= MAHASISWA =================
    public function mahasiswa(Request $request)
    {
        $mhs = Mahasiswa::with('prodi', 'dosenWali')->where('user_id', $request->user()->id)->firstOrFail();

        $krs = $mhs->krs()->with('kelas.mataKuliah', 'kelas.dosen')->get();
        $disetujui = $krs->where('status', 'disetujui');

        // Nilai hanya dihitung/ditampilkan jika dosen sudah mengunci nilai kelas tsb.
        $dinilai = $disetujui->filter(fn ($r) => $r->kelas->nilai_terkunci && $r->nilai_huruf);

        $sksDinilai = $dinilai->sum(fn ($r) => $r->kelas->mataKuliah->sks);
        $mutu       = $dinilai->sum(fn ($r) => self::BOBOT[$r->nilai_huruf] * $r->kelas->mataKuliah->sks);
        $ipk        = $sksDinilai ? round($mutu / $sksDinilai, 2) : 0;

        $absen = Absensi::where('mahasiswa_id', $mhs->id)
            ->whereHas('pertemuan', fn ($q) => $q->whereIn('kelas_id', $disetujui->pluck('kelas_id')))
            ->get()->countBy('status');
        $totalPertemuan = Pertemuan::whereIn('kelas_id', $disetujui->pluck('kelas_id'))->count();
        $persenHadir = $totalPertemuan ? round(($absen['hadir'] ?? 0) / $totalPertemuan * 100) : 0;

        return view('mahasiswa.dashboard', [
            'mhs'          => $mhs,
            'krs'          => $krs,
            'totalSks'     => $disetujui->sum(fn ($r) => $r->kelas->mataKuliah->sks),
            'ipk'          => $ipk,
            'persenHadir'  => $persenHadir,
            'nilaiLabels'  => $dinilai->map(fn ($r) => $r->kelas->mataKuliah->nama)->values()->all(),
            'nilaiData'    => $dinilai->map(fn ($r) => (float) $r->nilai_akhir)->values()->all(),
            'hadirData'    => [
                (int) ($absen['hadir'] ?? 0), (int) ($absen['izin'] ?? 0),
                (int) ($absen['sakit'] ?? 0), (int) ($absen['alpa'] ?? 0),
            ],
        ]);
    }
}
