<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends CrudController
{
    protected function config(): array
    {
        return [
            'model' => User::class, 'title' => 'Pengguna', 'route' => 'admin.users', 'icon' => 'bi-people',
            'search' => ['name', 'email'], 'order' => ['name', 'asc'],
            'filters' => [['name' => 'role', 'label' => 'Role', 'options' => User::ROLES]],
            'columns' => [
                ['label' => 'Nama', 'key' => 'name'],
                ['label' => 'Email', 'key' => 'email'],
                ['label' => 'Role', 'key' => 'role', 'badge' => true],
                ['label' => 'Status', 'value' => fn ($r) => $r->is_active ? 'aktif' : 'nonaktif', 'badge' => true],
            ],
            'row_actions' => [[
                'label' => fn ($r) => $r->is_active ? 'Nonaktifkan' : 'Aktifkan',
                'icon' => 'bi-power', 'class' => 'btn-outline-warning', 'method' => 'PATCH',
                'url' => fn ($r) => route('admin.users.toggle', $r->id),
                'if' => fn ($r) => $r->id !== auth()->id(),
                'confirm' => 'Ubah status akun ini?',
            ]],
            'fields' => [
                ['name' => 'name', 'label' => 'Nama', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                ['name' => 'role', 'label' => 'Role', 'type' => 'select', 'options' => User::ROLES, 'required' => true,
                    'help' => 'Role akun yang sudah punya profil mahasiswa/dosen tidak dapat diubah.'],
                ['name' => 'password', 'label' => 'Kata Sandi', 'type' => 'password', 'required' => 'create',
                    'help' => 'Saat edit, isi hanya untuk mereset kata sandi.'],
                ['name' => 'is_active', 'label' => 'Akun aktif', 'type' => 'checkbox', 'default' => true, 'col' => 12],
            ],
        ];
    }

    protected function rules(?Model $model = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($model?->id)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => [$model ? 'nullable' : 'required', Password::min(8)->letters()->numbers()],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function beforeSave(array $data, ?Model $model, Request $request): array
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        if ($model) {
            // Role tidak boleh diubah bila akun terhubung ke profil akademik.
            if ($model->mahasiswa()->exists() || $model->dosen()->exists()) {
                $data['role'] = $model->role;
            }
            // Admin tidak boleh menurunkan/menonaktifkan akunnya sendiri.
            if ($model->id === $request->user()->id) {
                $data['role'] = 'admin';
                $data['is_active'] = true;
            }
        }
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }

    protected function beforeDelete(Model $model): void
    {
        if ($model->id === auth()->id()) {
            throw new DomainException('Anda tidak dapat menghapus akun sendiri.');
        }
        if ($model->mahasiswa()->exists() || $model->dosen()->exists()) {
            throw new DomainException('Akun ini terhubung ke profil mahasiswa/dosen. Hapus melalui menu Mahasiswa atau Dosen.');
        }
    }

    public function toggle($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }
        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', 'Akun ' . $user->name . ($user->is_active ? ' diaktifkan.' : ' dinonaktifkan.'));
    }
}
