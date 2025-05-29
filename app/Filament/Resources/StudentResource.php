<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use App\Models\AcademicYear;
use App\Models\StudentClass;
use App\Models\StudentTransfer;

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
        $studentRole = Role::where('name', 'Student')->first();
        if ($studentRole) {
            $data['role_id'] = $studentRole->id;
        }

        return $data;
    }

    public static function afterCreate(Model $record, array $data): void
    {
        // If we have a class_id stored, attach the student to the class
        if (isset($data['_class_id'])) {
            try {
                // Get current academic year
                $currentYear = \App\Models\AcademicYear::where('is_active', true)->first();

                if (!$currentYear) {
                    throw new \Exception('No active academic year found. Please set an active academic year first.');
                }

                // Insert into student_class with academic_year_id
                DB::table('student_class')->insert([
                    'student_id' => $record->id,
                    'school_class_id' => $data['_class_id'],
                    'academic_year_id' => $currentYear->id,
                    'is_promoted' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Log::info('Student attached to class successfully', [
                    'student_id' => $record->id,
                    'class_id' => $data['_class_id'],
                    'academic_year_id' => $currentYear->id
                ]);
            } catch (\Exception $e) {
                Log::error('Error attaching student to class: ' . $e->getMessage(), [
                    'student_id' => $record->id,
                    'class_id' => $data['_class_id'],
                    'academic_year_id' => $currentYear->id ?? null
                ]);
                throw $e;
            }
        }
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('manageClass')
                    ->label(fn(User $record) => $record->classes->first() ? 'Change Class' : 'Add to Class')
                    ->icon('heroicon-o-academic-cap')
                    ->form([
                        Forms\Components\Select::make('class_id')
                            ->label('Class')
                            ->options(SchoolClass::pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (User $record, array $data) {
                        $currentYear = \App\Models\AcademicYear::where('is_active', true)->first();

                        if (!$currentYear) {
                            throw new \Exception('No active academic year found. Please set an active academic year first.');
                        }

                        // Check if student is already in a class for this academic year
                        $existingClass = DB::table('student_class')
                            ->where('student_id', $record->id)
                            ->where('academic_year_id', $currentYear->id)
                            ->first();

                        if ($existingClass) {
                            // Update existing class
                            DB::table('student_class')
                                ->where('student_id', $record->id)
                                ->where('academic_year_id', $currentYear->id)
                                ->update([
                                    'school_class_id' => $data['class_id'],
                                    'updated_at' => now(),
                                ]);

                            Notification::make()
                                ->title('Student class updated successfully')
                                ->success()
                                ->send();
                        } else {
                            // Add student to new class
                            DB::table('student_class')->insert([
                                'student_id' => $record->id,
                                'school_class_id' => $data['class_id'],
                                'academic_year_id' => $currentYear->id,
                                'is_promoted' => false,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Student added to class successfully')
                                ->success()
                                ->send();
                        }
                    })
                    ->visible(fn(User $record) => $record->role->name === 'Student'),
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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereHas('role', function ($q) {
            $q->where('name', 'Student');
        })->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $count = static::getModel()::whereHas('role', function ($q) {
            $q->where('name', 'Student');
        })->count();
        return "Total Student: {$count}";
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
