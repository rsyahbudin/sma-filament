<?php

namespace App\Filament\Resources\SchoolClassResource\Pages;

use App\Filament\Resources\SchoolClassResource;
use App\Filament\Traits\HasRedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchoolClass extends EditRecord
{
    use HasRedirectToList;

    protected static string $resource = SchoolClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
