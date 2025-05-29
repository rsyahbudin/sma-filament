<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClassPromotionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from_class_id',
        'to_class_id',
        'academic_year_id',
        'failed_subjects_count',
        'is_promoted',
        'is_graduated',
        'notes',
    ];

    protected $casts = [
        'is_promoted' => 'boolean',
        'is_graduated' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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
