<?php

namespace App\Filament\Resources\ClassSubjectTeacherResource\Pages;

use App\Filament\Resources\ClassSubjectTeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClassSubjectTeachers extends ListRecords
{
    protected static string $resource = ClassSubjectTeacherResource::class;

    public function getHeading(): string
    {
        return 'Teaching Assignment';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Teacher Assignment'),
        ];
    }
}
