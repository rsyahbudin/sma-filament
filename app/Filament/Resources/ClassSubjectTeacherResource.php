<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassSubjectTeacherResource\Pages;
use App\Filament\Resources\ClassSubjectTeacherResource\RelationManagers;
use App\Models\ClassSubjectTeacher;
use App\Models\SchoolClass;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ClassSubjectTeacherResource extends Resource
{
    protected static ?string $model = ClassSubjectTeacher::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Academic Management';
    protected static ?string $navigationLabel = 'Teaching Assignments';
    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return 'Teaching Assignments';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Academic Management';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-academic-cap';
    }

    public static function getNavigationSort(): ?int
    {
        return 4;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $count = static::getModel()::count();
        return "Total Teaching Assignments: {$count}";
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('academic_year_id')
                    ->relationship('academicYear', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(),
                Forms\Components\Select::make('school_class_id')
                    ->label('Class')
                    ->options(function (callable $get) {
                        $yearId = $get('academic_year_id');
                        if (!$yearId) return [];

                        return SchoolClass::where('academic_year_id', $yearId)
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(),
                Forms\Components\Select::make('subject_id')
                    ->relationship('subject', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('teacher_id')
                    ->label('Teacher')
                    ->options(function () {
                        return User::whereHas('role', function ($query) {
                            $query->where('name', 'Teacher');
                        })->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('semester')
                    ->options([
                        1 => 'Semester 1',
                        2 => 'Semester 2',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('academicYear.name')
                    ->label('Academic Year')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('schoolClass.name')
                    ->label('Class')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Teacher')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('semester')
                    ->formatStateUsing(fn($state): string => "Semester {$state}")
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->relationship('academicYear', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('school_class_id')
                    ->relationship('schoolClass', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('subject_id')
                    ->relationship('subject', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label('Teacher')
                    ->options(function () {
                        return User::whereHas('role', function ($query) {
                            $query->where('name', 'Teacher');
                        })->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('semester')
                    ->options([
                        1 => 'Semester 1',
                        2 => 'Semester 2',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('academic_year_id', 'desc');
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
            'index' => Pages\ListClassSubjectTeachers::route('/'),
            'create' => Pages\CreateClassSubjectTeacher::route('/create'),
            'edit' => Pages\EditClassSubjectTeacher::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['academicYear', 'schoolClass', 'subject', 'teacher']);
    }

    public static function canViewAny(): bool
    {
        return in_array(Auth::user()->role->name, ['Admin', 'Teacher', 'Student']);
    }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return in_array(Auth::user()->role->name, ['Admin', 'Teacher', 'Student']);
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
}
