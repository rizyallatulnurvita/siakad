<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Concerns\MemilikiKelas;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;

class NilaiController extends Controller
{
    use MemilikiKelas;

    public function edit(Request $request, Kelas $kelas)
    {
        $this->pastikanKelasMilikDosen($request, $kelas);

        $kelas->load('mataKuliah');
        $peserta = $kelas->krs()->where('status', 'disetujui')->with('mahasiswa')->get()
            ->sortBy('mahasiswa.nim');

        return view('dosen.nilai.edit', compact('kelas', 'peserta'));
    }

    public function update(Request $request, Kelas $kelas)
    {
        $this->pastikanKelasMilikDosen($request, $kelas);

        if ($kelas->nilai_terkunci) {
            return back()->withErrors(['nilai' => 'Nilai sudah dikunci. Minta admin untuk membuka kunci.']);
        }

        $request->validate([
            'nilai'         => ['required', 'array'],
            'nilai.*.tugas' => ['nullable', 'numeric', 'between:0,100'],
            'nilai.*.uts'   => ['nullable', 'numeric', 'between:0,100'],
            'nilai.*.uas'   => ['nullable', 'numeric', 'between:0,100'],
        ]);

        $daftar = $kelas->krs()->where('status', 'disetujui')->get()->keyBy('id');

        foreach ($request->input('nilai') as $krsId => $n) {
            $krs = $daftar->get((int) $krsId);
            if (! $krs) {
                continue;
            }

            $krs->nilai_tugas = $n['tugas'] ?? null;
            $krs->nilai_uts   = $n['uts'] ?? null;
            $krs->nilai_uas   = $n['uas'] ?? null;

            if ($krs->nilai_tugas !== null && $krs->nilai_uts !== null && $krs->nilai_uas !== null) {
                $krs->hitungNilai();
            } else {
                $krs->nilai_akhir = null;
                $krs->nilai_huruf = null;
            }

            $krs->save();
        }

        return back()->with('status', 'Nilai tersimpan.');
    }

    public function kunci(Request $request, Kelas $kelas)
    {
        $this->pastikanKelasMilikDosen($request, $kelas);

        if ($kelas->nilai_terkunci) {
            return back();
        }

        $belumLengkap = $kelas->krs()->where('status', 'disetujui')
            ->where(fn ($q) => $q->whereNull('nilai_tugas')->orWhereNull('nilai_uts')->orWhereNull('nilai_uas'))
            ->count();

        if ($belumLengkap > 0) {
            return back()->withErrors(['nilai' => "Belum bisa dikunci: {$belumLengkap} mahasiswa belum lengkap nilainya."]);
        }

        $kelas->update(['nilai_terkunci' => true, 'dikunci_pada' => now()]);

        return back()->with('status', 'Nilai dikunci dan kini tampil di akun mahasiswa.');
    }
}
