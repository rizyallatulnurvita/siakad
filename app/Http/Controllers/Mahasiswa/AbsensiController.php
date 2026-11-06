<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\{Absensi, Mahasiswa};
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    /** Rekap kehadiran per mata kuliah. */
    public function index(Request $request)
    {
        $mhs = Mahasiswa::where('user_id', $request->user()->id)->firstOrFail();

        $krs = $mhs->krs()->where('status', 'disetujui')->with('kelas.mataKuliah')->get();

        $rekap = $krs->map(function ($k) use ($mhs) {
            $total = $k->kelas->pertemuan()->count();
            $hitung = Absensi::where('mahasiswa_id', $mhs->id)
                ->whereHas('pertemuan', fn ($q) => $q->where('kelas_id', $k->kelas_id))
                ->get()->countBy('status');

            $hadir = (int) ($hitung['hadir'] ?? 0);

            return [
                'mk'     => $k->kelas->mataKuliah->nama,
                'kelas'  => $k->kelas->nama,
                'total'  => $total,
                'hadir'  => $hadir,
                'izin'   => (int) ($hitung['izin'] ?? 0),
                'sakit'  => (int) ($hitung['sakit'] ?? 0),
                'alpa'   => (int) ($hitung['alpa'] ?? 0),
                'persen' => $total ? (int) round($hadir / $total * 100) : 0,
            ];
        });

        return view('mahasiswa.absensi.index', compact('rekap'));
    }
}
