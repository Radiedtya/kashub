<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    use WithoutModelEvents;
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'guru', 'display_name' => 'Guru'],
            ['name' => 'bendahara', 'display_name' => 'Bendahara'],
            ['name' => 'siswa', 'display_name' => 'Siswa'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }

        $this->command->info('✅ Role berhasil dibuat!');
    }
}
