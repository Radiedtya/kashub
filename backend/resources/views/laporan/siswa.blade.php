<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Siswa</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .info { margin: 20px 0; }
        .info td { border: none; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>

    <table class="info">
        <tr>
            <td>Nama</td>
            <td>{{ $siswa->user->name }}</td>
        </tr>
        <tr>
            <td>NIS</td>
            <td>{{ $siswa->nis }}</td>
        </tr>
        <tr>
            <td>Kelas</td>
            <td>{{ $siswa->kelas->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td>Total Bayar</td>
            <td>Rp {{ number_format($total_bayar, 0, ',', '.') }}</td>
        </tr>
    </table>

    <h3>Riwayat Transaksi</h3>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Bulan</th>
                <th>Tahun</th>
                <th>Jumlah</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi as $t)
            <tr>
                <td>{{ $t->tanggal_bayar }}</td>
                <td>{{ $t->iuran->bulan ?? '-' }}</td>
                <td>{{ $t->iuran->tahun ?? '-' }}</td>
                <td>Rp {{ number_format($t->jumlah, 0, ',', '.') }}</td>
                <td>{{ $t->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>