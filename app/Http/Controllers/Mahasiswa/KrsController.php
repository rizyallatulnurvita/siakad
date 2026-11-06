<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Admin\Concerns\ResolvesProfile;
use App\Http\Controllers\Controller;
use App\Models\KelasKuliah;
use App\Models\Krs;
use App\Models\TahunAkademik;
use App\Services\KrsService;
use DomainException;
use Illuminate\Http\Request;

class KrsController extends Controller
{
    use ResolvesProfile;

    public function __construct(private KrsService $svc)
    {
    }

    public function index(Request $request)
    {
        $m = $this->mahasiswaSaya();
        $ta = TahunAkademik::aktif();
        $krs = null;
        $kelas = null;
        $maks = null;

        if ($ta) {
            $krs = Krs::with(['details.kelas.mataKuliah', 'details.kelas.dosen', 'details.kelas.jadwal'])
                ->where('mahasiswa_id', $m->id)->where('tahun_akademik_id', $ta->id)->first();
            $maks = $this->svc->maksSks($m, $ta);

            $dipilih = $krs?->details->pluck('kelas_kuliah_id')->all() ?? [];
            $s = trim((string) $request->input('q'));
            $kelas = KelasKuliah::withTerisi()->with(['mataKuliah', 'dosen', 'jadwal'])
                ->where('tahun_akademik_id', $ta->id)
                ->whereHas('mataKuliah', function ($q) use ($m, $s) {
                    $q->where('program_studi_id', $m->program_studi_id);
                    if ($s !== '') {
                        $like = '%' . addcslashes($s, '%_\\') . '%';
                        $q->where(fn ($w) => $w->where('nama', 'like', $like)->orWhere('kode', 'like', $like));
                    }
                })
                ->whereNotIn('id', $dipilih)
                ->orderBy('id')->paginate(8)->withQueryString();
        }

        return view('mahasiswa.krs', [
            'ta' => $ta, 'krs' => $krs, 'kelas' => $kelas, 'maks' => $maks,
            'editable' => !$krs || in_array($krs->status, KrsService::EDITABLE, true),
        ]);
    }

    public function tambah(Request $request)
    {
        $data = $request->validate(['kelas_kuliah_id' => ['required', 'integer', 'exists:kelas_kuliah,id']]);
        try {
            $this->svc->tambah($this->mahasiswaSaya(), (int) $data['kelas_kuliah_id']);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Kelas ditambahkan ke KRS.');
    }

    public function hapus(int $detail)
    {
        try {
            $this->svc->hapus($this->mahasiswaSaya(), $detail);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Kelas dihapus dari KRS.');
    }

    public function ajukan()
    {
        try {
            $this->svc->ajukan($this->mahasiswaSaya());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'KRS berhasil diajukan. Tunggu persetujuan admin.');
    }
}
