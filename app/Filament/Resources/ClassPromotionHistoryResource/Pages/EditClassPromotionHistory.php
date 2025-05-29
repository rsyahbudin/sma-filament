<?php

namespace App\Filament\Resources\ClassPromotionHistoryResource\Pages;

use App\Filament\Resources\ClassPromotionHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClassPromotionHistory extends EditRecord
{
    protected static string $resource = ClassPromotionHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
