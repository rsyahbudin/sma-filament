<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentParent extends Model
{
    protected $table = 'parents';

    protected $fillable = [
        'student_id',
        'name',
        'phone',
        'address',
        'occupation',
        'type',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
