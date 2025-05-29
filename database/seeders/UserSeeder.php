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
        // Get role IDs
        $adminRole = Role::where('name', 'Admin')->first();
        $teacherRole = Role::where('name', 'Teacher')->first();
        $studentRole = Role::where('name', 'Student')->first();

        // Admins
        $admins = [
            ['name' => 'Admin Satu', 'email' => 'admin1@example.com', 'gender' => 'male'],
            ['name' => 'Admin Dua', 'email' => 'admin2@example.com', 'gender' => 'female'],
            ['name' => 'Admin Tiga', 'email' => 'admin3@example.com', 'gender' => 'male'],
            ['name' => 'Admin Empat', 'email' => 'admin4@example.com', 'gender' => 'female'],
            ['name' => 'Admin Lima', 'email' => 'admin5@example.com', 'gender' => 'male'],
        ];
        foreach ($admins as $admin) {
            User::create([
                'name' => $admin['name'],
                'email' => $admin['email'],
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'gender' => $admin['gender'],
                'phone' => '0812345678' . rand(10, 99),
                'address' => 'Jl. Admin No. ' . rand(1, 99),
                'date_of_birth' => '1990-01-01',
            ]);
        }

        // Teachers
        $teachers = [
            ['name' => 'Budi Santoso', 'email' => 'budi.santoso@example.com', 'gender' => 'male'],
            ['name' => 'Siti Aminah', 'email' => 'siti.aminah@example.com', 'gender' => 'female'],
            ['name' => 'Agus Prasetyo', 'email' => 'agus.prasetyo@example.com', 'gender' => 'male'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi.lestari@example.com', 'gender' => 'female'],
            ['name' => 'Rudi Hartono', 'email' => 'rudi.hartono@example.com', 'gender' => 'male'],
        ];
        foreach ($teachers as $teacher) {
            User::create([
                'name' => $teacher['name'],
                'email' => $teacher['email'],
                'password' => Hash::make('password'),
                'role_id' => $teacherRole->id,
                'gender' => $teacher['gender'],
                'phone' => '0812345678' . rand(10, 99),
                'address' => 'Jl. Guru No. ' . rand(1, 99),
                'date_of_birth' => '1985-01-01',
            ]);
        }

        // Students
        $students = [
            ['name' => 'Andi Wijaya', 'email' => 'andi.wijaya@example.com', 'gender' => 'male'],
            ['name' => 'Putri Ayu', 'email' => 'putri.ayu@example.com', 'gender' => 'female'],
            ['name' => 'Joko Susilo', 'email' => 'joko.susilo@example.com', 'gender' => 'male'],
            ['name' => 'Rina Marlina', 'email' => 'rina.marlina@example.com', 'gender' => 'female'],
            ['name' => 'Dedi Kurniawan', 'email' => 'dedi.kurniawan@example.com', 'gender' => 'male'],
        ];
        foreach ($students as $student) {
            User::create([
                'name' => $student['name'],
                'email' => $student['email'],
                'password' => Hash::make('password'),
                'role_id' => $studentRole->id,
                'gender' => $student['gender'],
                'phone' => '0812345678' . rand(10, 99),
                'address' => 'Jl. Siswa No. ' . rand(1, 99),
                'date_of_birth' => '2005-01-01',
            ]);
        }
    }
}
