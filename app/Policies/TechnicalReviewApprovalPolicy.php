<?php

namespace App\Policies;

use App\Models\TechnicalReviewApproval;
use App\Models\User;

class TechnicalReviewApprovalPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TechnicalReviewApproval $item): bool
    {
        if (!$item->area_id) {
            return true;
        }

        return $user->isAdmin() || $user->isAccreditor() || $user->areas()->whereKey($item->area_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, TechnicalReviewApproval $item): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, TechnicalReviewApproval $item): bool
    {
        return $user->isAdmin();
    }
}
