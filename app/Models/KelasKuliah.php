<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KelasKuliah extends Model
{
    protected $table = 'kelas_kuliah';

    protected $fillable = ['mata_kuliah_id', 'dosen_id', 'tahun_akademik_id', 'nama', 'kapasitas'];

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalKuliah::class, 'kelas_kuliah_id');
    }

    public function krsDetail(): HasMany
    {
        return $this->hasMany(KrsDetail::class, 'kelas_kuliah_id');
    }

    /** Menambahkan kolom "terisi" = jumlah KRS berstatus diajukan/disetujui. */
    public function scopeWithTerisi(Builder $q): Builder
    {
        return $q->withCount(['krsDetail as terisi' => function ($d) {
            $d->whereHas('krs', fn ($k) => $k->whereIn('status', ['diajukan', 'disetujui']));
        }]);
    }

    public function getLabelAttribute(): string
    {
        $mk = $this->mataKuliah;
        $ta = $this->tahunAkademik;

        return trim(($mk?->kode ?? '?') . ' - ' . ($mk?->nama ?? '?') . ' (' . $this->nama . ')'
            . ($ta ? ' — ' . $ta->label : ''));
    }
}
