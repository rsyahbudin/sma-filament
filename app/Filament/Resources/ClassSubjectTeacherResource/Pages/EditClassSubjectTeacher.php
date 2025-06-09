<?php

namespace App\Filament\Resources\ClassSubjectTeacherResource\Pages;

use App\Filament\Resources\ClassSubjectTeacherResource;
use App\Filament\Traits\HasRedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClassSubjectTeacher extends EditRecord
{
    use HasRedirectToList;

    protected static string $resource = ClassSubjectTeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Update the teacher_id in the subjects table
        $this->record->subject()->update(['teacher_id' => $this->record->teacher_id]);
    }
}
