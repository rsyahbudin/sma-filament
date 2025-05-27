<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;

class RolePolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->role->name === 'Admin';
    }

    public function view(User $user, Model $model): bool
    {
        return $user->role->name === 'Admin';
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
