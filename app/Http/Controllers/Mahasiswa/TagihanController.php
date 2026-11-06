<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\{Mahasiswa, Pembayaran, Tagihan};
use Illuminate\Http\Request;

class TagihanController extends Controller
{
    public function index(Request $request)
    {
        $id = $this->mahasiswaId($request);

        $tagihan = Tagihan::where('mahasiswa_id', $id)
            ->withSum(['pembayaran as terbayar' => fn ($q) => $q->where('status', 'diterima')], 'jumlah')
            ->withSum(['pembayaran as menunggu' => fn ($q) => $q->where('status', 'menunggu')], 'jumlah')
            ->orderByDesc('jatuh_tempo')
            ->get();

        return view('mahasiswa.tagihan.index', [
            'tagihan'      => $tagihan,
            'totalTagihan' => (int) $tagihan->sum('jumlah'),
            'totalBayar'   => (int) $tagihan->sum('terbayar'),
        ]);
    }

    public function show(Request $request, Tagihan $tagihan)
    {
        $this->pastikanMilik($request, $tagihan);

        $riwayat = $tagihan->pembayaran()->orderByDesc('id')->get();

        return view('mahasiswa.tagihan.show', compact('tagihan', 'riwayat'));
    }

    public function bayar(Request $request, Tagihan $tagihan)
    {
        $this->pastikanMilik($request, $tagihan);

        $maks = $this->batasBayar($tagihan);
        if ($maks <= 0) {
            return redirect()->route('mahasiswa.tagihan.show', $tagihan)
                ->withErrors(['jumlah' => 'Tagihan ini sudah lunas atau seluruh sisanya sedang menunggu verifikasi.']);
        }

        return view('mahasiswa.tagihan.bayar', compact('tagihan', 'maks'));
    }

    public function store(Request $request, Tagihan $tagihan)
    {
        $this->pastikanMilik($request, $tagihan);

        $maks = $this->batasBayar($tagihan);
        if ($maks <= 0) {
            return redirect()->route('mahasiswa.tagihan.show', $tagihan)
                ->withErrors(['jumlah' => 'Tagihan ini sudah lunas atau seluruh sisanya sedang menunggu verifikasi.']);
        }

        $request->merge(['jumlah' => preg_replace('/\D/', '', (string) $request->input('jumlah'))]);

        $data = $request->validate([
            'jumlah'        => ['required', 'integer', 'min:1', "max:{$maks}"],
            'tanggal_bayar' => ['required', 'date', 'before_or_equal:today'],
            'metode'        => ['required', 'in:Transfer Bank,QRIS,E-Wallet'],
            'bukti'         => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'catatan'       => ['nullable', 'string', 'max:255'],
        ], [
            'jumlah.max'  => 'Jumlah melebihi sisa tagihan yang bisa dibayar (maksimal Rp ' . number_format($maks, 0, ',', '.') . ').',
            'bukti.max'   => 'Ukuran bukti maksimal 2 MB.',
            'bukti.mimes' => 'Bukti harus berupa JPG, PNG, atau PDF.',
        ]);

        $path = $request->file('bukti')->store('bukti-pembayaran', 'local');

        Pembayaran::create([
            'tagihan_id'    => $tagihan->id,
            'jumlah'        => $data['jumlah'],
            'tanggal_bayar' => $data['tanggal_bayar'],
            'metode'        => $data['metode'],
            'bukti_path'    => $path,
            'catatan'       => $data['catatan'] ?? null,
            'status'        => 'menunggu',
        ]);

        return redirect()->route('mahasiswa.tagihan.show', $tagihan)
            ->with('status', 'Bukti pembayaran terkirim. Menunggu verifikasi admin.');
    }

    // ---------------------------------------------------------------

    private function mahasiswaId(Request $request): int
    {
        return (int) Mahasiswa::where('user_id', $request->user()->id)->value('id');
    }

    private function pastikanMilik(Request $request, Tagihan $tagihan): void
    {
        $id = $this->mahasiswaId($request);

        abort_unless($id && $tagihan->mahasiswa_id === $id, 403, 'Bukan tagihan Anda.');
    }

    /** Sisa yang masih boleh dibayar = jumlah - diterima - menunggu verifikasi. */
    private function batasBayar(Tagihan $tagihan): int
    {
        return max(0, $tagihan->jumlah - $tagihan->terbayar() - $tagihan->menunggu());
    }
}
