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
        'academic_year_id',
        'teacher_id',
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
        return $this->belongsToMany(User::class, 'student_class', 'class_id', 'student_id')
            ->withPivot('is_promoted')
            ->withTimestamps();
    }

    public function grades()
    {
        return $this->hasMany(Grade::class, 'class_id');
    }
}
