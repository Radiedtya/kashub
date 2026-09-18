<?php

namespace App\Exports;

use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SiswaListExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Ambil data yang mau di-export
     */
    public function collection(): Collection
    {
        // Kita eager load relasinya biar gak N+1 query
        return Siswa::with(['user', 'kelas'])->get();
    }

    /**
     * Header kolom di file Excel
     */
    public function headings(): array
    {
        return [
            'NIS',
            'NISN',
            'Nama Siswa',
            'Kelas',
            'Email',
            'No HP',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Alamat',
            'Nama Ortu',
            'No HP Ortu',
        ];
    }

    /**
     * Mapping data tiap baris
     */
    public function map($siswa): array
    {
        return [
            $siswa->nis,
            $siswa->nisn,
            $siswa->user->name ?? '-',
            $siswa->kelas->nama ?? '-',
            $siswa->user->email ?? '-',
            $siswa->user->no_hp ?? '-',
            $siswa->tempat_lahir ?? '-',
            $siswa->tanggal_lahir ? $siswa->tanggal_lahir->format('d-m-Y') : '-',
            $siswa->alamat ?? '-',
            $siswa->nama_ortu ?? '-',
            $siswa->no_hp_ortu ?? '-',
        ];
    }
}