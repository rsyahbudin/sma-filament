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
        $subjects = [
            [
                'name' => 'Matematika',
                'code' => 'MTK',
                'minimum_score' => 70,
                'description' => 'Mata pelajaran matematika mencakup aljabar, geometri, dan kalkulus',
            ],
            [
                'name' => 'Fisika',
                'code' => 'FIS',
                'minimum_score' => 70,
                'description' => 'Mata pelajaran fisika mencakup mekanika, termodinamika, dan elektromagnetik',
            ],
            [
                'name' => 'Kimia',
                'code' => 'KIM',
                'minimum_score' => 70,
                'description' => 'Mata pelajaran kimia mencakup struktur atom, reaksi kimia, dan stoikiometri',
            ],
            [
                'name' => 'Biologi',
                'code' => 'BIO',
                'minimum_score' => 70,
                'description' => 'Mata pelajaran biologi mencakup sel, genetika, dan ekosistem',
            ],
            [
                'name' => 'Bahasa Indonesia',
                'code' => 'BIN',
                'minimum_score' => 75,
                'description' => 'Mata pelajaran bahasa Indonesia mencakup tata bahasa, sastra, dan menulis',
            ],
            [
                'name' => 'Bahasa Inggris',
                'code' => 'BIG',
                'minimum_score' => 75,
                'description' => 'Mata pelajaran bahasa Inggris mencakup grammar, reading, dan speaking',
            ],
            [
                'name' => 'Sejarah Indonesia',
                'code' => 'SEJ',
                'minimum_score' => 70,
                'description' => 'Mata pelajaran sejarah Indonesia mencakup perjuangan kemerdekaan dan perkembangan bangsa',
            ],
            [
                'name' => 'Geografi',
                'code' => 'GEO',
                'minimum_score' => 70,
                'description' => 'Mata pelajaran geografi mencakup peta, iklim, dan sumber daya alam',
            ],
            [
                'name' => 'Ekonomi',
                'code' => 'EKO',
                'minimum_score' => 70,
                'description' => 'Mata pelajaran ekonomi mencakup mikroekonomi dan makroekonomi',
            ],
            [
                'name' => 'Sosiologi',
                'code' => 'SOS',
                'minimum_score' => 70,
                'description' => 'Mata pelajaran sosiologi mencakup interaksi sosial dan struktur masyarakat',
            ],
            [
                'name' => 'Pendidikan Agama',
                'code' => 'PAI',
                'minimum_score' => 75,
                'description' => 'Mata pelajaran pendidikan agama mencakup nilai-nilai keagamaan dan moral',
            ],
            [
                'name' => 'Pendidikan Pancasila',
                'code' => 'PPKN',
                'minimum_score' => 75,
                'description' => 'Mata pelajaran pendidikan pancasila mencakup nilai-nilai pancasila dan kewarganegaraan',
            ],
        ];

        $teachers = User::whereHas('role', function ($query) {
            $query->where('name', 'Teacher');
        })->get();

        foreach ($subjects as $index => $subject) {
            $createdSubject = Subject::create($subject);

            // Assign teacher to subject
            if (isset($teachers[$index])) {
                $createdSubject->teachers()->attach($teachers[$index]->id);
            }
        }
    }
}
