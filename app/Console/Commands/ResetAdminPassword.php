<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetAdminPassword extends Command
{
    protected $signature = 'auth:reset-admin-password';

    protected $description = 'Reset password akun admin menggunakan hash Laravel';

    public function handle(): int
    {
        $admin = User::where('username', 'admin')->first();

        if (! $admin) {
            $this->error('Akun admin tidak ditemukan. Jalankan database seeder terlebih dahulu.');
            return self::FAILURE;
        }

        $password = $this->secret('Password admin baru');
        $confirmation = $this->secret('Ulangi password admin baru');

        if (! is_string($password) || strlen($password) < 6) {
            $this->error('Password minimal 6 karakter.');
            return self::FAILURE;
        }

        if (! hash_equals($password, (string) $confirmation)) {
            $this->error('Konfirmasi password tidak cocok.');
            return self::FAILURE;
        }

        $admin->forceFill([
            'password' => Hash::make($password),
        ])->save();

        $this->info('Password admin berhasil diperbarui dengan hash Laravel.');
        return self::SUCCESS;
    }
}