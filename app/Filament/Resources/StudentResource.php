<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
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
                            ->minLength(8)
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state)),
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

                Forms\Components\Section::make('Parent Information')
                    ->schema([
                        Forms\Components\Repeater::make('parents')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('address')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('occupation')
                                    ->maxLength(255),
                                Forms\Components\Select::make('type')
                                    ->options([
                                        'father' => 'Father',
                                        'mother' => 'Mother',
                                        'guardian' => 'Guardian',
                                    ])
                                    ->required(),
                            ])
                            ->columns(2)
                            ->minItems(1)
                            ->maxItems(3)
                            ->defaultItems(1),
                    ]),
            ]);
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        // Assign student role
        $studentRole = Role::where('name', 'Student')->first();
        if ($studentRole) {
            $data['role_id'] = $studentRole->id;
        }

        // Generate NIS if it's a new student
        if ($studentRole && $studentRole->name === 'Student') {
            // Get the current year
            $currentYear = date('Y');

            // Get the last NIS for the current year
            $lastNIS = User::where('nis', 'like', $currentYear . '%')
                ->orderBy('nis', 'desc')
                ->first();

            if ($lastNIS && $lastNIS->nis) {
                // Extract the sequence number and increment it
                $sequence = (int)substr($lastNIS->nis, -4);
                $newSequence = $sequence + 1;
            } else {
                // If no NIS exists for this year, start with 1
                $newSequence = 1;
            }

            // Format: YYYYXXXX (YYYY = year, XXXX = sequence number)
            $data['nis'] = $currentYear . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
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

    public static function afterSave(Model $record, array $data): void
    {
        try {
            // Handle parent data for updates
            if (isset($data['parents'])) {
                // Delete existing parents
                $record->parents()->delete();

                // Create new parents
                foreach ($data['parents'] as $parent) {
                    $record->parents()->create([
                        'name' => $parent['name'],
                        'phone' => $parent['phone'],
                        'address' => $parent['address'] ?? null,
                        'occupation' => $parent['occupation'] ?? null,
                        'type' => $parent['type'],
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error updating parent data: ' . $e->getMessage(), [
                'student_id' => $record->id,
                'parent_data' => $data['parents'] ?? []
            ]);
            throw $e;
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),
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
                        $currentYear = AcademicYear::where('is_active', true)->first();
                        if (!$currentYear) return '-';

                        $currentClass = $record->classes()
                            ->wherePivot('academic_year_id', $currentYear->id)
                            ->first();

                        return $currentClass ? $currentClass->name : '-';
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
                Tables\Actions\Action::make('manageClass')
                    ->label(fn(User $record) => $record->classes->first() ? 'Change Class' : 'Add to Class')
                    ->icon('heroicon-o-academic-cap')
                    ->form([
                        Forms\Components\Select::make('class_id')
                            ->label('Class')
                            ->options(function () {
                                $currentYear = AcademicYear::where('is_active', true)->first();
                                if (!$currentYear) {
                                    return [];
                                }
                                return SchoolClass::where('academic_year_id', $currentYear->id)
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->helperText(function () {
                                $currentYear = AcademicYear::where('is_active', true)->first();
                                return $currentYear ? "Classes for Academic Year: {$currentYear->name}" : "No active academic year found";
                            }),
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
                    ->visible(fn() => Auth::user()->role->name === 'Admin'),
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
        return Auth::user()->role->name === 'Admin';
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
