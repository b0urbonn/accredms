<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function view(User $user, Document $document): bool
    {
        $areaId = $document->subfolder->parameterCategory->parameter->area_id;
        return $user->areas()->where('areas.id', $areaId)->exists();
    }

    public function stream(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }

    public function download(User $user, Document $document): bool
    {
        if ($user->isAccreditor()) {
            // Configurable download for accreditor - default false unless allowed
            return config('accredms.accreditor_download_allowed', false);
        }

        return $this->view($user, $document);
    }

    public function upload(User $user, Document $document): bool
    {
        $areaId = $document->subfolder->parameterCategory->parameter->area_id;
        return $user->areas()
            ->where('areas.id', $areaId)
            ->wherePivotIn('assignment_role', ['handler', 'co-handler', 'member'])
            ->exists();
    }

    public function update(User $user, Document $document): bool
    {
        if ($document->uploaded_by === $user->id) {
            return true;
        }

        $areaId = $document->subfolder->parameterCategory->parameter->area_id;
        return $user->areas()
            ->where('areas.id', $areaId)
            ->wherePivotIn('assignment_role', ['handler', 'co-handler'])
            ->exists();
    }

    public function delete(User $user, Document $document): bool
    {
        if ($document->uploaded_by === $user->id) {
            return true;
        }

        $areaId = $document->subfolder->parameterCategory->parameter->area_id;
        return $user->areas()
            ->where('areas.id', $areaId)
            ->wherePivotIn('assignment_role', ['handler', 'co-handler'])
            ->exists();
    }

    public function addRemark(User $user, Document $document): bool
    {
        if ($user->isAccreditor()) {
            return $this->view($user, $document);
        }

        return $user->isAdmin();
    }
}
