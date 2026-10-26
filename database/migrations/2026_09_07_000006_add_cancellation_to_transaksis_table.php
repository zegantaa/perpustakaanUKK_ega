<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dateTime('tanggal_pembatalan')->nullable()->after('tanggal_kembali');
        });

        DB::statement("ALTER TABLE transaksis MODIFY status ENUM('dipinjam', 'dikembalikan', 'terlambat', 'dibatalkan') NOT NULL DEFAULT 'dipinjam'");
    }

    public function down(): void
    {
        DB::table('transaksis')->where('status', 'dibatalkan')->update(['status' => 'dikembalikan', 'tanggal_pembatalan' => null]);
        DB::statement("ALTER TABLE transaksis MODIFY status ENUM('dipinjam', 'dikembalikan', 'terlambat') NOT NULL DEFAULT 'dipinjam'");

        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn('tanggal_pembatalan');
        });
    }
};