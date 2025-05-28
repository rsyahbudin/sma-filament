<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeacherSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachers = DB::table('users')->where('role_id', DB::table('roles')->where('name', 'Teacher')->value('id'))->get();
        $subjects = DB::table('subjects')->get();

        $data = [];
        // Assign each subject to a teacher
        for ($i = 0; $i < count($subjects); $i++) {
            $teacherId = $teachers[$i % count($teachers)]->id; // Cycle through teachers
            $data[] = [
                'user_id' => $teacherId,
                'subject_id' => $subjects[$i]->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('teacher_subject')->insert($data);
    }
}