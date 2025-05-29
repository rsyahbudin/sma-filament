<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassPromotionHistoryResource\Pages;
use App\Filament\Resources\ClassPromotionHistoryResource\RelationManagers;
use App\Models\ClassPromotionHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClassPromotionHistoryResource extends Resource
{
    protected static ?string $model = ClassPromotionHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Student Management';

    protected static ?string $navigationLabel = 'Promotion History';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled(),
                Forms\Components\Select::make('from_class_id')
                    ->relationship('fromClass', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled(),
                Forms\Components\Select::make('to_class_id')
                    ->relationship('toClass', 'name')
                    ->searchable()
                    ->preload()
                    ->disabled(),
                Forms\Components\Select::make('academic_year_id')
                    ->relationship('academicYear', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled(),
                Forms\Components\TextInput::make('failed_subjects_count')
                    ->required()
                    ->numeric()
                    ->disabled(),
                Forms\Components\Toggle::make('is_promoted')
                    ->required()
                    ->disabled(),
                Forms\Components\Toggle::make('is_graduated')
                    ->required()
                    ->disabled(),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fromClass.name')
                    ->label('From Class')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('toClass.name')
                    ->label('To Class')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('academicYear.name')
                    ->label('Academic Year')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('failed_subjects_count')
                    ->label('Failed Subjects')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_promoted')
                    ->label('Promoted')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_graduated')
                    ->label('Graduated')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->relationship('academicYear', 'name')
                    ->label('Academic Year'),
                Tables\Filters\SelectFilter::make('is_promoted')
                    ->options([
                        '1' => 'Promoted',
                        '0' => 'Not Promoted',
                    ]),
                Tables\Filters\SelectFilter::make('is_graduated')
                    ->options([
                        '1' => 'Graduated',
                        '0' => 'Not Graduated',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
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
            'index' => Pages\ListClassPromotionHistories::route('/'),
        ];
    }
}
