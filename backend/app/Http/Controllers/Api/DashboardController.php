<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Iuran;
use App\Models\Transaksi;
use App\Models\Pengeluaran;
use App\Models\Keterlambatan;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Exception;

class DashboardController extends Controller
{

    /**
     * Dashboard untuk Guru (Scoped per Kelas)
     */
    private function getGuruDashboard(Request $request)
    {
        $user = $request->user();
        $kelasId = $user->kelas_id; // Ambil kelas_id dari Guru yang login

        // Total siswa HANYA di kelasnya
        $totalSiswa = Siswa::where('kelas_id', $kelasId)->count();

        // Total kas HANYA dari transaksi & pengeluaran kelasnya
        $totalPemasukan = Transaksi::whereHas('siswa', function($q) use ($kelasId) {
            $q->where('kelas_id', $kelasId);
        })->where('status', 'confirmed')->sum('jumlah');
        
        $totalPengeluaran = Pengeluaran::where('kelas_id', $kelasId)
                                        ->where('status', 'approved')
                                        ->sum('jumlah');
        $totalKas = $totalPemasukan - $totalPengeluaran;

        // Siswa telat bayar HANYA di kelasnya
        $siswaTelat = Keterlambatan::whereHas('siswa', function($q) use ($kelasId) {
            $q->where('kelas_id', $kelasId);
        })->where('status', 'belum_bayar')->distinct('siswa_id')->count('siswa_id');

        // Total iuran aktif HANYA di kelasnya
        $totalIuranAktif = Iuran::where('kelas_id', $kelasId)->where('is_active', true)->count();

        // Data grafik pembayaran & pengeluaran per bulan (6 bulan terakhir) HANYA kelasnya
        $grafikPembayaran = $this->getGrafikPembayaranByKelas($kelasId);
        $grafikPengeluaran = $this->getGrafikPengeluaranByKelas($kelasId);

        // Daftar siswa telat (top 10) HANYA di kelasnya
        $daftarSiswaTelat = Keterlambatan::with(['siswa.user', 'siswa.kelas'])
                                         ->whereHas('siswa', function($q) use ($kelasId) {
                                            $q->where('kelas_id', $kelasId);
                                         })
                                         ->where('status', 'belum_bayar')
                                         ->orderBy('hari_telat', 'desc')
                                         ->limit(10)
                                         ->get();

        // Status Iuran buat Chart Donut HANYA kelasnya
        $statusIuran = [
            'lunas' => Transaksi::whereHas('siswa', function($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            })->where('status', 'confirmed')->count(),
            'pending' => Transaksi::whereHas('siswa', function($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            })->where('status', 'pending')->count(),
            'ditolak' => Transaksi::whereHas('siswa', function($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            })->where('status', 'rejected')->count(),
        ];

        // Iuran Aktif List (5 terbaru) HANYA kelasnya
        $iuranAktifList = Iuran::where('kelas_id', $kelasId)->where('is_active', true)->latest()->limit(5)->get();

        // Transaksi Terbaru (5 terbaru) HANYA kelasnya
        $transaksiTerbaru = Transaksi::with(['siswa.user', 'iuran.kelas'])
                                     ->whereHas('siswa', function($q) use ($kelasId) {
                                        $q->where('kelas_id', $kelasId);
                                     })
                                     ->latest()
                                     ->limit(5)
                                     ->get();

        // Notifikasi belum dibaca
        $notifikasiBelumDibaca = Notifikasi::where('user_id', $user->id)
                                           ->where('is_read', false)
                                           ->count();

        // 5 notifikasi terbaru
        $notifikasiTerbaru = Notifikasi::where('user_id', $user->id)
                                       ->orderBy('created_at', 'desc')
                                       ->limit(5)
                                       ->get();

        return [
            'role' => 'guru',
            'statistik' => [
                'total_siswa' => $totalSiswa,
                'total_kas' => (float) $totalKas,
                'siswa_telat' => $siswaTelat,
                'total_iuran_aktif' => $totalIuranAktif,
            ],
            'grafik' => [
                'pembayaran_per_bulan' => $grafikPembayaran,
                'pengeluaran_per_bulan' => $grafikPengeluaran,
            ],
            'status_iuran' => $statusIuran,
            'siswa_telat' => $daftarSiswaTelat,
            'iuran_aktif' => $iuranAktifList,
            'transaksi_terbaru' => $transaksiTerbaru,
            'notifikasi' => [
                'belum_dibaca' => $notifikasiBelumDibaca,
                'terbaru' => $notifikasiTerbaru,
            ],
        ];
    }

    /**
     * Dashboard untuk Bendahara
     */
    private function getBendaharaDashboard(Request $request)
    {
        $user = $request->user();

        // Total kas
        $totalPemasukan = Transaksi::where('status', 'confirmed')->sum('jumlah');
        $totalPengeluaran = Pengeluaran::where('status', 'approved')->sum('jumlah');
        $totalKas = $totalPemasukan - $totalPengeluaran;

        // Transaksi pending (belum dikonfirmasi)
        $transaksiPending = Transaksi::where('status', 'pending')->count();

        // Pengeluaran pending (belum disetujui)
        $pengeluaranPending = Pengeluaran::where('status', 'pending')->count();

        // Pengeluaran bulan ini
        $pengeluaranBulanIni = Pengeluaran::where('status', 'approved')
                                          ->whereMonth('tanggal', now()->month)
                                          ->whereYear('tanggal', now()->year)
                                          ->sum('jumlah');

        // Pemasukan bulan ini
        $pemasukanBulanIni = Transaksi::where('status', 'confirmed')
                                      ->whereMonth('tanggal_bayar', now()->month)
                                      ->whereYear('tanggal_bayar', now()->year)
                                      ->sum('jumlah');

        // Total siswa
        $totalSiswa = Siswa::count();

        // Data grafik pemasukan vs pengeluaran (6 bulan terakhir)
        $grafikKas = $this->getGrafikKas();

        // --- TAMBAHAN UNTUK UI CHART ---
        $statusIuran = [
            'lunas' => Transaksi::where('status', 'confirmed')->count(),
            'pending' => $transaksiPending,
            'ditolak' => Transaksi::where('status', 'rejected')->count(),
        ];

        // Daftar transaksi pending (top 10)
        $daftarPending = Transaksi::with(['siswa.user', 'iuran.kelas'])
                                  ->where('status', 'pending')
                                  ->orderBy('created_at', 'asc')
                                  ->limit(10)
                                  ->get();
        // ------------------------------

        // Notifikasi
        $notifikasiBelumDibaca = Notifikasi::where('user_id', $user->id)->where('is_read', false)->count();
        $notifikasiTerbaru = Notifikasi::where('user_id', $user->id)->orderBy('created_at', 'desc')->limit(5)->get();

        return [
            'role' => 'bendahara',
            'statistik' => [
                'total_kas' => (float) $totalKas,
                'transaksi_pending' => $transaksiPending,
                'pengeluaran_pending' => $pengeluaranPending,
                'pemasukan_bulan_ini' => (float) $pemasukanBulanIni,
                'pengeluaran_bulan_ini' => (float) $pengeluaranBulanIni,
                'total_siswa' => $totalSiswa,
            ],
            'grafik' => $grafikKas,
            'status_iuran' => $statusIuran,
            'transaksi_pending_list' => $daftarPending,
            'notifikasi' => [
                'belum_dibaca' => $notifikasiBelumDibaca,
                'terbaru' => $notifikasiTerbaru,
            ],
        ];
    }

    /**
     * Dashboard untuk Siswa
     */
    private function getSiswaDashboard(Request $request)
    {
        $user = $request->user();

        // Cari data siswa
        $siswa = Siswa::with(['kelas'])->where('user_id', $user->id)->first();

        if (!$siswa) {
            return [
                'role' => 'siswa',
                'message' => 'Data siswa tidak ditemukan',
            ];
        }

        // Total transaksi (confirmed)
        $totalTransaksi = Transaksi::where('siswa_id', $siswa->id)
                                   ->where('status', 'confirmed')
                                   ->count();

        $totalIuranKelas = Iuran::where('kelas_id', $siswa->kelas_id)
                                ->where('is_active', true)
                                ->count();                           
                                   
        $totalBayar = Transaksi::where('siswa_id', $siswa->id)
                               ->where('status', 'confirmed')
                               ->sum('jumlah');

        // Total transaksi pending
        $transaksiPending = Transaksi::where('siswa_id', $siswa->id)
                                     ->where('status', 'pending')
                                     ->count();

        // Total keterlambatan
        $totalKeterlambatan = Keterlambatan::where('siswa_id', $siswa->id)
                                           ->where('status', 'belum_bayar')
                                           ->count();

        $totalDenda = Keterlambatan::where('siswa_id', $siswa->id)
                                   ->sum('denda');

        // FIX: Ambil iuran TERBARU yang aktif di kelas tersebut (bukan cuma bulan ini)
        $iuranBulanIni = Iuran::where('kelas_id', $siswa->kelas_id)
                              ->where('is_active', true)
                              ->orderBy('tahun', 'desc')
                              ->orderBy('bulan', 'desc')
                              ->first();

        $statusBayar = 'belum_bayar';
        $tanggalBayar = null;

        if ($iuranBulanIni) {
            $transaksi = Transaksi::where('siswa_id', $siswa->id)
                                  ->where('iuran_id', $iuranBulanIni->id)
                                  ->first();

            if ($transaksi) {
                $statusBayar = $transaksi->status;
                $tanggalBayar = $transaksi->tanggal_bayar;
            }
        }

        // Tambahan: Grafik Pembayaran 6 Bulan Terakhir
        $grafikPembayaran = [
            'labels' => [],
            'data' => []
        ];
        for ($i = 5; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $grafikPembayaran['labels'][] = $bulan->format('M Y');

            $total = Transaksi::where('siswa_id', $siswa->id)
                              ->where('status', 'confirmed')
                              ->whereMonth('tanggal_bayar', $bulan->month)
                              ->whereYear('tanggal_bayar', $bulan->year)
                              ->sum('jumlah');
            $grafikPembayaran['data'][] = (float) $total;
        }

        // Riwayat transaksi (5 terakhir)
        $riwayatTransaksi = Transaksi::with(['iuran.kelas'])
                                     ->where('siswa_id', $siswa->id)
                                     ->orderBy('created_at', 'desc')
                                     ->limit(5)
                                     ->get();

        // Notifikasi belum dibaca
        $notifikasiBelumDibaca = Notifikasi::where('user_id', $user->id)
                                           ->where('is_read', false)
                                           ->count();

        // 5 notifikasi terbaru
        $notifikasiTerbaru = Notifikasi::where('user_id', $user->id)
                                       ->orderBy('created_at', 'desc')
                                       ->limit(5)
                                       ->get();

        return [
            'role' => 'siswa',
            'profil' => [
                'nama' => $user->name,
                'nis' => $siswa->nis,
                'kelas' => $siswa->kelas->nama ?? '-',
            ],
            'statistik' => [
                'total_transaksi' => $totalTransaksi,
                'total_bayar' => (float) $totalBayar,
                'transaksi_pending' => $transaksiPending,
                'total_keterlambatan' => $totalKeterlambatan,
                'total_denda' => (float) $totalDenda,
                'total_iuran_kelas' => $totalIuranKelas,
            ],
            'grafik' => $grafikPembayaran,
            'status_bayar_bulan_ini' => [
                'iuran' => $iuranBulanIni ? "{$iuranBulanIni->bulan}/{$iuranBulanIni->tahun}" : null,
                'status' => $statusBayar,
                'tanggal_bayar' => $tanggalBayar,
            ],
            'riwayat_transaksi' => $riwayatTransaksi,
            'notifikasi' => [
                'belum_dibaca' => $notifikasiBelumDibaca,
                'terbaru' => $notifikasiTerbaru,
            ],
        ];
    }

    /**
     * Data grafik pembayaran per bulan (6 bulan terakhir) - GLOBAL (Bendahara)
     */
    private function getGrafikPembayaran()
    {
        $data = [];
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $labels[] = $bulan->format('M Y');

            $total = Transaksi::where('status', 'confirmed')
                              ->whereMonth('tanggal_bayar', $bulan->month)
                              ->whereYear('tanggal_bayar', $bulan->year)
                              ->sum('jumlah');

            $data[] = (float) $total;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Data grafik pengeluaran per bulan (6 bulan terakhir) - GLOBAL (Bendahara)
     */
    private function getGrafikPengeluaran()
    {
        $data = [];
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $labels[] = $bulan->format('M Y');

            $total = Pengeluaran::where('status', 'approved')
                                ->whereMonth('tanggal', $bulan->month)
                                ->whereYear('tanggal', $bulan->year)
                                ->sum('jumlah');

            $data[] = (float) $total;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Data grafik pembayaran per bulan (6 bulan terakhir) - PER KELAS (Guru)
     */
    private function getGrafikPembayaranByKelas($kelasId)
    {
        $data = [];
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $labels[] = $bulan->format('M Y');

            $total = Transaksi::whereHas('siswa', function($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            })->where('status', 'confirmed')
              ->whereMonth('tanggal_bayar', $bulan->month)
              ->whereYear('tanggal_bayar', $bulan->year)
              ->sum('jumlah');

            $data[] = (float) $total;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Data grafik pengeluaran per bulan (6 bulan terakhir) - PER KELAS (Guru)
     */
    private function getGrafikPengeluaranByKelas($kelasId)
    {
        $data = [];
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $labels[] = $bulan->format('M Y');

            $total = Pengeluaran::where('kelas_id', $kelasId)
                                ->where('status', 'approved')
                                ->whereMonth('tanggal', $bulan->month)
                                ->whereYear('tanggal', $bulan->year)
                                ->sum('jumlah');

            $data[] = (float) $total;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Data grafik kas (pemasukan vs pengeluaran) per bulan (6 bulan terakhir) - GLOBAL (Bendahara)
     */
    private function getGrafikKas()
    {
        $pemasukan = [];
        $pengeluaran = [];
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $labels[] = $bulan->format('M Y');

            // Pemasukan
            $totalPemasukan = Transaksi::where('status', 'confirmed')
                                       ->whereMonth('tanggal_bayar', $bulan->month)
                                       ->whereYear('tanggal_bayar', $bulan->year)
                                       ->sum('jumlah');

            // Pengeluaran
            $totalPengeluaran = Pengeluaran::where('status', 'approved')
                                           ->whereMonth('tanggal', $bulan->month)
                                           ->whereYear('tanggal', $bulan->year)
                                           ->sum('jumlah');

            $pemasukan[] = (float) $totalPemasukan;
            $pengeluaran[] = (float) $totalPengeluaran;
        }

        return [
            'labels' => $labels,
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
        ];
    }

    /**
     * Get dashboard data berdasarkan role user
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 401);
            }

            // Cek role menggunakan method yang sudah ada di model User
            if ($user->isGuru()) {
                $data = $this->getGuruDashboard($request);
            } elseif ($user->isBendahara()) {
                $data = $this->getBendaharaDashboard($request);
            } elseif ($user->isSiswa()) {
                $data = $this->getSiswaDashboard($request);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Role tidak dikenali',
                    'role' => $user->role->name ?? 'tidak ada role'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data dashboard berhasil diambil',
                'data' => $data
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}