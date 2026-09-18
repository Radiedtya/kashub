<?php

namespace Database\Seeders;

use App\Models\Iuran;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IuranSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('iurans')->truncate();
        Schema::enableForeignKeyConstraints();

        $kelasRPL1 = Kelas::where('nama', 'XII RPL 1')->first();
        $guru = User::where('email', 'buherna@klasify.com')->first();

        // Bikin 10 iuran untuk kelas XII RPL 1 (Januari - Oktober 2025)
        for ($i = 1; $i <= 10; $i++) {
            Iuran::create([
                'kelas_id' => $kelasRPL1->id,
                'bulan' => $i,
                'tahun' => 2025,
                'nominal' => 50000,
                'jatuh_tempo' => now()->setMonth($i)->setYear(2025)->endOfMonth(),
                'is_active' => true,
                'created_by' => $guru->id,
            ]);
        }

        $this->command->info('✅ 10 Iuran untuk XII RPL 1 berhasil dibuat!');
    }
}