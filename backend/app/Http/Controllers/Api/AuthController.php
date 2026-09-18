<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Exception;

class AuthController extends Controller
{
    /**
     * Register - khusus untuk siswa
     */
    public function register(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|string|email|max:100|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'no_hp' => 'nullable|string|max:15',
                'kelas_id' => 'required|exists:kelas,id',
                'nis' => 'required|string|max:20|unique:siswas,nis',
                'nisn' => 'nullable|string|max:20|unique:siswas,nisn',
                'tempat_lahir' => 'nullable|string|max:50',
                'tanggal_lahir' => 'nullable|date',
                'alamat' => 'nullable|string',
                'nama_ortu' => 'nullable|string|max:100',
                'no_hp_ortu' => 'nullable|string|max:15',
            ]);

            // Cari role siswa
            $roleSiswa = Role::where('name', 'siswa')->first();
            
            if (!$roleSiswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role siswa tidak ditemukan. Silakan jalankan seeder terlebih dahulu.'
                ], 500);
            }

            // DB Transaction agar jika salah satu gagal, semuanya rollback
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
                    'is_active' => true,
                ]);

                // Buat data siswa
                $siswa = $user->siswa()->create([
                    'nis' => $request->nis,
                    'nisn' => $request->nisn,
                    'kelas_id' => $request->kelas_id,
                    'tempat_lahir' => $request->tempat_lahir,
                    'tanggal_lahir' => $request->tanggal_lahir,
                    'alamat' => $request->alamat,
                    'nama_ortu' => $request->nama_ortu,
                    'no_hp_ortu' => $request->no_hp_ortu,
                ]);

                // Buat token
                $token = $user->createToken('auth_token')->plainTextToken;

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Registrasi berhasil',
                    'data' => [
                        'user' => $user->load('role', 'siswa'),
                        'token' => $token,
                        'token_type' => 'Bearer',
                    ]
                ], 201);

            } catch (QueryException $e) {
                DB::rollBack();
                
                // Cek jika error duplicate entry
                if ($e->errorInfo[1] == 1062) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data sudah terdaftar. Periksa email, NIS, atau NISN.',
                        'error' => $e->getMessage()
                    ], 422);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan data. Silakan coba lagi.',
                    'error' => $e->getMessage()
                ], 500);
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
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login
     */
    public function login(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            // Cek user
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password salah',
                ], 401);
            }

            // Cek apakah user aktif
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi guru.',
                ], 403);
            }

            // Hapus token lama (opsional)
            $user->tokens()->delete();

            // Buat token baru
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => $user->load('role', 'kelas', 'siswa'),
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'role' => $user->role->name ?? null,
                ]
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
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        try {
            // Cek apakah user sudah login
            if (!$request->user()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            // Hapus token yang sedang digunakan
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile
     */
    public function me(Request $request)
    {
        try {
            // Cek apakah user sudah login
            if (!$request->user()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $request->user()->load('role', 'kelas', 'siswa'),
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh token (opsional)
     */
    public function refresh(Request $request)
    {
        try {
            if (!$request->user()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            // Hapus semua token lama
            $request->user()->tokens()->delete();

            // Buat token baru
            $token = $request->user()->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Token berhasil diperbarui',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal refresh token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'email' => 'required|string|email|max:100|unique:users,email,' . $user->id,
                'no_hp' => 'nullable|string|max:15',
                'foto' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user->update($request->only('name', 'email', 'no_hp', 'foto'));

            $user->load('role');

            return response()->json([
                'success' => true,
                'message' => 'Profile berhasil diperbarui',
                'data' => $user
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cek password lama
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password lama tidak sesuai'
                ], 422);
            }

            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah password',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}