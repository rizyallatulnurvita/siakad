<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $kelas = Dosen::where('user_id', $request->user()->id)->firstOrFail()->kelas()
            ->with('mataKuliah')
            ->withCount(['krs' => fn ($q) => $q->where('status', 'disetujui')])
            ->get();

        return view('dosen.kelas.index', compact('kelas'));
    }
}
