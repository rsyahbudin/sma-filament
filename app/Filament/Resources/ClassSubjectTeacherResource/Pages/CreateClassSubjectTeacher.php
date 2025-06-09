<?php

namespace App\Filament\Resources\ClassSubjectTeacherResource\Pages;

use App\Filament\Resources\ClassSubjectTeacherResource;
use App\Filament\Traits\HasRedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateClassSubjectTeacher extends CreateRecord
{
    use HasRedirectToList;

    protected static string $resource = ClassSubjectTeacherResource::class;

    public function getHeading(): string
    {
        return 'Create Teacher Assignment';
    }

    protected function afterCreate(): void
    {
        // Update the teacher_id in the subjects table
        $this->record->subject()->update(['teacher_id' => $this->record->teacher_id]);
    }

    protected function getRedirectUrl(): string
    {
        // Implement the logic to get the redirect URL
        return ''; // Placeholder return, actual implementation needed
    }
}
