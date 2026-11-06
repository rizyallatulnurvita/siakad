<?php

namespace App\Http\Controllers\Admin;

use App\Models\Krs;
use App\Models\TahunAkademik;
use App\Services\KrsService;
use DomainException;
use Illuminate\Http\Request;

class KrsController extends CrudController
{
    protected function config(): array
    {
        $ta = TahunAkademik::orderByDesc('tanggal_mulai')->get()->pluck('label', 'id')->all();

        return [
            'model' => Krs::class, 'title' => 'KRS', 'route' => 'admin.krs', 'icon' => 'bi-card-checklist', 'readonly' => true,
            'with' => ['mahasiswa.programStudi', 'tahunAkademik'],
            'search' => ['mahasiswa.nim', 'mahasiswa.nama'], 'order' => ['updated_at', 'desc'],
            'filters' => [
                ['name' => 'tahun_akademik_id', 'label' => 'Tahun Akademik', 'options' => $ta],
                ['name' => 'status', 'label' => 'Status', 'options' => ['draft' => 'Draft', 'diajukan' => 'Diajukan', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak']],
            ],
            'columns' => [
                ['label' => 'NIM', 'key' => 'mahasiswa.nim'],
                ['label' => 'Nama', 'key' => 'mahasiswa.nama'],
                ['label' => 'Program Studi', 'key' => 'mahasiswa.programStudi.nama'],
                ['label' => 'Semester', 'key' => 'tahunAkademik.label'],
                ['label' => 'SKS', 'key' => 'total_sks'],
                ['label' => 'Status', 'key' => 'status', 'badge' => true],
                ['label' => 'Diajukan', 'value' => fn ($r) => $r->diajukan_at?->format('d/m/Y H:i') ?? '-'],
            ],
            'row_actions' => [[
                'label' => 'Tinjau', 'icon' => 'bi-search', 'class' => 'btn-outline-primary', 'method' => 'GET',
                'url' => fn ($r) => route('admin.krs.show', $r->id),
            ]],
        ];
    }

    public function show($id)
    {
        $krs = Krs::with([
            'mahasiswa.programStudi', 'tahunAkademik', 'pemroses',
            'details.kelas.mataKuliah', 'details.kelas.dosen', 'details.kelas.jadwal',
        ])->findOrFail($id);

        $svc = app(KrsService::class);
        $terisi = $krs->details->mapWithKeys(fn ($d) => [$d->kelas_kuliah_id => $svc->terisi($d->kelas_kuliah_id)]);

        return view('admin.krs.show', ['krs' => $krs, 'terisi' => $terisi]);
    }

    public function setujui(Request $request, Krs $krs, KrsService $svc)
    {
        $data = $request->validate(['catatan' => ['nullable', 'string', 'max:500']]);
        try {
            $svc->setujui($krs, $request->user(), $data['catatan'] ?? null);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.krs.index')->with('success', 'KRS berhasil disetujui.');
    }

    public function tolak(Request $request, Krs $krs, KrsService $svc)
    {
        $data = $request->validate(['catatan' => ['required', 'string', 'max:500']], [
            'catatan.required' => 'Catatan penolakan wajib diisi.',
        ]);
        try {
            $svc->tolak($krs, $request->user(), $data['catatan']);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.krs.index')->with('success', 'KRS ditolak dan dikembalikan ke mahasiswa.');
    }
}
