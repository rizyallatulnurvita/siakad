<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->boolean('nilai_terkunci')->default(false)->after('kuota');
            $table->timestamp('dikunci_pada')->nullable()->after('nilai_terkunci');
        });
    }

    public function down(): void
    {
        Schema::table('kelas', fn (Blueprint $table) => $table->dropColumn(['nilai_terkunci', 'dikunci_pada']));
    }
};
