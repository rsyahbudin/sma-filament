<?php

namespace App\Filament\Resources\ClassPromotionResource\Pages;

use App\Filament\Resources\ClassPromotionResource;
use App\Filament\Traits\HasRedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateClassPromotion extends CreateRecord
{
    use HasRedirectToList;

    protected static string $resource = ClassPromotionResource::class;
}
