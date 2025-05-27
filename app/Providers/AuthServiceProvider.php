<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Grade;
use App\Models\Role;
use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Policies\UserPolicy;
use App\Policies\GradePolicy;
use App\Policies\RolePolicy;
use App\Policies\AcademicYearPolicy;
use App\Policies\SchedulePolicy;
use App\Policies\SchoolClassPolicy;
use App\Policies\SubjectPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Grade::class => GradePolicy::class,
        Role::class => RolePolicy::class,
        AcademicYear::class => AcademicYearPolicy::class,
        Schedule::class => SchedulePolicy::class,
        SchoolClass::class => SchoolClassPolicy::class,
        Subject::class => SubjectPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        // Register gates for other models
        Gate::define('manage-grades', function (User $user) {
            return $user->role->name === 'Teacher';
        });

        Gate::define('view-dashboard', function (User $user) {
            return in_array($user->role->name, ['Admin', 'Teacher', 'Student']);
        });

        // Gate for profile editing
        Gate::define('edit-profile', function (User $user, User $model) {
            return $user->id === $model->id;
        });
    }
}
