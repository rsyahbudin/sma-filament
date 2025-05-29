<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateClassSubjectAcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first academic year (or you can specify which one you want to use)
        $academicYear = DB::table('academic_years')->first();

        if ($academicYear) {
            // Update all existing class_subject records with the academic year
            DB::table('class_subject')
                ->whereNull('academic_year_id')
                ->update(['academic_year_id' => $academicYear->id]);
        }
    }
}
