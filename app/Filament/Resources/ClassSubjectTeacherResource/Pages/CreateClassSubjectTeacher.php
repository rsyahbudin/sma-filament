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
}
