<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar | Perpustakaan Sekolah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="login-page">
    <div class="login-box register-box">
        <div class="brand login-brand">
            <div class="brand-mark">PS</div>
            <div><strong>Perpustakaan</strong><small>Sekolah</small></div>
        </div>
        <h1>Buat akun baru</h1>
        <p class="muted">Daftarkan akun anggota untuk mengakses koleksi perpustakaan.</p>
        @if($errors->any())
            <div class="alert danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('register.process') }}">
            @csrf
            <label>Username<input type="text" name="username" value="{{ old('username') }}" required autofocus></label>
            <label>Nama Lengkap<input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required></label>
            <label>Email<input type="email" name="email" value="{{ old('email') }}" required></label>
            <label>NIS<input type="text" name="nis" value="{{ old('nis') }}" required></label>
            <label>Kelas<input type="text" name="kelas" value="{{ old('kelas') }}" placeholder="Contoh: XI RPL 1" required></label>
            <label>Alamat<textarea name="alamat" rows="3">{{ old('alamat') }}</textarea></label>
            <label>Password<input type="password" name="password" required></label>
            <label>Konfirmasi Password<input type="password" name="password_confirmation" required></label>
            <button class="button primary wide" type="submit">Daftar</button>
        </form>
        <p class="register-link">Sudah memiliki akun? <a href="{{ route('login') }}">Masuk di sini</a></p>
    </div>
</body>
</html>
