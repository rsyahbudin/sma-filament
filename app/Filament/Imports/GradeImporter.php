<?php

namespace App\Filament\Imports;

use App\Models\Grade;
use App\Models\User;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GradeImporter extends Importer
{
    protected static ?string $model = Grade::class;

    public static function getColumns(): array
    {
        $teacher = Auth::user();

        return [
            ImportColumn::make('student_nis')
                ->label('Student NIS')
                ->requiredMapping()
                ->rules(['required'])
                ->helperText('Enter the student\'s NIS number')
                ->fillRecordUsing(function (Grade $record, string $state): void {
                    // NIS hanya digunakan untuk validasi, tidak disimpan
                }),
            ImportColumn::make('student_name')
                ->label('Student Name')
                ->requiredMapping()
                ->rules(['required'])
                ->helperText('Enter the student\'s full name')
                ->fillRecordUsing(function (Grade $record, string $state): void {
                    // Nama siswa hanya digunakan untuk validasi, tidak disimpan
                }),
            ImportColumn::make('subject_name')
                ->label('Subject')
                ->requiredMapping()
                ->rules(['required'])
                ->helperText('Enter the subject name')
                ->fillRecordUsing(function (Grade $record, string $state): void {
                    // Nama mata pelajaran hanya digunakan untuk validasi, tidak disimpan
                }),
            ImportColumn::make('class_name')
                ->label('Class')
                ->requiredMapping()
                ->rules(['required'])
                ->helperText('Enter the class name')
                ->fillRecordUsing(function (Grade $record, string $state): void {
                    // Nama kelas hanya digunakan untuk validasi, tidak disimpan
                }),
            ImportColumn::make('academic_year_name')
                ->label('Academic Year')
                ->requiredMapping()
                ->rules(['required'])
                ->helperText('Enter the academic year (e.g. 2023/2024)')
                ->fillRecordUsing(function (Grade $record, string $state): void {
                    // Nama tahun ajaran hanya digunakan untuk validasi, tidak disimpan
                }),
            ImportColumn::make('semester')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer', 'in:1,2'])
                ->helperText('Enter semester (1 or 2)'),
            ImportColumn::make('score')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0', 'max:100'])
                ->helperText('Enter score between 0-100'),
            ImportColumn::make('notes')
                ->helperText('Optional notes about the grade'),
        ];
    }


    public function resolveRecord(): ?Grade
    {
        try {
            Log::info('Processing import row:', $this->data);

            $student = User::where('nis', $this->data['student_nis'])
                ->where('name', $this->data['student_name'])
                ->first();

            if (!$student) {
                throw new RowImportFailedException("Student not found with NIS [{$this->data['student_nis']}] and name [{$this->data['student_name']}].");
            }

            $subject = Subject::where('name', $this->data['subject_name'])->first();
            if (!$subject) {
                throw new RowImportFailedException("Subject not found: [{$this->data['subject_name']}]");
            }

            $class = SchoolClass::where('name', $this->data['class_name'])->first();
            if (!$class) {
                throw new RowImportFailedException("Class not found: [{$this->data['class_name']}]");
            }

            $academicYear = AcademicYear::where('name', $this->data['academic_year_name'])->first();
            if (!$academicYear) {
                throw new RowImportFailedException("Academic year not found: [{$this->data['academic_year_name']}]");
            }

            // Find existing grade or create new one
            $grade = Grade::firstOrNew([
                'user_id' => $student->id,
                'subject_id' => $subject->id,
                'class_id' => $class->id,
                'academic_year_id' => $academicYear->id,
                'semester' => $this->data['semester'],
            ]);

            // Only set fields that exist in the grades table
            $grade->teacher_id = Auth::id();
            $grade->score = $this->data['score'];

            if (isset($this->data['notes'])) {
                $grade->notes = $this->data['notes'];
            }

            // Save the grade and check if it was successful
            if (!$grade->save()) {
                throw new RowImportFailedException("Failed to save grade for student [{$student->name}]");
            }

            Log::info('Successfully saved grade:', [
                'student' => $student->name,
                'subject' => $subject->name,
                'class' => $class->name,
                'score' => $grade->score
            ]);

            return $grade;
        } catch (\Exception $e) {
            Log::error('Error importing grade:', [
                'error' => $e->getMessage(),
                'data' => $this->data
            ]);
            throw $e;
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your grade import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }


    public static function hasExampleRows(): bool
    {
        return false;
    }

    public static function getExampleRows(): ?array
    {
        return null;
    }
}
