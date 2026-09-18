<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Pengeluaran;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Iuran;
use App\Exports\LaporanKasExport;
use App\Exports\LaporanSiswaExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Exception;
use Carbon\Carbon;

class LaporanController extends Controller
{
    /**
     * Laporan Kas Total
     */
    public function kas(Request $request)
    {
        try {
            $totalPemasukan = Transaksi::where('status', 'confirmed')->sum('jumlah');
            $totalPengeluaran = Pengeluaran::where('status', 'approved')->sum('jumlah');
            $saldo = $totalPemasukan - $totalPengeluaran;

            return response()->json([
                'success' => true,
                'message' => 'Laporan kas berhasil diambil',
                'data' => [
                    'total_pemasukan' => (float) $totalPemasukan,
                    'total_pengeluaran' => (float) $totalPengeluaran,
                    'saldo' => (float) $saldo,
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil laporan kas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Laporan Per Bulan
     */
    public function perBulan(Request $request, int $bulan, int $tahun)
    {
        try {
            // Pemasukan bulan ini
            $pemasukan = Transaksi::where('status', 'confirmed')
                                  ->whereMonth('tanggal_bayar', $bulan)
                                  ->whereYear('tanggal_bayar', $tahun)
                                  ->sum('jumlah');

            // Pengeluaran bulan ini
            $pengeluaran = Pengeluaran::where('status', 'approved')
                                      ->whereMonth('tanggal', $bulan)
                                      ->whereYear('tanggal', $tahun)
                                      ->sum('jumlah');

            // Detail transaksi bulan ini
            $transaksi = Transaksi::with(['siswa.user', 'iuran.kelas'])
                                  ->where('status', 'confirmed')
                                  ->whereMonth('tanggal_bayar', $bulan)
                                  ->whereYear('tanggal_bayar', $tahun)
                                  ->orderBy('tanggal_bayar', 'desc')
                                  ->get();

            // Detail pengeluaran bulan ini
            $pengeluaranList = Pengeluaran::with(['createdBy', 'approvedBy'])
                                          ->where('status', 'approved')
                                          ->whereMonth('tanggal', $bulan)
                                          ->whereYear('tanggal', $tahun)
                                          ->orderBy('tanggal', 'desc')
                                          ->get();

            $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F');

            return response()->json([
                'success' => true,
                'message' => 'Laporan bulan berhasil diambil',
                'data' => [
                    'bulan' => $namaBulan,
                    'tahun' => $tahun,
                    'total_pemasukan' => (float) $pemasukan,
                    'total_pengeluaran' => (float) $pengeluaran,
                    'saldo' => (float) ($pemasukan - $pengeluaran),
                    'transaksi' => $transaksi,
                    'pengeluaran' => $pengeluaranList,
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil laporan bulan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Laporan Per Siswa
     */
    public function perSiswa(Request $request, int $siswaId)
    {
        try {
            $siswa = Siswa::with(['user', 'kelas'])->find($siswaId);

            if (!$siswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa tidak ditemukan'
                ], 404);
            }

            $transaksi = Transaksi::with(['iuran.kelas'])
                                  ->where('siswa_id', $siswaId)
                                  ->orderBy('tanggal_bayar', 'desc')
                                  ->get();

            $totalBayar = $transaksi->where('status', 'confirmed')->sum('jumlah');
            $totalPending = $transaksi->where('status', 'pending')->sum('jumlah');

            return response()->json([
                'success' => true,
                'message' => 'Laporan siswa berhasil diambil',
                'data' => [
                    'siswa' => $siswa,
                    'total_transaksi' => $transaksi->count(),
                    'total_bayar' => (float) $totalBayar,
                    'total_pending' => (float) $totalPending,
                    'transaksi' => $transaksi,
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil laporan siswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Laporan Per Kelas
     */
    public function perKelas(Request $request, int $kelasId)
    {
        try {
            $kelas = Kelas::find($kelasId);

            if (!$kelas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelas tidak ditemukan'
                ], 404);
            }

            // Ambil semua siswa di kelas
            $siswa = Siswa::with(['user'])->where('kelas_id', $kelasId)->get();

            // Ambil semua iuran kelas
            $iuran = Iuran::where('kelas_id', $kelasId)->where('is_active', true)->get();

            // Total pemasukan dari kelas ini
            $totalPemasukan = Transaksi::whereHas('siswa', function ($q) use ($kelasId) {
                                        $q->where('kelas_id', $kelasId);
                                    })
                                    ->where('status', 'confirmed')
                                    ->sum('jumlah');

            // Total pengeluaran kelas ini
            $totalPengeluaran = Pengeluaran::where('kelas_id', $kelasId)
                                          ->where('status', 'approved')
                                          ->sum('jumlah');

            // Status pembayaran per siswa
            $statusSiswa = [];
            foreach ($siswa as $s) {
                $sudahBayar = Transaksi::where('siswa_id', $s->id)
                                       ->whereIn('iuran_id', $iuran->pluck('id'))
                                       ->where('status', 'confirmed')
                                       ->count();

                $totalIuran = $iuran->count();
                $statusSiswa[] = [
                    'siswa_id' => $s->id,
                    'nama' => $s->user->name,
                    'nis' => $s->nis,
                    'total_bayar' => $sudahBayar,
                    'total_iuran' => $totalIuran,
                    'status' => $sudahBayar == $totalIuran ? 'lunas' : 'belum_lunas',
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Laporan kelas berhasil diambil',
                'data' => [
                    'kelas' => $kelas,
                    'total_siswa' => $siswa->count(),
                    'total_iuran' => $iuran->count(),
                    'total_pemasukan' => (float) $totalPemasukan,
                    'total_pengeluaran' => (float) $totalPengeluaran,
                    'saldo' => (float) ($totalPemasukan - $totalPengeluaran),
                    'status_siswa' => $statusSiswa,
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil laporan kelas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export PDF
     */
        public function exportPdf(Request $request)
        {
            try {
                $type = $request->get('type', 'kas');
                $data = [];

                if ($type == 'kas') {
                    // ... (kode lama type kas tetap)
                    $totalPemasukan = Transaksi::where('status', 'confirmed')->sum('jumlah');
                    $totalPengeluaran = Pengeluaran::where('status', 'approved')->sum('jumlah');
                    
                    $data = [
                        'title' => 'Laporan Kas Kelas',
                        'total_pemasukan' => $totalPemasukan,
                        'total_pengeluaran' => $totalPengeluaran,
                        'saldo' => $totalPemasukan - $totalPengeluaran,
                        'transaksi' => Transaksi::with(['siswa.user', 'iuran.kelas'])
                                                ->where('status', 'confirmed')
                                                ->orderBy('tanggal_bayar', 'desc')
                                                ->limit(50)
                                                ->get(),
                    ];
                    
                    $pdf = Pdf::loadView('laporan.kas', $data);
                    return $pdf->download('laporan_kas.pdf');

                } elseif ($type == 'siswa_list') {
                    // TAMBAHAN: Export Data Siswa ke PDF
                    $siswa = Siswa::with(['user', 'kelas'])->get();
                    $data = [
                        'title' => 'Data Siswa',
                        'siswa' => $siswa
                    ];

                    $pdf = Pdf::loadView('laporan.siswa_list', $data); // Pastikan view ini ada
                    return $pdf->download('data_siswa.pdf');

                } elseif ($type == 'siswa' && $request->has('siswa_id')) {
                    // ... (kode lama type siswa tetap)
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Parameter export tidak valid'
                    ], 422);
                }

            } catch (Exception $e) {
                // ... (catch error)
            }
        }

    /**
     * Export Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            $type = $request->get('type', 'kas');
            $bulan = $request->get('bulan');
            $tahun = $request->get('tahun');

            if ($type == 'kas') {
                return Excel::download(new LaporanKasExport($bulan, $tahun), 'laporan_kas.xlsx');
            } elseif ($type == 'siswa_list') {
                // TAMBAHAN: Export Data Siswa ke Excel
                // Catatan: Anda harus bikin Class Export SiswaListExport dulu
                return Excel::download(new \App\Exports\SiswaListExport, 'data_siswa.xlsx');
            } elseif ($type == 'siswa' && $request->has('siswa_id')) {
                return Excel::download(new LaporanSiswaExport($request->siswa_id), 'laporan_siswa.xlsx');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter export tidak valid'
                ], 422);
            }

        } catch (Exception $e) {
            // ... (catch error)
        }
    }
}