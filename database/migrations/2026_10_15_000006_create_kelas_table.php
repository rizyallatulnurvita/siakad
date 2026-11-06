<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('dosen');
            $table->string('nama', 5);                       // A, B, C
            $table->string('tahun_ajaran', 9);               // 2026/2027
            $table->enum('semester', ['ganjil', 'genap']);
            $table->enum('hari', ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'])->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->string('ruang', 30)->nullable();
            $table->unsignedSmallInteger('kuota')->default(40);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('kelas'); }
};
