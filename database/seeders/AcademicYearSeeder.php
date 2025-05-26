<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academicYears = [
            [
                'name' => '2023/2024',
                'start_date' => '2023-07-01',
                'end_date' => '2024-06-30',
                'is_active' => true,
            ],
            [
                'name' => '2024/2025',
                'start_date' => '2024-07-01',
                'end_date' => '2025-06-30',
                'is_active' => false,
            ],
        ];

        foreach ($academicYears as $academicYear) {
            AcademicYear::create($academicYear);
        }
    }
}
