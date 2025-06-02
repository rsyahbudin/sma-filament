<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GradeTemplate implements FromCollection, WithHeadings
{
    protected $teacherId;

    public function __construct($teacherId)
    {
        $this->teacherId = $teacherId;
    }

    public function collection()
    {
        return DB::table('users as u_teacher')
            ->join('class_subject_teacher as cst', 'u_teacher.id', '=', 'cst.teacher_id')
            ->join('subjects as s', 'cst.subject_id', '=', 's.id')
            ->join('school_classes as sc', 'cst.school_class_id', '=', 'sc.id')
            ->join('academic_years as ay', 'cst.academic_year_id', '=', 'ay.id')
            ->leftJoin('student_class as stc', function ($join) {
                $join->on('sc.id', '=', 'stc.school_class_id')
                     ->on('ay.id', '=', 'stc.academic_year_id');
            })
            ->leftJoin('users as u_student', 'stc.student_id', '=', 'u_student.id')
            ->leftJoin('grades as g', function ($join) {
                $join->on('u_student.id', '=', 'g.user_id')
                     ->on('s.id', '=', 'g.subject_id')
                     ->on('sc.id', '=', 'g.class_id')
                     ->on('ay.id', '=', 'g.academic_year_id')
                     ->on('cst.semester', '=', 'g.semester');
            })
            ->where('u_teacher.id', $this->teacherId)
            ->where('ay.is_active', 1)
            ->select(
                'u_teacher.name as teacher_name',
                's.name as subject_name',
                'sc.name as class_name',
                'u_student.nis as student_nis',
                'u_student.name as student_name',
                'cst.semester',
                'ay.name as academic_year_name',
                'g.score',
                'g.notes'
            )
            ->get();
    }

    public function headings(): array
    {
        return [
            'Teacher Name',
            'Subject',
            'Class',
            'Student NIS',
            'Student Name',
            'Semester',
            'Academic Year',
            'Score',
            'Notes',
        ];
    }
}
