@extends('layouts.app')
@section('content')
<div class="page-heading"><div><span class="eyebrow">Sirkulasi koleksi</span><h1>Riwayat Transaksi</h1><p class="muted">Riwayat peminjaman dan pengembalian buku.</p></div></div>
<section class="panel">
    <form class="toolbar" method="GET">
        <select name="status"><option value="">Semua status</option>@foreach(['dipinjam','terlambat','dikembalikan','dibatalkan'] as $item)<option value="{{ $item }}" @selected($status === $item)>{{ ucfirst($item) }}</option>@endforeach</select>
        <button class="button secondary">Tampilkan</button>
    </form>
    <div class="table-wrap"><table><thead><tr><th>No</th><th>Peminjam</th><th>Judul Buku</th><th>Tanggal Peminjaman</th><th>Tanggal Pengembalian</th><th>Status</th><th>Denda</th></tr></thead><tbody>
    @forelse($transaksis as $i => $transaksi)
        <tr><td>{{ $transaksis->firstItem() + $i }}</td><td>{{ $transaksi->anggota->nama_lengkap }}</td><td>{{ $transaksi->buku->judul_buku }}</td><td>{{ $transaksi->tanggal_pinjam->format('d/m/Y') }}</td><td>{{ $transaksi->tanggal_kembali?->format('d/m/Y') ?? '-' }}</td><td>@include('partials.status-badge', ['value' => $transaksi->status])</td><td>Rp {{ number_format($transaksi->denda, 0, ',', '.') }}</td></tr>
    @empty
        <tr><td colspan="7" class="empty">Belum ada riwayat transaksi.</td></tr>
    @endforelse
    </tbody></table></div>{{ $transaksis->links() }}
</section>
@endsection
