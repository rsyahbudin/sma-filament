<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin
        $adminRole = Role::where('name', 'Admin')->first();
        User::create([
            'name' => 'Admin Satu',
            'email' => 'admin1@example.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'gender' => 'male',
            'phone' => '081234567887',
            'address' => 'Jl. Admin No. 95',
            'date_of_birth' => '1990-01-01',
        ]);

        // Create 12 teachers
        $teacherRole = Role::where('name', 'Teacher')->first();
        $teacherNames = [
            'Budi Santoso',
            'Siti Aminah',
            'Ahmad Hidayat',
            'Dewi Lestari',
            'Rudi Hartono',
            'Nina Wijaya',
            'Muhammad Ali',
            'Linda Sari',
            'Joko Widodo',
            'Ani Susanti',
            'Herman Setiawan',
            'Rina Putri'
        ];

        foreach ($teacherNames as $index => $name) {
            User::create([
                'name' => $name,
                'email' => 'guru' . ($index + 1) . '@example.com',
                'password' => Hash::make('password'),
                'role_id' => $teacherRole->id,
                'gender' => $index % 2 == 0 ? 'male' : 'female',
                'phone' => '08' . rand(1000000000, 9999999999),
                'address' => 'Jl. Guru No. ' . ($index + 1),
                'date_of_birth' => rand(1980, 1990) . '-' . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT),
            ]);
        }

        // Create students (5 per class)
        $studentRole = Role::where('name', 'Student')->first();
        $classes = [
            'X IPA 1',
            'X IPA 2',
            'X IPS 1',
            'X IPS 2',
            'XI IPA 1',
            'XI IPA 2',
            'XI IPS 1',
            'XI IPS 2',
            'XII IPA 1',
            'XII IPA 2',
            'XII IPS 1',
            'XII IPS 2'
        ];

        $studentIndex = 1;
        foreach ($classes as $class) {
            for ($i = 1; $i <= 5; $i++) {
                $gender = rand(0, 1) ? 'male' : 'female';
                $name = $gender == 'male' ?
                    'Siswa ' . $class . ' ' . $i :
                    'Siswi ' . $class . ' ' . $i;

                User::create([
                    'name' => $name,
                    'email' => 'siswa' . $studentIndex . '@example.com',
                    'password' => Hash::make('password'),
                    'role_id' => $studentRole->id,
                    'gender' => $gender,
                    'phone' => '08' . rand(1000000000, 9999999999),
                    'address' => 'Jl. Siswa No. ' . $studentIndex,
                    'date_of_birth' => rand(2005, 2007) . '-' . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT),
                ]);
                $studentIndex++;
            }
        }
    }
}
