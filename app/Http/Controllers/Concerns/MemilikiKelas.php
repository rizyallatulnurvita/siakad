<?php

namespace App\Http\Controllers\Concerns;

use App\Models\{Dosen, Kelas};
use Illuminate\Http\Request;

trait MemilikiKelas
{
    /** Dosen hanya boleh mengakses kelas yang ia ampu. */
    protected function pastikanKelasMilikDosen(Request $request, Kelas $kelas): void
    {
        $dosenId = Dosen::where('user_id', $request->user()->id)->value('id');

        abort_unless($dosenId && $kelas->dosen_id === $dosenId, 403, 'Bukan kelas Anda.');
    }
}
