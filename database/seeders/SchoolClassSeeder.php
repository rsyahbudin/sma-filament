<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activeAcademicYearId = DB::table('academic_years')->where('is_active', 1)->value('id');

        // Assign teachers dynamically
        $teacherIds = DB::table('users')->where('role_id', DB::table('roles')->where('name', 'Teacher')->value('id'))->pluck('id');
        $teacherIndex = 0;

        $classes = [
            ['X IPA 1', 'X-IPA1', '10'],
            ['X IPA 2', 'X-IPA2', '10'],
            ['X IPS 1', 'X-IPS1', '10'],
            ['X IPS 2', 'X-IPS2', '10'],
            ['XI IPA 1', 'XI-IPA1', '11'],
            ['XI IPA 2', 'XI-IPA2', '11'],
            ['XI IPS 1', 'XI-IPS1', '11'],
            ['XI IPS 2', 'XI-IPS2', '11'],
            ['XII IPA 1', 'XII-IPA1', '12'],
            ['XII IPA 2', 'XII-IPA2', '12'],
            ['XII IPS 1', 'XII-IPS1', '12'],
            ['XII IPS 2', 'XII-IPS2', '12'],
        ];

        foreach ($classes as $class) {
            DB::table('school_classes')->insert([
                'name' => $class[0],
                'code' => $class[1],
                'grade_level' => $class[2],
                'academic_year_id' => $activeAcademicYearId,
                'teacher_id' => $teacherIds[$teacherIndex], // Assign teacher as homeroom
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $teacherIndex = ($teacherIndex + 1) % count($teacherIds); // Cycle through teachers
        }
    }
}