<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            AcademicYearSeeder::class,
            UserSeeder::class,
            SubjectSeeder::class,
            SchoolClassSeeder::class,
            TeacherSubjectSeeder::class,
            ScheduleSeeder::class,
            StudentClassSeeder::class,
            GradeSeeder::class,
            // Jika ada seeder lain di masa mendatang, tambahkan di sini
        ]);
    }
}