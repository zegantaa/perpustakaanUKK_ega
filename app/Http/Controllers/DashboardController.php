<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Transaksi;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'jumlahBuku' => Buku::count(),
            'jumlahAnggota' => Anggota::count(),
            'sedangDipinjam' => Transaksi::whereIn('status', ['dipinjam', 'terlambat'])->count(),
            'terlambat' => Transaksi::where('status', 'terlambat')->count(),
            'transaksiTerbaru' => Transaksi::with(['anggota', 'buku'])->latest('created_at')->limit(8)->get(),
        ]);
    }
}