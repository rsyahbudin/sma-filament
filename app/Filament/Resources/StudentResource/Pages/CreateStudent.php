<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Filament\Traits\HasRedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateStudent extends CreateRecord
{
    use HasRedirectToList;

    protected static string $resource = StudentResource::class;

    public function getHeading(): string
    {
        return 'Create Student';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password'] = Hash::make('student123');
        $data['role_id'] = 3; // ID role Student

        // Generate NIS otomatis
        $currentYear = date('Y');
        $lastNIS = \App\Models\User::where('nis', 'like', $currentYear . '%')
            ->orderBy('nis', 'desc')
            ->first();
        if ($lastNIS && $lastNIS->nis) {
            $sequence = (int)substr($lastNIS->nis, -4);
            $newSequence = $sequence + 1;
        } else {
            $newSequence = 1;
        }
        $data['nis'] = $currentYear . str_pad($newSequence, 4, '0', STR_PAD_LEFT);

        return $data;
    }
}
