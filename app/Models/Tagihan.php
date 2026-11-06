<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    protected $table = 'tagihan';
    protected $fillable = [
        'mahasiswa_id', 'jenis', 'keterangan', 'tahun_ajaran', 'semester',
        'jumlah', 'jatuh_tempo', 'status',
    ];
    protected $casts = ['jatuh_tempo' => 'date', 'jumlah' => 'integer'];

    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class); }
    public function pembayaran() { return $this->hasMany(Pembayaran::class); }

    /** Total pembayaran yang sudah diterima admin. */
    public function terbayar(): int
    {
        return (int) $this->pembayaran()->where('status', 'diterima')->sum('jumlah');
    }

    /** Total pembayaran yang masih menunggu verifikasi. */
    public function menunggu(): int
    {
        return (int) $this->pembayaran()->where('status', 'menunggu')->sum('jumlah');
    }

    public function sisa(): int
    {
        return max(0, $this->jumlah - $this->terbayar());
    }

    /** Hitung ulang status tagihan dari pembayaran yang diterima. */
    public function perbaruiStatus(): void
    {
        $bayar = $this->terbayar();

        $this->status = $bayar >= $this->jumlah ? 'lunas' : ($bayar > 0 ? 'sebagian' : 'belum_bayar');
        $this->save();
    }
}
