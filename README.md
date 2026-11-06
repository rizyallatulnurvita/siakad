# SIAKAD — Paket Fitur Tambahan

Fitur: dashboard (3 kartu + 2 grafik + 1 tabel per role), diagram nilai, sistem lock nilai,
jadwal perkuliahan, absensi, dan data dummy.

## Pasang
1. Salin semua folder (app, database, resources, routes) ke project, timpa file yang sama
   (DashboardController, Kelas, app-layout, 3 view dashboard, routes/web.php).
2. Jalankan:
       php artisan migrate
       php artisan db:seed --class=DummyDataSeeder
       php artisan optimize:clear
   Atau dari nol:
       php artisan migrate:fresh --seed
       php artisan db:seed --class=DummyDataSeeder

## Akun dummy (password: password)
- admin@siakad.test
- dosen1@siakad.test ... dosen6@siakad.test (+ dosen@siakad.test)
- mhs1@siakad.test ... mhs36@siakad.test (+ mahasiswa@siakad.test)

## Cara kerja sistem lock (nilai)
- Dosen mengisi nilai -> Simpan -> "Kunci & Publikasikan Nilai" (hanya bisa jika semua nilai lengkap).
- Kelas terkunci: dosen tidak bisa mengubah nilai lagi; nilai baru tampil di akun mahasiswa
  dan ikut dihitung ke IPK.
- Hanya admin yang bisa membuka kunci (menu "Kelas & Kunci Nilai").

## Catatan
- Grafik memakai Chart.js dari CDN, tampilan memakai Tailwind CDN: butuh internet saat membuka halaman.
- Jika memakai Breeze (bukan paket login manual), tambahkan sendiri link menu ke layout Breeze:
  route('jadwal'), route('admin.kelas.index'), route('dosen.kelas.index'), route('mahasiswa.absensi.index').
