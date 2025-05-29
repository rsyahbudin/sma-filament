<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'previous_grade',
        'previous_academic_year_id',
        'previous_school',
        'transfer_reason',
        'transfer_date',
        'previous_grades',
        'previous_semester',
    ];

    protected $casts = [
        'previous_grades' => 'array',
        'transfer_date' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function previousAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'previous_academic_year_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
