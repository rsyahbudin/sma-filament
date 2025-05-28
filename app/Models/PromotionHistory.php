<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PromotionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'from_class_id',
        'to_class_id',
        'academic_year_id',
        'average_score',
        'failed_subjects',
        'is_promoted',
        'notes',
    ];

    protected $casts = [
        'is_promoted' => 'boolean',
        'average_score' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function fromClass()
    {
        return $this->belongsTo(SchoolClass::class, 'from_class_id');
    }

    public function toClass()
    {
        return $this->belongsTo(SchoolClass::class, 'to_class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
