<?php

namespace App\Policies;

use App\Models\BoardReview;
use App\Models\User;

class BoardReviewPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, BoardReview $boardReview): bool
    {
        if (!$boardReview->area_id) {
            return true;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isAccreditor();
    }

    public function update(User $user, BoardReview $boardReview): bool
    {
        return $user->isAdmin() || $user->isAccreditor();
    }

    public function delete(User $user, BoardReview $boardReview): bool
    {
        return $user->isAdmin();
    }
}
