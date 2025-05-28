<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil Role IDs
        $adminRoleId = DB::table('roles')->where('name', 'Admin')->value('id');
        $teacherRoleId = DB::table('roles')->where('name', 'Teacher')->value('id');
        $studentRoleId = DB::table('roles')->where('name', 'Student')->value('id');

        // Admin User
        DB::table('users')->insert([
            'role_id' => $adminRoleId,
            'status' => 'active',
            'name' => 'Admin Satu',
            'email' => 'admin1@example.com',
            'password' => Hash::make('password'), // Ganti dengan password yang kuat di produksi
            'phone' => '081234567887',
            'address' => 'Jl. Admin No. 95',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Teachers (id 2-13)
        $teachers = [
            ['Budi Santoso', 'guru1@example.com', '084284193353', 'Jl. Guru No. 1', '1986-04-03', 'male'],
            ['Siti Aminah', 'guru2@example.com', '082927791601', 'Jl. Guru No. 2', '1983-12-01', 'female'],
            ['Ahmad Hidayat', 'guru3@example.com', '089066342814', 'Jl. Guru No. 3', '1989-12-05', 'male'],
            ['Dewi Lestari', 'guru4@example.com', '081790095235', 'Jl. Guru No. 4', '1981-11-28', 'female'],
            ['Rudi Hartono', 'guru5@example.com', '084961506689', 'Jl. Guru No. 5', '1987-10-14', 'male'],
            ['Nina Wijaya', 'guru6@example.com', '086361601903', 'Jl. Guru No. 6', '1989-11-02', 'female'],
            ['Muhammad Ali', 'guru7@example.com', '088593696929', 'Jl. Guru No. 7', '1981-01-16', 'male'],
            ['Linda Sari', 'guru8@example.com', '085804563875', 'Jl. Guru No. 8', '1989-10-20', 'female'],
            ['Joko Widodo', 'guru9@example.com', '086373246259', 'Jl. Guru No. 9', '1987-08-22', 'male'],
            ['Ani Susanti', 'guru10@example.com', '084475170106', 'Jl. Guru No. 10', '1987-08-15', 'female'],
            ['Herman Setiawan', 'guru11@example.com', '085446188614', 'Jl. Guru No. 11', '1986-10-24', 'male'],
            ['Rina Putri', 'guru12@example.com', '087040164147', 'Jl. Guru No. 12', '1988-04-09', 'female'],
        ];

        foreach ($teachers as $teacher) {
            DB::table('users')->insert([
                'role_id' => $teacherRoleId,
                'status' => 'active',
                'name' => $teacher[0],
                'email' => $teacher[1],
                'password' => Hash::make('password'),
                'phone' => $teacher[2],
                'address' => $teacher[3],
                'date_of_birth' => $teacher[4],
                'gender' => $teacher[5],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Students (id 14-73, assuming 60 students as in your SQL dump)
        // Adjust this loop if you have more/fewer students or different naming conventions
        for ($i = 1; $i <= 60; $i++) {
            $gender = ($i % 2 == 0) ? 'male' : 'female'; // Alternating gender for variety
            $classPrefix = 'X';
            if ($i > 10 && $i <= 20) $classPrefix = 'XI'; // Adjust class distribution as needed
            if ($i > 20) $classPrefix = 'XII'; // Adjust class distribution as needed

            DB::table('users')->insert([
                'role_id' => $studentRoleId,
                'status' => 'active',
                'name' => 'Siswa ' . $classPrefix . ' ' . (($i % 2 == 0) ? 'IPA' : 'IPS') . ' ' . $i, // Example naming
                'email' => 'siswa' . $i . '@example.com',
                'password' => Hash::make('password'),
                'phone' => '08' . rand(1000000000, 9999999999), // Random phone
                'address' => 'Jl. Siswa No. ' . $i,
                'date_of_birth' => date('Y-m-d', strtotime('-1' . rand(15, 18) . ' years -' . rand(0, 364) . ' days')), // Random birth date for high school age
                'gender' => $gender,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}