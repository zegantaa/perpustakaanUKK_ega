<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Buku extends Model
{
    use HasFactory;

    protected $table = 'bukus';
    protected $primaryKey = 'id_buku';
    protected $fillable = ['judul_buku', 'penulis', 'penerbit', 'tahun', 'kategori', 'stok', 'deskripsi_buku', 'cover'];

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover && Storage::disk('public')->exists($this->cover)
            ? asset('storage/' . ltrim($this->cover, '/'))
            : null;
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'id_buku', 'id_buku');
    }
}