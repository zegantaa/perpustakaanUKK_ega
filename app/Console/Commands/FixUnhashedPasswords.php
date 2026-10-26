<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class FixUnhashedPasswords extends Command
{
    protected $signature = 'auth:fix-passwords';

    protected $description = 'Mengubah password user yang tersimpan tanpa hash (plain text) menjadi hash bcrypt';

    public function handle(): int
    {
        $users = User::query()->select(['id_user', 'username', 'password'])->get();
        $fixed = 0;

        foreach ($users as $user) {
            $stored = (string) $user->password;

            if ($stored === '' || Hash::isHashed($stored)) {
                continue;
            }

            $this->line(sprintf('  %-20s -> meng-hash password lama', $user->username));

            $user->forceFill(['password' => $stored])->save();
            $fixed++;
        }

        if ($fixed === 0) {
            $this->info('Semua password sudah tersimpan dalam bentuk hash.');
        } else {
            $this->info("Selesai. {$fixed} password berhasil diperbaiki.");
        }

        return self::SUCCESS;
    }
}