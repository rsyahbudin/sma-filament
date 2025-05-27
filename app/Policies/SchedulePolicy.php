<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;

class SchedulePolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return in_array($user->role->name, ['Admin', 'Teacher', 'Student']);
    }

    public function view(User $user, Model $model): bool
    {
        return in_array($user->role->name, ['Admin', 'Teacher', 'Student']);
    }

    public function create(User $user): bool
    {
        return $user->role->name === 'Admin';
    }

    public function update(User $user, Model $model): bool
    {
        return $user->role->name === 'Admin';
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->role->name === 'Admin';
    }
}
