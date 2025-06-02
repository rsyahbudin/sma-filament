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
        $exampleData = self::getExampleData($teacher->id);

        return [
            ImportColumn::make('student_nis')
                ->label('Student NIS')
                ->requiredMapping()
                ->rules(['required'])
                ->example($exampleData['student_nis'] ?? '')
                ->helperText('Enter the student\'s NIS number')
                ->fillRecordUsing(function (Grade $record, string $state): void {
                    // NIS hanya digunakan untuk validasi, tidak disimpan
                }),
            ImportColumn::make('student_name')
                ->label('Student Name')
                ->requiredMapping()
                ->rules(['required'])
                ->example($exampleData['student_name'] ?? '')
                ->helperText('Enter the student\'s full name')
                ->fillRecordUsing(function (Grade $record, string $state): void {
                    // Nama siswa hanya digunakan untuk validasi, tidak disimpan
                }),
            ImportColumn::make('subject_name')
                ->label('Subject')
                ->requiredMapping()
                ->rules(['required'])
                ->example($exampleData['subject_name'] ?? '')
                ->helperText('Enter the subject name')
                ->fillRecordUsing(function (Grade $record, string $state): void {
                    // Nama mata pelajaran hanya digunakan untuk validasi, tidak disimpan
                }),
            ImportColumn::make('class_name')
                ->label('Class')
                ->requiredMapping()
                ->rules(['required'])
                ->example($exampleData['class_name'] ?? '')
                ->helperText('Enter the class name')
                ->fillRecordUsing(function (Grade $record, string $state): void {
                    // Nama kelas hanya digunakan untuk validasi, tidak disimpan
                }),
            ImportColumn::make('academic_year_name')
                ->label('Academic Year')
                ->requiredMapping()
                ->rules(['required'])
                ->example($exampleData['academic_year_name'] ?? '')
                ->helperText('Enter the academic year (e.g. 2023/2024)')
                ->fillRecordUsing(function (Grade $record, string $state): void {
                    // Nama tahun ajaran hanya digunakan untuk validasi, tidak disimpan
                }),
            ImportColumn::make('semester')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer', 'in:1,2'])
                ->example($exampleData['semester'] ?? '')
                ->helperText('Enter semester (1 or 2)'),
            ImportColumn::make('score')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0', 'max:100'])
                ->example($exampleData['score'] ?? '')
                ->helperText('Enter score between 0-100'),
            ImportColumn::make('notes')
                ->example($exampleData['notes'] ?? '')
                ->helperText('Optional notes about the grade'),
        ];
    }

    protected static function getExampleData(int $teacherId): array
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) {
            return [];
        }

        $data = DB::table('users as u_teacher')
            ->join('class_subject_teacher as cst', 'u_teacher.id', '=', 'cst.teacher_id')
            ->join('subjects as s', 'cst.subject_id', '=', 's.id')
            ->join('school_classes as sc', 'cst.school_class_id', '=', 'sc.id')
            ->join('academic_years as ay', 'cst.academic_year_id', '=', 'ay.id')
            ->leftJoin('student_class as stc', function ($join) {
                $join->on('sc.id', '=', 'stc.school_class_id')
                    ->on('ay.id', '=', 'stc.academic_year_id');
            })
            ->leftJoin('users as u_student', 'stc.student_id', '=', 'u_student.id')
            ->leftJoin('grades as g', function ($join) {
                $join->on('u_student.id', '=', 'g.user_id')
                    ->on('s.id', '=', 'g.subject_id')
                    ->on('sc.id', '=', 'g.class_id')
                    ->on('ay.id', '=', 'g.academic_year_id')
                    ->on('cst.semester', '=', 'g.semester');
            })
            ->where('u_teacher.id', $teacherId)
            ->where('ay.is_active', true)
            ->whereNotNull('u_student.id')
            ->select([
                'u_student.nis as student_nis',
                'u_student.name as student_name',
                's.name as subject_name',
                'sc.name as class_name',
                'ay.name as academic_year_name',
                'cst.semester',
                'g.score',
                'g.notes'
            ])
            ->first();

        if (!$data) {
            return [
                'student_nis' => '12345',
                'student_name' => 'John Doe',
                'subject_name' => 'Mathematics',
                'class_name' => 'X IPA 1',
                'academic_year_name' => $activeYear->name,
                'semester' => 1,
                'score' => 85,
                'notes' => 'Good performance in class'
            ];
        }

        return (array) $data;
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
}
