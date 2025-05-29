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
                Forms\Components\Section::make('Student Information')
                    ->schema([
                        Forms\Components\Select::make('academic_year_id')
                            ->label('Academic Year')
                            ->options(function () {
                                $user = Auth::user();
                                if ($user->role->name === 'Teacher') {
                                    $years = \App\Models\ClassSubjectTeacher::where('teacher_id', $user->id)
                                        ->pluck('academic_year_id')->unique();
                                    return \App\Models\AcademicYear::whereIn('id', $years)->pluck('name', 'id');
                                }
                                return \App\Models\AcademicYear::pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(),
                        Forms\Components\Hidden::make('academic_year_id')
                            ->dehydrated(fn(callable $get) => $get('academic_year_id') !== null),
                        Forms\Components\Select::make('user_id')
                            ->label('Student')
                            ->options(function (callable $get) {
                                $yearId = $get('academic_year_id');
                                $user = Auth::user();
                                if ($user->role->name === 'Teacher' && $yearId) {
                                    $teacherClasses = \App\Models\ClassSubjectTeacher::where('teacher_id', $user->id)
                                        ->where('academic_year_id', $yearId)
                                        ->pluck('school_class_id');
                                    return User::whereHas('role', function ($query) {
                                        $query->where('name', 'Student');
                                    })
                                        ->whereHas('classes', function ($query) use ($teacherClasses, $yearId) {
                                            $query->whereIn('school_classes.id', $teacherClasses)
                                                ->where('student_class.academic_year_id', $yearId);
                                        })
                                        ->pluck('name', 'id');
                                }
                                return User::whereHas('role', function ($query) {
                                    $query->where('name', 'Student');
                                })->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (!$state) return;
                                $yearId = $get('academic_year_id');
                                $user = User::find($state);
                                $teacher = Auth::user();
                                if (!$user || $teacher->role->name !== 'Teacher' || !$yearId) return;
                                $currentClass = $user->classes()->wherePivot('academic_year_id', $yearId)->first();
                                if (!$currentClass) return;
                                $assignments = \App\Models\ClassSubjectTeacher::where('school_class_id', $currentClass->id)
                                    ->where('academic_year_id', $yearId)
                                    ->where('teacher_id', $teacher->id)
                                    ->get();
                                if ($assignments->count() === 1) {
                                    $set('class_id', $currentClass->id);
                                    $set('subject_id', $assignments->first()->subject_id);
                                    $set('semester', $assignments->first()->semester);
                                } else if ($assignments->count() > 1) {
                                    $set('class_id', $currentClass->id);
                                    // subject_id dan semester tetap bisa dipilih
                                }
                            }),
                    ])->columns(1),

                Forms\Components\Section::make('Grade Information')
                    ->schema([
                        Forms\Components\Select::make('class_id')
                            ->label('Class')
                            ->options(function (callable $get) {
                                $userId = $get('user_id');
                                $yearId = $get('academic_year_id');
                                if (!$userId || !$yearId) return [];
                                $user = User::find($userId);
                                $currentClass = $user->classes()->wherePivot('academic_year_id', $yearId)->first();
                                return $currentClass ? [$currentClass->id => $currentClass->name] : [];
                            })
                            ->required()
                            ->afterStateHydrated(function (callable $set, callable $get) {
                                $userId = $get('user_id');
                                $yearId = $get('academic_year_id');
                                if (!$userId || !$yearId) return;
                                $user = User::find($userId);
                                $currentClass = $user->classes()->wherePivot('academic_year_id', $yearId)->first();
                                if ($currentClass) {
                                    $set('class_id', $currentClass->id);
                                }
                            }),
                        Forms\Components\Select::make('subject_id')
                            ->label('Subject')
                            ->options(function (callable $get) {
                                $classId = $get('class_id');
                                $yearId = $get('academic_year_id');
                                $teacher = Auth::user();
                                if ($teacher->role->name === 'Teacher' && $classId && $yearId) {
                                    $assignments = \App\Models\ClassSubjectTeacher::where('school_class_id', $classId)
                                        ->where('academic_year_id', $yearId)
                                        ->where('teacher_id', $teacher->id)
                                        ->get();
                                    $subjects = $assignments->pluck('subject_id')->unique();
                                    return \App\Models\Subject::whereIn('id', $subjects)->pluck('name', 'id');
                                }
                                return [];
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->afterStateHydrated(function (callable $set, callable $get) {
                                $classId = $get('class_id');
                                $yearId = $get('academic_year_id');
                                $teacher = Auth::user();
                                if ($teacher->role->name === 'Teacher' && $classId && $yearId) {
                                    $assignments = \App\Models\ClassSubjectTeacher::where('school_class_id', $classId)
                                        ->where('academic_year_id', $yearId)
                                        ->where('teacher_id', $teacher->id)
                                        ->get();
                                    $subjects = $assignments->pluck('subject_id')->unique();
                                    if ($subjects->count() === 1) {
                                        $set('subject_id', $subjects->first());
                                    }
                                }
                            }),
                        Forms\Components\Select::make('semester')
                            ->label('Semester')
                            ->options(function (callable $get) {
                                $classId = $get('class_id');
                                $yearId = $get('academic_year_id');
                                $subjectId = $get('subject_id');
                                $teacher = Auth::user();
                                if ($teacher->role->name === 'Teacher' && $classId && $yearId && $subjectId) {
                                    $semesters = \App\Models\ClassSubjectTeacher::where('school_class_id', $classId)
                                        ->where('academic_year_id', $yearId)
                                        ->where('subject_id', $subjectId)
                                        ->where('teacher_id', $teacher->id)
                                        ->pluck('semester')
                                        ->unique();
                                    $options = [];
                                    foreach ($semesters as $s) {
                                        $options[$s] = 'Semester ' . $s;
                                    }
                                    return $options;
                                }
                                return [1 => 'Semester 1', 2 => 'Semester 2'];
                            })
                            ->required()
                            ->afterStateHydrated(function (callable $set, callable $get) {
                                $classId = $get('class_id');
                                $yearId = $get('academic_year_id');
                                $subjectId = $get('subject_id');
                                $teacher = Auth::user();
                                if ($teacher->role->name === 'Teacher' && $classId && $yearId && $subjectId) {
                                    $semesters = \App\Models\ClassSubjectTeacher::where('school_class_id', $classId)
                                        ->where('academic_year_id', $yearId)
                                        ->where('subject_id', $subjectId)
                                        ->where('teacher_id', $teacher->id)
                                        ->pluck('semester')
                                        ->unique();
                                    if ($semesters->count() === 1) {
                                        $set('semester', $semesters->first());
                                    }
                                }
                            }),
                        Forms\Components\TextInput::make('score')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->helperText('Enter score between 0-100'),

                        Forms\Components\Textarea::make('notes')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable(query: function ($query, $search) {
                        $query->whereHas('student', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable()
                    ->formatStateUsing(fn($state, $record) => $record->student?->name ?? '-')
                    ->visible(fn() => Auth::user()->role->name !== 'Student'),
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject')
                    ->searchable(query: function ($query, $search) {
                        $query->whereHas('subject', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Teacher')
                    ->formatStateUsing(fn($state, $record) => $record->teacher?->name ?? '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('class.name')
                    ->label('Class')
                    ->searchable(query: function ($query, $search) {
                        $query->whereHas('class', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('academicYear.name')
                    ->label('Academic Year')
                    ->searchable(query: function ($query, $search) {
                        $query->whereHas('academicYear', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
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
                Tables\Actions\Action::make('downloadReport')
                    ->label('Download Report Card')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        $user = Auth::user();
                        $grades = Grade::where('user_id', $user->id)
                            ->with(['student', 'subject', 'class', 'academicYear'])
                            ->get()
                            ->groupBy(['academic_year_id', 'semester']);

                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.report-card', [
                            'student' => $user,
                            'grades' => $grades,
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
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['student', 'subject.teachers', 'class', 'academicYear']));
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
        return Auth::user()->role->name === 'Teacher' || Auth::user()->role->name === 'Admin';
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()->role->name === 'Teacher' || Auth::user()->role->name === 'Admin';
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()->role->name === 'Teacher' || Auth::user()->role->name === 'Admin';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['student', 'subject', 'class', 'academicYear']);

        if (Auth::user()->role->name === 'Student') {
            return $query->where('user_id', Auth::id());
        }

        if (Auth::user()->role->name === 'Teacher') {
            return $query->whereHas('subject.classSubjectTeachers', function ($query) {
                $query->where('teacher_id', Auth::id());
            });
        }

        return $query;
    }
}
