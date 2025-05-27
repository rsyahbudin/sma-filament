<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $classes = SchoolClass::all();
        $subjects = Subject::all();
        $activeYear = AcademicYear::where('is_active', true)->first();

        if (!$activeYear) {
            return; // Skip if no active academic year
        }

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $timeSlots = [
            ['start' => '07:00', 'end' => '07:45'],
            ['start' => '07:45', 'end' => '08:30'],
            ['start' => '08:30', 'end' => '09:15'],
            ['start' => '09:15', 'end' => '10:00'],
            ['start' => '10:00', 'end' => '10:15'], // Istirahat
            ['start' => '10:15', 'end' => '11:00'],
            ['start' => '11:00', 'end' => '11:45'],
            ['start' => '11:45', 'end' => '12:30'],
            ['start' => '12:30', 'end' => '13:00'], // Istirahat
            ['start' => '13:00', 'end' => '13:45'],
            ['start' => '13:45', 'end' => '14:30'],
        ];

        foreach ($classes as $class) {
            $classSubjects = $subjects;

            // Add specific subjects based on class type
            if (str_contains($class->name, 'IPA')) {
                $classSubjects = $subjects->filter(function ($subject) {
                    return in_array($subject->code, ['MTK', 'FIS', 'KIM', 'BIO']);
                });
            } elseif (str_contains($class->name, 'IPS')) {
                $classSubjects = $subjects->filter(function ($subject) {
                    return in_array($subject->code, ['SEJ', 'BIN', 'BIG']);
                });
            }

            foreach ($days as $day) {
                $usedTimeSlots = [];

                foreach ($classSubjects as $subject) {
                    // Get the first teacher for this subject
                    $teacher = $subject->teachers()->first();

                    if (!$teacher) {
                        continue; // Skip if no teacher is assigned
                    }

                    // Find available time slot
                    foreach ($timeSlots as $slot) {
                        if (!in_array($slot['start'], $usedTimeSlots)) {
                            Schedule::create([
                                'class_id' => $class->id,
                                'subject_id' => $subject->id,
                                'teacher_id' => $teacher->id,
                                'academic_year_id' => $activeYear->id,
                                'day' => $day,
                                'start_time' => $slot['start'],
                                'end_time' => $slot['end'],
                            ]);

                            $usedTimeSlots[] = $slot['start'];
                            break;
                        }
                    }
                }
            }
        }
    }
}
