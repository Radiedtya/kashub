<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Keterlambatan;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Exception;

class KeterlambatanController extends Controller
{
    /**
     * Get all late payments
     */
    public function index(Request $request)
    {
        try {
            $query = Keterlambatan::with(['siswa.user', 'siswa.kelas', 'iuran']);

            // Filter by kelas
            if ($request->has('kelas_id')) {
                $query->whereHas('siswa', function ($q) use ($request) {
                    $q->where('kelas_id', $request->kelas_id);
                });
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $keterlambatan = $query->orderBy('hari_telat', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data keterlambatan berhasil diambil',
                'data' => $keterlambatan
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data keterlambatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get late payments by student
     */
    public function getBySiswa(int $siswaId)
    {
        try {
            $siswa = Siswa::find($siswaId);

            if (!$siswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa tidak ditemukan'
                ], 404);
            }

            // Cek akses: siswa hanya bisa lihat data sendiri
            $user = request()->user();
            if ($user->isSiswa()) {
                $currentSiswa = Siswa::where('user_id', $user->id)->first();
                if ($siswaId != $currentSiswa->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses ke data ini'
                    ], 403);
                }
            }

            $keterlambatan = Keterlambatan::with(['iuran.kelas'])
                                          ->where('siswa_id', $siswaId)
                                          ->orderBy('created_at', 'desc')
                                          ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data keterlambatan siswa berhasil diambil',
                'data' => [
                    'siswa' => $siswa->load('user', 'kelas'),
                    'total_keterlambatan' => $keterlambatan->count(),
                    'total_denda' => $keterlambatan->sum('denda'),
                    'keterlambatan' => $keterlambatan
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data keterlambatan siswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get my late payments (for siswa)
     */
    public function getMyKeterlambatan(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->isSiswa()) {
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

            $keterlambatan = Keterlambatan::with(['iuran.kelas', 'siswa.user', 'siswa.kelas'])
                                          ->where('siswa_id', $siswa->id)
                                          ->orderBy('created_at', 'desc')
                                          ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data keterlambatan Anda berhasil diambil',
                'data' => [
                    'siswa' => $siswa->load('user', 'kelas'),
                    'total_keterlambatan' => $keterlambatan->count(),
                    'total_denda' => $keterlambatan->sum('denda'),
                    'keterlambatan' => $keterlambatan
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data keterlambatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}