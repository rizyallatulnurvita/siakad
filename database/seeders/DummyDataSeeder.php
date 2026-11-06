<?php

namespace Database\Seeders;

use App\Models\{Absensi, Dosen, Kelas, Krs, Mahasiswa, MataKuliah, Pertemuan, Prodi, User};
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data dummy: 3 prodi, 12 mata kuliah/kelas, 6 dosen, 36 mahasiswa,
 * KRS, nilai, 8 pertemuan per kelas + absensi.
 * Aman dijalankan ulang (memakai firstOrCreate).
 * Jalankan: php artisan db:seed --class=DummyDataSeeder
 */
class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake('id_ID');

        // ---- Prodi ----
        $prodi = collect([['TI', 'Teknik Informatika'], ['SI', 'Sistem Informasi'], ['MI', 'Manajemen Informatika']])
            ->map(fn ($p) => Prodi::firstOrCreate(['kode' => $p[0]], ['nama' => $p[1], 'jenjang' => 'S1']))
            ->values();

        // ---- Dosen ----
        $dosen = collect();
        for ($i = 1; $i <= 6; $i++) {
            $user = $this->buatUser("dosen{$i}@siakad.test", 'Dr. ' . $faker->name(), 'dosen');
            $dosen->push(Dosen::firstOrCreate(
                ['user_id' => $user->id],
                ['prodi_id' => $prodi[($i - 1) % 3]->id, 'nidn' => sprintf('00%08d', 1000 + $i), 'nama' => $user->name]
            ));
        }
        if ($demo = Dosen::where('nidn', '0011223344')->first()) {
            $dosen->push($demo);
        }

        // ---- Mata kuliah & kelas ----
        $mkData = [
            0 => [['TI101', 'Pemrograman Web', 3], ['TI102', 'Basis Data', 3], ['TI103', 'Jaringan Komputer', 3], ['TI104', 'Algoritma dan Struktur Data', 4]],
            1 => [['SI101', 'Analisis Sistem', 3], ['SI102', 'Manajemen Proyek TI', 3], ['SI103', 'Basis Data Lanjut', 3], ['SI104', 'Sistem Informasi Manajemen', 2]],
            2 => [['MI101', 'Pemrograman Dasar', 3], ['MI102', 'Desain Antarmuka', 2], ['MI103', 'Administrasi Basis Data', 3], ['MI104', 'Komputer Akuntansi', 3]],
        ];
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $jam  = ['08:00', '10:30', '13:00'];

        $kelasAll = collect();
        $idx = 0;
        foreach ($mkData as $pi => $rows) {
            foreach ($rows as [$kode, $nama, $sks]) {
                $mk = MataKuliah::firstOrCreate(
                    ['kode' => $kode],
                    ['prodi_id' => $prodi[$pi]->id, 'nama' => $nama, 'sks' => $sks, 'semester' => 3]
                );
                $mulai = $jam[$idx % 3];
                $kelas = Kelas::firstOrCreate(
                    ['mata_kuliah_id' => $mk->id, 'nama' => 'A', 'tahun_ajaran' => '2026/2027'],
                    [
                        'dosen_id'     => $dosen[$idx % $dosen->count()]->id,
                        'semester'     => 'ganjil',
                        'hari'         => $hari[$idx % 5],
                        'jam_mulai'    => $mulai,
                        'jam_selesai'  => Carbon::createFromFormat('H:i', $mulai)->addMinutes(150)->format('H:i'),
                        'ruang'        => 'R-' . (101 + $idx),
                        'kuota'        => 40,
                    ]
                );
                $kelasAll->push($kelas);
                $idx++;
            }
        }

        // ---- Mahasiswa ----
        for ($i = 1; $i <= 36; $i++) {
            $pr = $prodi[($i - 1) % 3];
            $user = $this->buatUser("mhs{$i}@siakad.test", $faker->name(), 'mahasiswa');
            Mahasiswa::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'prodi_id'      => $pr->id,
                    'dosen_wali_id' => $dosen->random()->id,
                    'nim'           => '2024' . str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                    'nama'          => $user->name,
                    'angkatan'      => [2023, 2024, 2025][$i % 3],
                    'status'        => 'aktif',
                ]
            );
        }

        // ---- KRS: setiap mahasiswa mengambil seluruh kelas di prodinya ----
$kelasAll->each(fn ($k) => $k->load('mataKuliah')); foreach (Mahasiswa::all() as $mhs) {
            foreach ($kelasAll as $kelas) {
                if ($kelas->mataKuliah->prodi_id === $mhs->prodi_id) {
                    Krs::firstOrCreate(
                        ['mahasiswa_id' => $mhs->id, 'kelas_id' => $kelas->id],
                        ['status' => 'disetujui']
                    );
                }
            }
        }

        // ---- Nilai: 10 kelas pertama terisi, 8 pertama dikunci, 2 terakhir kosong ----
        foreach ($kelasAll->values() as $n => $kelas) {
            if ($n < 10) {
                foreach ($kelas->krs()->where('status', 'disetujui')->get() as $krs) {
                    if ($krs->nilai_tugas === null) {
                        $krs->nilai_tugas = mt_rand(55, 100);
                        $krs->nilai_uts   = mt_rand(45, 100);
                        $krs->nilai_uas   = mt_rand(40, 100);
                        $krs->hitungNilai();
                        $krs->save();
                    }
                }
            }
            if ($n < 8 && ! $kelas->nilai_terkunci) {
                $kelas->update(['nilai_terkunci' => true, 'dikunci_pada' => now()->subDays(mt_rand(1, 10))]);
            }
        }

        // ---- Pertemuan & absensi (8 pertemuan per kelas) ----
        foreach ($kelasAll as $kelas) {
            $mahasiswaIds = $kelas->krs()->where('status', 'disetujui')->pluck('mahasiswa_id');
            for ($p = 1; $p <= 8; $p++) {
                $pertemuan = Pertemuan::firstOrCreate(
                    ['kelas_id' => $kelas->id, 'pertemuan_ke' => $p],
                    ['tanggal' => Carbon::create(2026, 8, 31)->addWeeks($p - 1)->toDateString(), 'materi' => "Materi pertemuan {$p}"]
                );
                foreach ($mahasiswaIds as $mid) {
                    Absensi::firstOrCreate(
                        ['pertemuan_id' => $pertemuan->id, 'mahasiswa_id' => $mid],
                        ['status' => $this->statusAcak()]
                    );
                }
            }
        }
    }

    /** Buat user (aman walau 'role' tidak ada di $fillable model User). */
    private function buatUser(string $email, string $nama, string $role): User
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            return $user;
        }

        $user = new User();
        $user->forceFill([
            'name' => $nama, 'email' => $email,
            'password' => Hash::make('password'), 'role' => $role,
        ])->save();

        return $user;
    }

    /** Sekitar 82% hadir, sisanya izin/sakit/alpa. */
    private function statusAcak(): string
    {
        $r = mt_rand(1, 100);

        return match (true) {
            $r <= 82 => 'hadir',
            $r <= 89 => 'izin',
            $r <= 95 => 'sakit',
            default  => 'alpa',
        };
    }
}
