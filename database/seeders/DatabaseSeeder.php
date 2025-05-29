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
            SchoolClassSeeder::class,
            SubjectSeeder::class,
            StudentClassSeeder::class,
            ClassSubjectSeeder::class,
            GradeSeeder::class,
            ScheduleSeeder::class,
        ]);
    }
}
