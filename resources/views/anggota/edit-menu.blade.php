@extends('layouts.app')
@section('content')
<div class="page-heading"><div><span class="eyebrow">Kelola data siswa</span><h1>Edit Anggota</h1><p class="muted">Pilih anggota yang ingin diperbarui.</p></div></div>
<section class="panel">
    <form class="toolbar" method="GET" action="{{ route('anggota.edit-menu') }}">
        <input name="cari" value="{{ $cari }}" placeholder="Cari NIS, nama, atau kelas...">
        <button class="button secondary" type="submit">Cari</button>
    </form>
    <div class="table-wrap"><table>
        <thead><tr><th>No</th><th>NIS</th><th>Nama Lengkap</th><th>Kelas</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($anggotas as $i => $anggota)
                <tr><td>{{ $anggotas->firstItem() + $i }}</td><td>{{ $anggota->nis }}</td><td><strong>{{ $anggota->nama_lengkap }}</strong></td><td>{{ $anggota->kelas }}</td><td><a class="button primary" href="{{ route('anggota.edit', $anggota) }}">Edit Anggota</a></td></tr>
            @empty
                <tr><td colspan="5" class="empty">Data anggota belum tersedia.</td></tr>
            @endforelse
        </tbody>
    </table></div>
    {{ $anggotas->links() }}
</section>
@endsection
