<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\JadwalKuliah;
use App\Models\KelasKuliah;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Nilai;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use App\Models\User;
use App\http\Services\GradeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * OPSIONAL — hanya untuk pengujian: php artisan db:seed --class=DemoSeeder
 * Password diambil dari DEMO_PASSWORD di .env, atau dibuat acak dan ditampilkan sekali.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $pw = env('DEMO_PASSWORD') ?: Str::random(12);

        DB::transaction(function () use ($pw) {
            $ti = ProgramStudi::firstOrCreate(['kode' => 'TI'], ['nama' => 'Teknik Informatika', 'jenjang' => 'S1']);
            $si = ProgramStudi::firstOrCreate(['kode' => 'SI'], ['nama' => 'Sistem Informasi', 'jenjang' => 'S1']);

            $lalu = TahunAkademik::firstOrCreate(['tahun' => '2025/2026', 'semester' => 'genap'], [
                'tanggal_mulai' => '2026-02-01', 'tanggal_selesai' => '2026-07-31', 'is_active' => false,
            ]);
            TahunAkademik::query()->update(['is_active' => false]);
            $kini = TahunAkademik::firstOrCreate(['tahun' => '2026/2027', 'semester' => 'ganjil'], [
                'tanggal_mulai' => '2026-08-15', 'tanggal_selesai' => '2027-01-31',
            ]);
            $kini->update(['is_active' => true]);

            $dosen = [];
            foreach ([1 => 'Dr. Budi Santoso', 2 => 'Siti Rahmawati, M.Kom'] as $i => $nama) {
                $u = User::firstOrCreate(['email' => "dosen$i@demo.test"], ['name' => $nama, 'password' => $pw, 'role' => 'dosen', 'is_active' => true]);
                $dosen[$i] = Dosen::firstOrCreate(['user_id' => $u->id], [
                    'nidn' => '00110' . $i, 'nama' => $nama, 'email' => $u->email,
                    'program_studi_id' => $ti->id, 'status' => 'aktif',
                ]);
            }

            $mk = [];
            foreach ([['TI101', 'Algoritma dan Pemrograman', 3, 1], ['TI102', 'Basis Data', 3, 1], ['TI103', 'Matematika Diskrit', 2, 1],
                      ['TI201', 'Pemrograman Web', 3, 3], ['TI202', 'Jaringan Komputer', 3, 3], ['TI203', 'Rekayasa Perangkat Lunak', 3, 3]] as [$k, $n, $s, $sem]) {
                $mk[$k] = MataKuliah::firstOrCreate(['kode' => $k], ['nama' => $n, 'sks' => $s, 'program_studi_id' => $ti->id, 'semester' => $sem]);
            }

            $mahasiswa = [];
            foreach ([1 => 'Andi Pratama', 2 => 'Dewi Lestari', 3 => 'Rizky Maulana'] as $i => $nama) {
                $u = User::firstOrCreate(['email' => "mhs$i@demo.test"], ['name' => $nama, 'password' => $pw, 'role' => 'mahasiswa', 'is_active' => true]);
                $mahasiswa[$i] = Mahasiswa::firstOrCreate(['user_id' => $u->id], [
                    'nim' => '2025000' . $i, 'nama' => $nama, 'email' => $u->email, 'program_studi_id' => $ti->id,
                    'angkatan' => 2025, 'jenis_kelamin' => $i === 2 ? 'P' : 'L', 'status' => 'aktif',
                ]);
            }

            // Semester lalu (sudah bernilai) agar IP/IPK bisa dicoba
            $grade = new GradeService();
            $lampau = [];
            foreach (['TI101' => 1, 'TI102' => 2, 'TI103' => 1] as $kode => $d) {
                $lampau[$kode] = KelasKuliah::firstOrCreate(
                    ['mata_kuliah_id' => $mk[$kode]->id, 'tahun_akademik_id' => $lalu->id, 'nama' => 'A'],
                    ['dosen_id' => $dosen[$d]->id, 'kapasitas' => 40]
                );
            }
            $skor = [1 => [[80, 85, 90], [70, 75, 72], [60, 65, 70]], 2 => [[90, 92, 95], [85, 80, 88], [78, 80, 82]]];
            foreach ($skor as $mi => $list) {
                $m = $mahasiswa[$mi];
                $krs = Krs::firstOrCreate(['mahasiswa_id' => $m->id, 'tahun_akademik_id' => $lalu->id], ['status' => 'disetujui', 'total_sks' => 8]);
                foreach (array_values($lampau) as $idx => $kelas) {
                    $detail = $krs->details()->firstOrCreate(['kelas_kuliah_id' => $kelas->id]);
                    [$t, $u1, $u2] = $list[$idx];
                    $akhir = $grade->nilaiAkhir($t, $u1, $u2);
                    $kv = $grade->konversi($akhir);
                    Nilai::updateOrCreate(['krs_detail_id' => $detail->id], [
                        'kelas_kuliah_id' => $kelas->id, 'mahasiswa_id' => $m->id,
                        'tugas' => $t, 'uts' => $u1, 'uas' => $u2,
                        'nilai_akhir' => $akhir, 'huruf' => $kv['huruf'], 'bobot' => $kv['bobot'],
                    ]);
                }
            }

            // Semester aktif: kelas + jadwal (tanpa bentrok)
            $slot = [
                ['TI201', 1, 'Senin', '08:00', '10:30', 'Lab 1'],
                ['TI202', 2, 'Selasa', '08:00', '10:30', 'R-201'],
                ['TI203', 1, 'Rabu', '13:00', '15:30', 'R-202'],
            ];
            foreach ($slot as [$kode, $d, $hari, $a, $b, $ruang]) {
                $k = KelasKuliah::firstOrCreate(
                    ['mata_kuliah_id' => $mk[$kode]->id, 'tahun_akademik_id' => $kini->id, 'nama' => 'A'],
                    ['dosen_id' => $dosen[$d]->id, 'kapasitas' => 40]
                );
                JadwalKuliah::firstOrCreate(['kelas_kuliah_id' => $k->id, 'hari' => $hari], ['jam_mulai' => $a, 'jam_selesai' => $b, 'ruang' => $ruang]);
            }
        });

        $this->command?->info('Data demo dibuat. Akun: dosen1@demo.test, dosen2@demo.test, mhs1@demo.test, mhs2@demo.test, mhs3@demo.test');
        $this->command?->warn('Password demo: ' . $pw . '  (atur tetap lewat DEMO_PASSWORD di .env)');
    }
}
