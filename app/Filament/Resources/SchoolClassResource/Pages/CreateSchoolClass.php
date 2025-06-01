<?php

namespace App\Filament\Resources\SchoolClassResource\Pages;

use App\Filament\Resources\SchoolClassResource;
use App\Filament\Traits\HasRedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSchoolClass extends CreateRecord
{
    use HasRedirectToList;

    protected static string $resource = SchoolClassResource::class;
}
