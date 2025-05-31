<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'student_class', 'school_class_id', 'student_id')
            ->withPivot('is_promoted', 'academic_year_id')
            ->withTimestamps();
    }

    public function grades()
    {
        return $this->hasMany(Grade::class, 'class_id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject_teacher', 'school_class_id', 'subject_id')
            ->withPivot('teacher_id', 'academic_year_id', 'semester')
            ->withTimestamps();
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_subject_teacher', 'school_class_id', 'teacher_id')
            ->withPivot('subject_id', 'academic_year_id', 'semester')
            ->withTimestamps();
    }

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(ClassSubjectTeacher::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
