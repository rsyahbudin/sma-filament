<?php

namespace App\Filament\Resources\ClassPromotionResource\Pages;

use App\Filament\Resources\ClassPromotionResource;
use App\Filament\Traits\HasRedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClassPromotion extends EditRecord
{
    use HasRedirectToList;

    protected static string $resource = ClassPromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
