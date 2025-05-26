<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            [
                'name' => 'Matematika',
                'code' => 'MTK',
                'description' => 'Mata pelajaran Matematika',
                'minimum_score' => 70,
            ],
            [
                'name' => 'Bahasa Indonesia',
                'code' => 'BIN',
                'description' => 'Mata pelajaran Bahasa Indonesia',
                'minimum_score' => 70,
            ],
            [
                'name' => 'Bahasa Inggris',
                'code' => 'BIG',
                'description' => 'Mata pelajaran Bahasa Inggris',
                'minimum_score' => 70,
            ],
            [
                'name' => 'Fisika',
                'code' => 'FIS',
                'description' => 'Mata pelajaran Fisika',
                'minimum_score' => 70,
            ],
            [
                'name' => 'Kimia',
                'code' => 'KIM',
                'description' => 'Mata pelajaran Kimia',
                'minimum_score' => 70,
            ],
            [
                'name' => 'Biologi',
                'code' => 'BIO',
                'description' => 'Mata pelajaran Biologi',
                'minimum_score' => 70,
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}
