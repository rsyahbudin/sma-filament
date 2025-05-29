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
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class GradeResource extends Resource
{
    protected static ?string $model = Grade::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Academic Management';

    protected static ?string $navigationLabel = 'Grades';

    protected static ?int $navigationSort = 5;

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
                    ->options(function () {
                        $user = Auth::user();
                        if ($user && $user->role && $user->role->name === 'Teacher') {
                            return Subject::where('teacher_id', $user->id)->pluck('name', 'id');
                        }
                        return Subject::pluck('name', 'id');
                    })
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
                        3 => 'Semester 3',
                        4 => 'Semester 4',
                        5 => 'Semester 5',
                        6 => 'Semester 6',
                    ])
                    ->required(),
                Forms\Components\Section::make('Grade Components')
                    ->schema([
                        Forms\Components\TextInput::make('knowledge_score')
                            ->label('Knowledge Score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        Forms\Components\TextInput::make('skill_score')
                            ->label('Skill Score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        Forms\Components\TextInput::make('attitude_score')
                            ->label('Attitude Score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        Forms\Components\TextInput::make('final_score')
                            ->label('Final Score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record) {
                                    $finalScore = ($record->knowledge_score * 0.4) +
                                        ($record->skill_score * 0.4) +
                                        ($record->attitude_score * 0.2);
                                    $component->state($finalScore);
                                }
                            }),
                    ])->columns(2),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
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
                    ->formatStateUsing(fn($state, $record) => $record->student?->name ?? '-')
                    ->visible(fn() => Auth::user()->role->name !== 'Student'),
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject.teachers.name')
                    ->label('Teacher')
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
                Tables\Columns\TextColumn::make('knowledge_score')
                    ->label('Knowledge')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('skill_score')
                    ->label('Skill')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('attitude_score')
                    ->label('Attitude')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('final_score')
                    ->label('Final Score')
                    ->numeric()
                    ->sortable()
                    ->state(function (Grade $record): float {
                        return ($record->knowledge_score * 0.4) +
                            ($record->skill_score * 0.4) +
                            ($record->attitude_score * 0.2);
                    }),
                Tables\Columns\IconColumn::make('is_passed')
                    ->label('Status')
                    ->boolean()
                    ->state(function (Grade $record): bool {
                        $finalScore = ($record->knowledge_score * 0.4) +
                            ($record->skill_score * 0.4) +
                            ($record->attitude_score * 0.2);
                        return $finalScore >= 75;
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('student')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Student')
                    ->visible(fn() => Auth::user()->role->name !== 'Student'),
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
                        3 => 'Semester 3',
                        4 => 'Semester 4',
                        5 => 'Semester 5',
                        6 => 'Semester 6',
                    ])
                    ->label('Semester'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn() => Auth::user()->role->name === 'Teacher'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => Auth::user()->role->name === 'Teacher'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('generateReport')
                    ->label('Generate Report Card')
                    ->icon('heroicon-o-document-text')
                    ->action(function () {
                        $user = Auth::user();
                        $grades = Grade::where('user_id', $user->id)
                            ->with(['student', 'subject', 'class', 'academicYear'])
                            ->get()
                            ->groupBy(['academic_year_id', 'semester']);

                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.report-card', [
                            'student' => $user,
                            'grades' => $grades,
                            'generated_at' => now(),
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, "report-card-{$user->name}.pdf");
                    })
                    ->visible(fn() => Auth::user()->role->name === 'Student'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()->role->name === 'Teacher'),
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

    public static function getNavigationGroup(): ?string
    {
        return 'Academic Management';
    }

    public static function getNavigationLabel(): string
    {
        return 'Grades';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-academic-cap';
    }

    public static function getNavigationSort(): ?int
    {
        return 5;
    }

    public static function getNavigationBadge(): ?string
    {
        if (Auth::user()->role->name === 'Student') {
            return static::getModel()::where('user_id', Auth::id())->count();
        }
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        if (Auth::user()->role->name === 'Student') {
            $count = static::getModel()::where('user_id', Auth::id())->count();
            return $count > 0 ? 'success' : 'warning';
        }
        return static::getModel()::count() > 10 ? 'warning' : 'primary';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        if (Auth::user()->role->name === 'Student') {
            return 'Total Nilai Anda';
        }
        return 'Total Penilaian';
    }

    public static function canViewAny(): bool
    {
        return Auth::user()->role->name === 'Admin' || Auth::user()->role->name === 'Teacher' || Auth::user()->role->name === 'Student';
    }

    public static function canCreate(): bool
    {
        return Auth::user()->role->name === 'Teacher';
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()->role->name === 'Teacher';
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()->role->name === 'Teacher';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Auth::user()->role->name === 'Student') {
            return $query->where('user_id', Auth::id());
        }

        return $query;
    }
}
