<?php

namespace App\Filament\Resources\StudentReportResource\Pages;

use App\Filament\Resources\StudentReportResource;
use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStudentReports extends ListRecords
{
    protected static string $resource = StudentReportResource::class;

    public function getHeading(): string
    {
        return 'Student Report';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Create Teacher'),
        ];
    }
}
