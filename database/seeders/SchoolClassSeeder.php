<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\User;

class SchoolClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gradeLevels = ['X', 'XI', 'XII'];
        $classTypes = ['IPA', 'IPS'];
        $classNumbers = [1, 2];

        $activeYear = AcademicYear::where('is_active', true)->first();

        // Get all teachers
        $teachers = User::whereHas('role', function ($query) {
            $query->where('name', 'Teacher');
        })->get();

        $teacherIndex = 0;
        foreach ($gradeLevels as $gradeLevel) {
            foreach ($classTypes as $type) {
                foreach ($classNumbers as $number) {
                    $code = $gradeLevel . '-' . $type . $number;
                    $name = $gradeLevel . ' ' . $type . ' ' . $number;

                    // Create class with teacher_id
                    SchoolClass::create([
                        'name' => $name,
                        'code' => $code,
                        'academic_year_id' => $activeYear ? $activeYear->id : 1,
                        'teacher_id' => isset($teachers[$teacherIndex]) ? $teachers[$teacherIndex]->id : null,
                    ]);

                    $teacherIndex++;
                }
            }
        }
    }
}
