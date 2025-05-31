<?php

namespace App\Filament\Widgets;

use App\Models\Grade;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademicYear;

class StudentGradeStats extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->role->name === 'Student';
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        $activeYear = AcademicYear::where('is_active', true)->first();

        $grades = Grade::where('user_id', $user->id)
            ->when($activeYear, function ($query) use ($activeYear) {
                $query->where('academic_year_id', $activeYear->id);
            })
            ->get();

        $averageScore = $grades->avg('score');
        $highestScore = $grades->max('score');
        $lowestScore = $grades->min('score');
        $passedSubjects = $grades->filter(fn($grade) => $grade->isPassed())->count();
        $totalSubjects = $grades->count();

        return [
            Stat::make('Rata-rata Nilai', number_format($averageScore, 2))
                ->description($activeYear ? "Rata-rata nilai semua mata pelajaran tahun ajaran {$activeYear->name}" : 'Rata-rata nilai semua mata pelajaran')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color($averageScore >= 70 ? 'success' : 'warning'),

            Stat::make('Nilai Tertinggi', number_format($highestScore, 2))
                ->description($activeYear ? "Nilai tertinggi yang dicapai tahun ajaran {$activeYear->name}" : 'Nilai tertinggi yang dicapai')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Nilai Terendah', number_format($lowestScore, 2))
                ->description($activeYear ? "Nilai terendah yang dicapai tahun ajaran {$activeYear->name}" : 'Nilai terendah yang dicapai')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color($lowestScore >= 70 ? 'success' : 'danger'),

            Stat::make('Mata Pelajaran Lulus', "{$passedSubjects}/{$totalSubjects}")
                ->description($activeYear ? "Jumlah mata pelajaran yang sudah lulus tahun ajaran {$activeYear->name}" : 'Jumlah mata pelajaran yang sudah lulus')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($passedSubjects === $totalSubjects ? 'success' : 'warning'),
        ];
    }
}
