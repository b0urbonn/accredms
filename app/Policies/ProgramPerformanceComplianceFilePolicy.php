<?php

namespace App\Policies;

use App\Models\Area;
use App\Models\ProgramPerformanceComplianceFile;
use App\Models\User;

class ProgramPerformanceComplianceFilePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ProgramPerformanceComplianceFile $file): bool
    {
        return $user->areas()->whereKey($file->area_id)->exists();
    }

    public function upload(User $user, Area $area): bool
    {
        return $user->isFaculty() && $user->areas()
            ->whereKey($area->id)
            ->wherePivotIn('assignment_role', ['handler', 'co-handler', 'member'])
            ->exists();
    }

    public function update(User $user, ProgramPerformanceComplianceFile $file): bool
    {
        return $this->upload($user, $file->area);
    }

    public function delete(User $user, ProgramPerformanceComplianceFile $file): bool
    {
        return $user->isFaculty() && $user->areas()
            ->whereKey($file->area_id)
            ->wherePivotIn('assignment_role', ['handler', 'co-handler'])
            ->exists();
    }
}