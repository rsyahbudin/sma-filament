<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'minimum_score',
    ];

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_subject')
            ->whereHas('role', function ($query) {
                $query->where('name', 'Teacher');
            });
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function classSubjectTeachers()
    {
        return $this->hasMany(ClassSubjectTeacher::class);
    }
}
