<?php

namespace App\Policies;

use App\Models\TechnicalReport;
use App\Models\User;

class TechnicalReportPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TechnicalReport $technicalReport): bool
    {
        if (!$technicalReport->area_id) {
            return true;
        }

        return $user->areas()->whereKey($technicalReport->area_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isAccreditor() || ($user->isFaculty() && $user->areas()
            ->wherePivotIn('assignment_role', ['handler', 'co-handler', 'member'])
            ->exists());
    }

    public function update(User $user, TechnicalReport $technicalReport): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isAccreditor()) {
            return !$technicalReport->area_id || $user->areas()->whereKey($technicalReport->area_id)->exists();
        }

        return $user->isFaculty() && $technicalReport->area_id && $user->areas()
            ->whereKey($technicalReport->area_id)
            ->wherePivotIn('assignment_role', ['handler', 'co-handler'])
            ->exists();
    }

    public function delete(User $user, TechnicalReport $technicalReport): bool
    {
        return $user->isAdmin();
    }
}
