<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\AcademicYear;

class ClassSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $classes = SchoolClass::all();
        $subjects = Subject::all();
        $academicYear = AcademicYear::first(); // Get the first academic year

        foreach ($classes as $class) {
            $randomSubjects = $subjects->random(3);
            $subjectIds = $randomSubjects->pluck('id')->toArray();

            // Create an array with academic_year_id for each subject
            $syncData = collect($subjectIds)->mapWithKeys(function ($subjectId) use ($academicYear) {
                return [$subjectId => ['academic_year_id' => $academicYear->id]];
            })->toArray();

            $class->subjects()->syncWithoutDetaching($syncData);
        }
    }
}
