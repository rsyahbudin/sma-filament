<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

class StudentClassSeeder extends Seeder
{
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

        // Get all classes
        $classes = SchoolClass::where('academic_year_id', $activeYear->id)->get();

        // Group students by their class name from their name
        $studentsByClass = [];
        foreach ($students as $student) {
            // Extract class name from student name (e.g., "Siswa X IPA 1 1" -> "X IPA 1")
            preg_match('/Siswa? (X{1,2}I{0,2} (?:IPA|IPS) \d+)/', $student->name, $matches);
            if (isset($matches[1])) {
                $className = $matches[1];
                if (!isset($studentsByClass[$className])) {
                    $studentsByClass[$className] = [];
                }
                $studentsByClass[$className][] = $student;
            }
        }

        // Assign students to their respective classes
        foreach ($classes as $class) {
            if (isset($studentsByClass[$class->name])) {
                foreach ($studentsByClass[$class->name] as $student) {
                    // Check if student is already assigned to a class
                    $existingAssignment = DB::table('student_class')
                        ->where('student_id', $student->id)
                        ->where('academic_year_id', $activeYear->id)
                        ->first();

                    if (!$existingAssignment) {
                        DB::table('student_class')->insert([
                            'student_id' => $student->id,
                            'school_class_id' => $class->id,
                            'academic_year_id' => $activeYear->id,
                            'is_promoted' => false,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        // Check for any unassigned students and assign them to appropriate classes
        $unassignedStudents = $students->filter(function ($student) use ($activeYear) {
            return !DB::table('student_class')
                ->where('student_id', $student->id)
                ->where('academic_year_id', $activeYear->id)
                ->exists();
        });

        if ($unassignedStudents->isNotEmpty()) {
            // Group unassigned students by grade level
            $studentsByGrade = [];
            foreach ($unassignedStudents as $student) {
                $gradeLevel = substr($student->name, 6, 2); // Extract grade level (X, XI, XII)
                if (!isset($studentsByGrade[$gradeLevel])) {
                    $studentsByGrade[$gradeLevel] = [];
                }
                $studentsByGrade[$gradeLevel][] = $student;
            }

            // Assign unassigned students to classes
            foreach ($studentsByGrade as $gradeLevel => $gradeStudents) {
                $gradeClasses = $classes->filter(function ($class) use ($gradeLevel) {
                    return strpos($class->name, $gradeLevel) === 0;
                })->values(); // Convert to array with numeric keys

                if ($gradeClasses->isNotEmpty()) {
                    $classCount = $gradeClasses->count();
                    foreach ($gradeStudents as $index => $student) {
                        $class = $gradeClasses[$index % $classCount];
                        DB::table('student_class')->insert([
                            'student_id' => $student->id,
                            'school_class_id' => $class->id,
                            'academic_year_id' => $activeYear->id,
                            'is_promoted' => false,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
