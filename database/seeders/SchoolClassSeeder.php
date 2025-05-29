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
        $years = AcademicYear::all();
        $teachers = User::whereHas('role', function ($q) {
            $q->where('name', 'Teacher');
        })->pluck('id');
        $classes = [
            // X IPA
            ['name' => 'X IPA 1', 'code' => 'X-IPA-1', 'level' => 'X', 'major' => 'IPA'],
            ['name' => 'X IPA 2', 'code' => 'X-IPA-2', 'level' => 'X', 'major' => 'IPA'],
            // X IPS
            ['name' => 'X IPS 1', 'code' => 'X-IPS-1', 'level' => 'X', 'major' => 'IPS'],
            ['name' => 'X IPS 2', 'code' => 'X-IPS-2', 'level' => 'X', 'major' => 'IPS'],
            // XI IPA
            ['name' => 'XI IPA 1', 'code' => 'XI-IPA-1', 'level' => 'XI', 'major' => 'IPA'],
            ['name' => 'XI IPA 2', 'code' => 'XI-IPA-2', 'level' => 'XI', 'major' => 'IPA'],
            // XI IPS
            ['name' => 'XI IPS 1', 'code' => 'XI-IPS-1', 'level' => 'XI', 'major' => 'IPS'],
            ['name' => 'XI IPS 2', 'code' => 'XI-IPS-2', 'level' => 'XI', 'major' => 'IPS'],
            // XII IPA
            ['name' => 'XII IPA 1', 'code' => 'XII-IPA-1', 'level' => 'XII', 'major' => 'IPA'],
            ['name' => 'XII IPA 2', 'code' => 'XII-IPA-2', 'level' => 'XII', 'major' => 'IPA'],
            // XII IPS
            ['name' => 'XII IPS 1', 'code' => 'XII-IPS-1', 'level' => 'XII', 'major' => 'IPS'],
            ['name' => 'XII IPS 2', 'code' => 'XII-IPS-2', 'level' => 'XII', 'major' => 'IPS'],
        ];
        foreach ($classes as $class) {
            SchoolClass::create([
                'name' => $class['name'],
                'code' => $class['code'],
                'level' => $class['level'],
                'major' => $class['major'],
                'academic_year_id' => $years->random()->id,
                'teacher_id' => $teachers->random(),
            ]);
        }
    }
}
