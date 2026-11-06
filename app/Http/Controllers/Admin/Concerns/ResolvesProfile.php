<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Dosen;
use App\Models\Mahasiswa;

trait ResolvesProfile
{
    protected function mahasiswaSaya(): Mahasiswa
    {
        $m = auth()->user()?->mahasiswa;
        abort_if(!$m, 403, 'Profil mahasiswa untuk akun ini belum dibuat. Hubungi administrator.');

        return $m;
    }

    protected function dosenSaya(): Dosen
    {
        $d = auth()->user()?->dosen;
        abort_if(!$d, 403, 'Profil dosen untuk akun ini belum dibuat. Hubungi administrator.');

        return $d;
    }
}
