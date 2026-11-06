<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalKuliah extends Model
{
    protected $table = 'jadwal_kuliah';

    protected $fillable = ['kelas_kuliah_id', 'hari', 'jam_mulai', 'jam_selesai', 'ruang'];

    public const HARI = [
        'Senin' => 'Senin', 'Selasa' => 'Selasa', 'Rabu' => 'Rabu',
        'Kamis' => 'Kamis', 'Jumat' => 'Jumat', 'Sabtu' => 'Sabtu', 'Minggu' => 'Minggu',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'kelas_kuliah_id');
    }

    public function getJamAttribute(): string
    {
        return substr((string) $this->jam_mulai, 0, 5) . ' - ' . substr((string) $this->jam_selesai, 0, 5);
    }
}
