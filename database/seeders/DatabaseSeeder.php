<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            AcademicYearSeeder::class,
            SubjectSeeder::class,
            SchoolClassSeeder::class,
            GradeSeeder::class,
            StudentClassSeeder::class,
        ]);
    }
}
