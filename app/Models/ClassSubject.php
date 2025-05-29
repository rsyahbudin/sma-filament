<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClassSubject extends Model
{
    use HasFactory;

    protected $table = 'class_subject';

    protected $fillable = [
        'school_class_id',
        'subject_id',
        'academic_year_id',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
