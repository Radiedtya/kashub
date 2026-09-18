<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\IuranController;
use App\Http\Controllers\Api\KelasController;
use App\Http\Controllers\Api\KeterlambatanController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\NotifikasiController;
use App\Http\Controllers\Api\PengeluaranController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SiswaController;
use App\Http\Controllers\Api\TransaksiController;
use Illuminate\Support\Facades\Route;

// ==========================================
// PUBLIC ROUTES (Tanpa Login)
// ==========================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ==========================================
// AUTHENTICATED ROUTES (Wajib Login)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    
    // --- Auth & Dashboard ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/password', [AuthController::class, 'updatePassword']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // --- Test Routes (Bisa dihapus kalau sudah tidak dipakai) ---
    Route::get('/guru/test', fn () => response()->json(['success' => true, 'message' => 'Anda adalah guru!']))->middleware('role:guru');
    Route::get('/bendahara/test', fn () => response()->json(['success' => true, 'message' => 'Anda adalah bendahara!']))->middleware('role:bendahara');
    Route::get('/siswa/test', fn () => response()->json(['success' => true, 'message' => 'Anda adalah siswa!']))->middleware('role:siswa');
    Route::get('/guru-bendahara/test', fn () => response()->json(['success' => true, 'message' => 'Anda adalah guru atau bendahara!']))->middleware('role:guru,bendahara');

    // ==========================================
    // KELAS ROUTES
    // ==========================================
    Route::prefix('kelas')->group(function () {
        Route::get('/', [KelasController::class, 'index'])->middleware('role:guru,bendahara');
        Route::get('/{id}', [KelasController::class, 'show'])->middleware('role:guru,bendahara');
        Route::get('/{id}/siswa', [KelasController::class, 'getSiswa'])->middleware('role:guru,bendahara');
        
        Route::post('/', [KelasController::class, 'store'])->middleware('role:guru');
        Route::put('/{id}', [KelasController::class, 'update'])->middleware('role:guru');
        Route::delete('/{id}', [KelasController::class, 'destroy'])->middleware('role:guru');
    });

    // ==========================================
    // USER ROUTES (Untuk Dropdown Wali Kelas, dll)
    // ==========================================
    Route::get('/users', function () {
        $users = \App\Models\User::with('role')->get();
        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diambil',
            'data' => $users
        ], 200);
    })->middleware('role:guru'); // Hanya guru yang bisa lihat list semua user

    // ==========================================
    // SISWA ROUTES
    // ==========================================
    Route::prefix('siswa')->group(function () {
        Route::get('/', [SiswaController::class, 'index'])->middleware('role:guru,bendahara');
        Route::get('/{id}', [SiswaController::class, 'show'])->middleware('role:guru,bendahara');
        
        Route::post('/', [SiswaController::class, 'store'])->middleware('role:guru');
        Route::put('/{id}', [SiswaController::class, 'update'])->middleware('role:guru');
        Route::delete('/{id}', [SiswaController::class, 'destroy'])->middleware('role:guru');
    });

    // ==========================================
    // IURAN ROUTES
    // ==========================================
    Route::prefix('iuran')->group(function () {
        // Static routes first!
        Route::get('/kelas/{kelas_id}', [IuranController::class, 'getByKelas'])->middleware('role:guru,bendahara,siswa');
        
        // Dynamic routes
        Route::get('/', [IuranController::class, 'index'])->middleware('role:guru,bendahara,siswa');
        Route::get('/{id}', [IuranController::class, 'show'])->middleware('role:guru,bendahara,siswa');
        Route::get('/{id}/siswa', [IuranController::class, 'getStatusSiswa'])->middleware('role:guru,bendahara,siswa');
        
        Route::post('/', [IuranController::class, 'store'])->middleware('role:guru');
        Route::put('/{id}', [IuranController::class, 'update'])->middleware('role:guru');
        Route::delete('/{id}', [IuranController::class, 'destroy'])->middleware('role:guru');
    });

    // ==========================================
    // TRANSAKSI ROUTES
    // ==========================================
    Route::prefix('transaksi')->group(function () {
        // Static routes (Harus di atas {id})
        Route::get('/saya', [TransaksiController::class, 'getMyTransaksi']); // Semua role bisa akses
        Route::get('/pending', [TransaksiController::class, 'getPending'])->middleware('role:guru,bendahara');
        Route::get('/siswa/{siswa_id}', [TransaksiController::class, 'getBySiswa'])->middleware('role:guru,bendahara');
        Route::get('/iuran/{iuran_id}', [TransaksiController::class, 'getByIuran'])->middleware('role:guru,bendahara');
        
        // Dynamic routes
        Route::get('/', [TransaksiController::class, 'index'])->middleware('role:guru,bendahara');
        Route::get('/{id}', [TransaksiController::class, 'show'])->middleware('role:guru,bendahara');
        
        // Aksi
        Route::post('/', [TransaksiController::class, 'store'])->middleware('role:siswa,bendahara,guru');
        Route::put('/{id}', [TransaksiController::class, 'update'])->middleware('role:guru,bendahara');
        Route::put('/{id}/konfirmasi', [TransaksiController::class, 'konfirmasi'])->middleware('role:guru,bendahara');
        Route::delete('/{id}', [TransaksiController::class, 'destroy'])->middleware('role:guru');
    });

    // ==========================================
    // PENGELUARAN ROUTES
    // ==========================================
    Route::prefix('pengeluaran')->group(function () {
        // Static routes first!
        Route::get('/pending', [PengeluaranController::class, 'getPending'])->middleware('role:guru');
        Route::get('/summary/kategori', [PengeluaranController::class, 'getSummaryByKategori'])->middleware('role:guru,bendahara,siswa');
        Route::get('/kelas/{kelas_id}', [PengeluaranController::class, 'getByKelas'])->middleware('role:guru,bendahara,siswa');
        
        // Dynamic routes
        Route::get('/', [PengeluaranController::class, 'index'])->middleware('role:guru,bendahara,siswa');
        Route::get('/{id}', [PengeluaranController::class, 'show'])->middleware('role:guru,bendahara,siswa');
        
        // Aksi
        Route::post('/', [PengeluaranController::class, 'store'])->middleware('role:bendahara');
        Route::put('/{id}', [PengeluaranController::class, 'update'])->middleware('role:bendahara');
        Route::put('/{id}/setujui', [PengeluaranController::class, 'setujui'])->middleware('role:guru');
        Route::delete('/{id}', [PengeluaranController::class, 'destroy'])->middleware('role:guru');
    });

    // ==========================================
    // KETERLAMBATAN ROUTES
    // ==========================================
    Route::prefix('keterlambatan')->group(function () {
        // Static route
        Route::get('/saya', [KeterlambatanController::class, 'getMyKeterlambatan']); // Semua role bisa akses
        
        // Dynamic routes
        Route::get('/', [KeterlambatanController::class, 'index'])->middleware('role:guru,bendahara');
        Route::get('/siswa/{siswa_id}', [KeterlambatanController::class, 'getBySiswa'])->middleware('role:guru,bendahara');
        
        // Cek keterlambatan (custom route)
        Route::post('/cek', function () {
            try {
                // Panggil command lewat Artisan facade, ini cara paling aman & resmi
                \Illuminate\Support\Facades\Artisan::call('keterlambatan:cek');
                
                return response()->json([
                    'success' => true,
                    'message' => 'Pengecekan keterlambatan berhasil dijalankan',
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menjalankan pengecekan keterlambatan: ' . $e->getMessage()
                ], 500);
            }
        })->middleware('role:guru');
    });

    // ==========================================
    // NOTIFIKASI ROUTES
    // ==========================================
    Route::prefix('notifikasi')->group(function () {
        // Static action routes first
        Route::get('/unread', [NotifikasiController::class, 'getUnread']);
        Route::put('/read-all', [NotifikasiController::class, 'markAllAsRead']);
        Route::delete('/read-all', [NotifikasiController::class, 'deleteAllRead']);
        
        // Send manual
        Route::post('/send', [NotifikasiController::class, 'sendManual'])->middleware('role:guru,bendahara');
        Route::post('/send-kelas', [NotifikasiController::class, 'sendToKelas'])->middleware('role:guru,bendahara');
        
        // Dynamic routes
        Route::get('/', [NotifikasiController::class, 'index']);
        Route::put('/{id}/read', [NotifikasiController::class, 'markAsRead']);
        Route::delete('/{id}', [NotifikasiController::class, 'destroy']);
    });

    // ==========================================
    // LAPORAN ROUTES
    // ==========================================
    Route::prefix('laporan')->middleware('role:guru,bendahara')->group(function () {
        // Static routes
        Route::get('/kas', [LaporanController::class, 'kas']);
        Route::get('/export/pdf', [LaporanController::class, 'exportPdf']);
        Route::get('/export/excel', [LaporanController::class, 'exportExcel']);
        
        // Dynamic routes
        Route::get('/bulan/{bulan}/{tahun}', [LaporanController::class, 'perBulan']);
        Route::get('/siswa/{siswa_id}', [LaporanController::class, 'perSiswa']);
        Route::get('/kelas/{kelas_id}', [LaporanController::class, 'perKelas']);
    });

    // ==========================================
    // SETTINGS ROUTES
    // ==========================================
    Route::get('/settings', [SettingController::class, 'index']);
    Route::put('/settings', [SettingController::class, 'update'])->middleware('role:guru');

});