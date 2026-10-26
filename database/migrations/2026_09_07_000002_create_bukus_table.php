<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bukus', function (Blueprint $table) {
            $table->bigIncrements('id_buku');
            $table->string('judul_buku');
            $table->string('penulis');
            $table->string('penerbit');
            $table->unsignedSmallInteger('tahun');
            $table->string('kategori');
            $table->unsignedInteger('stok')->default(0);
            $table->text('deskripsi_buku')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bukus');
    }
};