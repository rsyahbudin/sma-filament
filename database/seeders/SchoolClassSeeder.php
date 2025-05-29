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
            ['name' => 'X IPA 1', 'code' => 'X-IPA-1'],
            ['name' => 'X IPA 2', 'code' => 'X-IPA-2'],
            ['name' => 'XI IPA 1', 'code' => 'XI-IPA-1'],
            ['name' => 'XI IPA 2', 'code' => 'XI-IPA-2'],
            ['name' => 'X IPS 1', 'code' => 'X-IPS-1'],
        ];
        foreach ($classes as $class) {
            SchoolClass::create([
                'name' => $class['name'],
                'code' => $class['code'],
                'academic_year_id' => $years->random()->id,
                'teacher_id' => $teachers->random(),
            ]);
        }
    }
}
