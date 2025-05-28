<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected $studentData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Store class data for afterCreate
        $this->studentData = [
            'class_id' => $data['class_id'],
            'academic_year_id' => $data['academic_year_id'],
        ];

        // Remove fields that shouldn't be in users table
        unset($data['class_id']);
        unset($data['academic_year_id']);

        // Set default password and role
        $data['password'] = Hash::make('student123');
        $data['role_id'] = 3; // ID role Student

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->record;

        // Insert into student_class table
        DB::table('student_class')->insert([
            'student_id' => $user->id,
            'school_class_id' => $this->studentData['class_id'],
            'academic_year_id' => $this->studentData['academic_year_id'],
            'is_promoted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
