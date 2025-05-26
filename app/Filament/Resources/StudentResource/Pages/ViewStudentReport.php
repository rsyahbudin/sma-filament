<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Barryvdh\DomPDF\Facade\Pdf;

class ViewStudentReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = StudentResource::class;

    protected static string $view = 'filament.resources.student-resource.pages.view-student-report';

    public $record;
    public $semester = 1;
    public $academicYear;

    public function mount($record): void
    {
        $this->record = $record;
        $this->academicYear = $record->classes()
            ->where('is_active', true)
            ->first()
            ->academicYear;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter')
                    ->schema([
                        Select::make('semester')
                            ->label('Semester')
                            ->options([
                                1 => 'Semester 1',
                                2 => 'Semester 2',
                                3 => 'Semester 3',
                                4 => 'Semester 4',
                                5 => 'Semester 5',
                                6 => 'Semester 6',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn() => $this->dispatch('semester-updated')),
                    ])
                    ->columns(1),
            ]);
    }

    public function downloadReport()
    {
        $grades = $this->record->grades()
            ->where('academic_year_id', $this->academicYear->id)
            ->where('semester', $this->semester)
            ->with(['subject', 'class'])
            ->get();

        $pdf = Pdf::loadView('reports.student', [
            'student' => $this->record,
            'grades' => $grades,
            'semester' => $this->semester,
            'academicYear' => $this->academicYear,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, "report-{$this->record->name}-semester-{$this->semester}.pdf");
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
