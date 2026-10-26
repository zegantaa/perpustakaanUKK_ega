<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'PERPUSTAKAAN DIGITAL' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/member.css') }}">
    <link rel="stylesheet" href="{{ asset('css/member-loan.css') }}">
    <link rel="stylesheet" href="{{ asset('css/book-cover.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cover-extra.css') }}">
    <link rel="stylesheet" href="{{ asset('css/member-reference.css') }}">
</head>
<body class="member-site">
<header class="member-navbar">
    <div class="member-nav-inner">
        <a class="member-brand" href="{{ route('user.dashboard') }}"><span class="member-brand-mark">PD</span><span><strong>PERPUSTAKAAN DIGITAL</strong><small>Digital Library</small></span></a>
        <button class="member-menu-toggle" type="button" aria-label="Buka menu">☰</button>
        <nav class="member-nav-links">
            <a class="{{ request()->routeIs('user.dashboard') ? 'active' : '' }}" href="{{ route('user.dashboard') }}">Beranda</a>
            <a class="{{ request()->routeIs('user.books*') ? 'active' : '' }}" href="{{ route('user.books') }}">Koleksi Buku</a>
            <a class="{{ request()->routeIs('user.loans') ? 'active' : '' }}" href="{{ route('user.loans') }}">Peminjaman Saya</a>
            <a class="{{ request()->routeIs('user.history') ? 'active' : '' }}" href="{{ route('user.history') }}">Riwayat</a>
        </nav>
        <div class="member-profile-wrap">
            <button class="member-profile-button" type="button"><span class="member-avatar">{{ strtoupper(substr(auth()->user()->nama_lengkap, 0, 1)) }}</span><span class="member-profile-name">{{ auth()->user()->nama_lengkap }}</span><span class="member-profile-role">Role: Anggota</span><span class="member-chevron">⌄</span></button>
            <div class="member-profile-menu"><a href="{{ route('user.profile') }}">Profil Saya</a><a href="{{ route('user.loans') }}">Peminjaman Saya</a><a href="{{ route('user.history') }}">Riwayat</a><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form></div>
        </div>
    </div>
</header>
<main class="member-main">
    @if(session('success'))<div class="member-alert success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="member-alert danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="member-alert danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
</main>
<footer class="member-footer"><div><strong>PERPUSTAKAAN DIGITAL</strong><p>Tempat membaca, belajar, dan menemukan pengetahuan.</p></div><nav><a href="{{ route('user.dashboard') }}">Beranda</a><a href="{{ route('user.books') }}">Koleksi Buku</a><a href="{{ route('user.loans') }}">Peminjaman</a><a href="{{ route('user.history') }}">Riwayat</a></nav><small>© {{ date('Y') }} Perpustakaan Digital</small></footer>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
