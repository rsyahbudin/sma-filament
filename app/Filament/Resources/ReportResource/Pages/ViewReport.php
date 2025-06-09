<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Barryvdh\DomPDF\Facade\Pdf;

class ViewReport extends Page
{
    protected static string $resource = ReportResource::class;

    public function getHeading(): string
    {
        return 'Student Report';
    }

    protected static string $view = 'filament.resources.report-resource.pages.view-report';

    public $record;
    public $academicYears;

    public function mount($record): void
    {
        $this->record = User::findOrFail($record);
        $this->academicYears = $this->record->grades()
            ->with('academicYear')
            ->get()
            ->groupBy('academic_year_id')
            ->map(function ($grades) {
                return [
                    'year' => $grades->first()->academicYear,
                    'semesters' => $grades->groupBy('semester')
                        ->map(function ($semesterGrades) {
                            return [
                                'semester' => $semesterGrades->first()->semester,
                                'grades' => $semesterGrades->load(['subject', 'class']),
                            ];
                        }),
                ];
            });
    }

    public function downloadReport()
    {
        $pdf = Pdf::loadView('reports.student', [
            'student' => $this->record,
            'academicYears' => $this->academicYears,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, "report-{$this->record->name}.pdf");
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download')
                ->label('Download PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->action('downloadReport'),
        ];
    }
}
