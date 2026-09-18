<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Iuran;
use App\Models\Kelas;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class IuranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Iuran::with(['kelas', 'createdBy']);

            // Filter by kelas
            if ($request->has('kelas_id')) {
                $query->where('kelas_id', $request->kelas_id);
            }

            // Filter by status
            if ($request->has('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            // Filter by bulan
            if ($request->has('bulan')) {
                $query->where('bulan', $request->bulan);
            }

            // Filter by tahun
            if ($request->has('tahun')) {
                $query->where('tahun', $request->tahun);
            }

            $iuran = $query->orderBy('tahun', 'desc')
                          ->orderBy('bulan', 'desc')
                          ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data iuran berhasil diambil',
                'data' => $iuran
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data iuran',
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
            $validator = Validator::make($request->all(), [
                'kelas_id' => 'required|exists:kelas,id',
                'bulan' => 'required|integer|min:1|max:12',
                'tahun' => 'required|integer|min:2000|max:2100',
                'nominal' => 'required|numeric|min:0',
                'jatuh_tempo' => 'required|date',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cek apakah iuran sudah ada untuk kelas, bulan, tahun yang sama
            $existingIuran = Iuran::where('kelas_id', $request->kelas_id)
                                  ->where('bulan', $request->bulan)
                                  ->where('tahun', $request->tahun)
                                  ->first();

            if ($existingIuran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Iuran untuk kelas, bulan, dan tahun ini sudah ada'
                ], 422);
            }

            $iuran = Iuran::create([
                'kelas_id' => $request->kelas_id,
                'bulan' => $request->bulan,
                'tahun' => $request->tahun,
                'nominal' => $request->nominal,
                'jatuh_tempo' => $request->jatuh_tempo,
                'is_active' => $request->is_active ?? true,
                'created_by' => $request->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Iuran berhasil ditambahkan',
                'data' => $iuran->load('kelas', 'createdBy')
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
                'message' => 'Gagal menambahkan iuran',
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
            $iuran = Iuran::with(['kelas', 'createdBy', 'transaksi.siswa.user'])->find($id);

            if (!$iuran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Iuran tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail iuran berhasil diambil',
                'data' => $iuran
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail iuran',
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
            $iuran = Iuran::find($id);

            if (!$iuran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Iuran tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'kelas_id' => 'sometimes|exists:kelas,id',
                'bulan' => 'sometimes|integer|min:1|max:12',
                'tahun' => 'sometimes|integer|min:2000|max:2100',
                'nominal' => 'sometimes|numeric|min:0',
                'jatuh_tempo' => 'sometimes|date',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cek duplikasi jika ada perubahan kelas/bulan/tahun
            if ($request->has('kelas_id') || $request->has('bulan') || $request->has('tahun')) {
                $kelasId = $request->kelas_id ?? $iuran->kelas_id;
                $bulan = $request->bulan ?? $iuran->bulan;
                $tahun = $request->tahun ?? $iuran->tahun;

                $existingIuran = Iuran::where('kelas_id', $kelasId)
                                      ->where('bulan', $bulan)
                                      ->where('tahun', $tahun)
                                      ->where('id', '!=', $id)
                                      ->first();

                if ($existingIuran) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Iuran untuk kelas, bulan, dan tahun ini sudah ada'
                    ], 422);
                }
            }

            $iuran->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Iuran berhasil diperbarui',
                'data' => $iuran->load('kelas', 'createdBy')
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
                'message' => 'Gagal memperbarui iuran',
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
            $iuran = Iuran::find($id);

            if (!$iuran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Iuran tidak ditemukan'
                ], 404);
            }

            // Cek apakah iuran sudah memiliki transaksi
            if ($iuran->transaksi()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Iuran tidak dapat dihapus karena sudah memiliki transaksi'
                ], 422);
            }

            $iuran->delete();

            return response()->json([
                'success' => true,
                'message' => 'Iuran berhasil dihapus'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus iuran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get iuran by kelas
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

            $iuran = Iuran::with(['kelas', 'createdBy'])
                          ->where('kelas_id', $kelasId)
                          ->orderBy('tahun', 'desc')
                          ->orderBy('bulan', 'desc')
                          ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data iuran kelas berhasil diambil',
                'data' => [
                    'kelas' => $kelas,
                    'iuran' => $iuran
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data iuran kelas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get status pembayaran siswa untuk iuran tertentu
     */
    public function getStatusSiswa(int $id)
    {
        try {
            $iuran = Iuran::with(['kelas.siswa.user'])->find($id);

            if (!$iuran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Iuran tidak ditemukan'
                ], 404);
            }

            $siswa = $iuran->kelas->siswa;
            $status = [];

            foreach ($siswa as $s) {
                // Cek apakah siswa sudah bayar
                $transaksi = Transaksi::where('siswa_id', $s->id)
                                      ->where('iuran_id', $id)
                                      ->where('status', 'confirmed')
                                      ->first();

                $transaksiPending = Transaksi::where('siswa_id', $s->id)
                                            ->where('iuran_id', $id)
                                            ->where('status', 'pending')
                                            ->first();

                $status[] = [
                    'siswa_id' => $s->id,
                    'nis' => $s->nis,
                    'name' => $s->user->name,
                    'status' => $transaksi ? 'lunas' : ($transaksiPending ? 'pending' : 'belum_bayar'),
                    'tanggal_bayar' => $transaksi ? $transaksi->tanggal_bayar : null,
                    'transaksi_id' => $transaksi ? $transaksi->id : null,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Status siswa berhasil diambil',
                'data' => [
                    'iuran' => $iuran,
                    'total_siswa' => count($status),
                    'lunas' => count(array_filter($status, function ($s) {
                        return $s['status'] == 'lunas';
                    })),
                    'pending' => count(array_filter($status, function ($s) {
                        return $s['status'] == 'pending';
                    })),
                    'belum_bayar' => count(array_filter($status, function ($s) {
                        return $s['status'] == 'belum_bayar';
                    })),
                    'siswa' => $status
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil status siswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}