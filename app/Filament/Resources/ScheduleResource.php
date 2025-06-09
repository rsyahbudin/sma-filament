<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Models\Schedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\AcademicYear;



class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Academic Management';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Schedules';

    public static function getNavigationGroup(): ?string
    {
        return 'Academic Management';
    }

    public static function getNavigationLabel(): string
    {
        return 'Schedules';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-clock';
    }

    public static function getNavigationSort(): ?int
    {
        return 4;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Schedule Information')
                    ->schema([
                        Forms\Components\Select::make('class_id')
                            ->label('Class')
                            ->options(function () {
                                // Cari academic year yang aktif
                                $activeYear = AcademicYear::where('is_active', true)->first();
                                if (!$activeYear) {
                                    return [];
                                }

                                return SchoolClass::where('academic_year_id', $activeYear->id)
                                    ->pluck('name', 'id');
                            })
                            ->required(),
                        Forms\Components\Select::make('subject_id')
                            ->relationship('subject', 'name')
                            ->required(),
                        Forms\Components\Select::make('teacher_id')
                            ->options(function () {
                                return User::whereHas('role', function ($query) {
                                    $query->where('name', 'Teacher');
                                })->pluck('name', 'id');
                            })
                            ->required(),
                        Forms\Components\Select::make('day')
                            ->options([
                                'Monday' => 'Monday',
                                'Tuesday' => 'Tuesday',
                                'Wednesday' => 'Wednesday',
                                'Thursday' => 'Thursday',
                                'Friday' => 'Friday',
                                'Saturday' => 'Saturday',
                            ])
                            ->required(),
                        Forms\Components\TimePicker::make('start_time')
                            ->required()
                            ->seconds(false),
                        Forms\Components\TimePicker::make('end_time')
                            ->required()
                            ->seconds(false),
                        Forms\Components\Select::make('academic_year_id')
                            ->relationship('academicYear', 'name')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('class.name')
                    ->label('Class')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject')
                    ->sortable(),
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Teacher')
                    ->sortable(),
                Tables\Columns\TextColumn::make('day')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('academicYear.name')
                    ->label('Academic Year')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('class')
                    ->relationship('class', 'name'),
                Tables\Filters\SelectFilter::make('subject')
                    ->relationship('subject', 'name'),
                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label('Teacher')
                    ->options(function () {
                        return User::whereHas('role', function ($query) {
                            $query->where('name', 'Teacher');
                        })->pluck('name', 'id');
                    }),
                Tables\Filters\SelectFilter::make('day')
                    ->options([
                        'Monday' => 'Monday',
                        'Tuesday' => 'Tuesday',
                        'Wednesday' => 'Wednesday',
                        'Thursday' => 'Thursday',
                        'Friday' => 'Friday',
                        'Saturday' => 'Saturday',
                    ]),
            ])
            ->actions([
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
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}
