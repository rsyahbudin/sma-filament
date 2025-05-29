<?php

namespace App\Filament\Resources\AcademicYearResource\Pages;

use App\Filament\Resources\AcademicYearResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Models\SchoolClass;

class CreateAcademicYear extends CreateRecord
{
    protected static string $resource = AcademicYearResource::class;

    protected function afterCreate(): void
    {
        $data = $this->form->getState();

        if (empty($data['source_year_id'])) {
            return;
        }

        $sourceClasses = SchoolClass::where('academic_year_id', $data['source_year_id'])->get();
        $count = 0;

        foreach ($sourceClasses as $class) {
            $newCode = $class->code . '-' . $this->record->id;
            $exists = SchoolClass::where('code', $newCode)
                ->where('academic_year_id', $this->record->id)
                ->exists();

            if ($exists) continue;

            SchoolClass::create([
                'name' => $class->name,
                'code' => $newCode,
                'level' => $class->level,
                'major' => $class->major,
                'academic_year_id' => $this->record->id,
                'teacher_id' => null,
            ]);
            $count++;
        }

        if ($count > 0) {
            Notification::make()
                ->title("Berhasil membuat $count kelas baru untuk tahun ajaran {$this->record->name}")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Tidak ada kelas yang diduplikasi dari tahun ajaran sumber!')
                ->danger()
                ->send();
        }
    }
}
