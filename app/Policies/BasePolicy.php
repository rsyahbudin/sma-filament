<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;

class BasePolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        // Admin can do everything
        if ($user->role->name === 'Admin') {
            return true;
        }

        // Teacher can only view and manage grades
        if ($user->role->name === 'Teacher') {
            if ($ability === 'viewAny' || $ability === 'view') {
                return true;
            }
            return false;
        }

        // Student can only view
        if ($user->role->name === 'Student') {
            if ($ability === 'viewAny' || $ability === 'view') {
                return true;
            }
            return false;
        }
    }

    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view lists
    }

    public function view(User $user, Model $model): bool
    {
        return true; // All authenticated users can view details
    }

    public function create(User $user): bool
    {
        return false; // Default to false, override in specific policies
    }

    public function update(User $user, Model $model): bool
    {
        return false; // Default to false, override in specific policies
    }

    public function delete(User $user, Model $model): bool
    {
        return false; // Default to false, override in specific policies
    }
}
 