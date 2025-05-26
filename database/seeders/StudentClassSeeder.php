<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\AcademicYear;

class StudentClassSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::whereHas('role', function ($query) {
            $query->where('name', 'Student');
        })->get();

        $classes = SchoolClass::all();
        $academicYears = AcademicYear::all();

        foreach ($students as $student) {
            // Assign student to 1-2 random classes
            $randomClasses = $classes->random(rand(1, 2));
            foreach ($randomClasses as $class) {
                $student->classes()->attach($class->id, [
                    'is_promoted' => rand(0, 1),
                    'academic_year_id' => $academicYears->random()->id,
                ]);
            }
        }
    }
}
