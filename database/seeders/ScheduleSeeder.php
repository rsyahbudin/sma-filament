<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Models\Schedule;
use App\Models\AcademicYear;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $classes = SchoolClass::all();
        $subjects = Subject::all();
        $activeYear = AcademicYear::where('is_active', true)->first();
        for ($i = 0; $i < 10; $i++) {
            $class = $classes->random();
            $subject = $subjects->random();
            // Cari guru yang mengajar subject ini
            $teacher = User::where('id', $subject->teacher_id)->first();
            if (!$teacher) {
                // Jika tidak ada guru, skip
                continue;
            }
            $day = $days[array_rand($days)];
            $startHour = rand(7, 13);
            $endHour = $startHour + 1;
            Schedule::create([
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'day' => $day,
                'start_time' => sprintf('%02d:00:00', $startHour),
                'end_time' => sprintf('%02d:00:00', $endHour),
                'academic_year_id' => $activeYear->id,
            ]);
        }
    }
}
