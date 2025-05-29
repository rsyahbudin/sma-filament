<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolClassResource\Pages;
use App\Filament\Resources\SchoolClassResource\RelationManagers;
use App\Models\SchoolClass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
        return "Total Class: {$count}";
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('level')
                    ->options(SchoolClass::LEVELS)
                    ->required(),
                Forms\Components\Select::make('major')
                    ->options(SchoolClass::MAJORS)
                    ->required(),
                Forms\Components\Select::make('academic_year_id')
                    ->relationship('academicYear', 'name')
                    ->required(),
                Forms\Components\Select::make('teacher_id')
                    ->options(function ($get) {
                        $currentId = $get('teacher_id');
                        $academicYearId = $get('academic_year_id');

                        // Hanya cek guru yang sudah jadi wali kelas di tahun ajaran yang sama
                        $usedTeacherIds = \App\Models\SchoolClass::whereNotNull('teacher_id')
                            ->where('academic_year_id', $academicYearId)
                            ->when($currentId, fn($q) => $q->where('teacher_id', '!=', $currentId))
                            ->pluck('teacher_id')
                            ->toArray();

                        return \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'Teacher'))
                            ->whereNotIn('id', $usedTeacherIds)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('level')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('major')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('academicYear.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('teacher.name')
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('level')
                    ->options(SchoolClass::LEVELS),
                Tables\Filters\SelectFilter::make('major')
                    ->options(SchoolClass::MAJORS),
                Tables\Filters\SelectFilter::make('academic_year')
                    ->relationship('academicYear', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('duplicateToNextYear')
                        ->label('Duplikasi ke Tahun Ajaran Berikutnya')
                        ->form([
                            Forms\Components\Select::make('target_year_id')
                                ->label('Tahun Ajaran Tujuan')
                                ->options(\App\Models\AcademicYear::orderBy('id', 'desc')->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            $count = 0;
                            foreach ($records as $class) {
                                if (!in_array($class->level, ['X', 'XI'])) continue;
                                $nextLevel = $class->level === 'X' ? 'XI' : ($class->level === 'XI' ? 'XII' : null);
                                if (!$nextLevel) continue;
                                $newName = preg_replace('/^X( |$)/', 'XI ', $class->name);
                                $newName = preg_replace('/^XI( |$)/', 'XII ', $newName);
                                if ($class->level === 'X') $newName = preg_replace('/^X( |$)/', 'XI ', $class->name);
                                if ($class->level === 'XI') $newName = preg_replace('/^XI( |$)/', 'XII ', $class->name);
                                $newCode = $class->code . '-' . $data['target_year_id'];
                                $exists = \App\Models\SchoolClass::where('code', $newCode)
                                    ->where('academic_year_id', $data['target_year_id'])
                                    ->exists();
                                if ($exists) continue;
                                \App\Models\SchoolClass::create([
                                    'name' => $newName,
                                    'code' => $newCode,
                                    'level' => $nextLevel,
                                    'major' => $class->major,
                                    'academic_year_id' => $data['target_year_id'],
                                    'teacher_id' => null,
                                ]);
                                $count++;
                            }
                            if ($count > 0) {
                                \Filament\Notifications\Notification::make()
                                    ->title("Berhasil menduplikasi $count kelas ke tahun ajaran baru.")
                                    ->success()
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Tidak ada kelas yang diduplikasi (mungkin sudah ada di tahun ajaran tujuan atau bukan kelas X/XI).')
                                    ->warning()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->modalHeading('Duplikasi Kelas ke Tahun Ajaran Berikutnya')
                        ->modalDescription('Semua kelas X dan XI yang dipilih akan diduplikasi ke tahun ajaran tujuan dengan level naik. Wali kelas diisi manual.'),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('generateAllNextYear')
                    ->label('Generate Kelas Tahun Ajaran Baru')
                    ->form([
                        Forms\Components\Select::make('source_year_id')
                            ->label('Tahun Ajaran Sumber')
                            ->options(\App\Models\AcademicYear::orderBy('id', 'desc')->pluck('name', 'id'))
                            ->required(),
                        Forms\Components\Select::make('target_year_id')
                            ->label('Tahun Ajaran Tujuan')
                            ->options(\App\Models\AcademicYear::orderBy('id', 'desc')->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $sourceClasses = \App\Models\SchoolClass::where('academic_year_id', $data['source_year_id'])
                            ->get();
                        $count = 0;
                        foreach ($sourceClasses as $class) {
                            $newCode = $class->code . '-' . $data['target_year_id'];
                            $exists = \App\Models\SchoolClass::where('code', $newCode)
                                ->where('academic_year_id', $data['target_year_id'])
                                ->exists();
                            if ($exists) continue;
                            \App\Models\SchoolClass::create([
                                'name' => $class->name,
                                'code' => $newCode,
                                'level' => $class->level,
                                'major' => $class->major,
                                'academic_year_id' => $data['target_year_id'],
                                'teacher_id' => null,
                            ]);
                            $count++;
                        }
                        if ($count > 0) {
                            \Filament\Notifications\Notification::make()
                                ->title("Berhasil generate $count kelas ke tahun ajaran baru.")
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Tidak ada kelas yang digenerate (mungkin sudah ada di tahun ajaran tujuan).')
                                ->warning()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Generate Kelas Tahun Ajaran Baru')
                    ->modalDescription('Semua kelas (X, XI, XII) dari tahun ajaran sumber akan diduplikasi ke tahun ajaran tujuan. Wali kelas diisi manual.'),
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
}
