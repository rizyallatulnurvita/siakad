<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Mahasiswa, Pembayaran, Prodi, Tagihan};
use Illuminate\Http\Request;

class TagihanController extends Controller
{
    public function index(Request $request)
    {
        $cari   = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        $tagihan = Tagihan::with('mahasiswa.prodi')
            ->withSum(['pembayaran as terbayar' => fn ($q) => $q->where('status', 'diterima')], 'jumlah')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($cari !== '', fn ($q) => $q->whereHas('mahasiswa', fn ($m) => $m->where(
                fn ($w) => $w->where('nama', 'like', "%{$cari}%")->orWhere('nim', 'like', "%{$cari}%")
            )))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.tagihan.index', [
            'tagihan'      => $tagihan,
            'cari'         => $cari,
            'status'       => $status,
            'totalTagihan' => (int) Tagihan::sum('jumlah'),
            'totalMasuk'   => (int) Pembayaran::where('status', 'diterima')->sum('jumlah'),
            'menunggu'     => Pembayaran::where('status', 'menunggu')->count(),
        ]);
    }

    public function create()
    {
        $mulai = now()->month >= 8 ? now()->year : now()->year - 1;

        return view('admin.tagihan.create', [
            'prodi'        => Prodi::orderBy('nama')->get(),
            'mahasiswa'    => Mahasiswa::orderBy('nim')->get(),
            'tahunAjaran'  => $mulai . '/' . ($mulai + 1),
            'semester'     => in_array(now()->month, [8, 9, 10, 11, 12, 1], true) ? 'ganjil' : 'genap',
        ]);
    }

    /** Buat tagihan untuk satu mahasiswa, satu prodi, atau semua mahasiswa aktif. */
    public function store(Request $request)
    {
        // izinkan input seperti "3.500.000"
        $request->merge(['jumlah' => preg_replace('/\D/', '', (string) $request->input('jumlah'))]);

        $data = $request->validate([
            'jenis'        => ['required', 'string', 'max:50'],
            'keterangan'   => ['nullable', 'string', 'max:255'],
            'tahun_ajaran' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'semester'     => ['required', 'in:ganjil,genap'],
            'jumlah'       => ['required', 'integer', 'min:1000', 'max:1000000000'],
            'jatuh_tempo'  => ['required', 'date'],
            'target'       => ['required', 'in:semua,prodi,mahasiswa'],
            'prodi_id'     => ['required_if:target,prodi', 'nullable', 'exists:prodi,id'],
            'mahasiswa_id' => ['required_if:target,mahasiswa', 'nullable', 'exists:mahasiswa,id'],
        ], [
            'tahun_ajaran.regex' => 'Format tahun ajaran harus seperti 2026/2027.',
            'jumlah.min'         => 'Jumlah minimal Rp 1.000.',
        ]);

        $daftar = match ($data['target']) {
            'semua' => Mahasiswa::where('status', 'aktif')->get(),
            'prodi' => Mahasiswa::where('status', 'aktif')->where('prodi_id', $data['prodi_id'])->get(),
            default => Mahasiswa::where('id', $data['mahasiswa_id'])->get(),
        };

        if ($daftar->isEmpty()) {
            return back()->withInput()->withErrors(['target' => 'Tidak ada mahasiswa aktif yang sesuai target.']);
        }

        $baru = 0;
        $lewat = 0;
        foreach ($daftar as $m) {
            $t = Tagihan::firstOrCreate(
                [
                    'mahasiswa_id' => $m->id, 'jenis' => $data['jenis'],
                    'tahun_ajaran' => $data['tahun_ajaran'], 'semester' => $data['semester'],
                ],
                [
                    'jumlah' => $data['jumlah'], 'jatuh_tempo' => $data['jatuh_tempo'],
                    'keterangan' => $data['keterangan'] ?? null, 'status' => 'belum_bayar',
                ]
            );
            $t->wasRecentlyCreated ? $baru++ : $lewat++;
        }

        return redirect()->route('admin.tagihan.index')->with(
            'status',
            "{$baru} tagihan dibuat" . ($lewat ? ", {$lewat} dilewati karena sudah ada." : '.')
        );
    }

    public function destroy(Tagihan $tagihan)
    {
        if ($tagihan->pembayaran()->exists()) {
            return back()->withErrors(['hapus' => 'Tagihan tidak bisa dihapus karena sudah ada riwayat pembayaran.']);
        }

        $tagihan->delete();

        return back()->with('status', 'Tagihan dihapus.');
    }
}
