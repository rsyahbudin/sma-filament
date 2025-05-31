<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentTransferResource\Pages;
use App\Filament\Resources\StudentTransferResource\RelationManagers;
use App\Models\StudentTransfer;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class StudentTransferResource extends Resource
{
    protected static ?string $model = StudentTransfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Student Management';

    protected static ?string $navigationLabel = 'Transfer Students';

    protected static ?int $navigationSort = 3;

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Student Information')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->relationship('student', 'name', fn($query) => $query->whereHas('role', fn($q) => $q->where('name', 'Student')))
                            ->required()
                            ->searchable()
                            ->preload(),
                    ]),
                Forms\Components\Section::make('Previous School Information')
                    ->schema([
                        Forms\Components\Select::make('previous_grade')
                            ->label('Previous Grade Level')
                            ->options([
                                'X' => 'Grade X',
                                'XI' => 'Grade XI',
                                'XII' => 'Grade XII',
                            ])
                            ->required(),
                        Forms\Components\Select::make('previous_academic_year_id')
                            ->relationship('previousAcademicYear', 'name')
                            ->required(),
                        Forms\Components\TextInput::make('previous_school')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('transfer_reason')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('previous_semester')
                            ->options([
                                '1' => 'Semester 1',
                                '2' => 'Semester 2',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('transfer_date')
                            ->label('Tanggal Pindah')
                            ->required(),
                    ]),
                Forms\Components\Section::make('Previous Report Card')
                    ->schema([
                        Forms\Components\Repeater::make('previous_grades')
                            ->schema([
                                Forms\Components\Select::make('subject_id')
                                    ->options(Subject::pluck('name', 'id'))
                                    ->required(),
                                Forms\Components\TextInput::make('score')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->required(),
                            ])
                            ->columns(2)
                            ->required()
                            ->minItems(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('previous_grade')
                    ->label('Previous Grade')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('previous_school')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('previousAcademicYear.name')
                    ->label('Previous Academic Year')
                    ->sortable(),
                Tables\Columns\TextColumn::make('previous_semester')
                    ->label('Previous Semester')
                    ->sortable(),
                Tables\Columns\TextColumn::make('transfer_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListStudentTransfers::route('/'),
            'create' => Pages\CreateStudentTransfer::route('/create'),
            'edit' => Pages\EditStudentTransfer::route('/{record}/edit'),
        ];
    }
}
