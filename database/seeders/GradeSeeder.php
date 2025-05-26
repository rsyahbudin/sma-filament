<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get active academic year
        $academicYear = \App\Models\AcademicYear::where('is_active', true)->first();

        // Get first class
        $class = SchoolClass::where('code', 'X-IPA-1')->first();

        // Get first student
        $student = User::where('email', 'siswa1@example.com')->first();

        // Get subjects
        $subjects = Subject::all();

        // Create grades for each subject
        foreach ($subjects as $subject) {
            Grade::create([
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'class_id' => $class->id,
                'academic_year_id' => $academicYear->id,
                'semester' => 1,
                'score' => rand(70, 100),
                'notes' => 'Nilai semester 1',
            ]);
        }
    }
}
