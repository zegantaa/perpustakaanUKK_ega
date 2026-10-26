<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Perpustakaan Sekolah' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/book-cover.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cover-extra.css') }}">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand"><div class="brand-mark">PS</div><div><strong>Perpustakaan</strong><small>Sekolah</small></div></div>
        <nav class="nav-menu">
            <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
            <a class="{{ request()->routeIs('buku.index', 'buku.create', 'buku.show', 'buku.edit') ? 'active' : '' }}" href="{{ route('buku.index') }}">Data Buku</a>
            <a class="{{ request()->routeIs('buku.edit-menu') ? 'active' : '' }}" href="{{ route('buku.edit-menu') }}">Edit Buku</a>
            <a class="{{ request()->routeIs('anggota.index', 'anggota.create', 'anggota.show', 'anggota.edit') ? 'active' : '' }}" href="{{ route('anggota.index') }}">Data Anggota</a>
            <a class="{{ request()->routeIs('anggota.edit-menu') ? 'active' : '' }}" href="{{ route('anggota.edit-menu') }}">Edit Anggota</a>
            <a class="{{ request()->routeIs('transaksi.*') ? 'active' : '' }}" href="{{ route('transaksi.index') }}">Transaksi Peminjaman</a>
            <a class="{{ request()->routeIs('pengembalian.*') ? 'active' : '' }}" href="{{ route('pengembalian.index') }}">Pengembalian</a>
            <a class="{{ request()->routeIs('riwayat.*') ? 'active' : '' }}" href="{{ route('riwayat.index') }}">Riwayat</a>
            <a class="{{ request()->routeIs('laporan.*') ? 'active' : '' }}" href="{{ route('laporan.index') }}">Laporan</a>
            @if(auth()->user()->role === 'admin')<a class="{{ request()->routeIs('user.*') ? 'active' : '' }}" href="{{ route('user.index') }}">Data User</a>@endif
        </nav>
        <form method="POST" action="{{ route('logout') }}" class="logout-form">@csrf<button type="submit">Keluar</button></form>
    </aside>
    <main class="main-content">
        <header class="topbar"><button class="menu-toggle" type="button" aria-label="Buka menu">☰</button><div><span class="eyebrow">Sistem Informasi</span><strong>Perpustakaan Sekolah</strong></div><div class="profile"><span>{{ auth()->user()->nama_lengkap }}</span><small>{{ ucfirst(auth()->user()->role) }}</small></div></header>
        <div class="page-content">
            @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert danger">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="alert danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </div>
    </main>
</div>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>