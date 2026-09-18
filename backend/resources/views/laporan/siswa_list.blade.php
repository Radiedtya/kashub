<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Siswa</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1 { text-align: center; margin-bottom: 20px; text-transform: uppercase; }
        p { text-align: center; margin-bottom: 20px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; text-transform: uppercase; font-size: 10px; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>Diunduh pada: {{ date('d M Y H:i:s') }}</p>
    
    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>NIS</th>
                <th>NISN</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Email</th>
                <th>No HP</th>
                <th>Nama Ortu</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($siswa as $s)
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td>{{ $s->nis ?? '-' }}</td>
                <td>{{ $s->nisn ?? '-' }}</td>
                <td>{{ $s->user->name ?? '-' }}</td>
                <td>{{ $s->kelas->nama ?? '-' }}</td>
                <td>{{ $s->user->email ?? '-' }}</td>
                <td>{{ $s->user->no_hp ?? '-' }}</td>
                <td>{{ $s->nama_ortu ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>