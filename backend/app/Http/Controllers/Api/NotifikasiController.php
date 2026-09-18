<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Models\User;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class NotifikasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            $query = Notifikasi::with('sender.role')->where('user_id', $user->id);

            // Filter by status (sudah dibaca atau belum)
            if ($request->has('is_read')) {
                $query->where('is_read', $request->is_read);
            }

            // Filter by tipe
            if ($request->has('tipe')) {
                $query->where('tipe', $request->tipe);
            }

            $notifikasi = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data notifikasi berhasil diambil',
                'data' => [
                    'total' => $notifikasi->count(),
                    'belum_dibaca' => $notifikasi->where('is_read', false)->count(),
                    'notifikasi' => $notifikasi
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread notifications
     */
    public function getUnread(Request $request)
    {
        try {
            $user = $request->user();

            $notifikasi = Notifikasi::with('sender.role')
                                    ->where('user_id', $user->id)
                                    ->where('is_read', false)
                                    ->orderBy('created_at', 'desc')
                                    ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data notifikasi belum dibaca berhasil diambil',
                'data' => [
                    'total' => $notifikasi->count(),
                    'notifikasi' => $notifikasi
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, int $id)
    {
        try {
            $user = $request->user();

            $notifikasi = Notifikasi::where('user_id', $user->id)
                                    ->where('id', $id)
                                    ->first();

            if (!$notifikasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notifikasi tidak ditemukan'
                ], 404);
            }

            $notifikasi->update([
                'is_read' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil ditandai sudah dibaca',
                'data' => $notifikasi
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $user = $request->user();

            $updated = Notifikasi::where('user_id', $user->id)
                                 ->where('is_read', false)
                                 ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => "{$updated} notifikasi berhasil ditandai sudah dibaca",
                'data' => [
                    'total_dibaca' => $updated
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai semua notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send manual notification (only for guru and bendahara)
     */
    public function sendManual(Request $request)
    {
        try {
            $user = $request->user();

            // Hanya guru dan bendahara yang bisa kirim notifikasi manual
            if (!$user->isGuru() && !$user->isBendahara()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengirim notifikasi'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'judul' => 'required|string|max:200',
                'pesan' => 'required|string',
                'tipe' => 'nullable|string|in:info,warning,danger,success',
                'link' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cek apakah user yang dikirim notifikasi ada
            $targetUser = User::find($request->user_id);
            if (!$targetUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tujuan tidak ditemukan'
                ], 404);
            }

            $notifikasi = Notifikasi::create([
                'user_id' => $request->user_id,
                'sender_id' => $user->id,
                'judul' => $request->judul,
                'pesan' => $request->pesan,
                'tipe' => $request->tipe ?? 'info',
                'is_read' => false,
                'link' => $request->link,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil dikirim',
                'data' => $notifikasi
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
                'message' => 'Gagal mengirim notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send notification to all students in a class (only for guru and bendahara)
     */
    public function sendToKelas(Request $request)
    {
        try {
            $user = $request->user();

            // Hanya guru dan bendahara yang bisa kirim notifikasi manual
            if (!$user->isGuru() && !$user->isBendahara()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengirim notifikasi'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'kelas_id' => 'required|exists:kelas,id',
                'judul' => 'required|string|max:200',
                'pesan' => 'required|string',
                'tipe' => 'nullable|string|in:info,warning,danger,success',
                'link' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Ambil semua siswa di kelas tersebut
            $siswa = Siswa::where('kelas_id', $request->kelas_id)->get();

            if ($siswa->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada siswa di kelas ini'
                ], 404);
            }

            $notifikasiData = [];
            foreach ($siswa as $s) {
                $notifikasiData[] = [
                    'user_id' => $s->user_id,
                    'sender_id' => $user->id,
                    'judul' => $request->judul,
                    'pesan' => $request->pesan,
                    'tipe' => $request->tipe ?? 'info',
                    'is_read' => false,
                    'link' => $request->link,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Bulk insert
            Notifikasi::insert($notifikasiData);

            return response()->json([
                'success' => true,
                'message' => "Notifikasi berhasil dikirim ke {$siswa->count()} siswa",
                'data' => [
                    'total_siswa' => $siswa->count(),
                    'judul' => $request->judul,
                    'pesan' => $request->pesan,
                ]
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
                'message' => 'Gagal mengirim notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete notification
     */
    public function destroy(Request $request, int $id)
    {
        try {
            $user = $request->user();

            $notifikasi = Notifikasi::where('user_id', $user->id)
                                    ->where('id', $id)
                                    ->first();

            if (!$notifikasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notifikasi tidak ditemukan'
                ], 404);
            }

            $notifikasi->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil dihapus'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead(Request $request)
    {
        try {
            $user = $request->user();

            $deleted = Notifikasi::where('user_id', $user->id)
                                 ->where('is_read', true)
                                 ->delete();

            return response()->json([
                'success' => true,
                'message' => "{$deleted} notifikasi yang sudah dibaca berhasil dihapus",
                'data' => [
                    'total_dihapus' => $deleted
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus notifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}