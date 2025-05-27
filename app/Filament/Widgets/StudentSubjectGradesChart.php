<?php

namespace App\Filament\Widgets;

use App\Models\Grade;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class StudentSubjectGradesChart extends ChartWidget
{
    protected static ?string $heading = 'Nilai per Mata Pelajaran';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 2;

    public ?string $filter = null;

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->role->name === 'Student';
    }

    protected function getFilters(): ?array
    {
        $academicYears = Grade::query()
            ->where('user_id', Auth::id())
            ->with('academicYear')
            ->get()
            ->pluck('academicYear.name', 'academicYear.id')
            ->unique()
            ->toArray();

        $semesters = Grade::query()
            ->where('user_id', Auth::id())
            ->pluck('semester')
            ->unique()
            ->sort()
            ->mapWithKeys(fn($semester) => ["semester_{$semester}" => "Semester {$semester}"])
            ->toArray();

        return [
            'all' => 'Semua',
            ...$academicYears,
            ...$semesters,
        ];
    }

    protected function getData(): array
    {
        $query = Grade::query()
            ->where('user_id', Auth::id())
            ->with(['subject', 'academicYear']);

        if ($this->filter && $this->filter !== 'all') {
            if (str_starts_with($this->filter, 'semester_')) {
                $semester = substr($this->filter, 9);
                $query->where('semester', $semester);
            } else {
                $query->where('academic_year_id', $this->filter);
            }
        }

        $grades = $query->get()->groupBy('subject.name');

        $datasets = [];
        $labels = [];

        foreach ($grades as $subjectName => $subjectGrades) {
            $data = [];
            $subjectLabels = [];

            foreach ($subjectGrades as $grade) {
                $data[] = $grade->score;
                $subjectLabels[] = "{$grade->academicYear->name} - Semester {$grade->semester}";
            }

            $datasets[] = [
                'label' => $subjectName,
                'data' => $data,
                'borderColor' => '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT),
                'tension' => 0.3,
            ];

            if (empty($labels)) {
                $labels = $subjectLabels;
            }
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                ],
            ],
        ];
    }
}
