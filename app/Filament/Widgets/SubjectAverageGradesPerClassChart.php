<?php

namespace App\Filament\Widgets;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Grade;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademicYear;

class SubjectAverageGradesPerClassChart extends ChartWidget
{
    protected static ?string $heading = 'Rata-rata Nilai per Mata Pelajaran per Kelas';
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 2;

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && ($user->role->name === 'Admin' || $user->role->name === 'Teacher');
    }

    public function getDescription(): ?string
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        return $activeYear
            ? "Rata-rata nilai siswa per kelas untuk setiap mata pelajaran pada tahun ajaran {$activeYear->name}."
            : 'Rata-rata nilai siswa per kelas untuk setiap mata pelajaran.';
    }

    protected function getData(): array
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $user = Auth::user();
        if ($user && $user->role && $user->role->name === 'Teacher') {
            $subjects = Subject::where('teacher_id', $user->id)->get();
        } else {
            $subjects = Subject::all();
        }

        $classes = SchoolClass::where('academic_year_id', $activeYear->id)->get();
        $colors = ['#3B82F6', '#EC4899', '#F59E0B', '#10B981', '#6366F1', '#F43F5E', '#84CC16', '#EAB308'];
        $datasets = [];

        foreach ($classes as $i => $class) {
            $averages = [];
            foreach ($subjects as $subject) {
                $avg = Grade::where('class_id', $class->id)
                    ->where('subject_id', $subject->id)
                    ->where('academic_year_id', $activeYear->id)
                    ->avg('score');
                $averages[] = $avg ? round($avg, 2) : 0;
            }
            $datasets[] = [
                'label' => $class->name,
                'data' => $averages,
                'backgroundColor' => $colors[$i % count($colors)],
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $subjects->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
