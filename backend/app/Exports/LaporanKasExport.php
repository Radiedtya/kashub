<?php

namespace App\Exports;

use App\Models\Transaksi;
use App\Models\Pengeluaran;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Collection;

class LaporanKasExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $bulan;
    protected $tahun;

    public function __construct($bulan = null, $tahun = null)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection(): Collection
    {
        $data = [];

        // Header
        $data[] = ['LAPORAN KAS KELAS'];
        $data[] = ['Periode: ' . ($this->bulan && $this->tahun ? $this->bulan . '/' . $this->tahun : 'Semua Periode')];
        $data[] = [''];
        $data[] = ['', 'Pemasukan', 'Pengeluaran', 'Saldo'];

        // Query pemasukan
        $pemasukanQuery = Transaksi::where('status', 'confirmed');
        if ($this->bulan && $this->tahun) {
            $pemasukanQuery->whereMonth('tanggal_bayar', $this->bulan)
                          ->whereYear('tanggal_bayar', $this->tahun);
        }
        $totalPemasukan = $pemasukanQuery->sum('jumlah');

        // Query pengeluaran
        $pengeluaranQuery = Pengeluaran::where('status', 'approved');
        if ($this->bulan && $this->tahun) {
            $pengeluaranQuery->whereMonth('tanggal', $this->bulan)
                           ->whereYear('tanggal', $this->tahun);
        }
        $totalPengeluaran = $pengeluaranQuery->sum('jumlah');

        $saldo = $totalPemasukan - $totalPengeluaran;

        $data[] = ['Total', $totalPemasukan, $totalPengeluaran, $saldo];
        $data[] = [''];

        // Detail transaksi
        $data[] = ['DETAIL TRANSAKSI'];
        $data[] = ['Tanggal', 'Siswa', 'Kelas', 'Jumlah', 'Status'];

        $transaksi = Transaksi::with(['siswa.user', 'iuran.kelas'])
                              ->where('status', 'confirmed')
                              ->orderBy('tanggal_bayar', 'desc');

        if ($this->bulan && $this->tahun) {
            $transaksi->whereMonth('tanggal_bayar', $this->bulan)
                     ->whereYear('tanggal_bayar', $this->tahun);
        }

        foreach ($transaksi->get() as $t) {
            $data[] = [
                $t->tanggal_bayar,
                $t->siswa->user->name ?? '-',
                $t->iuran->kelas->nama ?? '-',
                $t->jumlah,
                $t->status,
            ];
        }

        return new Collection($data);
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet): array
    {
        // Merge cells untuk title
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');
        $sheet->mergeCells('A4:A5');

        // Style title
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);

        // Style header pemasukan/pengeluaran
        $sheet->getStyle('A4:E4')->getFont()->setBold(true);

        // Style header detail transaksi (row 8)
        $sheet->getStyle('A8:E8')->getFont()->setBold(true);
        $sheet->getStyle('A8:E8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Center alignment header
        $sheet->getStyle('A4:D5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Borders untuk seluruh tabel detail transaksi (kolom A-E)
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A8:E' . $lastRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Borders untuk ringkasan (row 4-5)
        $sheet->getStyle('A4:D5')
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // WAJIB return array (boleh kosong) — styling sudah di-apply langsung ke $sheet
        return [];
    }
}