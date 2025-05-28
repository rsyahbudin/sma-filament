<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionHistoryResource\Pages;
use App\Models\PromotionHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PromotionHistoryResource extends Resource
{
    protected static ?string $model = PromotionHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Academic Management';

    protected static ?string $navigationLabel = 'Promotion History';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fromClass.name')
                    ->label('From Class')
                    ->sortable(),
                TextColumn::make('toClass.name')
                    ->label('To Class')
                    ->sortable(),
                TextColumn::make('academicYear.name')
                    ->label('Academic Year')
                    ->sortable(),
                TextColumn::make('average_score')
                    ->label('Average Score')
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => number_format($state, 2)),
                TextColumn::make('failed_subjects')
                    ->label('Failed Subjects')
                    ->sortable(),
                IconColumn::make('is_promoted')
                    ->label('Promoted')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Processed At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('academic_year')
                    ->relationship('academicYear', 'name')
                    ->label('Academic Year'),
                Filter::make('is_promoted')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options([
                                '1' => 'Promoted',
                                '0' => 'Not Promoted',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['status'],
                            fn(Builder $query, $status): Builder => $query->where('is_promoted', $status),
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('export')
                    ->label('Export Selected')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (Collection $records) {
                        // Export logic here
                    }),
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
            'index' => Pages\ListPromotionHistories::route('/'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Academic Management';
    }

    public static function getNavigationLabel(): string
    {
        return 'Promotion History';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-clock';
    }

    public static function getNavigationSort(): ?int
    {
        return 8;
    }

    public static function canViewAny(): bool
    {
        return Auth::user()->role->name === 'Admin' || Auth::user()->role->name === 'Teacher';
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
