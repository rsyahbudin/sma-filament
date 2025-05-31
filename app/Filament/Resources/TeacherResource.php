<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResource\Pages;
use App\Models\User;
use App\Models\Role;
use App\Models\AcademicYear;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class TeacherResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'Teachers';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $activeYear = AcademicYear::where('is_active', true)->first();

        return parent::getEloquentQuery()
            ->whereHas('role', function ($query) {
                $query->where('name', 'Teacher');
            })
            ->with(['teachingAssignments' => function ($query) use ($activeYear) {
                $query->where('academic_year_id', $activeYear->id)
                    ->with(['subject', 'schoolClass', 'academicYear']);
            }]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Teacher Information')
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
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create'),
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

    public static function mutateFormDataBeforeSave(array $data): array
    {
        $teacherRole = Role::where('name', 'Teacher')->first();
        if ($teacherRole) {
            $data['role_id'] = $teacherRole->id;
        }
        return $data;
    }

    public static function table(Table $table): Table
    {
        $activeYear = AcademicYear::where('is_active', true)->first();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('teachingAssignments.subject.name')
                    ->label('Teaching Subjects')
                    ->listWithLineBreaks()
                    ->formatStateUsing(function ($record) use ($activeYear) {
                        $subjects = $record->teachingAssignments
                            ->where('academic_year_id', $activeYear->id)
                            ->pluck('subject.name')
                            ->unique()
                            ->join(', ');
                        return $subjects ?: '-';
                    }),
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
            'index' => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            'edit' => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereHas('role', function ($q) {
            $q->where('name', 'Teacher');
        })->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $count = static::getModel()::whereHas('role', function ($q) {
            $q->where('name', 'Teacher');
        })->count();
        return "Total Teacher: {$count}";
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return Auth::user()->id === $record->id || Auth::user()->role->name === 'Admin';
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

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return Auth::user()->role->name === 'Admin';
    }
}
