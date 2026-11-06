<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Dosen, Kelas, MataKuliah};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::with(['mataKuliah', 'dosen'])
            ->withCount(['krs' => fn ($q) => $q->where('status', 'disetujui')])
            ->orderByDesc('tahun_ajaran')->orderBy('id')
            ->get();

        return view('admin.kelas.index', compact('kelas'));
    }

    public function create()
    {
        $kelas = new Kelas([
            'tahun_ajaran' => $this->tahunAjaranSekarang(),
            'semester'     => in_array(now()->month, [8, 9, 10, 11, 12, 1], true) ? 'ganjil' : 'genap',
            'kuota'        => 40,
        ]);

        return view('admin.kelas.form', $this->dataForm($kelas));
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        if ($pesan = $this->bentrok($data)) {
            return back()->withInput()->withErrors(['jam_mulai' => $pesan]);
        }

        Kelas::create($data);

        return redirect()->route('admin.kelas.index')->with('status', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kelas)
    {
        return view('admin.kelas.form', $this->dataForm($kelas));
    }

    public function update(Request $request, Kelas $kelas)
    {
        $sudahAdaKrs = $kelas->krs()->exists();
        $data = $this->validasi($request, $kelas);

        // Mata kuliah tidak boleh diganti jika kelas sudah punya peserta.
        if ($sudahAdaKrs) {
            $data['mata_kuliah_id'] = $kelas->mata_kuliah_id;
        }

        if ($pesan = $this->bentrok($data, $kelas)) {
            return back()->withInput()->withErrors(['jam_mulai' => $pesan]);
        }

        $kelas->update($data);

        return redirect()->route('admin.kelas.index')->with('status', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas)
    {
        if ($kelas->krs()->exists()) {
            return back()->withErrors(['hapus' => 'Kelas tidak bisa dihapus karena sudah memiliki peserta (KRS).']);
        }

        $kelas->delete();

        return back()->with('status', 'Kelas dihapus.');
    }

    /** Kunci / buka kunci nilai sebuah kelas. */
    public function toggleKunci(Kelas $kelas)
    {
        $kelas->update([
            'nilai_terkunci' => ! $kelas->nilai_terkunci,
            'dikunci_pada'   => $kelas->nilai_terkunci ? null : now(),
        ]);

        return back()->with('status', $kelas->nilai_terkunci
            ? 'Nilai kelas dikunci.'
            : 'Kunci nilai dibuka, dosen dapat mengubah nilai lagi.');
    }

    // ---------------------------------------------------------------

    private function dataForm(Kelas $kelas): array
    {
        return [
            'kelas'      => $kelas,
            'mataKuliah' => MataKuliah::orderBy('kode')->get(),
            'dosen'      => Dosen::orderBy('nama')->get(),
            'adaKrs'     => $kelas->exists && $kelas->krs()->exists(),
        ];
    }

    private function tahunAjaranSekarang(): string
    {
        $mulai = now()->month >= 8 ? now()->year : now()->year - 1;

        return $mulai . '/' . ($mulai + 1);
    }

    private function validasi(Request $request, ?Kelas $kelas = null): array
    {
        $minKuota = $kelas ? max(1, $kelas->krs()->where('status', 'disetujui')->count()) : 1;

        return $request->validate([
            'mata_kuliah_id' => ['required', 'exists:mata_kuliah,id'],
            'dosen_id'       => ['required', 'exists:dosen,id'],
            'nama'           => ['required', 'string', 'max:5',
                Rule::unique('kelas', 'nama')
                    ->where(fn ($q) => $q
                        ->where('mata_kuliah_id', $request->input('mata_kuliah_id'))
                        ->where('tahun_ajaran', $request->input('tahun_ajaran')))
                    ->ignore($kelas?->id)],
            'tahun_ajaran'   => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'semester'       => ['required', 'in:ganjil,genap'],
            'hari'           => ['nullable', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu'],
            'jam_mulai'      => ['nullable', 'required_with:jam_selesai', 'date_format:H:i'],
            'jam_selesai'    => ['nullable', 'required_with:jam_mulai', 'date_format:H:i', 'after:jam_mulai'],
            'ruang'          => ['nullable', 'string', 'max:30'],
            'kuota'          => ['required', 'integer', "min:{$minKuota}", 'max:500'],
        ], [
            'nama.unique'     => 'Kelas dengan nama ini sudah ada untuk mata kuliah dan tahun ajaran yang sama.',
            'tahun_ajaran.regex' => 'Format tahun ajaran harus seperti 2026/2027.',
            'jam_selesai.after'  => 'Jam selesai harus setelah jam mulai.',
            'kuota.min'          => "Kuota tidak boleh kurang dari jumlah peserta saat ini ({$minKuota}).",
        ]);
    }

    /** Cek bentrok jadwal dosen / ruang pada hari & jam yang sama. */
    private function bentrok(array $d, ?Kelas $kelas = null): ?string
    {
        if (empty($d['hari']) || empty($d['jam_mulai']) || empty($d['jam_selesai'])) {
            return null;
        }

        $query = Kelas::with('mataKuliah')
            ->where('hari', $d['hari'])
            ->where('tahun_ajaran', $d['tahun_ajaran'])
            ->where('semester', $d['semester'])
            ->where('jam_mulai', '<', $d['jam_selesai'])
            ->where('jam_selesai', '>', $d['jam_mulai'])
            ->where(function ($w) use ($d) {
                $w->where('dosen_id', $d['dosen_id']);
                if (! empty($d['ruang'])) {
                    $w->orWhere('ruang', $d['ruang']);
                }
            });

        if ($kelas) {
            $query->where('id', '!=', $kelas->id);
        }

        $lain = $query->first();
        if (! $lain) {
            return null;
        }

        $info = "{$lain->mataKuliah->nama} kelas {$lain->nama}";

        return (int) $lain->dosen_id === (int) $d['dosen_id']
            ? "Dosen tersebut sudah mengajar pada jam itu ({$info})."
            : "Ruang {$d['ruang']} sudah dipakai pada jam itu ({$info}).";
    }
}
