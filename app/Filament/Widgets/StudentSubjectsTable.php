<?php

namespace App\Filament\Widgets;

use App\Models\Grade;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class StudentSubjectsTable extends BaseWidget
{
    protected static ?string $heading = 'Mata Pelajaran yang Perlu Perhatian';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 2;

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->role->name === 'Student';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Grade::query()
                    ->where('user_id', Auth::id())
                    ->whereColumn('score', '<', 'subjects.minimum_score')
                    ->join('subjects', 'grades.subject_id', '=', 'subjects.id')
                    ->join('academic_years', 'grades.academic_year_id', '=', 'academic_years.id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('score')
                    ->label('Nilai')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subjects.minimum_score')
                    ->label('KKM')
                    ->numeric()
                    ->sortable()
                    ->state(fn($record) => $record->subject->minimum_score),
                Tables\Columns\TextColumn::make('semester')
                    ->label('Semester')
                    ->formatStateUsing(fn(string $state): string => "Semester {$state}")
                    ->sortable(),
                Tables\Columns\TextColumn::make('academic_years.name')
                    ->label('Tahun Ajaran')
                    ->sortable()
                    ->state(fn($record) => $record->academicYear->name),
            ])
            ->defaultSort('score', 'asc')
            ->paginated(false);
    }
}
