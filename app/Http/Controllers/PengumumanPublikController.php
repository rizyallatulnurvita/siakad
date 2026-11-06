<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;

class PengumumanPublikController extends Controller
{
    public function index(Request $request)
    {
        $rows = Pengumuman::aktif()->untuk($request->user()->role)->latest('tanggal_mulai')->latest('id')->paginate(10);

        return view('pengumuman.index', compact('rows'));
    }
}
