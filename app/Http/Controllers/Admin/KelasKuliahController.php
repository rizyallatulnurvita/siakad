<?php

namespace App\Http\Controllers\Admin;

use App\Models\Dosen;
use App\Models\KelasKuliah;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KelasKuliahController extends CrudController
{
    protected function config(): array
    {
        $mk = MataKuliah::orderBy('kode')->get()->mapWithKeys(fn ($m) => [$m->id => "{$m->kode} - {$m->nama} ({$m->sks} SKS)"])->all();
        $dosen = Dosen::where('status', 'aktif')->orderBy('nama')->get()->mapWithKeys(fn ($d) => [$d->id => "{$d->nama} ({$d->nidn})"])->all();
        $ta = TahunAkademik::orderByDesc('tanggal_mulai')->get()->pluck('label', 'id')->all();

        return [
            'model' => KelasKuliah::class, 'title' => 'Kelas Kuliah', 'route' => 'admin.kelas-kuliah', 'icon' => 'bi-easel',
            'with' => ['mataKuliah', 'dosen', 'tahunAkademik'], 'order' => ['id', 'desc'],
            'search' => ['nama', 'mataKuliah.nama', 'mataKuliah.kode', 'dosen.nama'],
            'filters' => [['name' => 'tahun_akademik_id', 'label' => 'Tahun Akademik', 'options' => $ta]],
            'columns' => [
                ['label' => 'Mata Kuliah', 'value' => fn ($r) => $r->mataKuliah->kode . ' - ' . $r->mataKuliah->nama],
                ['label' => 'Kelas', 'key' => 'nama'],
                ['label' => 'Dosen Pengampu', 'key' => 'dosen.nama'],
                ['label' => 'Semester', 'key' => 'tahunAkademik.label'],
                ['label' => 'Terisi', 'value' => fn ($r) => ($r->terisi ?? 0) . ' / ' . $r->kapasitas],
            ],
            'row_actions' => [[
                'label' => 'Nilai & Peserta', 'icon' => 'bi-people', 'class' => 'btn-outline-secondary', 'method' => 'GET',
                'url' => fn ($r) => route('admin.nilai.index', ['kelas_kuliah_id' => $r->id]),
            ]],
            'fields' => [
                ['name' => 'mata_kuliah_id', 'label' => 'Mata Kuliah', 'type' => 'select', 'options' => $mk, 'required' => true],
                ['name' => 'dosen_id', 'label' => 'Dosen Pengampu', 'type' => 'select', 'options' => $dosen, 'required' => true],
                ['name' => 'tahun_akademik_id', 'label' => 'Tahun Akademik', 'type' => 'select', 'options' => $ta, 'required' => true],
                ['name' => 'nama', 'label' => 'Nama Kelas (mis. A)', 'type' => 'text', 'required' => true],
                ['name' => 'kapasitas', 'label' => 'Kapasitas', 'type' => 'number', 'required' => true, 'default' => 40, 'attrs' => 'min=1 max=500'],
            ],
        ];
    }

    protected function query(Request $request)
    {
        return KelasKuliah::query()->with(['mataKuliah', 'dosen', 'tahunAkademik'])->withTerisi();
    }

    protected function rules(?Model $model = null): array
    {
        return [
            'mata_kuliah_id' => ['required', 'exists:mata_kuliah,id'],
            'dosen_id' => ['required', 'exists:dosen,id'],
            'tahun_akademik_id' => ['required', 'exists:tahun_akademik,id'],
            'nama' => ['required', 'string', 'max:10', Rule::unique('kelas_kuliah', 'nama')
                ->where('mata_kuliah_id', request('mata_kuliah_id'))
                ->where('tahun_akademik_id', request('tahun_akademik_id'))
                ->ignore($model?->id)],
            'kapasitas' => ['required', 'integer', 'between:1,500'],
        ];
    }

    protected function extraValidate(array $data, ?Model $model): void
    {
        if ($model && (int) $data['kapasitas'] < (int) ($model->terisi ?? 0)) {
            throw ValidationException::withMessages([
                'kapasitas' => 'Kapasitas tidak boleh lebih kecil dari jumlah mahasiswa yang sudah terdaftar (' . $model->terisi . ').',
            ]);
        }
    }
}
