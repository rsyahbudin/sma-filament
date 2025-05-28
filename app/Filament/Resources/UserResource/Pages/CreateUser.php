<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected $studentData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Store the data we need for afterCreate
        $this->studentData = [
            'class_id' => $data['class_id'] ?? null,
            'academic_year_id' => $data['academic_year_id'] ?? null,
        ];

        // Log the data for debugging
        Log::info('Student data before create:', $this->studentData);

        // Remove fields that shouldn't be in users table
        unset($data['class_id']);
        unset($data['academic_year_id']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->record;

        if ($user->role->name === 'Student' && isset($this->studentData['class_id'])) {
            $classes = $this->studentData['class_id'];
            $academicYearId = $this->studentData['academic_year_id'];

            // Log the data for debugging
            Log::info('Student data in afterCreate:', [
                'user_id' => $user->id,
                'classes' => $classes,
                'academic_year_id' => $academicYearId
            ]);

            if (!$academicYearId) {
                Log::error('Academic year ID is missing for student:', ['user_id' => $user->id]);
                return;
            }

            // Insert directly into student_class table
            foreach ($classes as $classId) {
                try {
                    DB::table('student_class')->insert([
                        'student_id' => $user->id,
                        'school_class_id' => $classId,
                        'academic_year_id' => $academicYearId,
                        'is_promoted' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    Log::info('Successfully inserted student class record:', [
                        'student_id' => $user->id,
                        'class_id' => $classId,
                        'academic_year_id' => $academicYearId
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to insert student class record:', [
                        'error' => $e->getMessage(),
                        'student_id' => $user->id,
                        'class_id' => $classId,
                        'academic_year_id' => $academicYearId
                    ]);
                }
            }
        }
    }
}
