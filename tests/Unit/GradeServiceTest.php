<?php

namespace Tests\Unit;

use App\http\Services\GradeService;
use App\Services\KrsService;
use PHPUnit\Framework\TestCase;

class GradeServiceTest extends TestCase
{
    private function svc(bool $terbaik = true): GradeService
    {
        return new GradeService(GradeService::DEFAULT_WEIGHTS, GradeService::DEFAULT_SCALE, $terbaik);
    }

    public function test_nilai_akhir_memakai_bobot(): void
    {
        // 80*0.3 + 70*0.3 + 90*0.4 = 24 + 21 + 36 = 81
        $this->assertSame(81.0, $this->svc()->nilaiAkhir(80, 70, 90));
    }

    public function test_nilai_akhir_null_bila_komponen_belum_lengkap(): void
    {
        $this->assertNull($this->svc()->nilaiAkhir(80, null, 90));
    }

    public function test_konversi_huruf_pada_batas(): void
    {
        $g = $this->svc();
        $this->assertSame(['huruf' => 'A', 'bobot' => 4.0], $g->konversi(85));
        $this->assertSame('A-', $g->konversi(84.99)['huruf']);
        $this->assertSame('E', $g->konversi(0)['huruf']);
        $this->assertNull($g->konversi(null));
    }

    public function test_ip_semester(): void
    {
        $rows = [['sks' => 3, 'bobot' => 4.0], ['sks' => 3, 'bobot' => 3.0], ['sks' => 2, 'bobot' => 2.0]];
        $r = $this->svc()->ringkas($rows);
        $this->assertSame(8, $r['sks']);
        $this->assertSame(25.0, $r['mutu']);
        $this->assertSame(3.13, $r['ip']); // (12 + 9 + 4) / 8 = 3.125
    }

    public function test_ipk_memakai_nilai_terbaik_untuk_mk_diulang(): void
    {
        $rows = [
            ['mk_id' => 1, 'sks' => 3, 'bobot' => 1.0],
            ['mk_id' => 1, 'sks' => 3, 'bobot' => 3.0], // diulang, lebih baik
            ['mk_id' => 2, 'sks' => 3, 'bobot' => 4.0],
        ];
        $this->assertSame(3.5, $this->svc(true)->ringkasKumulatif($rows)['ip']);   // (9+12)/6
        $this->assertSame(2.67, $this->svc(false)->ringkasKumulatif($rows)['ip']); // (3+9+12)/9
    }

    public function test_sks_lulus_tidak_menghitung_nilai_e(): void
    {
        $r = $this->svc()->ringkas([['sks' => 3, 'bobot' => 0.0], ['sks' => 2, 'bobot' => 2.0]]);
        $this->assertSame(5, $r['sks']);
        $this->assertSame(2, $r['sks_lulus']);
    }

    public function test_deteksi_bentrok_jadwal(): void
    {
        $a = [['hari' => 'Senin', 'jam_mulai' => '08:00:00', 'jam_selesai' => '10:00:00']];
        $this->assertTrue(KrsService::jadwalBentrok($a, [['hari' => 'Senin', 'jam_mulai' => '09:30', 'jam_selesai' => '11:00']]));
        $this->assertFalse(KrsService::jadwalBentrok($a, [['hari' => 'Senin', 'jam_mulai' => '10:00', 'jam_selesai' => '12:00']]));
        $this->assertFalse(KrsService::jadwalBentrok($a, [['hari' => 'Selasa', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']]));
    }
}
