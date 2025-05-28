<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activeAcademicYearId = DB::table('academic_years')->where('is_active', 1)->value('id');
        $students = DB::table('users')->where('role_id', DB::table('roles')->where('name', 'Student')->value('id'))->get();
        $subjects = DB::table('subjects')->get();

        foreach ($students as $student) {
            // Get the student's current class for the active academic year
            $studentClass = DB::table('student_class')
                              ->where('student_id', $student->id)
                              ->where('academic_year_id', $activeAcademicYearId)
                              ->first();

            if ($studentClass) {
                foreach ($subjects as $subject) {
                    // Generate a single score for each subject for both semesters
                    for ($semester = 1; $semester <= 2; $semester++) { // Assuming 2 semesters
                        $score = rand(60, 100); // Random score between 60 and 100
                        DB::table('grades')->insert([
                            'user_id' => $student->id,
                            'subject_id' => $subject->id,
                            'class_id' => $studentClass->school_class_id,
                            'academic_year_id' => $activeAcademicYearId,
                            'semester' => $semester,
                            'score' => $score,
                            'notes' => null, // No specific notes for now
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}