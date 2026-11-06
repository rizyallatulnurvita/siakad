<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\User;

trait SyncsUserAccount
{
    /** Membuat/memperbarui akun login untuk profil mahasiswa/dosen. Password di-hash oleh cast model. */
    protected function syncUser(?User $user, array $data, string $role): User
    {
        $attrs = ['name' => $data['nama'], 'email' => $data['email']];
        if (!empty($data['password'])) {
            $attrs['password'] = $data['password'];
        }

        if ($user) {
            $user->update($attrs);

            return $user;
        }

        return User::create($attrs + ['role' => $role, 'is_active' => true]);
    }
}
