<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $fillable = [
        'tagihan_id', 'jumlah', 'tanggal_bayar', 'metode', 'bukti_path', 'catatan',
        'status', 'catatan_admin', 'diverifikasi_oleh', 'diverifikasi_pada',
    ];
    protected $casts = [
        'tanggal_bayar'     => 'date',
        'diverifikasi_pada' => 'datetime',
        'jumlah'            => 'integer',
    ];

    public function tagihan() { return $this->belongsTo(Tagihan::class); }
    public function verifikator() { return $this->belongsTo(User::class, 'diverifikasi_oleh'); }
}
