<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAkademik extends Model
{
    protected $table = 'tahun_akademik';

    protected $fillable = [
        'tahun', 'semester', 'tanggal_mulai', 'tanggal_selesai',
        'mulai_krs', 'selesai_krs', 'mulai_nilai', 'selesai_nilai', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'mulai_krs' => 'date',
            'selesai_krs' => 'date',
            'mulai_nilai' => 'date',
            'selesai_nilai' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public static function aktif(): ?self
    {
        return static::where('is_active', true)->first();
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(KelasKuliah::class, 'tahun_akademik_id');
    }

    public function getLabelAttribute(): string
    {
        return $this->tahun . ' ' . ucfirst($this->semester);
    }

    private static function dalamPeriode($mulai, $selesai): bool
    {
        $today = now()->startOfDay();
        if ($mulai && $today->lt($mulai)) {
            return false;
        }
        if ($selesai && $today->gt($selesai)) {
            return false;
        }

        return true;
    }

    /** Jika periode tidak diatur (kosong) dianggap selalu terbuka. */
    public function periodeKrsTerbuka(): bool
    {
        return self::dalamPeriode($this->mulai_krs, $this->selesai_krs);
    }

    public function periodeNilaiTerbuka(): bool
    {
        return self::dalamPeriode($this->mulai_nilai, $this->selesai_nilai);
    }
}
