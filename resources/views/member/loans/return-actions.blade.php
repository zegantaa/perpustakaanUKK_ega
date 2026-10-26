@php($activeLoans = $loans->getCollection()->whereIn('status', ['dipinjam', 'terlambat']))
@if($activeLoans->isNotEmpty())
<section class="member-table-panel return-actions-panel">
    <div class="member-section-heading"><div><span class="member-kicker">Pengembalian mandiri</span><h2>Buku yang sedang dipinjam</h2></div></div>
    <div class="member-table-wrap">
        <table class="member-table">
            <thead><tr><th>Buku</th><th>Batas Kembali</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            @foreach($activeLoans as $loan)
                <tr>
                    <td><strong>{{ $loan->buku->judul_buku }}</strong><small>{{ $loan->buku->penulis }}</small></td>
                    <td>{{ $loan->tanggal_jatuh_tempo?->format('d M Y') ?? '-' }}</td>
                    <td><span class="member-badge {{ $loan->status }}">{{ ucfirst($loan->status) }}</span></td>
                    <td><form method="POST" action="{{ route('user.loans.return', $loan) }}" onsubmit="return confirm('Kembalikan buku ini sekarang?')">@csrf @method('PATCH')<button class="member-button primary" type="submit">Mengembalikan Buku</button></form></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif
