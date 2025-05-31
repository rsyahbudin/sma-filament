<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Academic Management';

    protected static ?string $navigationLabel = 'Subjects';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Subject Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Generate code from name
                                $words = explode(' ', $state);
                                $code = '';

                                // Special handling for language subjects
                                if (str_contains(strtolower($state), 'bahasa')) {
                                    if (str_contains(strtolower($state), 'indonesia')) {
                                        $code = 'BIN';
                                    } elseif (str_contains(strtolower($state), 'inggris')) {
                                        $code = 'BIG';
                                    } else {
                                        // For other languages, take first letter of each word
                                        foreach ($words as $word) {
                                            $code .= strtoupper(substr($word, 0, 1));
                                        }
                                    }
                                } else {
                                    // If single word, take first 3 letters
                                    if (count($words) === 1) {
                                        $code = strtoupper(substr($state, 0, 3));
                                    } else {
                                        // If multiple words, take first letter of each word
                                        foreach ($words as $word) {
                                            $code .= strtoupper(substr($word, 0, 1));
                                        }
                                    }
                                }

                                $set('code', $code);
                            }),
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('minimum_score')
                            ->required()
                            ->numeric()
                            ->default(70)
                            ->minValue(0)
                            ->maxValue(100),
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
                Tables\Columns\TextColumn::make('minimum_score')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('classSubjectTeachers.teacher.name')
                    ->label('Teachers')
                    ->listWithLineBreaks()
                    ->searchable()
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
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $count = static::getModel()::count();
        return "Total Subjects: {$count}";
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
