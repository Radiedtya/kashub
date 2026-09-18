<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Kas Kelas</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .summary { margin: 20px 0; }
        .summary td { border: none; font-weight: bold; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>Tanggal: {{ now()->format('d/m/Y H:i') }}</p>

    <table class="summary">
        <tr>
            <td>Total Pemasukan</td>
            <td class="text-right">Rp {{ number_format($total_pemasukan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Pengeluaran</td>
            <td class="text-right">Rp {{ number_format($total_pengeluaran, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Saldo</td>
            <td class="text-right">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
        </tr>
    </table>

    <h3>Detail Transaksi</h3>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Siswa</th>
                <th>Kelas</th>
                <th>Jumlah</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi as $t)
            <tr>
                <td>{{ $t->tanggal_bayar }}</td>
                <td>{{ $t->siswa->user->name ?? '-' }}</td>
                <td>{{ $t->iuran->kelas->nama ?? '-' }}</td>
                <td>Rp {{ number_format($t->jumlah, 0, ',', '.') }}</td>
                <td>{{ $t->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>