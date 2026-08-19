<?php

namespace App\Policies;

use App\Models\CopcFile;
use App\Models\User;

class CopcFilePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CopcFile $file): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, CopcFile $file): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, CopcFile $file): bool
    {
        return $user->isAdmin();
    }
}