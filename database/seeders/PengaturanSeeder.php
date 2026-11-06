<?php

namespace Database\Seeders;

use App\Models\Pengaturan;
use App\http\Services\GradeService;
use Illuminate\Database\Seeder;

class PengaturanSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'bobot_nilai' => GradeService::DEFAULT_WEIGHTS,
            'skala_nilai' => GradeService::DEFAULT_SCALE,
            'maks_sks_default' => 20,
            'ips_batas_tinggi' => 3.0,
            'maks_sks_tinggi' => 24,
            'ipk_nilai_terbaik' => true,
        ];
        foreach ($defaults as $k => $v) {
            if (!Pengaturan::where('kunci', $k)->exists()) {
                Pengaturan::put($k, $v);
            }
        }
    }
}
