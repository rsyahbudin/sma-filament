<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
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
                            ->required(fn(string $context): bool => $context === 'create')
                            ->visible(fn() => Auth::user()?->role?->name === 'Admin' || Auth::user()?->id === request()->route('record')),
                    ])->columns(2),

                Forms\Components\Section::make('Role Information')
                    ->schema([
                        Forms\Components\Select::make('role_id')
                            ->relationship('role', 'name')
                            ->required()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $role = \App\Models\Role::find($state);
                                    if ($role && $role->name === 'Student') {
                                        $set('show_student_fields', true);
                                        $set('show_teacher_fields', false);
                                    } elseif ($role && $role->name === 'Teacher') {
                                        $set('show_student_fields', false);
                                        $set('show_teacher_fields', true);
                                    } else {
                                        $set('show_student_fields', false);
                                        $set('show_teacher_fields', false);
                                    }
                                }
                            })
                            ->visible(fn() => Auth::user()?->role?->name === 'Admin'),
                    ]),

                Forms\Components\Section::make('Student Information')
                    ->schema([
                        Forms\Components\Select::make('academic_year_id')
                            ->label('Academic Year')
                            ->options(function () {
                                return \App\Models\AcademicYear::pluck('name', 'id');
                            })
                            ->required(fn(callable $get) => $get('show_student_fields'))
                            ->visible(fn(callable $get) => $get('show_student_fields')),
                        Forms\Components\Select::make('class_id')
                            ->relationship('classes', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required(fn(callable $get) => $get('show_student_fields'))
                            ->visible(fn(callable $get) => $get('show_student_fields')),
                        Forms\Components\TextInput::make('student_id')
                            ->label('Student ID')
                            ->required(fn(callable $get) => $get('show_student_fields'))
                            ->visible(fn(callable $get) => $get('show_student_fields')),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'graduated' => 'Graduated',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active')
                            ->visible(fn(callable $get) => $get('show_student_fields')),
                    ])
                    ->visible(fn(callable $get) => $get('show_student_fields')),

                Forms\Components\Section::make('Teacher Information')
                    ->schema([
                        Forms\Components\TextInput::make('teacher_id')
                            ->label('Teacher ID')
                            ->required(fn(callable $get) => $get('show_teacher_fields'))
                            ->visible(fn(callable $get) => $get('show_teacher_fields')),
                        Forms\Components\Select::make('subjects')
                            ->relationship('subjects', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->visible(fn(callable $get) => $get('show_teacher_fields')),
                    ])
                    ->visible(fn(callable $get) => $get('show_teacher_fields')),

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
                Tables\Columns\TextColumn::make('role.name')
                    ->label('Role')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'graduated' => 'primary',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->visible(fn($record) => $record?->role?->name === 'Student'),
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
                Tables\Filters\SelectFilter::make('role')
                    ->relationship('role', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'graduated' => 'Graduated',
                        'inactive' => 'Inactive',
                    ])
                    ->visible(fn() => Auth::user()?->role?->name === 'Admin'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['role', 'classes', 'subjects']));
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role?->name === 'Admin';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'User Management';
    }

    public static function getNavigationLabel(): string
    {
        return 'Users';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
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
        return "Total User: {$count}";
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        \Log::info('User attempting to view users:', [
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'role_id' => $user?->role_id,
            'role_name' => $user?->role?->name
        ]);
        return $user?->role?->name === 'Admin';
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->role?->name === 'Admin';
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->id === $record->id || Auth::user()?->role?->name === 'Admin';
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->role?->name === 'Admin';
    }
}
