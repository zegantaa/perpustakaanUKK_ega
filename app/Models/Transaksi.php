<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksis';
    protected $primaryKey = 'id_transaksi';
    protected $fillable = ['id_anggota', 'id_buku', 'user_id', 'tanggal_pinjam', 'tanggal_jatuh_tempo', 'tanggal_kembali', 'tanggal_pembatalan', 'status', 'denda'];
    protected $casts = ['tanggal_pinjam' => 'date', 'tanggal_jatuh_tempo' => 'date', 'tanggal_kembali' => 'date', 'tanggal_pembatalan' => 'datetime'];

    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota', 'id_anggota');
    }

    public function buku()
    {
        return $this->belongsTo(Buku::class, 'id_buku', 'id_buku');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }
}