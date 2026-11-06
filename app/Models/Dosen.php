<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    protected $table = 'dosen';
    protected $fillable = ['user_id', 'prodi_id', 'nidn', 'nama', 'no_hp'];

    public function user() { return $this->belongsTo(User::class); }
    public function prodi() { return $this->belongsTo(Prodi::class); }
    public function kelas() { return $this->hasMany(Kelas::class); }
    public function mahasiswaWali() { return $this->hasMany(Mahasiswa::class, 'dosen_wali_id'); }
}
