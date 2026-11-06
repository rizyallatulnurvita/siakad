<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prodi_id')->constrained('prodi');
            $table->foreignId('dosen_wali_id')->nullable()->constrained('dosen')->nullOnDelete();
            $table->string('nim', 20)->unique();
            $table->string('nama');
            $table->year('angkatan');
            $table->enum('status', ['aktif', 'cuti', 'lulus', 'keluar'])->default('aktif');
            $table->string('no_hp', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('mahasiswa'); }
};
