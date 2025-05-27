<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;
use App\Models\Subject;

class ClassSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $classes = SchoolClass::all();
        $subjects = Subject::all();
        foreach ($classes as $class) {
            $randomSubjects = $subjects->random(3);
            $class->subjects()->syncWithoutDetaching($randomSubjects->pluck('id')->toArray());
        }
    }
}
 