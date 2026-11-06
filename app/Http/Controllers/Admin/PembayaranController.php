<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    /** Daftar pembayaran; default hanya yang menunggu verifikasi. */
    public function index(Request $request)
    {
        $status = $request->query('status', 'menunggu');

        $pembayaran = Pembayaran::with('tagihan.mahasiswa')
            ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.pembayaran.index', [
            'pembayaran' => $pembayaran,
            'status'     => $status,
        ]);
    }

    public function verifikasi(Request $request, Pembayaran $pembayaran)
    {
        $data = $request->validate([
            'aksi'          => ['required', 'in:terima,tolak'],
            'catatan_admin' => ['nullable', 'string', 'max:255'],
        ]);

        if ($pembayaran->status !== 'menunggu') {
            return back()->withErrors(['aksi' => 'Pembayaran ini sudah diverifikasi sebelumnya.']);
        }

        if ($data['aksi'] === 'tolak' && empty($data['catatan_admin'])) {
            return back()->withErrors(['aksi' => 'Isi alasan penolakan terlebih dahulu.']);
        }

        $pembayaran->update([
            'status'            => $data['aksi'] === 'terima' ? 'diterima' : 'ditolak',
            'catatan_admin'     => $data['catatan_admin'] ?? null,
            'diverifikasi_oleh' => $request->user()->id,
            'diverifikasi_pada' => now(),
        ]);

        $pembayaran->tagihan->perbaruiStatus();

        return back()->with('status', $data['aksi'] === 'terima' ? 'Pembayaran diterima.' : 'Pembayaran ditolak.');
    }
}
