<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function index()
    {
        try {
            $settings = Setting::all()->pluck('value', 'key');
            return response()->json([
                'success' => true,
                'message' => 'Data pengaturan berhasil diambil',
                'data' => $settings
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil pengaturan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'denda_per_hari' => 'required|numeric|min:0',
                'maks_denda' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            foreach ($request->all() as $key => $value) {
                Setting::where('key', $key)->update(['value' => $value]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengaturan berhasil diperbarui',
                'data' => Setting::all()->pluck('value', 'key')
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui pengaturan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}