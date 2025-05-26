<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReportResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Academic Management';

    protected static ?string $navigationLabel = 'Student Reports';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('role', function ($query) {
            $query->where('name', 'Student');
        });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('classes.name')
                    ->label('Current Class')
                    ->formatStateUsing(function ($record) {
                        $currentClass = $record->classes->first();
                        return $currentClass ? $currentClass->name : '-';
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_promoted')
                    ->label('Promotion Status')
                    ->boolean()
                    ->state(function ($record) {
                        $currentClass = $record->classes->first();
                        return $currentClass?->pivot->is_promoted ?? false;
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('class')
                    ->relationship('classes', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Class'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_report')
                    ->label('View Report')
                    ->icon('heroicon-o-document-text')
                    ->url(fn(User $record): string => route('filament.admin.resources.reports.view-report', $record))
                    ->openUrlInNewTab(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'view-report' => Pages\ViewReport::route('/{record}/report'),
        ];
    }
}
