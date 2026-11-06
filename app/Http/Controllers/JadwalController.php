<?php

namespace App\Http\Controllers;

use App\Models\{Dosen, Kelas, Mahasiswa};
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    /** Jadwal kuliah: admin = semua, dosen = kelas yang diampu, mahasiswa = KRS yang disetujui. */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Kelas::with(['mataKuliah', 'dosen']);

        if ($user->role === 'dosen') {
            $query->where('dosen_id', Dosen::where('user_id', $user->id)->value('id') ?? 0);
        } elseif ($user->role === 'mahasiswa') {
            $query->whereHas('krs', fn ($k) => $k
                ->where('mahasiswa_id', Mahasiswa::where('user_id', $user->id)->value('id') ?? 0)
                ->where('status', 'disetujui'));
        }

        return view('jadwal.index', [
            'hari'   => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
            'jadwal' => $query->get()->sortBy('jam_mulai')->groupBy('hari'),
        ]);
    }
}
