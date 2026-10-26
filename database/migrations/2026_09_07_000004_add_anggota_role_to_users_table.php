<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'petugas', 'anggota') NOT NULL DEFAULT 'anggota'");
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'anggota')->update(['role' => 'petugas']);
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'petugas') NOT NULL DEFAULT 'petugas'");
    }
};