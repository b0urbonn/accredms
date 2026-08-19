<?php

namespace App\Http\Controllers;

use App\Models\Subfolder;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EvidenceStatusController extends Controller
{
    public function update(Request $request, Subfolder $subfolder)
    {
        $validated = $request->validate([
            'evidence_status' => ['required', Rule::in(Subfolder::EVIDENCE_STATUSES)],
        ]);

        $user = $request->user();
        $area = $subfolder->parameterCategory->parameter->area;
        $newStatus = $validated['evidence_status'];
        $currentStatus = $subfolder->evidence_status ?? 'draft';

        if ($user->isAdmin()) {
            abort(403, 'Administrators can view evidence status but cannot change its workflow state.');
        }

        $isAssignedFaculty = $user->isFaculty() && $user->areas()
            ->where('areas.id', $area->id)
            ->wherePivotIn('assignment_role', ['handler', 'co-handler', 'member'])
            ->exists();
        $isAssignedAccreditor = $user->isAccreditor() && $user->areas()
            ->where('areas.id', $area->id)
            ->wherePivot('assignment_role', 'accreditor')
            ->exists();

        if ($isAssignedFaculty) {
            $this->authorizeFacultySubmission($subfolder, $currentStatus, $newStatus);
        } elseif ($isAssignedAccreditor) {
            $this->authorizeAccreditorReview($currentStatus, $newStatus);
        } else {
            abort(403, 'You are not assigned to update evidence status for this Area.');
        }

        $subfolder->update(['evidence_status' => $newStatus]);
        AuditLogService::log(
            'update_evidence_status',
            $subfolder,
            "{$user->name} changed evidence status for statement {$subfolder->code} in Area {$area->code} from {$currentStatus} to {$newStatus}"
        );

        return redirect()->back()->with('success', "Evidence status updated to " . str_replace('_', ' ', $newStatus) . '.');
    }

    private function authorizeFacultySubmission(Subfolder $subfolder, string $currentStatus, string $newStatus): void
    {
        if ($newStatus !== 'submitted' || !in_array($currentStatus, ['draft', 'needs_revision'], true)) {
            $this->invalidTransition();
        }

        if (!$subfolder->hasDocumentsInTree()) {
            throw ValidationException::withMessages([
                'evidence_status' => 'Upload evidence before submitting this statement for review.',
            ]);
        }
    }

    private function authorizeAccreditorReview(string $currentStatus, string $newStatus): void
    {
        $allowedTransitions = [
            'submitted' => ['under_review', 'needs_revision', 'approved'],
            'under_review' => ['needs_revision', 'approved'],
        ];

        if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [], true)) {
            $this->invalidTransition();
        }
    }

    private function invalidTransition(): void
    {
        throw ValidationException::withMessages([
            'evidence_status' => 'This evidence status transition is not allowed.',
        ]);
    }
}