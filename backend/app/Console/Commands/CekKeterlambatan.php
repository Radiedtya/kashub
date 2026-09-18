<?php

namespace App\Console\Commands;

use App\Events\SiswaTelatBayar;
use App\Models\Iuran;
use App\Models\Keterlambatan;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CekKeterlambatan extends Command
{
    protected $signature = 'keterlambatan:cek';
    protected $description = 'Cek siswa yang telat bayar iuran';

    public function handle()
    {
        $this->info('🔄 Memulai pengecekan keterlambatan...');

        $today = Carbon::now()->startOfDay();
        $totalKeterlambatan = 0;

        try {
            DB::beginTransaction();

            // Ambil semua iuran yang aktif dan sudah lewat jatuh tempo
            $iuranList = Iuran::where('is_active', true)
                              ->where('jatuh_tempo', '<', $today)
                              ->get();

            $this->info("📋 Ditemukan " . $iuranList->count() . " iuran yang sudah lewat jatuh tempo");

            foreach ($iuranList as $iuran) {
                // Ambil semua siswa di kelas tersebut
                $siswaList = Siswa::where('kelas_id', $iuran->kelas_id)->get();

                foreach ($siswaList as $siswa) {
                    // Cek apakah siswa sudah bayar (confirmed)
                    $sudahBayar = Transaksi::where('siswa_id', $siswa->id)
                                           ->where('iuran_id', $iuran->id)
                                           ->where('status', 'confirmed')
                                           ->exists();

                    if ($sudahBayar) {
                        continue;
                    }

                    // Hitung hari telat
                    $jatuhTempo = Carbon::parse($iuran->jatuh_tempo);
                    $hariTelat = $today->diffInDays($jatuhTempo);

                    // ✅ FIX: Pastikan hari telat minimal 1 SEBELUM hitung denda
                    if ($hariTelat < 1) {
                        $hariTelat = 1;
                    }

                    // Ambil setting denda dari database
                    $dendaPerHari = Setting::where('key', 'denda_per_hari')->value('value') ?? 5000;
                    $maksDenda = Setting::where('key', 'maks_denda')->value('value') ?? 50000;

                    // Hitung denda
                    $denda = $this->hitungDenda($hariTelat, (float) $dendaPerHari, (float) $maksDenda);

                    // Cek apakah sudah ada data keterlambatan
                    $existingKeterlambatan = Keterlambatan::where('siswa_id', $siswa->id)
                                                          ->where('iuran_id', $iuran->id)
                                                          ->first();

                    if ($existingKeterlambatan) {
                        // Update hari telat
                        $existingKeterlambatan->update([
                            'hari_telat' => (int) $hariTelat,
                            'denda' => $denda
                        ]);
                    } else {
                        // Buat data keterlambatan baru
                        $keterlambatan = Keterlambatan::create([
                            'siswa_id' => $siswa->id,
                            'iuran_id' => $iuran->id,
                            'hari_telat' => (int) $hariTelat,
                            'denda' => $denda,
                            'status' => 'belum_bayar',
                        ]);

                        // Fire event
                        event(new SiswaTelatBayar($siswa, $iuran, $keterlambatan, $hariTelat));

                        $totalKeterlambatan++;
                    }
                }
            }

            DB::commit();

            $this->info("✅ Selesai! Total {$totalKeterlambatan} keterlambatan baru ditemukan.");
            Log::info("Cek keterlambatan selesai", [
                'total_keterlambatan_baru' => $totalKeterlambatan,
                'timestamp' => now()
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error: " . $e->getMessage());
            Log::error("Error cek keterlambatan: " . $e->getMessage());

            return Command::FAILURE;
        }
    }

    private function hitungDenda(int $hariTelat, float $dendaPerHari, float $maksDenda): float
    {
        $denda = $hariTelat * $dendaPerHari;
        return (float) min($denda, $maksDenda);
    }
}