<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeResource\Pages;
use App\Models\Grade;
use App\Models\User;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GradeResource extends Resource
{
    protected static ?string $model = Grade::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Academic Management';

    protected static ?string $navigationLabel = 'Grades';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Student')
                    ->options(User::whereHas('role', function ($query) {
                        $query->where('name', 'Student');
                    })->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $user = \App\Models\User::find($state);
                        $currentClass = $user?->classes()->latest('student_class.academic_year_id')->first();
                        $set('class_id', $currentClass?->id);
                        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
                        $set('academic_year_id', $activeYear?->id);
                    }),
                Forms\Components\Select::make('class_id')
                    ->label('Class')
                    ->options(SchoolClass::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('subject_id')
                    ->label('Subject')
                    ->options(Subject::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('academic_year_id')
                    ->label('Academic Year')
                    ->options(AcademicYear::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('semester')
                    ->options([
                        1 => 'Semester 1',
                        2 => 'Semester 2',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('score')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(100),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state, $record) => $record->student?->name ?? '-'),
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('class.name')
                    ->label('Class')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('academicYear.name')
                    ->label('Academic Year')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('semester')
                    ->formatStateUsing(fn(string $state): string => "Semester {$state}")
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('score')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_passed')
                    ->label('Status')
                    ->boolean()
                    ->state(function (Grade $record): bool {
                        return $record->isPassed();
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('student')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Student'),
                Tables\Filters\SelectFilter::make('subject')
                    ->relationship('subject', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Subject'),
                Tables\Filters\SelectFilter::make('class')
                    ->relationship('class', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Class'),
                Tables\Filters\SelectFilter::make('academic_year')
                    ->relationship('academicYear', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Academic Year'),
                Tables\Filters\SelectFilter::make('semester')
                    ->options([
                        1 => 'Semester 1',
                        2 => 'Semester 2',
                    ])
                    ->label('Semester'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['student', 'subject', 'class', 'academicYear']));
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGrades::route('/'),
            'create' => Pages\CreateGrade::route('/create'),
            'edit' => Pages\EditGrade::route('/{record}/edit'),
        ];
    }
}
