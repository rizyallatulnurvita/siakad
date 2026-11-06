<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SyncsUserAccount;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class MahasiswaController extends CrudController
{
    use SyncsUserAccount;

    protected function config(): array
    {
        $prodi = ProgramStudi::orderBy('nama')->pluck('nama', 'id')->all();

        return [
            'model' => Mahasiswa::class, 'title' => 'Mahasiswa', 'route' => 'admin.mahasiswa', 'icon' => 'bi-mortarboard',
            'with' => ['programStudi', 'user'], 'search' => ['nim', 'nama', 'email'], 'order' => ['nim', 'asc'],
            'filters' => [
                ['name' => 'program_studi_id', 'label' => 'Program Studi', 'options' => $prodi],
                ['name' => 'angkatan', 'label' => 'Angkatan', 'options' => Mahasiswa::query()->distinct()->orderByDesc('angkatan')->pluck('angkatan', 'angkatan')->all()],
                ['name' => 'status', 'label' => 'Status', 'options' => Mahasiswa::STATUS],
            ],
            'columns' => [
                ['label' => 'NIM', 'key' => 'nim'],
                ['label' => 'Nama', 'key' => 'nama'],
                ['label' => 'Program Studi', 'key' => 'programStudi.nama'],
                ['label' => 'Angkatan', 'key' => 'angkatan'],
                ['label' => 'Status', 'key' => 'status', 'badge' => true],
            ],
            'fields' => [
                ['name' => 'nim', 'label' => 'NIM', 'type' => 'text', 'required' => true],
                ['name' => 'nama', 'label' => 'Nama Lengkap', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'Email (untuk login)', 'type' => 'email', 'required' => true],
                ['name' => 'program_studi_id', 'label' => 'Program Studi', 'type' => 'select', 'options' => $prodi, 'required' => true],
                ['name' => 'angkatan', 'label' => 'Angkatan', 'type' => 'number', 'required' => true, 'attrs' => 'min=2000 max=2100'],
                ['name' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'type' => 'select', 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan'], 'required' => true],
                ['name' => 'telepon', 'label' => 'Nomor Telepon', 'type' => 'text'],
                ['name' => 'status', 'label' => 'Status Mahasiswa', 'type' => 'select', 'options' => Mahasiswa::STATUS, 'required' => true],
                ['name' => 'password', 'label' => 'Kata Sandi', 'type' => 'password', 'required' => 'create',
                    'help' => 'Saat edit, kosongkan jika tidak ingin mengubah (atau isi untuk reset kata sandi).'],
                ['name' => 'alamat', 'label' => 'Alamat', 'type' => 'textarea', 'col' => 12],
            ],
        ];
    }

    protected function rules(?Model $model = null): array
    {
        return [
            'nim' => ['required', 'alpha_num', 'max:20', Rule::unique('mahasiswa', 'nim')->ignore($model?->id)],
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($model?->user_id)],
            'program_studi_id' => ['required', 'exists:program_studi,id'],
            'angkatan' => ['required', 'integer', 'between:2000,2100'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'telepon' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in(array_keys(Mahasiswa::STATUS))],
            'password' => [$model ? 'nullable' : 'required', Password::min(8)->letters()->numbers()],
            'alamat' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function beforeSave(array $data, ?Model $model, Request $request): array
    {
        $user = $this->syncUser($model?->user, $data, 'mahasiswa');
        $data['user_id'] = $user->id;
        unset($data['password']);

        return $data;
    }

    protected function afterDelete(Model $model): void
    {
        User::whereKey($model->user_id)->where('role', 'mahasiswa')->delete();
    }
}
