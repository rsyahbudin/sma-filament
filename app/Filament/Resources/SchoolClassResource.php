<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolClassResource\Pages;
use App\Filament\Resources\SchoolClassResource\RelationManagers;
use App\Models\SchoolClass;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SchoolClassResource extends Resource
{
    protected static ?string $model = SchoolClass::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Academic Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Classes';

    public static function getNavigationGroup(): ?string
    {
        return 'Academic Management';
    }

    public static function getNavigationLabel(): string
    {
        return 'Classes';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-academic-cap';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getNavigationBadge() > 10 ? 'warning' : 'primary';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $count = static::getModel()::count();
        return "Total Classes: {$count}";
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Class Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Extract level and major from name
                                $parts = explode(' ', $state);
                                if (count($parts) >= 2) {
                                    $level = $parts[0];
                                    $major = $parts[1];
                                    $number = $parts[2] ?? '1';

                                    // Generate code based on level and major
                                    $code = strtoupper($level . $major . $number);
                                    $set('code', $code);
                                }
                            }),
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('level')
                            ->options(SchoolClass::LEVELS)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                $name = $get('name');
                                if ($name) {
                                    $parts = explode(' ', $name);
                                    if (count($parts) >= 2) {
                                        $major = $parts[1];
                                        $number = $parts[2] ?? '1';
                                        $set('name', $state . ' ' . $major . ' ' . $number);
                                    }
                                }
                            }),
                        Forms\Components\Select::make('major')
                            ->options(SchoolClass::MAJORS)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                $name = $get('name');
                                if ($name) {
                                    $parts = explode(' ', $name);
                                    if (count($parts) >= 2) {
                                        $level = $parts[0];
                                        $number = $parts[2] ?? '1';
                                        $set('name', $level . ' ' . $state . ' ' . $number);
                                    }
                                }
                            }),
                        Forms\Components\Select::make('academic_year_id')
                            ->relationship('academicYear', 'name')
                            ->required(),
                        Forms\Components\Select::make('teacher_id')
                            ->label('Class Teacher')
                            ->options(function () {
                                return User::whereHas('role', function ($query) {
                                    $query->where('name', 'Teacher');
                                })->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('level')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('major')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('academicYear.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Class Teacher')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->options(SchoolClass::LEVELS),
                Tables\Filters\SelectFilter::make('major')
                    ->options(SchoolClass::MAJORS),
                Tables\Filters\SelectFilter::make('academic_year')
                    ->relationship('academicYear', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->visible(fn() => \Illuminate\Support\Facades\Auth::user()->role->name === 'Admin'),
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
            'index' => Pages\ListSchoolClasses::route('/'),
            'create' => Pages\CreateSchoolClass::route('/create'),
            'edit' => Pages\EditSchoolClass::route('/{record}/edit'),
        ];
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
