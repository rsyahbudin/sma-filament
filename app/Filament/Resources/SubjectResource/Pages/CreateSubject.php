<?php

namespace App\Filament\Resources\SubjectResource\Pages;

use App\Filament\Resources\SubjectResource;
use App\Filament\Traits\HasRedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSubject extends CreateRecord
{
    use HasRedirectToList;

    protected static string $resource = SubjectResource::class;
}
