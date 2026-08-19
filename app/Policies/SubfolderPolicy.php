<?php

namespace App\Policies;

use App\Models\Subfolder;
use App\Models\User;

class SubfolderPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function view(User $user, Subfolder $subfolder): bool
    {
        $areaId = $subfolder->parameterCategory->parameter->area_id;
        return $user->areas()->where('areas.id', $areaId)->exists();
    }

    public function create(User $user, Subfolder $subfolder): bool
    {
        $areaId = $subfolder->parameterCategory->parameter->area_id;
        $isAssignedFaculty = $user->areas()
            ->where('areas.id', $areaId)
            ->wherePivotIn('assignment_role', ['handler', 'co-handler', 'member'])
            ->exists();

        return $isAssignedFaculty;
    }

    public function update(User $user, Subfolder $subfolder): bool
    {
        if ($subfolder->created_by === $user->id) {
            return true;
        }

        $areaId = $subfolder->parameterCategory->parameter->area_id;
        return $user->areas()
            ->where('areas.id', $areaId)
            ->wherePivotIn('assignment_role', ['handler', 'co-handler'])
            ->exists();
    }

    public function delete(User $user, Subfolder $subfolder): bool
    {
        if ($subfolder->created_by === $user->id) {
            return true;
        }

        $areaId = $subfolder->parameterCategory->parameter->area_id;
        return $user->areas()
            ->where('areas.id', $areaId)
            ->wherePivotIn('assignment_role', ['handler', 'co-handler'])
            ->exists();
    }
}
