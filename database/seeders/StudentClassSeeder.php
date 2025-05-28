<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activeAcademicYearId = DB::table('academic_years')->where('is_active', 1)->value('id');
        $students = DB::table('users')->where('role_id', DB::table('roles')->where('name', 'Student')->value('id'))->get();
        $classes = DB::table('school_classes')->where('academic_year_id', $activeAcademicYearId)->get();

        $classIndex = 0;
        foreach ($students as $student) {
            // Assign students to classes in a round-robin fashion
            $assignedClass = $classes[$classIndex];
            DB::table('student_class')->insert([
                'student_id' => $student->id,
                'school_class_id' => $assignedClass->id,
                'academic_year_id' => $activeAcademicYearId,
                'is_promoted' => 0, // Default to not yet promoted for the current year
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $classIndex = ($classIndex + 1) % count($classes); // Move to next class
        }
    }
}