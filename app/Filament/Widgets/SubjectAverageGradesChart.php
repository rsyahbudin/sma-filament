<?php

namespace App\Filament\Widgets;

use App\Models\Grade;
use App\Models\Subject;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubjectAverageGradesChart extends ChartWidget
{
    protected static ?string $heading = 'Rata-rata Nilai per Mata Pelajaran';
    protected static ?int $sort = 4;

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->role->name === 'Teacher';
    }

    protected function getData(): array
    {
        $teacher = Auth::user();
        $subjects = Subject::where('teacher_id', $teacher->id)->get();

        $averages = [];
        foreach ($subjects as $subject) {
            $average = Grade::where('subject_id', $subject->id)
                ->avg('score');
            $averages[] = round($average, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Nilai',
                    'data' => $averages,
                    'backgroundColor' => '#F59E0B',
                    'borderColor' => '#D97706',
                ],
            ],
            'labels' => $subjects->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
