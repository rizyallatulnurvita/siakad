<?php

namespace App\Http\Controllers\Admin;

use App\Models\ProgramStudi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProgramStudiController extends CrudController
{
    protected function config(): array
    {
        return [
            'model' => ProgramStudi::class, 'title' => 'Program Studi', 'route' => 'admin.program-studi',
            'icon' => 'bi-building', 'search' => ['kode', 'nama'], 'order' => ['kode', 'asc'],
            'columns' => [
                ['label' => 'Kode', 'key' => 'kode'],
                ['label' => 'Nama', 'key' => 'nama'],
                ['label' => 'Jenjang', 'key' => 'jenjang'],
                ['label' => 'Mahasiswa', 'key' => 'mahasiswa_count'],
                ['label' => 'Dosen', 'key' => 'dosen_count'],
                ['label' => 'Mata Kuliah', 'key' => 'mata_kuliah_count'],
            ],
            'fields' => [
                ['name' => 'kode', 'label' => 'Kode Program Studi', 'type' => 'text', 'required' => true],
                ['name' => 'nama', 'label' => 'Nama Program Studi', 'type' => 'text', 'required' => true],
                ['name' => 'jenjang', 'label' => 'Jenjang', 'type' => 'select', 'options' => ProgramStudi::JENJANG, 'required' => true],
                ['name' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'textarea', 'col' => 12],
            ],
        ];
    }

    protected function query(Request $request)
    {
        return ProgramStudi::query()->withCount(['mahasiswa', 'dosen', 'mataKuliah']);
    }

    protected function rules(?Model $model = null): array
    {
        return [
            'kode' => ['required', 'string', 'max:10', Rule::unique('program_studi', 'kode')->ignore($model?->id)],
            'nama' => ['required', 'string', 'max:255'],
            'jenjang' => ['required', Rule::in(array_keys(ProgramStudi::JENJANG))],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
