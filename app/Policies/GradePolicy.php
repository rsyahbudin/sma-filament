<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Grade;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;

class GradePolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function create(User $user): bool
    {
        return $user->role->name === 'Teacher' || $user->role->name === 'Admin';
    }

    public function update(User $user, Model $model): bool
    {
        return $user->role->name === 'Teacher' || $user->role->name === 'Admin';
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->role->name === 'Teacher' || $user->role->name === 'Admin';
    }

    public function viewAny(User $user): bool
    {
        return in_array($user->role->name, ['Admin', 'Teacher', 'Student']);
    }

    public function view(User $user, Model $model): bool
    {
        return in_array($user->role->name, ['Admin', 'Teacher', 'Student']);
    }
}
 