<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Krs extends Model
{
    protected $table = 'krs';
    protected $fillable = [
        'mahasiswa_id', 'kelas_id', 'status',
        'nilai_tugas', 'nilai_uts', 'nilai_uas', 'nilai_akhir', 'nilai_huruf',
    ];

    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class); }
    public function kelas() { return $this->belongsTo(Kelas::class); }

    /** Bobot contoh: tugas 30%, UTS 30%, UAS 40% — sesuaikan dengan aturan kampus. */
    public function hitungNilai(): void
    {
        $akhir = ($this->nilai_tugas * 0.3) + ($this->nilai_uts * 0.3) + ($this->nilai_uas * 0.4);
        $this->nilai_akhir = round($akhir, 2);
        $this->nilai_huruf = match (true) {
            $akhir >= 85 => 'A',
            $akhir >= 75 => 'B',
            $akhir >= 65 => 'C',
            $akhir >= 50 => 'D',
            default      => 'E',
        };
    }
}
