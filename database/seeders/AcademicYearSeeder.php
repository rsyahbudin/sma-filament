<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $years = [
            ['name' => '2020/2021', 'start_date' => '2020-07-01', 'end_date' => '2021-06-30', 'is_active' => false],
            ['name' => '2021/2022', 'start_date' => '2021-07-01', 'end_date' => '2022-06-30', 'is_active' => false],
            ['name' => '2022/2023', 'start_date' => '2022-07-01', 'end_date' => '2023-06-30', 'is_active' => false],
            ['name' => '2023/2024', 'start_date' => '2023-07-01', 'end_date' => '2024-06-30', 'is_active' => false],
            ['name' => '2024/2025', 'start_date' => '2024-07-01', 'end_date' => '2025-06-30', 'is_active' => true],
        ];
        foreach ($years as $year) {
            AcademicYear::create($year);
        }
    }
}
