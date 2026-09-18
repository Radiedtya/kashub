<?php

namespace Database\Seeders;

use App\Models\Iuran;
use App\Models\Siswa;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransaksiSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('transaksis')->truncate();
        DB::table('keterlambatans')->truncate();
        Schema::enableForeignKeyConstraints();

        $guru = User::where('email', 'buherna@klasify.com')->first();
        $siswas = Siswa::all();
        $iurans = Iuran::all();

        // Kita buat transaksi confirmed untuk 5 iuran pertama, buat 10 siswa pertama.
        // Jadi progress bar mereka bakal nunjukkin 5/10 (50%)
        foreach ($siswas->take(10) as $siswa) {
            foreach ($iurans->take(5) as $iuran) {
                Transaksi::create([
                    'siswa_id' => $siswa->id,
                    'iuran_id' => $iuran->id,
                    'jumlah' => $iuran->nominal,
                    'tanggal_bayar' => now()->subDays(rand(1, 30)),
                    'metode' => 'cash',
                    'status' => 'confirmed',
                    'confirmed_by' => $guru->id,
                    'confirmed_at' => now(),
                ]);
            }
        }

        // Kita buat 1 transaksi pending buat siswa pertama (iuran ke-6)
        if ($siswas->isNotEmpty() && $iurans->count() >= 6) {
            Transaksi::create([
                'siswa_id' => $siswas->first()->id,
                'iuran_id' => $iurans->get(5)->id,
                'jumlah' => $iurans->get(5)->nominal,
                'tanggal_bayar' => now(),
                'metode' => 'transfer',
                'status' => 'pending',
            ]);
        }

        $this->command->info('✅ Transaksi dummy berhasil dibuat!');
    }
}