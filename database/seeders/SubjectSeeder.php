<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\User;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachers = User::whereHas('role', function ($q) {
            $q->where('name', 'Teacher');
        })->get();
        $subjects = [
            ['name' => 'Matematika', 'code' => 'MTK'],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIN'],
            ['name' => 'Fisika', 'code' => 'FIS'],
            ['name' => 'Kimia', 'code' => 'KIM'],
            ['name' => 'Ekonomi', 'code' => 'EKO'],
        ];
        foreach ($subjects as $i => $subject) {
            $teacher = $teachers[$i] ?? $teachers[0];
            Subject::create([
                'name' => $subject['name'],
                'code' => $subject['code'],
                'teacher_id' => $teacher->id,
            ]);
        }
    }
}
