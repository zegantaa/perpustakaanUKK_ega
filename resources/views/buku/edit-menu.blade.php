@extends('layouts.app')
@section('content')
<div class="page-heading"><div><span class="eyebrow">Kelola koleksi</span><h1>Edit Buku</h1><p class="muted">Pilih buku yang ingin diperbarui.</p></div></div>
<section class="panel">
    <form class="toolbar" method="GET" action="{{ route('buku.edit-menu') }}">
        <input name="cari" value="{{ $cari }}" placeholder="Cari judul, penulis, atau kategori...">
        <button class="button secondary" type="submit">Cari</button>
    </form>
    <div class="table-wrap"><table>
        <thead><tr><th>No</th><th>Judul Buku</th><th>Penulis</th><th>Kategori</th><th>Stok</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($bukus as $i => $buku)
                <tr><td>{{ $bukus->firstItem() + $i }}</td><td><strong>{{ $buku->judul_buku }}</strong></td><td>{{ $buku->penulis }}</td><td>{{ $buku->kategori }}</td><td>{{ $buku->stok }}</td><td><a class="button primary" href="{{ route('buku.edit', $buku) }}">Edit Buku</a></td></tr>
            @empty
                <tr><td colspan="6" class="empty">Data buku belum tersedia.</td></tr>
            @endforelse
        </tbody>
    </table></div>
    {{ $bukus->links() }}
</section>
@endsection
