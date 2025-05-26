<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'description' => 'Administrator sistem',
            ],
            [
                'name' => 'Teacher',
                'description' => 'Guru pengajar',
            ],
            [
                'name' => 'Student',
                'description' => 'Siswa',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
