<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassSubjectResource\Pages;
use App\Filament\Resources\ClassSubjectResource\RelationManagers;
use App\Models\ClassSubject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClassSubjectResource extends Resource
{
    protected static ?string $model = ClassSubject::class;

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
                    ->preload(),
                Forms\Components\Select::make('school_class_id')
                    ->label('Class')
                    ->relationship('schoolClass', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('subject_id')
                    ->label('Subject')
                    ->relationship('subject', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('academicYear.name')->label('Academic Year')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('schoolClass.name')->label('Class')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('subject.name')->label('Subject')->sortable()->searchable(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageClassSubjects::route('/'),
        ];
    }
}
