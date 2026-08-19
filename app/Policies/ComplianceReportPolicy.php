<?php

namespace App\Policies;

use App\Models\ComplianceReport;
use App\Models\User;

class ComplianceReportPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ComplianceReport $report): bool
    {
        return $user->areas()->whereKey($report->area_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isFaculty() && $user->areas()
            ->wherePivotIn('assignment_role', ['handler', 'co-handler', 'member'])
            ->exists();
    }

    public function update(User $user, ComplianceReport $report): bool
    {
        return $user->isFaculty() && $user->areas()
            ->whereKey($report->area_id)
            ->wherePivotIn('assignment_role', ['handler', 'co-handler', 'member'])
            ->exists();
    }

    public function delete(User $user, ComplianceReport $report): bool
    {
        return $user->isFaculty() && $user->areas()
            ->whereKey($report->area_id)
            ->wherePivotIn('assignment_role', ['handler', 'co-handler'])
            ->exists();
    }
}