<?php

namespace App\Filament\Resources\StudentTransferResource\Pages;

use App\Filament\Resources\StudentTransferResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudentTransfers extends ListRecords
{
    protected static string $resource = StudentTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
