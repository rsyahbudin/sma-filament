<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'minimum_score',
        'is_active',
    ];

    protected $casts = [
        'minimum_score' => 'float',
        'is_active' => 'boolean',
    ];

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_subject_teacher', 'subject_id', 'teacher_id')
            ->withPivot('school_class_id', 'academic_year_id', 'semester')
            ->withTimestamps();
    }

    public function schoolClasses(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject_teacher', 'subject_id', 'school_class_id')
            ->withPivot('teacher_id', 'academic_year_id', 'semester')
            ->withTimestamps();
    }

    public function classSubjectTeachers(): HasMany
    {
        return $this->hasMany(ClassSubjectTeacher::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
