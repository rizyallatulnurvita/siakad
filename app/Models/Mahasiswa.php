<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswa';
    protected $fillable = ['user_id', 'prodi_id', 'dosen_wali_id', 'nim', 'nama', 'angkatan', 'status', 'no_hp'];

    public function user() { return $this->belongsTo(User::class); }
    public function prodi() { return $this->belongsTo(Prodi::class); }
    public function dosenWali() { return $this->belongsTo(Dosen::class, 'dosen_wali_id'); }
    public function krs() { return $this->hasMany(Krs::class); }
}
