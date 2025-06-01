<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource;
use App\Filament\Traits\HasRedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSchedule extends CreateRecord
{
    use HasRedirectToList;

    protected static string $resource = ScheduleResource::class;
}
