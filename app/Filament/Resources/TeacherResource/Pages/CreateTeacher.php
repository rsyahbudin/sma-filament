<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Filament\Traits\HasRedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateTeacher extends CreateRecord
{
    use HasRedirectToList;

    protected static string $resource = TeacherResource::class;

    public function getHeading(): string
    {
        return 'Create Teacher';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password'] = Hash::make('teacher123');
        $data['role_id'] = 2; // ID role Teacher

        return $data;
    }

    protected function afterCreate(): void
    {
        // Additional logic after creating the teacher
    }
}
