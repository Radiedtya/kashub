<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Siswa::with(['user', 'kelas']);

            // Filter by kelas
            if ($request->has('kelas_id')) {
                $query->where('kelas_id', $request->kelas_id);
            }

            // Filter by status (aktif/nonaktif)
            if ($request->has('is_active')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('is_active', $request->is_active);
                });
            }

            // Search by name or NIS
            if ($request->has('search')) {
                $search = $request->search;
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })->orWhere('nis', 'like', '%' . $search . '%');
            }

            $siswa = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Data siswa berhasil diambil',
                'data' => $siswa
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data siswa',
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
                'name' => 'required|string|max:100',
                'email' => 'required|string|email|max:100|unique:users,email',
                'password' => 'required|string|min:8',
                'no_hp' => 'nullable|string|max:15',
                'kelas_id' => 'required|exists:kelas,id',
                'nis' => 'required|string|max:20|unique:siswas,nis',
                'nisn' => 'nullable|string|max:20|unique:siswas,nisn',
                'tempat_lahir' => 'nullable|string|max:50',
                'tanggal_lahir' => 'nullable|date',
                'alamat' => 'nullable|string',
                'nama_ortu' => 'nullable|string|max:100',
                'no_hp_ortu' => 'nullable|string|max:15',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cari role siswa
            $roleSiswa = Role::where('name', 'siswa')->first();
            if (!$roleSiswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role siswa tidak ditemukan'
                ], 500);
            }

            DB::beginTransaction();

            try {
                // Buat user
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role_id' => $roleSiswa->id,
                    'kelas_id' => $request->kelas_id,
                    'no_hp' => $request->no_hp,
                    'is_active' => $request->is_active ?? true,
                ]);

                // Buat data siswa
                $siswa = Siswa::create([
                    'user_id' => $user->id,
                    'nis' => $request->nis,
                    'nisn' => $request->nisn,
                    'kelas_id' => $request->kelas_id,
                    'tempat_lahir' => $request->tempat_lahir,
                    'tanggal_lahir' => $request->tanggal_lahir,
                    'alamat' => $request->alamat,
                    'nama_ortu' => $request->nama_ortu,
                    'no_hp_ortu' => $request->no_hp_ortu,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Siswa berhasil ditambahkan',
                    'data' => $siswa->load('user', 'kelas')
                ], 201);

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
                'message' => 'Gagal menambahkan siswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $siswa = Siswa::with(['user', 'kelas', 'transaksi.iuran'])->find($id);

            if (!$siswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail siswa berhasil diambil',
                'data' => $siswa
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail siswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $siswa = Siswa::find($id);

            if (!$siswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:100',
                'email' => 'sometimes|string|email|max:100|unique:users,email,' . $siswa->user_id,
                'password' => 'nullable|string|min:8',
                'no_hp' => 'nullable|string|max:15',
                'kelas_id' => 'sometimes|exists:kelas,id',
                'nis' => 'sometimes|string|max:20|unique:siswas,nis,' . $id,
                'nisn' => 'nullable|string|max:20|unique:siswas,nisn,' . $id,
                'tempat_lahir' => 'nullable|string|max:50',
                'tanggal_lahir' => 'nullable|date',
                'alamat' => 'nullable|string',
                'nama_ortu' => 'nullable|string|max:100',
                'no_hp_ortu' => 'nullable|string|max:15',
                'is_active' => 'boolean'
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
                // Update user
                $userData = [
                    'name' => $request->name ?? $siswa->user->name,
                    'email' => $request->email ?? $siswa->user->email,
                    'no_hp' => $request->no_hp ?? $siswa->user->no_hp,
                    'kelas_id' => $request->kelas_id ?? $siswa->kelas_id,
                ];

                if ($request->has('is_active')) {
                    $userData['is_active'] = $request->is_active;
                }

                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }

                $siswa->user->update($userData);

                // Update siswa
                $siswaData = [
                    'kelas_id' => $request->kelas_id ?? $siswa->kelas_id,
                ];

                if ($request->has('nis')) {
                    $siswaData['nis'] = $request->nis;
                }
                if ($request->has('nisn')) {
                    $siswaData['nisn'] = $request->nisn;
                }
                if ($request->has('tempat_lahir')) {
                    $siswaData['tempat_lahir'] = $request->tempat_lahir;
                }
                if ($request->has('tanggal_lahir')) {
                    $siswaData['tanggal_lahir'] = $request->tanggal_lahir;
                }
                if ($request->has('alamat')) {
                    $siswaData['alamat'] = $request->alamat;
                }
                if ($request->has('nama_ortu')) {
                    $siswaData['nama_ortu'] = $request->nama_ortu;
                }
                if ($request->has('no_hp_ortu')) {
                    $siswaData['no_hp_ortu'] = $request->no_hp_ortu;
                }

                $siswa->update($siswaData);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Siswa berhasil diperbarui',
                    'data' => $siswa->load('user', 'kelas')
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
                'message' => 'Gagal memperbarui siswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $siswa = Siswa::find($id);

            if (!$siswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa tidak ditemukan'
                ], 404);
            }

            // Cek apakah siswa memiliki transaksi
            if ($siswa->transaksi()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa tidak dapat dihapus karena masih memiliki riwayat transaksi'
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Hapus siswa
                $siswa->delete();

                // Hapus user
                $siswa->user->delete();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Siswa berhasil dihapus'
                ], 200);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus siswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}