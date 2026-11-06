<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->string('jenis', 50);                       // UKT, SPP, Praktikum, dll.
            $table->string('keterangan')->nullable();
            $table->string('tahun_ajaran', 9);                 // 2026/2027
            $table->enum('semester', ['ganjil', 'genap']);
            $table->unsignedBigInteger('jumlah');              // rupiah
            $table->date('jatuh_tempo');
            $table->enum('status', ['belum_bayar', 'sebagian', 'lunas'])->default('belum_bayar');
            $table->timestamps();

            $table->unique(['mahasiswa_id', 'jenis', 'tahun_ajaran', 'semester']);
        });
    }

    public function down(): void { Schema::dropIfExists('tagihan'); }
};
