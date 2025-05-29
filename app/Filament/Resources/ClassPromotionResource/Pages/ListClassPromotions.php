<?php

namespace App\Filament\Resources\ClassPromotionResource\Pages;

use App\Filament\Resources\ClassPromotionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClassPromotions extends ListRecords
{
    protected static string $resource = ClassPromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
