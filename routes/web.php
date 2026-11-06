<?php

use App\Http\Controllers\Admin\KelasController as AdminKelasController;
use App\Http\Controllers\Admin\PembayaranController as AdminPembayaranController;
use App\Http\Controllers\Admin\TagihanController as AdminTagihanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuktiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Dosen\AbsensiController as DosenAbsensiController;
use App\Http\Controllers\Dosen\KelasController as DosenKelasController;
use App\Http\Controllers\Dosen\NilaiController as DosenNilaiController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\Mahasiswa\AbsensiController as MahasiswaAbsensiController;
use App\Http\Controllers\Mahasiswa\TagihanController as MahasiswaTagihanController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

// ===== AUTH (manual, tanpa Breeze) =====
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal');
    Route::get('/bukti/{pembayaran}', [BuktiController::class, 'show'])->name('bukti.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // ===== ADMIN =====
    Route::middleware(RoleMiddleware::class . ':admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [DashboardController::class, 'admin'])->name('dashboard');
        Route::get('/kelas', [AdminKelasController::class, 'index'])->name('kelas.index');
        Route::get('/kelas/create', [AdminKelasController::class, 'create'])->name('kelas.create');
        Route::post('/kelas', [AdminKelasController::class, 'store'])->name('kelas.store');
        Route::get('/kelas/{kelas}/edit', [AdminKelasController::class, 'edit'])->name('kelas.edit');
        Route::put('/kelas/{kelas}', [AdminKelasController::class, 'update'])->name('kelas.update');
        Route::delete('/kelas/{kelas}', [AdminKelasController::class, 'destroy'])->name('kelas.destroy');
        Route::patch('/kelas/{kelas}/kunci', [AdminKelasController::class, 'toggleKunci'])->name('kelas.kunci');

        Route::get('/tagihan', [AdminTagihanController::class, 'index'])->name('tagihan.index');
        Route::get('/tagihan/create', [AdminTagihanController::class, 'create'])->name('tagihan.create');
        Route::post('/tagihan', [AdminTagihanController::class, 'store'])->name('tagihan.store');
        Route::delete('/tagihan/{tagihan}', [AdminTagihanController::class, 'destroy'])->name('tagihan.destroy');
        Route::get('/pembayaran', [AdminPembayaranController::class, 'index'])->name('pembayaran.index');
        Route::patch('/pembayaran/{pembayaran}', [AdminPembayaranController::class, 'verifikasi'])->name('pembayaran.verifikasi');
    });

    // ===== DOSEN =====
    Route::middleware(RoleMiddleware::class . ':dosen')->prefix('dosen')->name('dosen.')->group(function () {
        Route::get('/', [DashboardController::class, 'dosen'])->name('dashboard');
        Route::get('/kelas', [DosenKelasController::class, 'index'])->name('kelas.index');

        Route::get('/kelas/{kelas}/nilai', [DosenNilaiController::class, 'edit'])->name('nilai.edit');
        Route::put('/kelas/{kelas}/nilai', [DosenNilaiController::class, 'update'])->name('nilai.update');
        Route::post('/kelas/{kelas}/nilai/kunci', [DosenNilaiController::class, 'kunci'])->name('nilai.kunci');

        Route::get('/kelas/{kelas}/absensi', [DosenAbsensiController::class, 'index'])->name('absensi.index');
        Route::post('/kelas/{kelas}/absensi', [DosenAbsensiController::class, 'store'])->name('absensi.store');
        Route::get('/absensi/{pertemuan}', [DosenAbsensiController::class, 'show'])->name('absensi.show');
        Route::put('/absensi/{pertemuan}', [DosenAbsensiController::class, 'update'])->name('absensi.update');
    });

    // ===== MAHASISWA =====
    Route::middleware(RoleMiddleware::class . ':mahasiswa')->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        Route::get('/', [DashboardController::class, 'mahasiswa'])->name('dashboard');
        Route::get('/absensi', [MahasiswaAbsensiController::class, 'index'])->name('absensi.index');

        Route::get('/tagihan', [MahasiswaTagihanController::class, 'index'])->name('tagihan.index');
        Route::get('/tagihan/{tagihan}', [MahasiswaTagihanController::class, 'show'])->name('tagihan.show');
        Route::get('/tagihan/{tagihan}/bayar', [MahasiswaTagihanController::class, 'bayar'])->name('tagihan.bayar');
        Route::post('/tagihan/{tagihan}/bayar', [MahasiswaTagihanController::class, 'store'])->name('tagihan.bayar.store');
    });
});
