<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Concerns\MemilikiKelas;
use App\Http\Controllers\Controller;
use App\Models\{Absensi, Kelas, Pertemuan};
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    use MemilikiKelas;

    /** Daftar pertemuan sebuah kelas. */
    public function index(Request $request, Kelas $kelas)
    {
        $this->pastikanKelasMilikDosen($request, $kelas);

        $kelas->load('mataKuliah');
        $pertemuan = $kelas->pertemuan()
            ->withCount([
                'absensi as total_count',
                'absensi as hadir_count' => fn ($q) => $q->where('status', 'hadir'),
            ])
            ->orderBy('pertemuan_ke')
            ->get();

        return view('dosen.absensi.index', compact('kelas', 'pertemuan'));
    }

    /** Buat pertemuan baru (nomor otomatis, maksimal 16). */
    public function store(Request $request, Kelas $kelas)
    {
        $this->pastikanKelasMilikDosen($request, $kelas);

        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'materi'  => ['nullable', 'string', 'max:255'],
        ]);

        $berikutnya = ((int) $kelas->pertemuan()->max('pertemuan_ke')) + 1;
        if ($berikutnya > 16) {
            return back()->withErrors(['tanggal' => 'Maksimal 16 pertemuan per kelas.']);
        }

        $pertemuan = $kelas->pertemuan()->create($data + ['pertemuan_ke' => $berikutnya]);

        return redirect()->route('dosen.absensi.show', $pertemuan)
            ->with('status', "Pertemuan {$berikutnya} dibuat. Silakan isi kehadiran.");
    }

    /** Form kehadiran satu pertemuan. */
    public function show(Request $request, Pertemuan $pertemuan)
    {
        $kelas = $pertemuan->kelas()->with('mataKuliah')->first();
        $this->pastikanKelasMilikDosen($request, $kelas);

        $peserta = $kelas->krs()->where('status', 'disetujui')->with('mahasiswa')->get()
            ->pluck('mahasiswa')->sortBy('nim');
        $absen = $pertemuan->absensi()->get()->keyBy('mahasiswa_id');

        return view('dosen.absensi.show', compact('kelas', 'pertemuan', 'peserta', 'absen'));
    }

    public function update(Request $request, Pertemuan $pertemuan)
    {
        $kelas = $pertemuan->kelas;
        $this->pastikanKelasMilikDosen($request, $kelas);

        $request->validate([
            'status'   => ['required', 'array'],
            'status.*' => ['in:hadir,izin,sakit,alpa'],
            'materi'   => ['nullable', 'string', 'max:255'],
        ]);

        $idValid = $kelas->krs()->where('status', 'disetujui')->pluck('mahasiswa_id')->all();

        foreach ($request->input('status') as $mahasiswaId => $status) {
            if (! in_array((int) $mahasiswaId, $idValid, true)) {
                continue;
            }
            Absensi::updateOrCreate(
                ['pertemuan_id' => $pertemuan->id, 'mahasiswa_id' => (int) $mahasiswaId],
                ['status' => $status]
            );
        }

        $pertemuan->update(['materi' => $request->input('materi')]);

        return back()->with('status', 'Kehadiran tersimpan.');
    }
}
