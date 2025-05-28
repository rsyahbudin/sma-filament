<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete existing roles to avoid duplicates
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Role::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create roles with specific IDs
        Role::create(['id' => 1, 'name' => 'Admin', 'description' => 'Administrator sistem']);
        Role::create(['id' => 2, 'name' => 'Teacher', 'description' => 'Guru pengajar']);
        Role::create(['id' => 3, 'name' => 'Student', 'description' => 'Siswa']);
    }
}
