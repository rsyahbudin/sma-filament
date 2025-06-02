<?php

namespace App\Exports;

use App\Models\Grade;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Auth;

class GradesExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        $user = Auth::user();

        $isAdmin = $user->role && $user->role->name === 'Admin';

        $query = Grade::with(['student', 'teacher', 'subject', 'class', 'academicYear']);

        if (! $isAdmin) {
            $query->where('teacher_id', $user->id);
        }

        $grades = $query->get();

        $data = [];

        foreach ($grades as $grade) {
            $data[] = [
                $grade->teacher->name ?? '',
                $grade->subject->name ?? '',
                $grade->student->nis ?? '',
                $grade->student->name ?? '',
                $grade->class->name ?? '',
                $grade->academicYear->name ?? '',
                $grade->semester,
                $grade->score,
                $grade->notes,
                $grade->created_at->format('Y-m-d H:i:s'),
                $grade->updated_at->format('Y-m-d H:i:s'),
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Teacher Name',
            'Subject Name',
            'NIS',
            'Student Name',
            'Class Name',
            'Academic Year',
            'Semester',
            'Score',
            'Notes',
            'Created At',
            'Updated At',
        ];
    }
}
