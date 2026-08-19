<?php

namespace App\Policies;

use App\Models\Parameter;
use App\Models\User;

class ParameterPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function view(User $user, Parameter $parameter): bool
    {
        return $user->areas()->where('areas.id', $parameter->area_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Parameter $parameter): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Parameter $parameter): bool
    {
        return $user->isAdmin();
    }
}
