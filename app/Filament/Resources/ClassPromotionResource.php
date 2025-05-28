<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassPromotionResource\Pages;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\User;
use App\Models\PromotionHistory;
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
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Support\Collection;

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
                Section::make('Academic Year Selection')
                    ->schema([
                        Select::make('current_academic_year_id')
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
                        Select::make('next_academic_year_id')
                            ->label('Next Academic Year')
                            ->options(AcademicYear::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(),
                    ])->columns(2),

                Section::make('Class Selection')
                    ->schema([
                        Select::make('current_class_id')
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
                        Select::make('next_class_id')
                            ->label('Next Class')
                            ->options(SchoolClass::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(),
                    ])->columns(2),

                Section::make('Promotion Criteria')
                    ->schema([
                        TextInput::make('minimum_average_score')
                            ->label('Minimum Average Score')
                            ->numeric()
                            ->default(75)
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        TextInput::make('maximum_failed_subjects')
                            ->label('Maximum Failed Subjects')
                            ->numeric()
                            ->default(2)
                            ->minValue(0)
                            ->required(),
                    ])->columns(2),

                Section::make('Additional Settings')
                    ->schema([
                        Toggle::make('send_notifications')
                            ->label('Send Notifications to Students/Parents')
                            ->default(true),
                        Textarea::make('notes')
                            ->label('Additional Notes')
                            ->placeholder('Enter any additional notes about this promotion process')
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Class')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('grade_level')
                    ->label('Grade Level')
                    ->sortable(),
                TextColumn::make('students_count')
                    ->label('Total Students')
                    ->counts('students')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('Promotion Status')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'pending',
                        'danger' => 'failed',
                    ])
                    ->getStateUsing(fn(SchoolClass $record) => $record->promotion_status),
            ])
            ->filters([
                SelectFilter::make('grade_level')
                    ->options([
                        10 => 'Grade 10',
                        11 => 'Grade 11',
                        12 => 'Grade 12',
                    ])
                    ->label('Grade Level'),
                Filter::make('promotion_status')
                    ->form([
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['status'],
                            fn(Builder $query, $status): Builder => $query->where('promotion_status', $status),
                        );
                    }),
            ])
            ->actions([
                Action::make('preview_promotion')
                    ->label('Preview Promotion')
                    ->icon('heroicon-o-eye')
                    ->action(function (SchoolClass $record, array $data) {
                        // Preview logic here
                    })
                    ->form([
                        // Preview form fields
                    ]),
                Action::make('process_promotion')
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

                                $averageScore = $grades->avg('score');

                                $failedSubjects = $grades->filter(function ($grade) {
                                    return $grade->score < 75;
                                })->count();

                                // Determine promotion status
                                $isPromoted = $averageScore >= $data['minimum_average_score'] &&
                                    $failedSubjects <= $data['maximum_failed_subjects'];

                                // Create promotion history record
                                PromotionHistory::create([
                                    'student_id' => $student->id,
                                    'from_class_id' => $record->id,
                                    'to_class_id' => $isPromoted ? $nextClass->id : $record->id,
                                    'academic_year_id' => $currentYear->id,
                                    'average_score' => $averageScore,
                                    'failed_subjects' => $failedSubjects,
                                    'is_promoted' => $isPromoted,
                                    'notes' => $data['notes'] ?? null,
                                ]);

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

                                // Send notification if enabled
                                if ($data['send_notifications'] ?? false) {
                                    // Notification logic here
                                }
                            }

                            // Update class promotion status
                            $record->update(['promotion_status' => 'completed']);

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
                        Section::make('Academic Year Selection')
                            ->schema([
                                Select::make('current_academic_year_id')
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
                                Select::make('next_academic_year_id')
                                    ->label('Next Academic Year')
                                    ->options(AcademicYear::pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(),
                            ])->columns(2),

                        Section::make('Class Selection')
                            ->schema([
                                Select::make('next_class_id')
                                    ->label('Next Class')
                                    ->options(SchoolClass::pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Section::make('Promotion Criteria')
                            ->schema([
                                TextInput::make('minimum_average_score')
                                    ->label('Minimum Average Score')
                                    ->numeric()
                                    ->default(75)
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->required(),
                                TextInput::make('maximum_failed_subjects')
                                    ->label('Maximum Failed Subjects')
                                    ->numeric()
                                    ->default(2)
                                    ->minValue(0)
                                    ->required(),
                            ])->columns(2),

                        Section::make('Additional Settings')
                            ->schema([
                                Toggle::make('send_notifications')
                                    ->label('Send Notifications to Students/Parents')
                                    ->default(true),
                                Textarea::make('notes')
                                    ->label('Additional Notes')
                                    ->placeholder('Enter any additional notes about this promotion process')
                                    ->rows(3),
                            ]),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Process Class Promotion')
                    ->modalDescription('Are you sure you want to process the class promotion? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, process promotion'),
                Action::make('view_history')
                    ->label('View History')
                    ->icon('heroicon-o-clock')
                    ->url(fn(SchoolClass $record): string => route('filament.admin.resources.promotion-histories.index', ['class_id' => $record->id])),
                Action::make('export_report')
                    ->label('Export Report')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (SchoolClass $record) {
                        // Export logic here
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulk_process_promotion')
                    ->label('Process Promotion for Selected Classes')
                    ->icon('heroicon-o-arrow-trending-up')
                    ->action(function (Collection $records, array $data) {
                        // Bulk process logic here
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Process Multiple Class Promotions')
                    ->modalDescription('Are you sure you want to process promotions for the selected classes? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, process promotions'),
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
            'create' => Pages\CreateClassPromotion::route('/create'),
            'edit' => Pages\EditClassPromotion::route('/{record}/edit'),
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
        $user = Auth::user();
        return $user->role->name === 'Admin' || $user->role->name === 'Teacher';
    }

    public static function canCreate(): bool
    {
        return Auth::user()->role->name === 'Admin';
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()->role->name === 'Admin';
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()->role->name === 'Admin';
    }
}
