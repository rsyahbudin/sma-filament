<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Models\User;
use App\Models\SchoolClass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'Students';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->whereHas('role', function ($query) {
            $query->where('name', 'Student');
        });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Student Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->required(),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                            ])
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
                    ->listWithLineBreaks(),
                Tables\Columns\IconColumn::make('is_promoted')
                    ->label('Promotion Status')
                    ->boolean()
                    ->state(function ($record) {
                        $currentClass = $record->classes->first();
                        return $currentClass?->pivot->is_promoted ?? false;
                    })
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('promote')
                    ->label('Promote')
                    ->icon('heroicon-o-academic-cap')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Select::make('next_class_id')
                            ->label('Next Class')
                            ->options(SchoolClass::pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (User $record, array $data) {
                        // Calculate average score for the current academic year
                        $currentYear = \App\Models\AcademicYear::where('is_active', true)->first();
                        $averageScore = $record->grades()
                            ->whereHas('academicYear', function ($query) use ($currentYear) {
                                $query->where('id', $currentYear->id);
                            })
                            ->avg('score');

                        if ($averageScore >= 70) {
                            // Get current class
                            $currentClass = $record->classes->first();

                            // Update promotion status for current class
                            if ($currentClass) {
                                $record->classes()->updateExistingPivot(
                                    $currentClass->id,
                                    ['is_promoted' => true]
                                );
                            }

                            // Attach student to new class
                            $record->classes()->attach($data['next_class_id'], [
                                'is_promoted' => false,
                                'academic_year_id' => $currentYear->id
                            ]);
                        }
                    })
                    ->visible(
                        fn(User $record) =>
                        auth()->check() &&
                            auth()->user()->role->name === 'Admin' &&
                            $record->grades()->exists() &&
                            !$record->classes->first()?->pivot->is_promoted
                    ),
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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
