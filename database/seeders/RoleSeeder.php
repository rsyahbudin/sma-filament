<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete existing roles to avoid duplicates
        Role::query()->delete();

        // Create roles
        Role::create(['name' => 'Admin',  'description' => 'Administrator sistem']);
        Role::create(['name' => 'Teacher', 'description' => 'Guru pengajar']);
        Role::create(['name' => 'Student', 'description' => 'Siswa']);
    }
}
