<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;
use App\Models\User;
use App\Models\Subject;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get active academic year
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) {
            return;
        }

        // Get all students
        $students = User::whereHas('role', function ($query) {
            $query->where('name', 'Student');
        })->get();

        // Get all subjects
        $subjects = Subject::all();

        // Get student classes
        $studentClasses = DB::table('student_class')
            ->where('academic_year_id', $activeYear->id)
            ->get()
            ->groupBy('student_id');

        foreach ($students as $student) {
            // Get student's class
            $studentClass = $studentClasses[$student->id][0] ?? null;
            if (!$studentClass) {
                continue;
            }

            // Get class name to determine available semesters
            $className = DB::table('school_classes')
                ->where('id', $studentClass->school_class_id)
                ->value('name');

            // Determine available semesters based on class name
            $gradeLevel = substr($className, 0, strpos($className, ' '));
            $availableSemesters = [];

            switch ($gradeLevel) {
                case 'X':
                    $availableSemesters = [1, 2];
                    break;
                case 'XI':
                    $availableSemesters = [1, 2, 3, 4];
                    break;
                case 'XII':
                    $availableSemesters = [1, 2, 3, 4, 5, 6];
                    break;
            }

            // Create grades for each subject and semester
            foreach ($subjects as $subject) {
                foreach ($availableSemesters as $semester) {
                    // Generate random score between 60 and 100
                    $score = rand(60, 100);

                    // Check if grade already exists
                    $existingGrade = Grade::where([
                        'user_id' => $student->id,
                        'subject_id' => $subject->id,
                        'class_id' => $studentClass->school_class_id,
                        'academic_year_id' => $activeYear->id,
                        'semester' => $semester,
                    ])->first();

                    if (!$existingGrade) {
                        Grade::create([
                            'user_id' => $student->id,
                            'subject_id' => $subject->id,
                            'class_id' => $studentClass->school_class_id,
                            'academic_year_id' => $activeYear->id,
                            'semester' => $semester,
                            'score' => $score,
                        ]);
                    }
                }
            }
        }
    }
}
