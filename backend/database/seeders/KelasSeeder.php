<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('kelas')->truncate();
        Schema::enableForeignKeyConstraints();

        $kelas = [
            [
                'nama' => 'XII RPL 1',
                'tahun_ajaran' => '2026/2027',
                'is_active' => true,
            ],
        ];

        foreach ($kelas as $data) {
            Kelas::create($data);
        }

        $this->command->info('✅ Kelas berhasil dibuat!');
    }
}