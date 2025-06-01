<?php

namespace App\Filament\Traits;

use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;

trait HasRedirectToList
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
