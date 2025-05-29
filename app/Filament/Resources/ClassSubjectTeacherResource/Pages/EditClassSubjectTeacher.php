<?php

namespace App\Filament\Resources\ClassSubjectTeacherResource\Pages;

use App\Filament\Resources\ClassSubjectTeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClassSubjectTeacher extends EditRecord
{
    protected static string $resource = ClassSubjectTeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
