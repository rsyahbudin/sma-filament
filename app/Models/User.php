<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Role;
use App\Models\Grade;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'date_of_birth',
        'gender',
        'role_id',
        'nis',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function isAdmin()
    {
        return $this->role->name === 'admin';
    }

    public function isTeacher()
    {
        return $this->role->name === 'teacher';
    }

    public function isStudent()
    {
        return $this->role->name === 'student';
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'student_class', 'student_id', 'school_class_id')
            ->withPivot('is_promoted', 'academic_year_id')
            ->withTimestamps();
    }

    public function grades()
    {
        return $this->hasMany(Grade::class, 'user_id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject_teacher', 'teacher_id', 'subject_id')
            ->withPivot('school_class_id', 'academic_year_id', 'semester')
            ->withTimestamps();
    }

    public function teachingAssignments()
    {
        return $this->hasMany(ClassSubjectTeacher::class, 'teacher_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'teacher_id');
    }

    public function parents(): HasMany
    {
        return $this->hasMany(StudentParent::class, 'student_id');
    }
}
