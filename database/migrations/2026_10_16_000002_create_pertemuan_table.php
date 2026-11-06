<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pertemuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->unsignedTinyInteger('pertemuan_ke');
            $table->date('tanggal');
            $table->string('materi')->nullable();
            $table->timestamps();

            $table->unique(['kelas_id', 'pertemuan_ke']);
        });
    }

    public function down(): void { Schema::dropIfExists('pertemuan'); }
};
