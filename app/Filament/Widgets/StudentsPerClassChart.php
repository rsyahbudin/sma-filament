<?php

namespace App\Filament\Widgets;

use App\Models\SchoolClass;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentsPerClassChart extends ChartWidget
{
    protected static ?string $heading = 'Jumlah Siswa per Kelas';
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && ($user->role->name === 'Admin' || $user->role->name === 'Teacher');
    }

    protected function getData(): array
    {
        $classes = SchoolClass::all();
        $labels = $classes->pluck('name')->toArray();

        // Get male students count per class
        $maleData = [];
        foreach ($classes as $class) {
            $maleCount = DB::table('student_class')
                ->join('users', 'student_class.student_id', '=', 'users.id')
                ->where('student_class.school_class_id', $class->id)
                ->where('users.gender', 'male')
                ->count();
            $maleData[] = $maleCount;
        }

        // Get female students count per class
        $femaleData = [];
        foreach ($classes as $class) {
            $femaleCount = DB::table('student_class')
                ->join('users', 'student_class.student_id', '=', 'users.id')
                ->where('student_class.school_class_id', $class->id)
                ->where('users.gender', 'female')
                ->count();
            $femaleData[] = $femaleCount;
        }

        Log::info('Male Data:', $maleData);
        Log::info('Female Data:', $femaleData);

        return [
            'datasets' => [
                [
                    'label' => 'Siswa Laki-laki',
                    'data' => $maleData,
                    'backgroundColor' => '#3B82F6',
                    'borderColor' => '#2563EB',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Siswa Perempuan',
                    'data' => $femaleData,
                    'backgroundColor' => '#EC4899',
                    'borderColor' => '#DB2777',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'animation' => [
                'duration' => 1000,
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'datalabels' => [
                    'display' => true,
                    'color' => '#000',
                    'anchor' => 'end',
                    'align' => 'top',
                    'offset' => 4,
                    'font' => [
                        'weight' => 'bold',
                    ],
                ],
            ],
        ];
    }

    protected function getPlugins(): array
    {
        return [
            'datalabels',
        ];
    }
}
