<?php

namespace App\Exports;

use App\Models\Siswa;
use App\Models\Transaksi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
// use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Collection;

class LaporanSiswaExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $siswaId;

    public function __construct($siswaId)
    {
        $this->siswaId = $siswaId;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection(): Collection
    {
        $siswa = Siswa::with(['user', 'kelas'])->find($this->siswaId);

        if (!$siswa) {
            return new Collection([]);
        }

        $data = [];

        // Header
        $data[] = ['LAPORAN SISWA'];
        $data[] = ['Nama: ' . $siswa->user->name];
        $data[] = ['NIS: ' . $siswa->nis];
        $data[] = ['Kelas: ' . $siswa->kelas->nama ?? '-'];
        $data[] = [''];
        $data[] = ['Tanggal', 'Bulan', 'Tahun', 'Jumlah', 'Status'];

        $transaksi = Transaksi::with(['iuran'])
                              ->where('siswa_id', $this->siswaId)
                              ->orderBy('tanggal_bayar', 'desc')
                              ->get();

        foreach ($transaksi as $t) {
            $data[] = [
                $t->tanggal_bayar,
                $t->iuran->bulan ?? '-',
                $t->iuran->tahun ?? '-',
                $t->jumlah,
                $t->status,
            ];
        }

        // Summary
        $data[] = [''];
        $data[] = ['Total Transaksi: ' . $transaksi->count()];
        $data[] = ['Total Bayar: ' . $transaksi->where('status', 'confirmed')->sum('jumlah')];

        return new Collection($data);
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet): array
    {
        // Merge cells
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');
        $sheet->mergeCells('A3:E3');
        $sheet->mergeCells('A4:E4');

        // Style title
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(12);

        // Style header
        $sheet->getStyle('A6:E6')->getFont()->setBold(true);

        // Borders
        $sheet->getStyle('A6:E' . $sheet->getHighestRow())->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        return [];
    }
}