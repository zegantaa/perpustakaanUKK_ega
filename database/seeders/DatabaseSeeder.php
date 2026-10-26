<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(['username' => 'admin'], [
            'nama_lengkap' => 'Administrator Perpustakaan',
            'email' => 'admin@perpustakaan.test',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        $petugas = User::updateOrCreate(['username' => 'petugas'], [
            'nama_lengkap' => 'Petugas Perpustakaan',
            'email' => 'petugas@perpustakaan.test',
            'password' => Hash::make('petugas123'),
            'role' => 'petugas',
            'status' => 'aktif',
        ]);

        $siswa = User::updateOrCreate(['username' => 'siswa'], [
            'nama_lengkap' => 'Siswa Perpustakaan',
            'email' => 'siswa@perpustakaan.test',
            'password' => Hash::make('siswa123'),
            'role' => 'anggota',
            'status' => 'aktif',
        ]);

        Anggota::updateOrCreate(['nis' => '2026001'], [
            'id_user' => $petugas->id_user,
            'nama_lengkap' => 'Petugas Perpustakaan',
            'kelas' => 'Staf',
            'alamat' => 'Sekolah',
        ]);

        Anggota::updateOrCreate(['nis' => '2026002'], [
            'id_user' => $siswa->id_user,
            'nama_lengkap' => 'Siswa Perpustakaan',
            'kelas' => 'XI RPL 1',
            'alamat' => 'Sekolah',
        ]);

        Buku::updateOrCreate(['judul_buku' => 'Pengantar Pemrograman'], [
            'penulis' => 'Tim Perpustakaan',
            'penerbit' => 'Penerbit Sekolah',
            'tahun' => 2024,
            'kategori' => 'Teknologi',
            'stok' => 5,
            'deskripsi_buku' => 'Buku pengantar dasar pemrograman untuk siswa.',
        ]);
    }
}
