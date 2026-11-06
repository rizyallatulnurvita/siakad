<?php

namespace Database\Seeders;

use App\Models\{Mahasiswa, Pembayaran, Tagihan};
use Illuminate\Database\Seeder;

/**
 * Data dummy tagihan UKT + pembayaran (lunas, sebagian, menunggu, ditolak, belum bayar).
 * Aman dijalankan ulang. Jalankan: php artisan db:seed --class=TagihanDummySeeder
 */
class TagihanDummySeeder extends Seeder
{
    public function run(): void
    {
        $nominal = [2500000, 3500000, 4500000];

        foreach (Mahasiswa::orderBy('id')->get() as $m) {
            $tagihan = Tagihan::firstOrCreate(
                ['mahasiswa_id' => $m->id, 'jenis' => 'UKT', 'tahun_ajaran' => '2026/2027', 'semester' => 'ganjil'],
                ['jumlah' => $nominal[$m->id % 3], 'jatuh_tempo' => '2026-09-30', 'keterangan' => 'UKT semester ganjil', 'status' => 'belum_bayar']
            );

            if (! $tagihan->wasRecentlyCreated) {
                continue;
            }

            $tgl = '2026-09-' . str_pad((string) (($m->id % 25) + 1), 2, '0', STR_PAD_LEFT);

            switch ($m->id % 5) {
                case 0: // lunas
                    $this->bayar($tagihan, $tagihan->jumlah, $tgl, 'diterima');
                    break;
                case 1: // sebagian (cicilan pertama diterima)
                    $this->bayar($tagihan, intdiv($tagihan->jumlah, 2), $tgl, 'diterima');
                    break;
                case 2: // menunggu verifikasi
                    $this->bayar($tagihan, $tagihan->jumlah, '2026-10-12', 'menunggu');
                    break;
                case 3: // belum bayar
                    break;
                default: // pernah ditolak
                    $this->bayar($tagihan, $tagihan->jumlah, $tgl, 'ditolak', 'Bukti tidak terbaca, silakan unggah ulang.');
            }

            $tagihan->perbaruiStatus();
        }
    }

    private function bayar(Tagihan $tagihan, int $jumlah, string $tanggal, string $status, ?string $catatanAdmin = null): void
    {
        Pembayaran::create([
            'tagihan_id'        => $tagihan->id,
            'jumlah'            => $jumlah,
            'tanggal_bayar'     => $tanggal,
            'metode'            => 'Transfer Bank',
            'status'            => $status,
            'catatan_admin'     => $catatanAdmin,
            'diverifikasi_pada' => $status === 'menunggu' ? null : now(),
        ]);
    }
}
