<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Notifikasi;
use App\Models\Pengeluaran;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PengeluaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Pengeluaran::with(['kelas', 'createdBy.role', 'approvedBy.role']);

            // Filter by kelas
            if ($request->has('kelas_id')) {
                $query->where('kelas_id', $request->kelas_id);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by kategori
            if ($request->has('kategori')) {
                $query->where('kategori', $request->kategori);
            }

            // Filter by tanggal
            if ($request->has('tanggal_mulai') && $request->has('tanggal_selesai')) {
                $query->whereBetween('tanggal', [
                    $request->tanggal_mulai,
                    $request->tanggal_selesai
                ]);
            }

            // Search by judul
            if ($request->has('search')) {
                $query->where('judul', 'like', '%' . $request->search . '%');
            }

            $pengeluaran = $query->orderBy('tanggal', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data pengeluaran berhasil diambil',
                'data' => $pengeluaran
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pengeluaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, User $user)
    {
        try {
            $validator = Validator::make($request->all(), [
                'kelas_id' => 'required|exists:kelas,id',
                'judul' => 'required|string|max:200',
                'deskripsi' => 'nullable|string',
                'jumlah' => 'required|numeric|min:0',
                'tanggal' => 'required|date',
                'kategori' => 'nullable|string|max:50',
                'bukti_foto' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pengeluaran = Pengeluaran::create([
                'kelas_id' => $request->kelas_id,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'jumlah' => $request->jumlah,
                'tanggal' => $request->tanggal,
                'kategori' => $request->kategori,
                'bukti_foto' => $request->bukti_foto,
                'created_by' => $request->user()->id,
                'status' => 'pending',
            ]);

            // KIRIM NOTIF KE GURU
            $gurus = User::whereHas('role', function($q) {
                $q->where('name', 'guru');
            })->get();

            foreach ($gurus as $guru) {
                Notifikasi::create([
                    'user_id' => $guru->id,
                    'sender_id' => $user->id,
                    'judul' => 'Pengajuan Dana Baru',
                    'pesan' => "Bendahara {$user->name} mengajukan dana sebesar Rp " . number_format($request->jumlah, 0, ',', '.') . " untuk {$request->judul}.",
                    'tipe' => 'warning',
                    'is_read' => false,
                    'link' => '/pengeluaran',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengeluaran berhasil diajukan. Menunggu persetujuan guru.',
                'data' => $pengeluaran->load('kelas', 'createdBy')
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
                'message' => 'Gagal mengajukan pengeluaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            $pengeluaran = Pengeluaran::with(['kelas', 'createdBy', 'approvedBy'])->find($id);

            if (!$pengeluaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengeluaran tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail pengeluaran berhasil diambil',
                'data' => $pengeluaran
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail pengeluaran',
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
            $pengeluaran = Pengeluaran::find($id);

            if (!$pengeluaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengeluaran tidak ditemukan'
                ], 404);
            }

            // Cek status: hanya pending yang bisa diupdate
            if ($pengeluaran->status != 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengeluaran yang sudah diproses tidak dapat diubah'
                ], 422);
            }

            // Cek akses: hanya pembuat yang bisa update
            if ($pengeluaran->created_by != $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengubah pengeluaran ini'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'kelas_id' => 'sometimes|exists:kelas,id',
                'judul' => 'sometimes|string|max:200',
                'deskripsi' => 'nullable|string',
                'jumlah' => 'sometimes|numeric|min:0',
                'tanggal' => 'sometimes|date',
                'kategori' => 'nullable|string|max:50',
                'bukti_foto' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pengeluaran->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Pengeluaran berhasil diperbarui',
                'data' => $pengeluaran->load('kelas', 'createdBy')
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
                'message' => 'Gagal memperbarui pengeluaran',
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
            $pengeluaran = Pengeluaran::find($id);

            if (!$pengeluaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengeluaran tidak ditemukan'
                ], 404);
            }

            // Cek status: hanya pending yang bisa dihapus
            if ($pengeluaran->status != 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengeluaran yang sudah diproses tidak dapat dihapus'
                ], 422);
            }

            $pengeluaran->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pengeluaran berhasil dihapus'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pengeluaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Setujui atau tolak pengeluaran
     */
    public function setujui(Request $request, int $id, User $user)
    {
        try {
            $pengeluaran = Pengeluaran::find($id);

            if (!$pengeluaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengeluaran tidak ditemukan'
                ], 404);
            }

            // Cek status: hanya pending yang bisa disetujui
            if ($pengeluaran->status != 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengeluaran sudah diproses sebelumnya'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:approved,rejected',
                'catatan' => 'nullable|string',
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
                $pengeluaran->update([
                    'status' => $request->status,
                    'approved_by' => $request->user()->id,
                    'approved_at' => now(),
                    'deskripsi' => $request->catatan 
                        ? $pengeluaran->deskripsi . "\n\nCatatan: " . $request->catatan 
                        : $pengeluaran->deskripsi,
                ]);

                // KIRIM NOTIF KE BENDAHARA (CREATED_BY)
                if ($pengeluaran->created_by) {
                    if ($request->status == 'approved') {
                        Notifikasi::create([
                            'user_id' => $pengeluaran->created_by,
                            'sender_id' => $user->id, // $user di sini adalah Guru
                            'judul' => 'Pengajuan Dana Disetujui',
                            'pesan' => "Pengajuan dana untuk \"{$pengeluaran->judul}\" telah disetujui oleh {$user->name}.",
                            'tipe' => 'success',
                            'is_read' => false,
                            'link' => '/pengeluaran',
                        ]);
                    } else {
                        Notifikasi::create([
                            'user_id' => $pengeluaran->created_by,
                            'sender_id' => $user->id,
                            'judul' => 'Pengajuan Dana Ditolak',
                            'pesan' => "Pengajuan dana untuk \"{$pengeluaran->judul}\" ditolak oleh {$user->name}.",
                            'tipe' => 'danger',
                            'is_read' => false,
                            'link' => '/pengeluaran',
                        ]);
                    }
                }

                DB::commit();

                $statusText = $request->status == 'approved' ? 'disetujui' : 'ditolak';

                return response()->json([
                    'success' => true,
                    'message' => "Pengeluaran berhasil {$statusText}",
                    'data' => $pengeluaran->load('kelas', 'createdBy', 'approvedBy')
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
                'message' => 'Gagal memproses pengeluaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending pengeluaran
     */
    public function getPending(Request $request)
    {
        try {
            $query = Pengeluaran::with(['kelas', 'createdBy'])
                                ->where('status', 'pending')
                                ->orderBy('created_at', 'asc');

            if ($request->has('kelas_id')) {
                $query->where('kelas_id', $request->kelas_id);
            }

            $pengeluaran = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Data pengeluaran pending berhasil diambil',
                'data' => $pengeluaran
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pengeluaran pending',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pengeluaran by kelas
     */
    public function getByKelas(int $kelasId)
    {
        try {
            $kelas = Kelas::find($kelasId);

            if (!$kelas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelas tidak ditemukan'
                ], 404);
            }

            $pengeluaran = Pengeluaran::with(['createdBy', 'approvedBy'])
                                      ->where('kelas_id', $kelasId)
                                      ->orderBy('tanggal', 'desc')
                                      ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data pengeluaran kelas berhasil diambil',
                'data' => [
                    'kelas' => $kelas,
                    'total_pengeluaran' => $pengeluaran->where('status', 'approved')->sum('jumlah'),
                    'pengeluaran' => $pengeluaran
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pengeluaran kelas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get summary pengeluaran by kategori
     */
    public function getSummaryByKategori(Request $request)
    {
        try {
            $query = Pengeluaran::where('status', 'approved');

            if ($request->has('kelas_id')) {
                $query->where('kelas_id', $request->kelas_id);
            }

            if ($request->has('tahun')) {
                $query->whereYear('tanggal', $request->tahun);
            }

            $summary = $query->selectRaw('kategori, SUM(jumlah) as total')
                             ->groupBy('kategori')
                             ->orderBy('total', 'desc')
                             ->get();

            return response()->json([
                'success' => true,
                'message' => 'Ringkasan pengeluaran berhasil diambil',
                'data' => $summary
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil ringkasan pengeluaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}