<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('users')->truncate();
        DB::table('siswas')->truncate();
        Schema::enableForeignKeyConstraints();

        // Ambil data role
        $roleGuru = Role::where('name', 'guru')->first();
        $roleBendahara = Role::where('name', 'bendahara')->first();
        $roleSiswa = Role::where('name', 'siswa')->first();

        // Ambil data kelas
        $kelasRPL1 = Kelas::where('nama', 'XII RPL 1')->first();
        // $kelasRPL2 = Kelas::where('nama', 'XII RPL 2')->first();

        // ============ BUAT USER GURU ============
        $guru = User::create([
            'name' => 'Hernadhia, S.Pd.',
            'email' => 'guru@kb.com',
            'password' => Hash::make('password'),
            'role_id' => $roleGuru->id,
            'kelas_id' => 1,
            'no_hp' => '081234567890',
            // 'foto' => 'https://smkassalaambandung.sch.id/storage/01J3CBDT767VXYK1KFMBWB75DV.JPG',
            'foto' => null,
            'is_active' => true,
        ]);

        // Update wali kelas
        $kelasRPL1->update(['wali_kelas_id' => $guru->id]);

        // ============ BUAT USER BENDAHARA ============
        $bendahara1 = User::create([
            'name' => 'Ahmad Fadilah',
            'email' => 'bendahara1@kb.com',
            'password' => Hash::make('password'),
            'role_id' => $roleBendahara->id,
            'kelas_id' => null,
            'no_hp' => '081298765432',
            'foto' => null,
            'is_active' => true,
        ]);

        $bendahara2 = User::create([
            'name' => 'Zaskia Ramdhani Putri',
            'email' => 'bendahara2@kb.com',
            'password' => Hash::make('password'),
            'role_id' => $roleBendahara->id,
            'kelas_id' => null,
            'no_hp' => '081298765432',
            'foto' => null,
            'is_active' => true,
        ]);

        // ============ BUAT USER SISWA (25 Siswa) ============
        $siswaData = [
            [
                'name' => 'Ahmad Fadilah',
                'email' => 'ahmad@kb.com',
                'nis' => '2025001',
                'nisn' => '1234567891',
                'kelas' => $kelasRPL1,
                'no_hp' => '081211112222',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2008-04-24',
                'alamat' => 'Jl. Merdeka No. 10, Jakarta',
                'nama_ortu' => 'Budi Santoso',
                'no_hp_ortu' => '081298765432',
            ],
            [
                'name' => 'Aliva Dian Nugraha',
                'email' => 'aliva@kb.com',
                'nis' => '2025002',
                'nisn' => '1234567892',
                'kelas' => $kelasRPL1,
                'no_hp' => '081222223333',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2008-01-01',
                'alamat' => 'Jl. Asia Afrika No. 5, Bandung',
                'nama_ortu' => 'Dedi Wijaya',
                'no_hp_ortu' => '081287654321',
            ],
            [
                'name' => 'Anggia Rahmania',
                'email' => 'anggia@kb.com',
                'nis' => '2025003',
                'nisn' => '1234567893',
                'kelas' => $kelasRPL1,
                'no_hp' => '081233334444',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2005-03-10',
                'alamat' => 'Jl. Raya Darmo No. 15, Surabaya',
                'nama_ortu' => 'Agus Supriyadi',
                'no_hp_ortu' => '081276543210',
            ],
            [
                'name' => 'Ani Nur Rahayu',
                'email' => 'ani@kb.com',
                'nis' => '2025004',
                'nisn' => '1234567894',
                'kelas' => $kelasRPL1,
                'no_hp' => '081244445555',
                'tempat_lahir' => 'Yogyakarta',
                'tanggal_lahir' => '2005-04-25',
                'alamat' => 'Jl. Malioboro No. 20, Yogyakarta',
                'nama_ortu' => 'Slamet Riyadi',
                'no_hp_ortu' => '081265432109',
            ],
            [
                'name' => 'Dikri Nur Rohmat',
                'email' => 'dikri@kb.com',
                'nis' => '2025005',
                'nisn' => '1234567895',
                'kelas' => $kelasRPL1,
                'no_hp' => '081255556666',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2005-05-30',
                'alamat' => 'Jl. Pandanaran No. 8, Bandung',
                'nama_ortu' => 'Haryono',
                'no_hp_ortu' => '081254321098',
            ],
            [
                'name' => 'Fakhri Ibnu Nabil',
                'email' => 'fakhri@kb.com',
                'nis' => '2025006',
                'nisn' => '1234567896',
                'kelas' => $kelasRPL1,
                'no_hp' => '081266667777',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2005-06-12',
                'alamat' => 'Jl. Sudirman No. 30, Bandung',
                'nama_ortu' => 'Rahmat Hidayat',
                'no_hp_ortu' => '081243210987',
            ],
            [
                'name' => 'Fatahillah Akbar',
                'email' => 'fatahillah@kb.com',
                'nis' => '2025007',
                'nisn' => '1234567897',
                'kelas' => $kelasRPL1,
                'no_hp' => '081277778888',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2005-07-18',
                'alamat' => 'Jl. Urip Sumoharjo No. 12, Bandung',
                'nama_ortu' => 'Hasanuddin',
                'no_hp_ortu' => '081232109876',
            ],
            [
                'name' => 'Feri Ramdani',
                'email' => 'feri@kb.com',
                'nis' => '2025008',
                'nisn' => '1234567898',
                'kelas' => $kelasRPL1,
                'no_hp' => '081288889999',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2005-08-22',
                'alamat' => 'Jl. Sunset Road No. 45, Bandung',
                'nama_ortu' => 'Nyoman Suarsa',
                'no_hp_ortu' => '081221098765',
            ],
            [
                'name' => 'Ilman Abidullah',
                'email' => 'ilman@kb.com',
                'nis' => '2025009',
                'nisn' => '1234567899',
                'kelas' => $kelasRPL1,
                'no_hp' => '081299990000',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2005-09-05',
                'alamat' => 'Jl. Veteran No. 7, Bandung',
                'nama_ortu' => 'Suryadi',
                'no_hp_ortu' => '081210987654',
            ],
            [
                'name' => 'Muhammad Jauf',
                'email' => 'jauf@kb.com',
                'nis' => '2025010',
                'nisn' => '1234567900',
                'kelas' => $kelasRPL1,
                'no_hp' => '081200001111',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2005-10-28',
                'alamat' => 'Jl. Jendral Sudirman No. 25, Bandung',
                'nama_ortu' => 'Mulyono',
                'no_hp_ortu' => '081209876543',
            ],
            [
                'name' => 'Marsha Bara Suwarna',
                'email' => 'marsha@kb.com',
                'nis' => '2025011',
                'nisn' => '1234567901',
                'kelas' => $kelasRPL1,
                'no_hp' => '081200001112',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2005-10-28',
                'alamat' => 'Jl. Jendral Sudirman No. 25, Bandung',
                'nama_ortu' => 'Asep',
                'no_hp_ortu' => '081209876543',
            ],
            [
                'name' => 'Muhammad Ilham',
                'email' => 'ilham@kb.com',
                'nis' => '2025012',
                'nisn' => '1234567902',
                'kelas' => $kelasRPL1,
                'no_hp' => '081200001113',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2005-10-28',
                'alamat' => 'Jl. Jendral Sudirman No. 25, Bandung',
                'nama_ortu' => 'Budi',
                'no_hp_ortu' => '081209876543',
            ],
            [
                'name' => 'Muhammad Reza Aditia',
                'email' => 'reza@kb.com',
                'nis' => '2025013',
                'nisn' => '1234567903',
                'kelas' => $kelasRPL1,
                'no_hp' => '081200001114',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2005-10-28',
                'alamat' => 'Jl. Jendral Sudirman No. 25, Bandung',
                'nama_ortu' => 'Jajang',
                'no_hp_ortu' => '081209876543',
            ],
            [
                'name' => 'Muhammad Jihad Putra Drajat',
                'email' => 'jihad@kb.com',
                'nis' => '2025014',
                'nisn' => '1234567904',
                'kelas' => $kelasRPL1,
                'no_hp' => '081200001115',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2005-10-28',
                'alamat' => 'Jl. Jendral Sudirman No. 25, Bandung',
                'nama_ortu' => 'Drajat',
                'no_hp_ortu' => '081209876543',
            ],
            [
                'name' => 'Muhammad Radiedtya Pratama',
                'email' => 'radit@kb.com',
                'nis' => '24251753112',
                'nisn' => '1234567905',
                'kelas' => $kelasRPL1,
                'no_hp' => '088222150964',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2009-02-20',
                'alamat' => 'Jl. Jendral Sudirman No. 25, Bandung',
                'nama_ortu' => 'Pratama',
                'no_hp_ortu' => '081209876543',
            ],
            [
                'name' => 'Rakha Alfarizqi Zahir',
                'email' => 'rakha@kb.com',
                'nis' => '2025016',
                'nisn' => '1234567906',
                'kelas' => $kelasRPL1,
                'no_hp' => '081200001117',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2009-02-20',
                'alamat' => 'Jl. Jendral Sudirman No. 25, Bandung',
                'nama_ortu' => 'Zahir',
                'no_hp_ortu' => '081209876543',
            ],
            [
                'name' => 'Rakha Rava Andrea',
                'email' => 'rava@kb.com',
                'nis' => '2025017',
                'nisn' => '1234567907',
                'kelas' => $kelasRPL1,
                'no_hp' => '081200001117',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2009-02-20',
                'alamat' => 'Jl. Jendral Sudirman No. 25, Bandung',
                'nama_ortu' => 'Andrea',
                'no_hp_ortu' => '081209876543',
            ],
            [
                'name' => 'Rehan Ramadhan',
                'email' => 'rehan@kb.com',
                'nis' => '2025018',
                'nisn' => '1234567908',
                'kelas' => $kelasRPL1,
                'no_hp' => '081200001118',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2009-02-20',
                'alamat' => 'Jl. Jendral Sudirman No. 25, Bandung',
                'nama_ortu' => 'Ramadhan',
                'no_hp_ortu' => '081209876543',
            ],
            [
                'name' => 'Reihan Azka Vahlepy',
                'email' => 'reihan@kb.com',
                'nis' => '2025019',
                'nisn' => '1234567909',
                'kelas' => $kelasRPL1,
                'no_hp' => '081200001119',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2009-02-20',
                'alamat' => 'Jl. Jendral Sudirman No. 25, Bandung',
                'nama_ortu' => 'Azka',
                'no_hp_ortu' => '081209876543',
            ],
            [
                'name' => 'Riska Aulia Sakinah',
                'email' => 'riska@kb.com',
                'nis' => '2025020',
                'nisn' => '12345679010',
                'kelas' => $kelasRPL1,
                'no_hp' => '081200001120',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2009-02-20',
                'alamat' => 'Jl. Jendral Sudirman No. 25, Bandung',
                'nama_ortu' => 'Sakinah',
                'no_hp_ortu' => '081209876543',
            ],
            [
                'name' => 'Rudy Gunawan',
                'email' => 'rudy@kb.com',
                'nis' => '2025021',
                'nisn' => '12345679011',
                'kelas' => $kelasRPL1,
                'no_hp' => '081200001120',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2009-02-20',
                'alamat' => 'Jl. Jendral Sudirman No. 25, Bandung',
                'nama_ortu' => 'Guna',
                'no_hp_ortu' => '081209876543',
            ],
            [
                'name' => 'Salwa Khaerunnisa',
                'email' => 'salwa@kb.com',
                'nis' => '2025022',
                'nisn' => '12345679012',
                'kelas' => $kelasRPL1,
                'no_hp' => '081200001120',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2009-02-20',
                'alamat' => 'Jl. Jendral Sudirman No. 25, Bandung',
                'nama_ortu' => 'Khaerunnisa',
                'no_hp_ortu' => '081209876543',
            ],
            [
                'name' => 'Siti Nurfadilla Hasanah',
                'email' => 'siti@kb.com',
                'nis' => '2025023',
                'nisn' => '12345679013',
                'kelas' => $kelasRPL1,
                'no_hp' => '081200001120',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2009-02-20',
                'alamat' => 'Jl. Jendral Sudirman No. 25, Bandung',
                'nama_ortu' => 'Hasanah',
                'no_hp_ortu' => '081209876543',
            ],
            [
                'name' => 'Teguh Firmansyah',
                'email' => 'teguh@kb.com',
                'nis' => '2025024',
                'nisn' => '12345679014',
                'kelas' => $kelasRPL1,
                'no_hp' => '081200001120',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2009-02-20',
                'alamat' => 'Jl. Jendral Sudirman No. 25, Bandung',
                'nama_ortu' => 'Hawa',
                'no_hp_ortu' => '081209876543',
            ],
            [
                'name' => 'Zaskia Ramadhani Putri',
                'email' => 'zaskia@kb.com',
                'nis' => '2025025',
                'nisn' => '12345679015',
                'kelas' => $kelasRPL1,
                'no_hp' => '081200001120',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2009-02-20',
                'alamat' => 'Jl. Jendral Sudirman No. 25, Bandung',
                'nama_ortu' => 'Putri',
                'no_hp_ortu' => '081209876543',
            ],

        ];

        foreach ($siswaData as $data) {
            // Buat user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role_id' => $roleSiswa->id,
                'kelas_id' => $data['kelas']->id,
                'no_hp' => $data['no_hp'],
                'foto' => null,
                'is_active' => true,
            ]);

            // Buat data siswa
            Siswa::create([
                'user_id' => $user->id,
                'nis' => $data['nis'],
                'nisn' => $data['nisn'],
                'kelas_id' => $data['kelas']->id,
                'tempat_lahir' => $data['tempat_lahir'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'alamat' => $data['alamat'],
                'nama_ortu' => $data['nama_ortu'],
                'no_hp_ortu' => $data['no_hp_ortu'],
            ]);
        }

        $this->command->info('✅ User (Guru, Bendahara, Siswa)'. count($siswaData) .'berhasil dibuat!');
    }
}