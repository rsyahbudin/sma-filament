<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Subject;

class TeacherSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = User::whereHas('role', function ($q) {
            $q->where('name', 'Teacher');
        })->get();
        $subjects = Subject::all();
        foreach ($teachers as $teacher) {
            $randomSubjects = $subjects->random(2);
            $teacher->subjects()->syncWithoutDetaching($randomSubjects->pluck('id')->toArray());
        }
    }
}
