<?php

namespace Database\Seeders;

use App\Models\{Dosen, Kelas, Krs, Mahasiswa, MataKuliah, Prodi, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $prodi = Prodi::firstOrCreate(['kode' => 'TI'], ['nama' => 'Teknik Informatika', 'jenjang' => 'S1']);

        $this->buatUser('admin@siakad.test', 'Administrator', 'admin');

        $uDosen = $this->buatUser('dosen@siakad.test', 'Dr. Budi Santoso', 'dosen');
        $dosen = Dosen::firstOrCreate(
            ['user_id' => $uDosen->id],
            ['prodi_id' => $prodi->id, 'nidn' => '0011223344', 'nama' => 'Dr. Budi Santoso']
        );

        $uMhs = $this->buatUser('mahasiswa@siakad.test', 'Siti Rahma', 'mahasiswa');
        $mhs = Mahasiswa::firstOrCreate(
            ['user_id' => $uMhs->id],
            ['prodi_id' => $prodi->id, 'dosen_wali_id' => $dosen->id, 'nim' => '2024001001', 'nama' => 'Siti Rahma', 'angkatan' => 2024]
        );

        $mk = MataKuliah::firstOrCreate(
            ['kode' => 'TI101'],
            ['prodi_id' => $prodi->id, 'nama' => 'Pemrograman Web', 'sks' => 3, 'semester' => 3]
        );
        $kelas = Kelas::firstOrCreate(
            ['mata_kuliah_id' => $mk->id, 'nama' => 'A', 'tahun_ajaran' => '2026/2027'],
            [
                'dosen_id' => $dosen->id, 'semester' => 'ganjil',
                'hari' => 'Senin', 'jam_mulai' => '08:00', 'jam_selesai' => '10:30', 'ruang' => 'Lab 1',
            ]
        );
        Krs::firstOrCreate(['mahasiswa_id' => $mhs->id, 'kelas_id' => $kelas->id], ['status' => 'disetujui']);

        // Data dummy lengkap (dosen, mahasiswa, KRS, nilai, absensi)
        $this->call(DummyDataSeeder::class);
        $this->call(TagihanDummySeeder::class);
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
}
