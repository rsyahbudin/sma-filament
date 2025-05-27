<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;

class UserPolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return in_array($user->role->name, ['Admin', 'Teacher']);
    }

    public function view(User $user, Model $model): bool
    {
        return in_array($user->role->name, ['Admin', 'Teacher']) || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->role->name === 'Admin';
    }

    public function update(User $user, Model $model): bool
    {
        // Users can only update their own profile
        return $user->id === $model->id;
    }

    public function delete(User $user, Model $model): bool
    {
        // Only admin can delete users
        return $user->role->name === 'Admin';
    }
}
