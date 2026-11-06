<?php

namespace App\Http\Controllers\Admin;

use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class MataKuliahController extends CrudController
{
    protected function config(): array
    {
        $prodi = ProgramStudi::orderBy('nama')->pluck('nama', 'id')->all();

        return [
            'model' => MataKuliah::class, 'title' => 'Mata Kuliah', 'route' => 'admin.mata-kuliah',
            'icon' => 'bi-journal-bookmark', 'with' => ['programStudi'],
            'search' => ['kode', 'nama'], 'order' => ['kode', 'asc'],
            'filters' => [['name' => 'program_studi_id', 'label' => 'Program Studi', 'options' => $prodi]],
            'columns' => [
                ['label' => 'Kode', 'key' => 'kode'],
                ['label' => 'Nama', 'key' => 'nama'],
                ['label' => 'SKS', 'key' => 'sks'],
                ['label' => 'Program Studi', 'key' => 'programStudi.nama'],
                ['label' => 'Semester', 'key' => 'semester'],
            ],
            'fields' => [
                ['name' => 'kode', 'label' => 'Kode Mata Kuliah', 'type' => 'text', 'required' => true],
                ['name' => 'nama', 'label' => 'Nama Mata Kuliah', 'type' => 'text', 'required' => true],
                ['name' => 'sks', 'label' => 'SKS', 'type' => 'number', 'required' => true, 'attrs' => 'min=1 max=6'],
                ['name' => 'program_studi_id', 'label' => 'Program Studi', 'type' => 'select', 'options' => $prodi, 'required' => true],
                ['name' => 'semester', 'label' => 'Semester Rekomendasi', 'type' => 'number', 'required' => true, 'attrs' => 'min=1 max=14'],
                ['name' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'textarea', 'col' => 12],
            ],
        ];
    }

    protected function rules(?Model $model = null): array
    {
        return [
            'kode' => ['required', 'string', 'max:15', Rule::unique('mata_kuliah', 'kode')->ignore($model?->id)],
            'nama' => ['required', 'string', 'max:255'],
            'sks' => ['required', 'integer', 'between:1,6'],
            'program_studi_id' => ['required', 'exists:program_studi,id'],
            'semester' => ['required', 'integer', 'between:1,14'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
