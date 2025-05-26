<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get active academic year
        $academicYear = \App\Models\AcademicYear::where('is_active', true)->first();

        // Get teacher
        $teacher = User::where('email', 'guru.matematika@example.com')->first();

        $classes = [
            [
                'name' => 'X IPA 1',
                'code' => 'X-IPA-1',
                'academic_year_id' => $academicYear->id,
                'teacher_id' => $teacher->id,
            ],
            [
                'name' => 'X IPA 2',
                'code' => 'X-IPA-2',
                'academic_year_id' => $academicYear->id,
                'teacher_id' => $teacher->id,
            ],
            [
                'name' => 'XI IPA 1',
                'code' => 'XI-IPA-1',
                'academic_year_id' => $academicYear->id,
                'teacher_id' => $teacher->id,
            ],
            [
                'name' => 'XI IPA 2',
                'code' => 'XI-IPA-2',
                'academic_year_id' => $academicYear->id,
                'teacher_id' => $teacher->id,
            ],
        ];

        foreach ($classes as $class) {
            SchoolClass::create($class);
        }
    }
}
