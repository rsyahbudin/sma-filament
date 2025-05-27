<?php

namespace App\Filament\Widgets;

use App\Models\SchoolClass;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class HomeroomClassStudentsChart extends ChartWidget
{
    protected static ?string $heading = 'Jumlah Siswa di Kelas Saya';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        $user = Auth::user();
        // Hanya guru yang menjadi wali kelas
        return $user && $user->role && $user->role->name === 'Teacher' && SchoolClass::where('teacher_id', $user->id)->exists();
    }

    protected function getData(): array
    {
        $user = Auth::user();
        $class = SchoolClass::where('teacher_id', $user->id)->first();
        if (!$class) {
            return [
                'datasets' => [
                    [
                        'label' => 'Jumlah Siswa',
                        'data' => [],
                        'backgroundColor' => ['#3B82F6'],
                        'borderColor' => ['#2563EB'],
                        'borderWidth' => 1,
                    ],
                ],
                'labels' => [],
            ];
        }
        $studentCount = $class->students()->count();
        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Siswa',
                    'data' => [$studentCount],
                    'backgroundColor' => ['#3B82F6'],
                    'borderColor' => ['#2563EB'],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => [$class->name],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
                'datalabels' => [
                    'display' => true,
                    'color' => '#000',
                    'anchor' => 'center',
                    'align' => 'center',
                    'font' => [
                        'weight' => 'bold',
                    ],
                ],
            ],
            'animation' => [
                'duration' => 1000,
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }

    protected function getPlugins(): array
    {
        return [
            'datalabels',
        ];
    }

    public function getDescription(): ?string
    {
        return 'Jumlah total siswa di kelas yang Anda wali kelaskan.';
    }
}
