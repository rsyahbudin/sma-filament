<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\AcademicYear;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $students = User::whereHas('role', function ($q) {
            $q->where('name', 'Student');
        })->get();
        foreach ($students as $student) {
            foreach ($student->classes as $class) {
                foreach ($class->subjects as $subject) {
                    Grade::create([
                        'user_id' => $student->id,
                        'subject_id' => $subject->id,
                        'class_id' => $class->id,
                        'academic_year_id' => $activeYear->id,
                        'semester' => 1,
                        'score' => rand(60, 100),
                        'notes' => 'Nilai otomatis',
                    ]);
                }
            }
        }
    }
}
