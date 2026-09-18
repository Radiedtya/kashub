<?php

namespace Database\Seeders;

// use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'tes@klasify.com',
        // ]);

        $this->call([
            RoleSeeder::class,
            KelasSeeder::class,
            UserSeeder::class,
            // IuranSeeder::class,
            // TransaksiSeeder::class,
        ]);
    }
}
