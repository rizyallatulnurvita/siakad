<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SyncsUserAccount;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class DosenController extends CrudController
{
    use SyncsUserAccount;

    protected function config(): array
    {
        $prodi = ProgramStudi::orderBy('nama')->pluck('nama', 'id')->all();

        return [
            'model' => Dosen::class, 'title' => 'Dosen', 'route' => 'admin.dosen', 'icon' => 'bi-person-workspace',
            'with' => ['programStudi', 'user'], 'search' => ['nidn', 'nama', 'email'], 'order' => ['nama', 'asc'],
            'filters' => [
                ['name' => 'program_studi_id', 'label' => 'Program Studi', 'options' => $prodi],
                ['name' => 'status', 'label' => 'Status', 'options' => Dosen::STATUS],
            ],
            'columns' => [
                ['label' => 'NIDN', 'key' => 'nidn'],
                ['label' => 'Nama', 'key' => 'nama'],
                ['label' => 'Email', 'key' => 'email'],
                ['label' => 'Program Studi', 'key' => 'programStudi.nama'],
                ['label' => 'Status', 'key' => 'status', 'badge' => true],
            ],
            'fields' => [
                ['name' => 'nidn', 'label' => 'NIDN', 'type' => 'text', 'required' => true],
                ['name' => 'nama', 'label' => 'Nama Lengkap', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'Email (untuk login)', 'type' => 'email', 'required' => true],
                ['name' => 'program_studi_id', 'label' => 'Program Studi', 'type' => 'select', 'options' => $prodi, 'required' => true],
                ['name' => 'telepon', 'label' => 'Nomor Telepon', 'type' => 'text'],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => Dosen::STATUS, 'required' => true],
                ['name' => 'password', 'label' => 'Kata Sandi', 'type' => 'password', 'required' => 'create',
                    'help' => 'Saat edit, kosongkan jika tidak ingin mengubah (atau isi untuk reset kata sandi).'],
                ['name' => 'alamat', 'label' => 'Alamat', 'type' => 'textarea', 'col' => 12],
            ],
        ];
    }

    protected function rules(?Model $model = null): array
    {
        return [
            'nidn' => ['required', 'alpha_num', 'max:20', Rule::unique('dosen', 'nidn')->ignore($model?->id)],
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($model?->user_id)],
            'program_studi_id' => ['required', 'exists:program_studi,id'],
            'telepon' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in(array_keys(Dosen::STATUS))],
            'password' => [$model ? 'nullable' : 'required', Password::min(8)->letters()->numbers()],
            'alamat' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function beforeSave(array $data, ?Model $model, Request $request): array
    {
        $user = $this->syncUser($model?->user, $data, 'dosen');
        $data['user_id'] = $user->id;
        unset($data['password']);

        return $data;
    }

    protected function afterDelete(Model $model): void
    {
        User::whereKey($model->user_id)->where('role', 'dosen')->delete();
    }
}
