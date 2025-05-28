<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('subjects')->insert([
            ['name' => 'Matematika', 'code' => 'MTK', 'description' => 'Mata pelajaran matematika mencakup aljabar, geometri, dan kalkulus', 'minimum_score' => 70, 'teacher_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fisika', 'code' => 'FIS', 'description' => 'Mata pelajaran fisika mencakup mekanika, termodinamika, dan elektromagnetik', 'minimum_score' => 70, 'teacher_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kimia', 'code' => 'KIM', 'description' => 'Mata pelajaran kimia mencakup struktur atom, reaksi kimia, dan stoikiometri', 'minimum_score' => 70, 'teacher_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Biologi', 'code' => 'BIO', 'description' => 'Mata pelajaran biologi mencakup sel, genetika, dan ekosistem', 'minimum_score' => 70, 'teacher_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIN', 'description' => 'Mata pelajaran bahasa Indonesia mencakup tata bahasa, sastra, dan menulis', 'minimum_score' => 75, 'teacher_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bahasa Inggris', 'code' => 'BIG', 'description' => 'Mata pelajaran bahasa Inggris mencakup grammar, reading, dan speaking', 'minimum_score' => 75, 'teacher_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sejarah Indonesia', 'code' => 'SEJ', 'description' => 'Mata pelajaran sejarah Indonesia mencakup perjuangan kemerdekaan dan perkembangan bangsa', 'minimum_score' => 70, 'teacher_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Geografi', 'code' => 'GEO', 'description' => 'Mata pelajaran geografi mencakup peta, iklim, dan sumber daya alam', 'minimum_score' => 70, 'teacher_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ekonomi', 'code' => 'EKO', 'description' => 'Mata pelajaran ekonomi mencakup mikroekonomi dan makroekonomi', 'minimum_score' => 70, 'teacher_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sosiologi', 'code' => 'SOS', 'description' => 'Mata pelajaran sosiologi mencakup interaksi sosial dan struktur masyarakat', 'minimum_score' => 70, 'teacher_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pendidikan Agama', 'code' => 'PAI', 'description' => 'Mata pelajaran pendidikan agama mencakup nilai-nilai keagamaan dan moral', 'minimum_score' => 75, 'teacher_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pendidikan Pancasila', 'code' => 'PPKN', 'description' => 'Mata pelajaran pendidikan pancasila mencakup nilai-nilai pancasila dan kewarganegaraan', 'minimum_score' => 75, 'teacher_id' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}