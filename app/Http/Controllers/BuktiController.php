<?php

namespace App\Http\Controllers;

use App\Models\{Mahasiswa, Pembayaran};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BuktiController extends Controller
{
    /** Tampilkan bukti pembayaran (hanya admin atau pemilik tagihan). */
    public function show(Request $request, Pembayaran $pembayaran)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            $id = Mahasiswa::where('user_id', $user->id)->value('id');
            abort_unless($id && $pembayaran->tagihan->mahasiswa_id === $id, 403);
        }

        abort_unless(
            $pembayaran->bukti_path && Storage::disk('local')->exists($pembayaran->bukti_path),
            404,
            'Bukti tidak tersedia.'
        );

        return Storage::disk('local')->response($pembayaran->bukti_path);
    }
}
