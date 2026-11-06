<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prodi extends Model
{
    protected $table = 'prodi';
    protected $fillable = ['kode', 'nama', 'jenjang'];

    public function mahasiswa() { return $this->hasMany(Mahasiswa::class); }
    public function dosen() { return $this->hasMany(Dosen::class); }
    public function mataKuliah() { return $this->hasMany(MataKuliah::class); }
}
