<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassPromotionResource\Pages;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class ClassPromotionResource extends Resource
{
    protected static ?string $model = SchoolClass::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Academic Management';

    protected static ?string $navigationLabel = 'Class Promotion';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('current_academic_year_id')
                    ->label('Current Academic Year')
                    ->options(AcademicYear::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $currentYear = AcademicYear::find($state);
                            $nextYear = AcademicYear::where('start_date', '>', $currentYear->end_date)
                                ->orderBy('start_date')
                                ->first();
                            $set('next_academic_year_id', $nextYear?->id);
                        }
                    }),
                Forms\Components\Select::make('next_academic_year_id')
                    ->label('Next Academic Year')
                    ->options(AcademicYear::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled(),
                Forms\Components\Select::make('current_class_id')
                    ->label('Current Class')
                    ->options(SchoolClass::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $currentClass = SchoolClass::find($state);
                            $nextClass = SchoolClass::where('grade_level', $currentClass->grade_level + 1)
                                ->where('name', 'like', '%' . substr($currentClass->name, -2))
                                ->first();
                            $set('next_class_id', $nextClass?->id);
                        }
                    }),
                Forms\Components\Select::make('next_class_id')
                    ->label('Next Class')
                    ->options(SchoolClass::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled(),
                Forms\Components\Section::make('Promotion Criteria')
                    ->schema([
                        Forms\Components\TextInput::make('minimum_average_score')
                            ->label('Minimum Average Score')
                            ->numeric()
                            ->default(75)
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        Forms\Components\TextInput::make('maximum_failed_subjects')
                            ->label('Maximum Failed Subjects')
                            ->numeric()
                            ->default(2)
                            ->minValue(0)
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Class')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grade_level')
                    ->label('Grade Level')
                    ->sortable(),
                Tables\Columns\TextColumn::make('students_count')
                    ->label('Total Students')
                    ->counts('students')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('grade_level')
                    ->options([
                        10 => 'Grade 10',
                        11 => 'Grade 11',
                        12 => 'Grade 12',
                    ])
                    ->label('Grade Level'),
            ])
            ->actions([
                Tables\Actions\Action::make('process_promotion')
                    ->label('Process Promotion')
                    ->icon('heroicon-o-arrow-trending-up')
                    ->action(function (SchoolClass $record, array $data) {
                        try {
                            DB::beginTransaction();

                            $currentYear = AcademicYear::find($data['current_academic_year_id']);
                            $nextYear = AcademicYear::find($data['next_academic_year_id']);
                            $nextClass = SchoolClass::find($data['next_class_id']);

                            // Get all students in the current class
                            $students = $record->students()
                                ->wherePivot('academic_year_id', $currentYear->id)
                                ->get();

                            foreach ($students as $student) {
                                // Calculate average score and count failed subjects
                                $grades = $student->grades()
                                    ->where('academic_year_id', $currentYear->id)
                                    ->where('semester', 2)
                                    ->get();

                                $averageScore = $grades->avg(function ($grade) {
                                    return ($grade->knowledge_score * 0.4) +
                                        ($grade->skill_score * 0.4) +
                                        ($grade->attitude_score * 0.2);
                                });

                                $failedSubjects = $grades->filter(function ($grade) {
                                    $finalScore = ($grade->knowledge_score * 0.4) +
                                        ($grade->skill_score * 0.4) +
                                        ($grade->attitude_score * 0.2);
                                    return $finalScore < 75;
                                })->count();

                                // Determine promotion status
                                $isPromoted = $averageScore >= $data['minimum_average_score'] &&
                                    $failedSubjects <= $data['maximum_failed_subjects'];

                                if ($isPromoted) {
                                    // If student is in grade 12, mark as graduated
                                    if ($record->grade_level === 12) {
                                        $student->update(['status' => 'graduated']);
                                    } else {
                                        // Move to next class
                                        $student->classes()->attach($nextClass->id, [
                                            'academic_year_id' => $nextYear->id,
                                            'is_promoted' => true,
                                        ]);
                                    }
                                } else {
                                    // Stay in the same class
                                    $student->classes()->attach($record->id, [
                                        'academic_year_id' => $nextYear->id,
                                        'is_promoted' => false,
                                    ]);
                                }
                            }

                            DB::commit();

                            Notification::make()
                                ->title('Class promotion processed successfully')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();

                            Notification::make()
                                ->title('Error processing class promotion')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->form([
                        Forms\Components\Select::make('current_academic_year_id')
                            ->label('Current Academic Year')
                            ->options(AcademicYear::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('next_academic_year_id')
                            ->label('Next Academic Year')
                            ->options(AcademicYear::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('next_class_id')
                            ->label('Next Class')
                            ->options(SchoolClass::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('minimum_average_score')
                            ->label('Minimum Average Score')
                            ->numeric()
                            ->default(75)
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        Forms\Components\TextInput::make('maximum_failed_subjects')
                            ->label('Maximum Failed Subjects')
                            ->numeric()
                            ->default(2)
                            ->minValue(0)
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Process Class Promotion')
                    ->modalDescription('Are you sure you want to process the class promotion? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, process promotion'),
            ])
            ->bulkActions([
                //
            ]);
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
            'index' => Pages\ListClassPromotions::route('/'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Academic Management';
    }

    public static function getNavigationLabel(): string
    {
        return 'Class Promotion';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-academic-cap';
    }

    public static function getNavigationSort(): ?int
    {
        return 7;
    }

    public static function canViewAny(): bool
    {
        return Auth::user()->role->name === 'Admin';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
