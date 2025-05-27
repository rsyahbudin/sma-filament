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
        $students = User::whereHas('role', function ($q) {
            $q->where('name', 'Student');
        })->get();
        $classes = SchoolClass::all();
        $years = AcademicYear::all();
        foreach ($students as $student) {
            $class = $classes->random();
            $year = $years->random();
            $student->classes()->attach($class->id, [
                'is_promoted' => false,
                'academic_year_id' => $year->id,
            ]);
        }
    }
}
