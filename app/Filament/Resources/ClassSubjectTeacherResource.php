<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassSubjectTeacherResource\Pages;
use App\Filament\Resources\ClassSubjectTeacherResource\RelationManagers;
use App\Models\ClassSubjectTeacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class ClassSubjectTeacherResource extends Resource
{
    protected static ?string $model = ClassSubjectTeacher::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('academic_year_id')
                    ->label('Academic Year')
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
                        return \App\Models\SchoolClass::where('academic_year_id', $yearId)
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(),
                Forms\Components\Select::make('subject_id')
                    ->label('Subject')
                    ->options(function (callable $get) {
                        $classId = $get('school_class_id');
                        $yearId = $get('academic_year_id');
                        if (!$classId || !$yearId) return [];

                        return \App\Models\ClassSubject::where('school_class_id', $classId)
                            ->where('academic_year_id', $yearId)
                            ->with('subject')
                            ->get()
                            ->pluck('subject.name', 'subject.id');
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(),
                Forms\Components\Select::make('teacher_id')
                    ->label('Teacher')
                    ->options(function (callable $get) {
                        $subjectId = $get('subject_id');
                        if (!$subjectId) return [];

                        return \App\Models\User::whereHas('role', function ($query) {
                            $query->where('name', 'Teacher');
                        })
                            ->whereHas('subjects', function ($query) use ($subjectId) {
                                $query->where('subjects.id', $subjectId);
                            })
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('semester')
                    ->label('Semester')
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
                Tables\Columns\TextColumn::make('schoolClass.name')->label('Class')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('subject.name')->label('Subject')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('teacher.name')->label('Teacher')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('academicYear.name')->label('Academic Year')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('semester')
                    ->label('Semester')
                    ->formatStateUsing(fn($state) => $state == 1 ? 'Semester 1' : 'Semester 2')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListClassSubjectTeachers::route('/'),
            'create' => Pages\CreateClassSubjectTeacher::route('/create'),
            'edit' => Pages\EditClassSubjectTeacher::route('/{record}/edit'),
        ];
    }
}
