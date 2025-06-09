<?php

namespace App\Filament\Resources\StudentReportResource\Pages;

use App\Filament\Resources\StudentReportResource;
use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Pages\CreateRecord;

class CreateStudentReport extends CreateRecord
{
    protected static string $resource = StudentReportResource::class;

    public function getHeading(): string
    {
        return 'Student Report';
    }

    protected function afterCreate(): void
    {
        // ... existing code ...
    }

    protected function getActions(): array
    {
        return [
            // ... existing code ...
        ];
    }
}
