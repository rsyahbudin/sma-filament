<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'level',
        'major',
        'academic_year_id',
        'teacher_id',
    ];

    public const LEVELS = [
        'X' => 'Kelas X',
        'XI' => 'Kelas XI',
        'XII' => 'Kelas XII',
    ];

    public const MAJORS = [
        'IPA' => 'Ilmu Pengetahuan Alam',
        'IPS' => 'Ilmu Pengetahuan Sosial',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'student_class', 'school_class_id', 'student_id')
            ->withPivot('is_promoted', 'academic_year_id')
            ->withTimestamps();
    }

    public function grades()
    {
        return $this->hasMany(Grade::class, 'class_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject')
            ->withTimestamps();
    }
}
