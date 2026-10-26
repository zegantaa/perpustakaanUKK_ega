<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Perpustakaan</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; color: #1d2939; font: 14px Arial, sans-serif; }
        .page { max-width: 1100px; margin: 0 auto; padding: 32px 0; }
        .actions { display: flex; gap: 8px; margin-bottom: 24px; }
        button, a { border: 0; background: #1d5d7e; color: #fff; cursor: pointer; padding: 10px 14px; text-decoration: none; font-size: 13px; }
        a { background: #e8eef2; color: #29445a; }
        header { border-bottom: 2px solid #1d5d7e; margin-bottom: 20px; padding-bottom: 14px; }
        h1 { margin: 0 0 6px; font-size: 24px; }
        p { color: #667788; margin: 0; }
        .summary { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-bottom: 24px; }
        .summary div { border: 1px solid #dfe5ea; padding: 12px; }
        .summary span, .summary strong { display: block; }
        .summary span { color: #667788; font-size: 12px; }
        .summary strong { font-size: 18px; margin-top: 6px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #dfe5ea; padding: 9px 10px; text-align: left; }
        th { background: #eef3f6; font-size: 12px; text-transform: uppercase; }
        .status { text-transform: capitalize; }
        @media (max-width: 700px) {
            .page { padding: 18px; }
            .summary { grid-template-columns: repeat(2, 1fr); }
            table { font-size: 12px; }
        }
        @media print {
            .page { max-width: none; padding: 0; }
            .actions { display: none; }
            @page { size: landscape; margin: 12mm; }
        }
    </style>
</head>
<body>
<main class="page">
    <div class="actions">
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
        <a href="{{ route('laporan.index') }}">Kembali</a>
    </div>
    <header>
        <h1>Laporan Sirkulasi Perpustakaan</h1>
        <p>Dicetak pada {{ now()->format('d/m/Y H:i') }}</p>
    </header>
    <section class="summary">
        <div><span>Total transaksi</span><strong>{{ $ringkasan['total'] }}</strong></div>
        <div><span>Sedang dipinjam</span><strong>{{ $ringkasan['dipinjam'] }}</strong></div>
        <div><span>Dikembalikan</span><strong>{{ $ringkasan['dikembalikan'] }}</strong></div>
        <div><span>Terlambat</span><strong>{{ $ringkasan['terlambat'] }}</strong></div>
        <div><span>Total denda</span><strong>Rp {{ number_format($ringkasan['denda'], 0, ',', '.') }}</strong></div>
    </section>
    <table>
        <thead><tr><th>No.</th><th>Peminjam</th><th>Judul Buku</th><th>Tanggal Pinjam</th><th>Jatuh Tempo</th><th>Tanggal Kembali</th><th>Status</th><th>Denda</th></tr></thead>
        <tbody>
        @forelse($transaksis as $transaksi)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $transaksi->anggota?->nama_lengkap ?? '-' }}</td>
                <td>{{ $transaksi->buku?->judul_buku ?? '-' }}</td>
                <td>{{ $transaksi->tanggal_pinjam?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $transaksi->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $transaksi->tanggal_kembali?->format('d/m/Y') ?? '-' }}</td>
                <td class="status">{{ $transaksi->status }}</td>
                <td>Rp {{ number_format($transaksi->denda, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="8">Belum ada transaksi.</td></tr>
        @endforelse
        </tbody>
    </table>
</main>
</body>
</html>