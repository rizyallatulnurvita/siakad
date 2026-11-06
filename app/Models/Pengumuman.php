<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengumuman extends Model
{
    protected $table = 'pengumuman';

    protected $fillable = ['judul', 'isi', 'target', 'tanggal_mulai', 'tanggal_selesai', 'user_id'];

    public const TARGET = ['semua' => 'Semua', 'dosen' => 'Dosen', 'mahasiswa' => 'Mahasiswa'];

    protected function casts(): array
    {
        return ['tanggal_mulai' => 'date', 'tanggal_selesai' => 'date'];
    }

    public function penulis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeAktif(Builder $q): Builder
    {
        $today = now()->toDateString();

        return $q->whereDate('tanggal_mulai', '<=', $today)
            ->where(fn ($w) => $w->whereNull('tanggal_selesai')->orWhereDate('tanggal_selesai', '>=', $today));
    }

    public function scopeUntuk(Builder $q, string $role): Builder
    {
        return $role === 'admin' ? $q : $q->whereIn('target', ['semua', $role]);
    }
}
