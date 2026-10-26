<?php

use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if (! auth()->check()) return view('auth.login');
    return auth()->user()->role === 'anggota' ? redirect()->route('user.dashboard') : redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->name('register.process');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::middleware('role:admin,petugas')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/buku/pilih-edit', [BukuController::class, 'editMenu'])->name('buku.edit-menu');
        Route::get('/anggota/pilih-edit', [AnggotaController::class, 'editMenu'])->name('anggota.edit-menu');
        Route::resource('buku', BukuController::class);
        Route::resource('anggota', AnggotaController::class)->parameters(['anggota' => 'anggota']);
        Route::resource('transaksi', TransaksiController::class);
        Route::get('/pengembalian', [TransaksiController::class, 'pengembalian'])->name('pengembalian.index');
        Route::patch('/pengembalian/{transaksi}', [TransaksiController::class, 'kembalikan'])->name('pengembalian.kembalikan');
        Route::get('/riwayat', [TransaksiController::class, 'history'])->name('riwayat.index');
        Route::get('/laporan', [TransaksiController::class, 'report'])->name('laporan.index');
        Route::get('/laporan/cetak', [TransaksiController::class, 'printReport'])->name('laporan.print');
    });
    Route::middleware('member')->prefix('user')->name('user.')->group(function () {
        Route::get('/dashboard', [MemberController::class, 'dashboard'])->name('dashboard');
        Route::get('/koleksi', [MemberController::class, 'books'])->name('books');
        Route::get('/koleksi/{buku}', [MemberController::class, 'book'])->name('books.show');
        Route::post('/koleksi/{buku}/pinjam', [MemberController::class, 'borrow'])->name('books.borrow');
        Route::get('/peminjaman', [MemberController::class, 'loans'])->name('loans');
        Route::patch('/peminjaman/{transaksi}/kembalikan', [MemberController::class, 'returnBook'])->name('loans.return');
        Route::patch('/peminjaman/{transaksi}/batalkan', [MemberController::class, 'cancel'])->name('loans.cancel');
        Route::get('/riwayat', [MemberController::class, 'history'])->name('history');
        Route::get('/profil', [MemberController::class, 'profile'])->name('profile');
        Route::get('/profil/edit', [MemberController::class, 'editProfile'])->name('profile.edit');
        Route::put('/profil', [MemberController::class, 'updateProfile'])->name('profile.update');
        Route::put('/profil/password', [MemberController::class, 'updatePassword'])->name('profile.password');
    });
    Route::middleware('role:admin')->group(function () {
        Route::resource('user', UserController::class);
    });
});
