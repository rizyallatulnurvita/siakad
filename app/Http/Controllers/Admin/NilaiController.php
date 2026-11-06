<?php

namespace App\Http\Controllers\Admin;

use App\Models\KelasKuliah;
use App\Models\Nilai;

class NilaiController extends CrudController
{
    protected function config(): array
    {
        $kelas = KelasKuliah::with(['mataKuliah', 'tahunAkademik'])->orderByDesc('tahun_akademik_id')->get()
            ->mapWithKeys(fn ($k) => [$k->id => $k->label])->all();
        $fmt = fn ($v) => $v === null ? '-' : rtrim(rtrim(number_format($v, 2, ',', '.'), '0'), ',');

        return [
            'model' => Nilai::class, 'title' => 'Rekap Nilai', 'route' => 'admin.nilai', 'icon' => 'bi-clipboard-data', 'readonly' => true,
            'with' => ['mahasiswa', 'kelas.mataKuliah'],
            'search' => ['mahasiswa.nim', 'mahasiswa.nama'], 'order' => ['id', 'desc'],
            'filters' => [['name' => 'kelas_kuliah_id', 'label' => 'Kelas', 'options' => $kelas]],
            'columns' => [
                ['label' => 'NIM', 'key' => 'mahasiswa.nim'],
                ['label' => 'Nama', 'key' => 'mahasiswa.nama'],
                ['label' => 'Mata Kuliah', 'key' => 'kelas.mataKuliah.nama'],
                ['label' => 'Tugas', 'value' => fn ($r) => $fmt($r->tugas)],
                ['label' => 'UTS', 'value' => fn ($r) => $fmt($r->uts)],
                ['label' => 'UAS', 'value' => fn ($r) => $fmt($r->uas)],
                ['label' => 'Akhir', 'value' => fn ($r) => $fmt($r->nilai_akhir)],
                ['label' => 'Huruf', 'value' => fn ($r) => $r->huruf ?? '-'],
            ],
        ];
    }
}
