<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\KelasKuliah;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, array $o = []): User
    {
        return User::create($o + [
            'name' => ucfirst($role), 'email' => $role . '.' . uniqid() . '@test.local', 'password' => 'Password123',
            'role' => $role, 'is_active' => true,
        ]);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/admin/mahasiswa')->assertRedirect('/login');
    }

    public function test_login_berhasil_dan_gagal(): void
    {
        $this->user('admin', ['email' => 'admin@test.local']);
        $this->post('/login', ['email' => 'admin@test.local', 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => 'admin@test.local', 'password' => 'Password123'])->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_akun_nonaktif_tidak_bisa_login(): void
    {
        $this->user('admin', ['email' => 'admin@test.local', 'is_active' => false]);
        $this->post('/login', ['email' => 'admin@test.local', 'password' => 'Password123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_dapat_membuka_halaman_admin(): void
    {
        $this->actingAs($this->user('admin'))->get('/admin/users')->assertOk();
        $this->actingAs($this->user('admin'))->get('/dashboard')->assertOk();
    }

    public function test_dosen_dan_mahasiswa_ditolak_di_area_admin(): void
    {
        $this->actingAs($this->user('dosen'))->get('/admin/users')->assertForbidden();
        $this->actingAs($this->user('mahasiswa'))->get('/admin/krs')->assertForbidden();
        $this->actingAs($this->user('mahasiswa'))->get('/dosen/kelas')->assertForbidden();
        $this->actingAs($this->user('dosen'))->get('/mahasiswa/krs')->assertForbidden();
    }

    public function test_dosen_tidak_bisa_membuka_atau_mengisi_nilai_kelas_dosen_lain(): void
    {
        $prodi = ProgramStudi::create(['kode' => 'TI', 'nama' => 'Teknik Informatika', 'jenjang' => 'S1']);
        $ta = TahunAkademik::create(['tahun' => '2026/2027', 'semester' => 'ganjil', 'tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2027-01-31', 'is_active' => true]);
        $mk = MataKuliah::create(['kode' => 'TI1', 'nama' => 'Algoritma', 'sks' => 3, 'program_studi_id' => $prodi->id, 'semester' => 1]);

        $mkDosen = function (string $n) use ($prodi) {
            $u = $this->user('dosen', ['email' => "$n@test.local"]);

            return [$u, Dosen::create(['user_id' => $u->id, 'nidn' => $n, 'nama' => $n, 'email' => $u->email, 'program_studi_id' => $prodi->id, 'status' => 'aktif'])];
        };
        [$u1, $d1] = $mkDosen('dosen1');
        [$u2, $d2] = $mkDosen('dosen2');
        $kelas = KelasKuliah::create(['mata_kuliah_id' => $mk->id, 'dosen_id' => $d1->id, 'tahun_akademik_id' => $ta->id, 'nama' => 'A', 'kapasitas' => 30]);

        $this->actingAs($u1)->get("/dosen/kelas/{$kelas->id}")->assertOk();
        $this->actingAs($u2)->get("/dosen/kelas/{$kelas->id}")->assertForbidden();
        $this->actingAs($u2)->post("/dosen/kelas/{$kelas->id}/nilai", ['nilai' => [1 => ['tugas' => 100]]])->assertForbidden();
    }

    public function test_pengguna_nonaktif_yang_sedang_login_dikeluarkan(): void
    {
        $u = $this->user('admin');
        $this->actingAs($u);
        $u->update(['is_active' => false]);
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
