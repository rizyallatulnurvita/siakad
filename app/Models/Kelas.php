<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $fillable = [
        'mata_kuliah_id', 'dosen_id', 'nama', 'tahun_ajaran', 'semester',
        'hari', 'jam_mulai', 'jam_selesai', 'ruang', 'kuota',
        'nilai_terkunci', 'dikunci_pada',
    ];

    protected $casts = [
        'nilai_terkunci' => 'boolean',
        'dikunci_pada'   => 'datetime',
    ];

    public function mataKuliah() { return $this->belongsTo(MataKuliah::class); }
    public function dosen() { return $this->belongsTo(Dosen::class); }
    public function krs() { return $this->hasMany(Krs::class); }
    public function pertemuan() { return $this->hasMany(Pertemuan::class); }
}
