<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ambil data student_class terbaru (atau aktif)
        $studentClass = DB::table('student_class')
            ->where('student_id', $data['id'])
            ->orderByDesc('academic_year_id')
            ->first();

        if ($studentClass) {
            $data['class_id'] = [$studentClass->school_class_id];
            $data['academic_year_id'] = $studentClass->academic_year_id;
        }

        return $data;
    }
}
