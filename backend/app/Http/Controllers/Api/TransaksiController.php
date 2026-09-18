<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Iuran;
use App\Models\Keterlambatan;
use App\Models\Notifikasi;
use App\Models\Siswa;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TransaksiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Transaksi::with(['siswa.user', 'iuran.kelas', 'confirmedBy.role']);

            // Filter by siswa
            if ($request->has('siswa_id')) {
                $query->where('siswa_id', $request->siswa_id);
            }

            // Filter by iuran
            if ($request->has('iuran_id')) {
                $query->where('iuran_id', $request->iuran_id);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by tanggal
            if ($request->has('tanggal_mulai') && $request->has('tanggal_selesai')) {
                $query->whereBetween('tanggal_bayar', [
                    $request->tanggal_mulai,
                    $request->tanggal_selesai
                ]);
            }

            // Filter by metode
            if ($request->has('metode')) {
                $query->where('metode', $request->metode);
            }

            // Search by siswa name or NIS
            if ($request->has('search')) {
                $search = $request->search;
                $query->whereHas('siswa.user', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })->orWhereHas('siswa', function ($q) use ($search) {
                    $q->where('nis', 'like', '%' . $search . '%');
                });
            }

            $transaksi = $query->orderBy('tanggal_bayar', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data transaksi berhasil diambil',
                'data' => $transaksi
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            // Validasi dasar
            $validator = Validator::make($request->all(), [
                'iuran_id' => 'required|exists:iurans,id',
                'jumlah' => 'required|numeric|min:0',
                'tanggal_bayar' => 'required|date',
                'metode' => 'required|string|in:tunai',
                'bukti_bayar' => 'nullable|string|max:255',
                'keterangan' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $siswa = null;
            $status = 'pending';
            $confirmedBy = null;
            $confirmedAt = null;

            // Cek role yang input
            if ($user->isSiswa()) {
                // Kalau Siswa yang input, cari datanya sendiri & status wajib pending
                $siswa = Siswa::where('user_id', $user->id)->first();
                if (!$siswa) {
                    return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan'], 404);
                }
                $status = 'pending';
            } else {
                // Kalau Guru/Bendahara yang input (Input Cash)
                $validatorRole = Validator::make($request->all(), [
                    'siswa_id' => 'required|exists:siswas,id',
                    'status' => 'required|string|in:pending,confirmed',
                ]);

                if ($validatorRole->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validasi gagal',
                        'errors' => $validatorRole->errors()
                    ], 422);
                }

                $siswa = Siswa::find($request->siswa_id);
                $status = $request->status;
                
                // Kalau langsung confirmed, catat siapa yang konfirmasi
                $confirmedBy = $status === 'confirmed' ? $user->id : null;
                $confirmedAt = $status === 'confirmed' ? now() : null;
            }

            // Cek apakah iuran ini sudah dibayar siswa (status confirmed/pending)
            $existingTransaksi = Transaksi::where('siswa_id', $siswa->id)
                                          ->where('iuran_id', $request->iuran_id)
                                          ->whereIn('status', ['confirmed', 'pending'])
                                          ->first();

            if ($existingTransaksi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Iuran ini sudah dibayar atau sedang menunggu konfirmasi'
                ], 422);
            }

            $dataTransaksi = [
                'siswa_id' => $siswa->id,
                'iuran_id' => $request->iuran_id,
                'jumlah' => $request->jumlah,
                'tanggal_bayar' => $request->tanggal_bayar,
                'metode' => $request->metode,
                'bukti_bayar' => $request->bukti_bayar,
                'keterangan' => $request->keterangan,
                'status' => $status,
            ];

            if (isset($confirmedBy)) {
                $dataTransaksi['confirmed_by'] = $confirmedBy;
                $dataTransaksi['confirmed_at'] = $confirmedAt;
            }

            $transaksi = Transaksi::create($dataTransaksi);

            if ($status == 'confirmed') {
                Keterlambatan::where('siswa_id', $siswa->id)
                    ->where('iuran_id', $request->iuran_id)
                    ->where('status', 'belum_bayar')
                    ->update(['status' => 'sudah_bayar_denda']);
            }

            // --- KIRIM NOTIF KE GURU & BENDAHARA (JIKA SISWA YANG BAYAR) ---
            if ($user->isSiswa()) {
                $guruBendahara = User::whereHas('role', function($q) {
                    $q->whereIn('name', ['guru', 'bendahara']);
                })->get();

                $bulanTahun = $transaksi->iuran ? Carbon::create()->month($transaksi->iuran->bulan)->format('F') . ' ' . $transaksi->iuran->tahun : '-';
                
                foreach ($guruBendahara as $penerima) {
                    Notifikasi::create([
                        'user_id' => $penerima->id,
                        'sender_id' => $user->id,
                        'judul' => 'Pembayaran Baru Menunggu Konfirmasi',
                        'pesan' => "Siswa {$siswa->user->name} mengirim bukti pembayaran iuran periode {$bulanTahun}.",
                        'tipe' => 'info',
                        'is_read' => false,
                        'link' => '/transaksi',
                    ]);
                }
            }
            // --------------------------------------------------------------------

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dikirim',
                'data' => $transaksi
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id)
    {
        try {
            $transaksi = Transaksi::with(['siswa.user', 'iuran.kelas', 'confirmedBy'])->find($id);

            if (!$transaksi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan'
                ], 404);
            }

            // Cek akses: siswa hanya bisa lihat transaksinya sendiri
            $user = $request->user();
            if ($user->isSiswa()) {
                $siswa = Siswa::where('user_id', $user->id)->first();
                if ($transaksi->siswa_id != $siswa->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses ke transaksi ini'
                    ], 403);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail transaksi berhasil diambil',
                'data' => $transaksi
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        try {
            $transaksi = Transaksi::find($id);

            if (!$transaksi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan'
                ], 404);
            }

            // Cek status: hanya pending yang bisa diupdate
            if ($transaksi->status != 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi yang sudah dikonfirmasi tidak dapat diubah'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'siswa_id' => 'sometimes|exists:siswas,id',
                'iuran_id' => 'sometimes|exists:iurans,id',
                'jumlah' => 'sometimes|numeric|min:0',
                'tanggal_bayar' => 'sometimes|date',
                'metode' => 'sometimes|string|in:tunai',
                'bukti_bayar' => 'nullable|string|max:255',
                'keterangan' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $transaksi->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diperbarui',
                'data' => $transaksi->load('siswa.user', 'iuran')
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $transaksi = Transaksi::find($id);

            if (!$transaksi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan'
                ], 404);
            }

            // Cek status: hanya pending yang bisa dihapus
            if ($transaksi->status != 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi yang sudah dikonfirmasi tidak dapat dihapus'
                ], 422);
            }

            $transaksi->delete();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Konfirmasi transaksi (Approve/Reject)
     */
    public function konfirmasi(Request $request, int $id)
    {
        try {
            $transaksi = Transaksi::find($id);

            if (!$transaksi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan'
                ], 404);
            }

            // Cek status: hanya pending yang bisa dikonfirmasi
            if ($transaksi->status != 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi sudah dikonfirmasi sebelumnya'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:confirmed,rejected',
                'keterangan' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            try {
                $user = $request->user(); // Ambil user yang lagi login

                $transaksi->update([
                    'status' => $request->status,
                    'confirmed_by' => $user->id,
                    'confirmed_at' => now(),
                    'keterangan' => $request->keterangan ?? $transaksi->keterangan,
                ]);

                if ($request->status == 'confirmed') {
                    try {
                        Keterlambatan::where('siswa_id', $transaksi->siswa_id)
                            ->where('iuran_id', $transaksi->iuran_id)
                            ->where('status', 'belum_bayar')
                            ->update(['status' => 'sudah_bayar_denda']);
                    } catch (\Exception $eKet) {
                        Log::error('Gagal update keterlambatan: ' . $eKet->getMessage());
                    }
                }
                
                $siswa = Siswa::find($transaksi->siswa_id);
                if ($siswa) {
                    $bulanTahun = $transaksi->iuran ? Carbon::create()->month($transaksi->iuran->bulan)->format('F') . ' ' . $transaksi->iuran->tahun : '-';
                    
                    if ($request->status == 'confirmed') {
                        Notifikasi::create([
                            'user_id' => $siswa->user_id,
                            'sender_id' => $user->id,
                            'judul' => 'Pembayaran Dikonfirmasi (Lunas)',
                            'pesan' => "Pembayaran iuran periode {$bulanTahun} kamu telah dikonfirmasi oleh {$user->name}.",
                            'tipe' => 'success',
                            'is_read' => false,
                            'link' => '/transaksi',
                        ]);
                    } else {
                        Notifikasi::create([
                            'user_id' => $siswa->user_id,
                            'sender_id' => $user->id,
                            'judul' => 'Pembayaran Ditolak',
                            'pesan' => "Pembayaran iuran periode {$bulanTahun} kamu ditolak oleh {$user->name}. Silakan hubungi guru/bendahara.",
                            'tipe' => 'danger',
                            'is_read' => false,
                            'link' => '/transaksi',
                        ]);
                    }
                }

                DB::commit();

                $statusText = $request->status == 'confirmed' ? 'dikonfirmasi' : 'ditolak';

                return response()->json([
                    'success' => true,
                    'message' => "Transaksi berhasil {$statusText}",
                    'data' => $transaksi->load('siswa.user', 'iuran', 'confirmedBy')
                ], 200);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengkonfirmasi transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transaksi by siswa
     */
    public function getBySiswa(Request $request, int $siswaId)
    {
        try {
            $siswa = Siswa::find($siswaId);

            if (!$siswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa tidak ditemukan'
                ], 404);
            }

            // Cek akses: siswa hanya bisa lihat transaksinya sendiri
            $user = $request->user();
            if ($user->isSiswa()) {
                $currentSiswa = Siswa::where('user_id', $user->id)->first();
                if ($siswaId != $currentSiswa->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses ke data siswa ini'
                    ], 403);
                }
            }

            $transaksi = Transaksi::with(['iuran.kelas', 'confirmedBy'])
                                  ->where('siswa_id', $siswaId)
                                  ->orderBy('tanggal_bayar', 'desc')
                                  ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data transaksi siswa berhasil diambil',
                'data' => [
                    'siswa' => $siswa->load('user', 'kelas'),
                    'total_transaksi' => $transaksi->count(),
                    'total_bayar' => $transaksi->where('status', 'confirmed')->sum('jumlah'),
                    'transaksi' => $transaksi
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data transaksi siswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transaksi by iuran
     */
    public function getByIuran(int $iuranId)
    {
        try {
            $iuran = Iuran::find($iuranId);

            if (!$iuran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Iuran tidak ditemukan'
                ], 404);
            }

            $transaksi = Transaksi::with(['siswa.user', 'confirmedBy'])
                                  ->where('iuran_id', $iuranId)
                                  ->orderBy('tanggal_bayar', 'desc')
                                  ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data transaksi iuran berhasil diambil',
                'data' => [
                    'iuran' => $iuran->load('kelas'),
                    'total_transaksi' => $transaksi->count(),
                    'total_bayar' => $transaksi->where('status', 'confirmed')->sum('jumlah'),
                    'transaksi' => $transaksi
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data transaksi iuran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending transactions
     */
    public function getPending(Request $request)
    {
        try {
            $query = Transaksi::with(['siswa.user', 'iuran.kelas'])
                              ->where('status', 'pending')
                              ->orderBy('created_at', 'asc');

            // Filter by kelas
            if ($request->has('kelas_id')) {
                $query->whereHas('iuran', function ($q) use ($request) {
                    $q->where('kelas_id', $request->kelas_id);
                });
            }

            $transaksi = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Data transaksi pending berhasil diambil',
                'data' => $transaksi
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data transaksi pending',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get my transactions (untuk siswa)
     */
    public function getMyTransaksi(Request $request)
    {
        try {
            $user = $request->user();

            // Cek role langsung dari relasi
            if ($user->role->name != 'siswa') {
                return response()->json([
                    'success' => false,
                    'message' => 'Fitur ini hanya untuk siswa'
                ], 403);
            }

            $siswa = Siswa::where('user_id', $user->id)->first();

            if (!$siswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data siswa tidak ditemukan'
                ], 404);
            }

            $transaksi = Transaksi::with(['siswa.user','iuran.kelas', 'confirmedBy'])
                                ->where('siswa_id', $siswa->id)
                                ->orderBy('tanggal_bayar', 'desc')
                                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data transaksi Anda berhasil diambil',
                'data' => [
                    'siswa' => $siswa->load('user', 'kelas'),
                    'total_transaksi' => $transaksi->count(),
                    'total_bayar' => $transaksi->where('status', 'confirmed')->sum('jumlah'),
                    'transaksi' => $transaksi
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}