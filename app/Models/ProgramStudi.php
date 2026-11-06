<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramStudi extends Model
{
    protected $table = 'program_studi';

    protected $fillable = ['kode', 'nama', 'jenjang', 'deskripsi'];

    public const JENJANG = ['D3' => 'D3', 'D4' => 'D4', 'S1' => 'S1', 'S2' => 'S2', 'S3' => 'S3'];

    public function mahasiswa(): HasMany
    {
        return $this->hasMany(Mahasiswa::class, 'program_studi_id');
    }

    public function dosen(): HasMany
    {
        return $this->hasMany(Dosen::class, 'program_studi_id');
    }

    public function mataKuliah(): HasMany
    {
        return $this->hasMany(MataKuliah::class, 'program_studi_id');
    }
}
