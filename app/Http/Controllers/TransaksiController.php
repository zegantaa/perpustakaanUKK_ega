<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        $transaksis = Transaksi::with(['anggota', 'buku', 'user'])
            ->when(in_array($status, ['dipinjam', 'dikembalikan', 'terlambat'], true), fn ($query) => $query->where('status', $status))
            ->latest('tanggal_pinjam')->paginate(10)->appends($request->query());
        return view('transaksi.index', compact('transaksis', 'status'));
    }

    public function create()
    {
        return view('transaksi.create', [
            'anggotas' => Anggota::orderBy('nama_lengkap')->get(),
            'bukus' => Buku::where('stok', '>', 0)->orderBy('judul_buku')->get(),
            'users' => User::where('status', 'aktif')->orderBy('nama_lengkap')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($data) {
            $buku = Buku::whereKey($data['id_buku'])->lockForUpdate()->firstOrFail();
            if ($buku->stok < 1) abort(422, 'Stok buku sedang habis.');
            if (in_array($data['status'], ['dipinjam', 'terlambat'], true)) {
                $buku->decrement('stok');
            }
            $data['tanggal_jatuh_tempo'] = $data['tanggal_jatuh_tempo'] ?? Carbon::parse($data['tanggal_pinjam'])->addDays(7)->toDateString();
            if ($data['status'] === 'dikembalikan') $data['tanggal_kembali'] = $data['tanggal_kembali'] ?: now()->toDateString();
            Transaksi::create($data);
        });
        return redirect()->route('transaksi.index')->with('success', 'Transaksi peminjaman berhasil disimpan.');
    }

    public function show(Transaksi $transaksi)
    {
        $transaksi->load(['anggota', 'buku', 'user']);
        return view('transaksi.show', compact('transaksi'));
    }

    public function edit(Transaksi $transaksi)
    {
        return view('transaksi.edit', [
            'transaksi' => $transaksi,
            'anggotas' => Anggota::orderBy('nama_lengkap')->get(),
            'bukus' => Buku::orderBy('judul_buku')->get(),
            'users' => User::where('status', 'aktif')->orderBy('nama_lengkap')->get(),
        ]);
    }

    public function update(Request $request, Transaksi $transaksi)
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($data, $transaksi) {
            $oldActive = in_array($transaksi->status, ['dipinjam', 'terlambat'], true);
            $newActive = in_array($data['status'], ['dipinjam', 'terlambat'], true);
            if ($oldActive !== $newActive || $transaksi->id_buku !== (int) $data['id_buku']) {
                $oldBook = Buku::whereKey($transaksi->id_buku)->lockForUpdate()->firstOrFail();
                $newBook = Buku::whereKey($data['id_buku'])->lockForUpdate()->firstOrFail();
                if ($oldActive) $oldBook->increment('stok');
                if ($newActive) {
                    if ($newBook->stok < 1) abort(422, 'Stok buku baru sedang habis.');
                    $newBook->decrement('stok');
                }
            }
            if ($data['status'] === 'dikembalikan') $data['tanggal_kembali'] = $data['tanggal_kembali'] ?: now()->toDateString();
            $transaksi->update($data);
        });
        return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Transaksi $transaksi)
    {
        DB::transaction(function () use ($transaksi) {
            if (in_array($transaksi->status, ['dipinjam', 'terlambat'], true)) Buku::whereKey($transaksi->id_buku)->lockForUpdate()->firstOrFail()->increment('stok');
            $transaksi->delete();
        });
        return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil dihapus.');
    }

    public function pengembalian()
    {
        $transaksis = Transaksi::with(['anggota', 'buku'])->whereIn('status', ['dipinjam', 'terlambat'])->latest('tanggal_pinjam')->paginate(10);
        return view('pengembalian.index', compact('transaksis'));
    }

    public function history(Request $request)
    {
        $status = $request->string('status')->toString();
        $transaksis = Transaksi::with(['anggota', 'buku'])
            ->when(in_array($status, ['dipinjam', 'dikembalikan', 'terlambat', 'dibatalkan'], true), fn ($query) => $query->where('status', $status))
            ->latest('tanggal_pinjam')
            ->paginate(15)
            ->appends($request->query());

        return view('riwayat.index', compact('transaksis', 'status'));
    }

    public function report()
    {
        $ringkasan = [
            'total' => Transaksi::count(),
            'dipinjam' => Transaksi::whereIn('status', ['dipinjam', 'terlambat'])->count(),
            'dikembalikan' => Transaksi::where('status', 'dikembalikan')->count(),
            'terlambat' => Transaksi::where('status', 'terlambat')->count(),
            'denda' => Transaksi::where('status', 'dikembalikan')->sum('denda'),
        ];
        $transaksiTerbaru = Transaksi::with(['anggota', 'buku'])->latest('tanggal_pinjam')->limit(10)->get();

        return view('laporan.index', compact('ringkasan', 'transaksiTerbaru'));
    }

    public function printReport()
    {
        $ringkasan = [
            'total' => Transaksi::count(),
            'dipinjam' => Transaksi::whereIn('status', ['dipinjam', 'terlambat'])->count(),
            'dikembalikan' => Transaksi::where('status', 'dikembalikan')->count(),
            'terlambat' => Transaksi::where('status', 'terlambat')->count(),
            'denda' => Transaksi::where('status', 'dikembalikan')->sum('denda'),
        ];
        $transaksis = Transaksi::with(['anggota', 'buku'])
            ->latest('tanggal_pinjam')
            ->get();

        return view('laporan.print', compact('ringkasan', 'transaksis'));
    }

    public function kembalikan(Request $request, Transaksi $transaksi)
    {
        $tanggalPinjam = Carbon::parse($transaksi->tanggal_pinjam);
        $request->validate(['tanggal_kembali' => ['required', 'date', 'after_or_equal:' . $tanggalPinjam->toDateString()]]);
        $tanggalKembali = $request->input('tanggal_kembali');
        $berhasil = DB::transaction(function () use ($transaksi, $tanggalPinjam, $tanggalKembali) {
            $transaksi = Transaksi::whereKey($transaksi->id_transaksi)->lockForUpdate()->firstOrFail();
            if (! in_array($transaksi->status, ['dipinjam', 'terlambat'], true)) return false;
            $denda = max(0, $tanggalPinjam->diffInDays(Carbon::parse($tanggalKembali)) - 7) * 1000;
            $transaksi->update(['status' => 'dikembalikan', 'tanggal_kembali' => $tanggalKembali, 'denda' => $denda]);
            Buku::whereKey($transaksi->id_buku)->lockForUpdate()->firstOrFail()->increment('stok');
            return true;
        });
        if (! $berhasil) return back()->with('error', 'Transaksi ini sudah dikembalikan.');
        return back()->with('success', 'Buku berhasil dikembalikan.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'id_anggota' => ['required', 'exists:anggotas,id_anggota'],
            'id_buku' => ['required', 'exists:bukus,id_buku'],
            'user_id' => ['required', 'exists:users,id_user'],
            'tanggal_pinjam' => ['required', 'date'],
            'tanggal_kembali' => ['nullable', 'date', 'after_or_equal:tanggal_pinjam'],
            'status' => ['required', 'in:dipinjam,dikembalikan,terlambat'],
            'denda' => ['required', 'integer', 'min:0'],
        ]);
    }
}