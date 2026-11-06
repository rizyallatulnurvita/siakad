<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class KrsDetail extends Model
{
    protected $table = 'krs_detail';

    protected $fillable = ['krs_id', 'kelas_kuliah_id'];

    public function krs(): BelongsTo
    {
        return $this->belongsTo(Krs::class, 'krs_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'kelas_kuliah_id');
    }

    public function nilai(): HasOne
    {
        return $this->hasOne(Nilai::class, 'krs_detail_id');
    }
}
