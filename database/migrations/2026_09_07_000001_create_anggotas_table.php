<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggotas', function (Blueprint $table) {
            $table->bigIncrements('id_anggota');
            $table->unsignedBigInteger('id_user')->unique();
            $table->string('nis')->unique();
            $table->string('nama_lengkap');
            $table->string('kelas');
            $table->text('alamat')->nullable();
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggotas');
    }
};