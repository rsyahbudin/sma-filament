<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
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

        // Create admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'phone' => '081234567890',
            'address' => 'Jl. Admin No. 1',
        ]);

        // Create teachers
        $teachers = [
            [
                'name' => 'Guru Matematika',
                'email' => 'guru.matematika@example.com',
                'phone' => '081234567891',
                'address' => 'Jl. Guru No. 1',
            ],
            [
                'name' => 'Guru Bahasa Indonesia',
                'email' => 'guru.bindo@example.com',
                'phone' => '081234567892',
                'address' => 'Jl. Guru No. 2',
            ],
        ];

        foreach ($teachers as $teacher) {
            User::create([
                'name' => $teacher['name'],
                'email' => $teacher['email'],
                'password' => Hash::make('password'),
                'role_id' => $teacherRole->id,
                'phone' => $teacher['phone'],
                'address' => $teacher['address'],
            ]);
        }

        // Create students
        $students = [
            [
                'name' => 'Siswa 1',
                'email' => 'siswa1@example.com',
                'phone' => '081234567893',
                'address' => 'Jl. Siswa No. 1',
            ],
            [
                'name' => 'Siswa 2',
                'email' => 'siswa2@example.com',
                'phone' => '081234567894',
                'address' => 'Jl. Siswa No. 2',
            ],
        ];

        foreach ($students as $student) {
            User::create([
                'name' => $student['name'],
                'email' => $student['email'],
                'password' => Hash::make('password'),
                'role_id' => $studentRole->id,
                'phone' => $student['phone'],
                'address' => $student['address'],
            ]);
        }
    }
}
