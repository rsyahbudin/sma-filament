<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassPromotionResource\Pages;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use App\Models\ClassPromotionHistory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class ClassPromotionResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Student Management';

    protected static ?string $navigationLabel = 'Class Promotion';

    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool
    {
        return Auth::user()->role->name === 'Admin';
    }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return Auth::user()->role->name === 'Admin';
    }

    public static function canCreate(): bool
    {
        return Auth::user()->role->name === 'Admin';
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return Auth::user()->role->name === 'Admin';
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return Auth::user()->role->name === 'Admin';
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('role', function ($query) {
                $query->where('name', 'Student');
            })
            ->where('is_graduated', false);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Student Information')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('Student')
                            ->options(function () {
                                $currentYear = AcademicYear::where('is_active', true)->first();
                                if (!$currentYear) return [];

                                return User::whereHas('role', fn($q) => $q->where('name', 'Student'))
                                    ->whereHas('classes', function ($query) use ($currentYear) {
                                        $query->where('academic_year_id', $currentYear->id)
                                            ->where('is_promoted', false);
                                    })
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (!$state) return;

                                $student = User::find($state);
                                $currentYear = AcademicYear::where('is_active', true)->first();
                                $currentClass = $student->classes()->wherePivot('academic_year_id', $currentYear?->id)->first();
                                $set('current_class_id', $currentClass?->id);

                                // Get failed subjects count
                                $failedSubjects = $student->grades()
                                    ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                                    ->where('score', '<', 70)
                                    ->count();

                                $set('failed_subjects_count', $failedSubjects);
                                // Get max failed subjects from settings
                                $maxFailed = (int) Setting::getValue('max_failed_subjects', 2);
                                $set('can_be_promoted', $failedSubjects <= $maxFailed);
                            }),
                        Forms\Components\Select::make('current_class_id')
                            ->label('Current Class')
                            ->options(SchoolClass::pluck('name', 'id'))
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('failed_subjects_count')
                            ->label('Failed Subjects')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Toggle::make('can_be_promoted')
                            ->label('Can Be Promoted')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Select::make('next_class_id')
                            ->label('Next Class')
                            ->options(SchoolClass::pluck('name', 'id'))
                            ->required()
                            ->visible(fn(callable $get) => $get('can_be_promoted')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->with(['grades' => function ($query) {
                    $query->whereHas('academicYear', fn($q) => $q->where('is_active', true));
                }]);
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_class')
                    ->getStateUsing(function ($record) {
                        $currentYear = AcademicYear::where('is_active', true)->first();
                        $currentClass = $record->classes()->wherePivot('academic_year_id', $currentYear?->id)->first();
                        return $currentClass ? "{$currentClass->name} ({$currentClass->level} {$currentClass->major})" : '-';
                    }),
                Tables\Columns\TextColumn::make('next_class')
                    ->getStateUsing(function ($record) {
                        $currentYear = AcademicYear::where('is_active', true)->first();
                        $currentClass = $record->classes()->wherePivot('academic_year_id', $currentYear?->id)->first();
                        if (!$currentClass) return '-';

                        // Jika sudah kelas XII, tidak ada kelas berikutnya
                        if ($currentClass->level === 'XII') {
                            return 'Lulus';
                        }

                        // Tentukan level berikutnya
                        $nextLevel = match ($currentClass->level) {
                            'X' => 'XI',
                            'XI' => 'XII',
                            default => null
                        };

                        if (!$nextLevel) return '-';

                        // Cari kelas dengan level dan major yang sama
                        $nextClass = SchoolClass::where('level', $nextLevel)
                            ->where('major', $currentClass->major)
                            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                            ->first();

                        return $nextClass ? "{$nextClass->name} ({$nextClass->level} {$nextClass->major})" : '-';
                    }),
                Tables\Columns\TextColumn::make('failed_subjects')
                    ->label('Failed Subjects')
                    ->getStateUsing(function ($record) {
                        $currentYear = AcademicYear::where('is_active', true)->first();
                        $currentClass = $record->classes()->wherePivot('academic_year_id', $currentYear?->id)->first();
                        $grades = $record->grades()
                            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                            ->get();
                        $failedSubjects = $grades->where('score', '<', 70)->count();
                        return $failedSubjects > 0 ? $failedSubjects : '-';
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withCount(['grades as failed_subjects_count' => function ($query) {
                            $query->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                                ->where('score', '<', 70);
                        }])
                            ->orderBy('failed_subjects_count', $direction);
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('grades', function ($query) use ($search) {
                            $query->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                                ->where('score', '<', 70);
                        });
                    }),
                Tables\Columns\IconColumn::make('can_be_promoted')
                    ->label('Can Be Promoted')
                    ->boolean()
                    ->state(function ($record) {
                        $currentYear = AcademicYear::where('is_active', true)->first();
                        $currentClass = $record->classes()->wherePivot('academic_year_id', $currentYear?->id)->first();
                        $failedSubjects = $record->grades()
                            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                            ->where('score', '<', 70)
                            ->count();

                        // Get max failed subjects from settings
                        $maxFailed = (int) Setting::getValue('max_failed_subjects', 2);

                        // Can only be promoted if not already promoted and has 2 or fewer failed subjects
                        return !$currentClass?->pivot->is_promoted && $failedSubjects <= $maxFailed;
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_promoted')
                    ->label('Promotion Status')
                    ->boolean()
                    ->state(function ($record) {
                        $currentYear = AcademicYear::where('is_active', true)->first();
                        $currentClass = $record->classes()->wherePivot('academic_year_id', $currentYear?->id)->first();
                        return $currentClass?->pivot->is_promoted ?? false;
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('promote')
                    ->label('Promote')
                    ->icon('heroicon-o-academic-cap')
                    ->form([
                        Forms\Components\Select::make('next_class_id')
                            ->label('Next Class')
                            ->options(function (User $record) {
                                $currentYear = AcademicYear::where('is_active', true)->first();
                                $currentClass = $record->classes()->wherePivot('academic_year_id', $currentYear?->id)->first();
                                if (!$currentClass) return [];

                                // Get current class level (X, XI, XII)
                                $currentLevel = $currentClass->name;

                                // If student is in grade 12, they will graduate
                                if (str_contains($currentLevel, 'XII')) {
                                    return [];
                                }

                                // Determine next class level
                                $nextLevel = match (true) {
                                    str_contains($currentLevel, 'X') => 'XI',
                                    str_contains($currentLevel, 'XI') => 'XII',
                                    default => null
                                };

                                if (!$nextLevel) return [];

                                // Get all classes for the next level
                                return SchoolClass::where('name', 'like', "%{$nextLevel}%")
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->visible(function (User $record) {
                                $currentYear = AcademicYear::where('is_active', true)->first();
                                $currentClass = $record->classes()->wherePivot('academic_year_id', $currentYear?->id)->first();
                                if (!$currentClass) return false;

                                // Don't show next class selection for grade 12
                                return !str_contains($currentClass->name, 'XII');
                            }),
                    ])
                    ->action(function (User $record, array $data) {
                        $currentYear = AcademicYear::where('is_active', true)->first();
                        if (!$currentYear) {
                            Notification::make()
                                ->title('No active academic year found')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Ambil tahun ajaran berikutnya
                        $nextYear = AcademicYear::where('id', '>', $currentYear->id)->orderBy('id')->first();
                        if (!$nextYear) {
                            Notification::make()
                                ->title('No next academic year found')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Get current class (tahun ajaran aktif)
                        $currentClass = $record->classes()->wherePivot('academic_year_id', $currentYear->id)->first();
                        if (!$currentClass) {
                            Notification::make()
                                ->title('Student is not assigned to a class in the current academic year')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Check failed subjects
                        $failedSubjects = $record->grades()
                            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                            ->where('score', '<', 70)
                            ->count();
                        $maxFailed = (int) Setting::getValue('max_failed_subjects', 2);

                        // Update promotion status for current class (tahun ajaran aktif)
                        $record->classes()->updateExistingPivot($currentClass->id, ['is_promoted' => true]);

                        // Jika siswa gagal promosi, tetap di kelas yang sama di tahun ajaran berikutnya
                        if ($failedSubjects > $maxFailed) {
                            $record->classes()->attach($currentClass->id, [
                                'is_promoted' => false,
                                'academic_year_id' => $nextYear->id
                            ]);
                            ClassPromotionHistory::create([
                                'user_id' => $record->id,
                                'from_class_id' => $currentClass->id,
                                'to_class_id' => $currentClass->id,
                                'academic_year_id' => $nextYear->id,
                                'failed_subjects_count' => $failedSubjects,
                                'is_promoted' => false,
                                'is_graduated' => false,
                                'notes' => 'Student retained in the same class due to poor performance'
                            ]);
                            Notification::make()
                                ->title('Student retained in the same class due to poor performance')
                                ->warning()
                                ->send();
                            return;
                        }

                        // Jika kelas XII, lulus
                        if ($currentClass->level === 'XII') {
                            $record->update(['is_graduated' => true]);
                            ClassPromotionHistory::create([
                                'user_id' => $record->id,
                                'from_class_id' => $currentClass->id,
                                'to_class_id' => null,
                                'academic_year_id' => $nextYear->id,
                                'failed_subjects_count' => $failedSubjects,
                                'is_promoted' => true,
                                'is_graduated' => true,
                                'notes' => 'Student has graduated'
                            ]);
                            Notification::make()
                                ->title('Student has graduated successfully')
                                ->success()
                                ->send();
                            return;
                        }

                        // Cari kelas tujuan di tahun ajaran berikutnya (level naik, major sama)
                        $nextLevel = match ($currentClass->level) {
                            'X' => 'XI',
                            'XI' => 'XII',
                            default => null
                        };
                        if (!$nextLevel) {
                            Notification::make()
                                ->title('Next class level not found')
                                ->danger()
                                ->send();
                            return;
                        }
                        $nextClass = SchoolClass::where('level', $nextLevel)
                            ->where('major', $currentClass->major)
                            ->where('academic_year_id', $nextYear->id)
                            ->first();
                        if (!$nextClass) {
                            Notification::make()
                                ->title('Next class not found for promotion')
                                ->danger()
                                ->send();
                            return;
                        }
                        // Attach ke kelas baru tahun ajaran berikutnya
                        $record->classes()->attach($nextClass->id, [
                            'is_promoted' => false,
                            'academic_year_id' => $nextYear->id
                        ]);
                        ClassPromotionHistory::create([
                            'user_id' => $record->id,
                            'from_class_id' => $currentClass->id,
                            'to_class_id' => $nextClass->id,
                            'academic_year_id' => $nextYear->id,
                            'failed_subjects_count' => $failedSubjects,
                            'is_promoted' => true,
                            'is_graduated' => false,
                            'notes' => 'Student promoted to next class'
                        ]);
                        Notification::make()
                            ->title('Student promoted successfully')
                            ->success()
                            ->send();
                    })
                    ->visible(function (User $record) {
                        $currentYear = AcademicYear::where('is_active', true)->first();
                        $currentClass = $record->classes()->wherePivot('academic_year_id', $currentYear?->id)->first();
                        if (!$currentClass) return false;

                        // Check if already processed for current academic year
                        return !$currentClass->pivot->is_promoted;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('promote')
                        ->label('Promote Selected')
                        ->icon('heroicon-o-academic-cap')
                        ->form([
                            Forms\Components\Select::make('next_class_id')
                                ->label('Next Class')
                                ->options(function (Tables\Actions\BulkAction $action) {
                                    $records = $action->getRecords();
                                    if ($records->isEmpty()) return [];

                                    $currentYear = AcademicYear::where('is_active', true)->first();
                                    $currentClass = $records->first()->classes()->wherePivot('academic_year_id', $currentYear?->id)->first();
                                    if (!$currentClass) return [];

                                    // Get current class level (X, XI, XII)
                                    $currentLevel = $currentClass->name;

                                    // If student is in grade 12, they will graduate
                                    if (str_contains($currentLevel, 'XII')) {
                                        return [];
                                    }

                                    // Determine next class level
                                    $nextLevel = match (true) {
                                        str_contains($currentLevel, 'X') => 'XI',
                                        str_contains($currentLevel, 'XI') => 'XII',
                                        default => null
                                    };

                                    if (!$nextLevel) return [];

                                    // Get all classes for the next level
                                    return SchoolClass::where('name', 'like', "%{$nextLevel}%")
                                        ->pluck('name', 'id');
                                })
                                ->required()
                                ->visible(function (Tables\Actions\BulkAction $action) {
                                    $records = $action->getRecords();
                                    if ($records->isEmpty()) return false;

                                    $currentYear = AcademicYear::where('is_active', true)->first();
                                    $currentClass = $records->first()->classes()->wherePivot('academic_year_id', $currentYear?->id)->first();
                                    if (!$currentClass) return false;

                                    // Don't show next class selection for grade 12
                                    return !str_contains($currentClass->name, 'XII');
                                }),
                        ])
                        ->action(function (Tables\Actions\BulkAction $action, array $data) {
                            $currentYear = AcademicYear::where('is_active', true)->first();
                            if (!$currentYear) {
                                Notification::make()
                                    ->title('No active academic year found')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            foreach ($action->getRecords() as $record) {
                                // Check failed subjects
                                $failedSubjects = $record->grades()
                                    ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                                    ->where('score', '<', 70)
                                    ->count();

                                // Get current class
                                $currentClass = $record->classes()->wherePivot('academic_year_id', $currentYear?->id)->first();

                                // Update promotion status for current class
                                if ($currentClass) {
                                    $record->classes()->updateExistingPivot(
                                        $currentClass->id,
                                        ['is_promoted' => true]
                                    );
                                }

                                // If student has more than 2 failed subjects, they stay in the same class
                                if ($failedSubjects > 2) {
                                    // Attach student to the same class for next academic year
                                    $record->classes()->attach($currentClass->id, [
                                        'is_promoted' => false,
                                        'academic_year_id' => $currentYear->id
                                    ]);

                                    // Save promotion history
                                    ClassPromotionHistory::create([
                                        'user_id' => $record->id,
                                        'from_class_id' => $currentClass->id,
                                        'to_class_id' => $currentClass->id,
                                        'academic_year_id' => $currentYear->id,
                                        'failed_subjects_count' => $failedSubjects,
                                        'is_promoted' => false,
                                        'is_graduated' => false,
                                        'notes' => 'Student retained in the same class due to poor performance'
                                    ]);

                                    Notification::make()
                                        ->title("Student {$record->name} retained in the same class due to poor performance")
                                        ->warning()
                                        ->send();
                                    continue;
                                }

                                // Check if student is in grade 12
                                if (str_contains($currentClass->name, 'XII')) {
                                    // Mark student as graduated
                                    $record->update(['is_graduated' => true]);

                                    // Save promotion history
                                    ClassPromotionHistory::create([
                                        'user_id' => $record->id,
                                        'from_class_id' => $currentClass->id,
                                        'to_class_id' => null,
                                        'academic_year_id' => $currentYear->id,
                                        'failed_subjects_count' => $failedSubjects,
                                        'is_promoted' => true,
                                        'is_graduated' => true,
                                        'notes' => 'Student has graduated'
                                    ]);

                                    Notification::make()
                                        ->title("Student {$record->name} has graduated successfully")
                                        ->success()
                                        ->send();
                                    continue;
                                }

                                // If student meets promotion criteria, move them to next class
                                $record->classes()->attach($data['next_class_id'], [
                                    'is_promoted' => false,
                                    'academic_year_id' => $currentYear->id
                                ]);

                                // Save promotion history
                                ClassPromotionHistory::create([
                                    'user_id' => $record->id,
                                    'from_class_id' => $currentClass->id,
                                    'to_class_id' => $data['next_class_id'],
                                    'academic_year_id' => $currentYear->id,
                                    'failed_subjects_count' => $failedSubjects,
                                    'is_promoted' => true,
                                    'is_graduated' => false,
                                    'notes' => 'Student promoted to next class'
                                ]);
                            }

                            Notification::make()
                                ->title('Selected students processed successfully')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
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
}
