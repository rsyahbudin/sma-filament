<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activeAcademicYearId = DB::table('academic_years')->where('is_active', 1)->value('id');
        $classes = DB::table('school_classes')->where('academic_year_id', $activeAcademicYearId)->get();
        $subjects = DB::table('subjects')->get();
        $teachers = DB::table('users')->where('role_id', DB::table('roles')->where('name', 'Teacher')->value('id'))->get();

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $startTimes = ['07:00:00', '07:45:00', '08:30:00', '09:15:00', '10:00:00', '10:45:00'];
        $endTimes = ['07:45:00', '08:30:00', '09:15:00', '10:00:00', '10:45:00', '11:30:00'];

        foreach ($classes as $class) {
            $subjectCounter = 0;
            foreach ($days as $day) {
                for ($i = 0; $i < 4; $i++) { // 4 lessons per day
                    if ($subjectCounter >= count($subjects)) {
                        $subjectCounter = 0; // Reset subject counter if all subjects are used
                    }
                    $subject = $subjects[$subjectCounter];
                    $teacher = $teachers->firstWhere('id', $subject->teacher_id); // Get the teacher associated with the subject

                    // Fallback if subject has no assigned teacher or teacher not found
                    if (!$teacher) {
                        $teacher = $teachers->random(); // Assign a random teacher
                    }

                    DB::table('schedules')->insert([
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacher->id,
                        'day' => $day,
                        'start_time' => $startTimes[$i],
                        'end_time' => $endTimes[$i],
                        'academic_year_id' => $activeAcademicYearId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $subjectCounter++;
                }
            }
        }
    }
}