<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MemberController extends Controller
{
    public function dashboard()
    {
        $anggota = auth()->user()->anggota;
        $transaksi = $anggota->transaksi();

        return view('member.dashboard', [
            'dipinjam' => (clone $transaksi)->whereIn('status', ['dipinjam', 'terlambat'])->count(),
            'dikembalikan' => (clone $transaksi)->where('status', 'dikembalikan')->count(),
            'terlambat' => (clone $transaksi)->where('status', 'terlambat')->count(),
            'totalDenda' => (clone $transaksi)->sum('denda'),
            'transaksiAktif' => (clone $transaksi)->with('buku')->whereIn('status', ['dipinjam', 'terlambat'])->latest('tanggal_pinjam')->limit(5)->get(),
            'bukuTerbaru' => Buku::latest()->limit(4)->get(),
        ]);
    }

    public function books(Request $request)
    {
        $cari = $request->string('cari')->trim()->toString();
        $kategori = $request->string('kategori')->trim()->toString();
        $tahun = $request->string('tahun')->trim()->toString();
        $ketersediaan = $request->string('ketersediaan')->trim()->toString();
        $query = Buku::query()
            ->when($cari, fn ($query) => $query->where(function ($query) use ($cari) {
                $query->where('judul_buku', 'like', "%{$cari}%")
                    ->orWhere('penulis', 'like', "%{$cari}%")
                    ->orWhere('penerbit', 'like', "%{$cari}%")
                    ->orWhere('kategori', 'like', "%{$cari}%");
            }))
            ->when($kategori, fn ($query) => $query->where('kategori', $kategori))
            ->when($tahun, fn ($query) => $query->where('tahun', $tahun))
            ->when($ketersediaan === 'tersedia', fn ($query) => $query->where('stok', '>', 0))
            ->when($ketersediaan === 'habis', fn ($query) => $query->where('stok', 0));

        return view('member.books.index', [
            'books' => $query->latest()->paginate(12)->appends($request->query()),
            'categories' => Buku::query()->whereNotNull('kategori')->distinct()->orderBy('kategori')->pluck('kategori'),
            'years' => Buku::query()->whereNotNull('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun'),
            'cari' => $cari,
            'kategori' => $kategori,
            'tahun' => $tahun,
            'ketersediaan' => $ketersediaan,
        ]);
    }

    public function book(Buku $buku)
    {
        return view('member.books.show', compact('buku'));
    }

    public function borrow(Request $request, Buku $buku)
    {
        $anggota = auth()->user()->anggota;
        $tanggalPinjam = now()->startOfDay();
        $tanggalJatuhTempo = $tanggalPinjam->copy()->addDays(7);

        DB::transaction(function () use ($anggota, $buku, $tanggalPinjam, $tanggalJatuhTempo) {
            $bukuTerkunci = Buku::whereKey($buku->id_buku)->lockForUpdate()->firstOrFail();
            $sudahMeminjam = Transaksi::where('id_anggota', $anggota->id_anggota)
                ->where('id_buku', $bukuTerkunci->id_buku)
                ->whereIn('status', ['dipinjam', 'terlambat'])
                ->lockForUpdate()
                ->exists();

            if ($sudahMeminjam) {
                throw ValidationException::withMessages([
                    'buku' => 'Kamu masih memiliki peminjaman aktif untuk buku ini.',
                ]);
            }

            if ($bukuTerkunci->stok < 1) {
                throw ValidationException::withMessages([
                    'buku' => 'Buku ini sedang tidak tersedia.',
                ]);
            }

            $bukuTerkunci->decrement('stok');
            Transaksi::create([
                'id_anggota' => $anggota->id_anggota,
                'id_buku' => $bukuTerkunci->id_buku,
                'user_id' => auth()->id(),
                'tanggal_pinjam' => $tanggalPinjam,
                'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
                'status' => 'dipinjam',
                'denda' => 0,
            ]);
        });

        return redirect()->route('user.loans')->with('success', 'Berhasil! Buku berhasil dipinjam secara online.');
    }

    public function loans()
    {
        $loans = auth()->user()->anggota->transaksi()->with('buku')->whereIn('status', ['dipinjam', 'terlambat', 'dibatalkan'])->latest('tanggal_pinjam')->paginate(10);
        return view('member.loans.index', compact('loans'));
    }

    public function returnBook(Transaksi $transaksi)
    {
        $anggota = auth()->user()->anggota;
        $tanggalKembali = now()->toDateString();

        DB::transaction(function () use ($anggota, $transaksi, $tanggalKembali) {
            $peminjaman = Transaksi::whereKey($transaksi->id_transaksi)
                ->where('id_anggota', $anggota->id_anggota)
                ->lockForUpdate()
                ->first();

            if (! $peminjaman) {
                throw ValidationException::withMessages(['peminjaman' => 'Anda tidak memiliki akses untuk mengembalikan peminjaman ini.']);
            }

            if (! in_array($peminjaman->status, ['dipinjam', 'terlambat'], true)) {
                throw ValidationException::withMessages(['peminjaman' => 'Peminjaman ini sudah tidak aktif.']);
            }

            $buku = Buku::whereKey($peminjaman->id_buku)->lockForUpdate()->firstOrFail();
            $batasKembali = $peminjaman->tanggal_jatuh_tempo
                ?? Carbon::parse($peminjaman->tanggal_pinjam)->addDays(7);
            $denda = max(0, Carbon::parse($batasKembali)->diffInDays(Carbon::parse($tanggalKembali), false)) * 1000;

            $peminjaman->update([
                'status' => 'dikembalikan',
                'tanggal_kembali' => $tanggalKembali,
                'denda' => $denda,
            ]);
            $buku->increment('stok');
        });

        return redirect()->route('user.loans')->with('success', 'Buku berhasil dikembalikan.');
    }

    public function cancel(Request $request, Transaksi $transaksi)
    {
        $anggota = auth()->user()->anggota;

        DB::transaction(function () use ($anggota, $transaksi) {
            $peminjaman = Transaksi::whereKey($transaksi->id_transaksi)
                ->where('id_anggota', $anggota->id_anggota)
                ->lockForUpdate()
                ->first();

            if (! $peminjaman) {
                throw ValidationException::withMessages(['peminjaman' => 'Anda tidak memiliki akses untuk membatalkan peminjaman ini.']);
            }

            if ($peminjaman->status === 'dibatalkan') {
                throw ValidationException::withMessages(['peminjaman' => 'Peminjaman ini sudah dibatalkan.']);
            }

            if ($peminjaman->status === 'dikembalikan') {
                throw ValidationException::withMessages(['peminjaman' => 'Buku yang sudah dikembalikan tidak dapat dibatalkan.']);
            }

            if ($peminjaman->status !== 'dipinjam') {
                throw ValidationException::withMessages(['peminjaman' => 'Peminjaman ini tidak dapat dibatalkan.']);
            }

            $buku = Buku::whereKey($peminjaman->id_buku)->lockForUpdate()->firstOrFail();
            $peminjaman->update([
                'status' => 'dibatalkan',
                'tanggal_pembatalan' => now(),
            ]);
            $buku->increment('stok');
        });

        return redirect()->route('user.loans')->with('success', 'Berhasil! Peminjaman buku telah dibatalkan.');
    }

    public function history()
    {
        $history = auth()->user()->anggota->transaksi()->with('buku')->latest('tanggal_pinjam')->paginate(10);
        return view('member.history.index', compact('history'));
    }

    public function profile()
    {
        $user = User::with('anggota')->findOrFail(auth()->id());
        return view('member.profile.index', compact('user'));
    }

    public function editProfile()
    {
        $user = User::with('anggota')->findOrFail(auth()->id());
        return view('member.profile.edit', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $anggota = $user->anggota;
        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email,' . $user->id_user . ',id_user'],
            'nis' => ['required', 'string', 'max:30', 'unique:anggotas,nis,' . $anggota->id_anggota . ',id_anggota'],
            'kelas' => ['required', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($user, $anggota, $data) {
            User::whereKey($user->id_user)->update(['nama_lengkap' => $data['nama_lengkap'], 'email' => $data['email']]);
            $anggota->update(['nama_lengkap' => $data['nama_lengkap'], 'nis' => $data['nis'], 'kelas' => $data['kelas'], 'alamat' => $data['alamat'] ?? null]);
        });
        return redirect()->route('user.profile')->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'password_lama' => ['required', 'current_password:web'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        User::whereKey(auth()->id())->update(['password' => Hash::make($data['password'])]);
        return redirect()->route('user.profile')->with('success', 'Password berhasil diubah.');
    }
}