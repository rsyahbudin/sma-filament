<?php

namespace App\Filament\Widgets;

use App\Models\Grade;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

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
        $grades = Grade::where('user_id', $user->id)->get();

        $averageScore = $grades->avg('score');
        $highestScore = $grades->max('score');
        $lowestScore = $grades->min('score');
        $passedSubjects = $grades->filter(fn($grade) => $grade->isPassed())->count();
        $totalSubjects = $grades->count();

        return [
            Stat::make('Rata-rata Nilai', number_format($averageScore, 2))
                ->description('Rata-rata nilai semua mata pelajaran')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color($averageScore >= 70 ? 'success' : 'warning'),

            Stat::make('Nilai Tertinggi', number_format($highestScore, 2))
                ->description('Nilai tertinggi yang dicapai')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Nilai Terendah', number_format($lowestScore, 2))
                ->description('Nilai terendah yang dicapai')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color($lowestScore >= 70 ? 'success' : 'danger'),

            Stat::make('Mata Pelajaran Lulus', "{$passedSubjects}/{$totalSubjects}")
                ->description('Jumlah mata pelajaran yang sudah lulus')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($passedSubjects === $totalSubjects ? 'success' : 'warning'),
        ];
    }
}
