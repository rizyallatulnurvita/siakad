<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nilai extends Model
{
    protected $table = 'nilai';

    protected $fillable = [
        'krs_detail_id', 'kelas_kuliah_id', 'mahasiswa_id',
        'tugas', 'uts', 'uas', 'nilai_akhir', 'huruf', 'bobot', 'diinput_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tugas' => 'float', 'uts' => 'float', 'uas' => 'float',
            'nilai_akhir' => 'float', 'bobot' => 'float',
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'kelas_kuliah_id');
    }

    public function krsDetail(): BelongsTo
    {
        return $this->belongsTo(KrsDetail::class, 'krs_detail_id');
    }
}
