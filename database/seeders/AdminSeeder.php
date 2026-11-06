<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Email/password dibaca dari .env (SEED_ADMIN_EMAIL, SEED_ADMIN_PASSWORD).
     * Bila password tidak diisi, dibuat acak dan ditampilkan SEKALI di terminal.
     */
    public function run(): void
    {
        $email = env('SEED_ADMIN_EMAIL', 'admin@siakad.test');
        if (User::where('email', $email)->exists()) {
            $this->command?->info("Admin $email sudah ada, dilewati.");

            return;
        }

        $password = env('SEED_ADMIN_PASSWORD');
        $generated = false;
        if (!$password) {
            $password = Str::random(16);
            $generated = true;
        }

        User::create([
            'name' => 'Administrator',
            'email' => $email,
            'password' => $password,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->command?->info("Admin dibuat: $email");
        if ($generated) {
            $this->command?->warn("Password sementara (catat sekarang, tidak ditampilkan lagi): $password");
        }
    }
}
