<?php

namespace App\Filament\Exports;

use App\Models\Grade;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class GradeExporter extends Exporter
{
    protected static ?string $model = Grade::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('student.nis')
                ->label('Student NIS')
                ->state(function (Grade $record): string {
                    return $record->student?->nis ?? '-';
                }),
            ExportColumn::make('student.name')
                ->label('Student Name')
                ->state(function (Grade $record): string {
                    return $record->student?->name ?? '-';
                }),
            ExportColumn::make('subject.name')
                ->label('Subject')
                ->state(function (Grade $record): string {
                    return $record->subject?->name ?? '-';
                }),
            ExportColumn::make('class.name')
                ->label('Class')
                ->state(function (Grade $record): string {
                    return $record->class?->name ?? '-';
                }),
            ExportColumn::make('academicYear.name')
                ->label('Academic Year')
                ->state(function (Grade $record): string {
                    return $record->academicYear?->name ?? '-';
                }),
            ExportColumn::make('semester')
                ->label('Semester'),
            ExportColumn::make('score')
                ->label('Score'),
            ExportColumn::make('notes')
                ->label('Notes'),
            ExportColumn::make('teacher.name')
                ->label('Teacher')
                ->state(function (Grade $record): string {
                    return $record->teacher?->name ?? '-';
                }),
            ExportColumn::make('created_at')
                ->label('Created At')
                ->formatStateUsing(fn($state) => $state->format('d/m/Y H:i')),
            ExportColumn::make('updated_at')
                ->label('Updated At')
                ->formatStateUsing(fn($state) => $state->format('d/m/Y H:i')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your grade export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['student', 'subject', 'class', 'academicYear', 'teacher']);
    }
}
