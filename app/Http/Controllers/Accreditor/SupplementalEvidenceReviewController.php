<?php

namespace App\Http\Controllers\Accreditor;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\SupplementalEvidenceReview;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class SupplementalEvidenceReviewController extends Controller
{
    public function store(Request $request, Document $document)
    {
        $user = $request->user();
        $subfolder = $document->subfolder;
        $area = $subfolder->parameterCategory->parameter->area;

        if (!$user->isAccreditor() || !$user->areas()
            ->where('areas.id', $area->id)
            ->wherePivot('assignment_role', 'accreditor')
            ->exists()) {
            abort(403, 'Only an assigned Accreditor can review supplemental evidence.');
        }

        $latestResubmission = $subfolder->additionalDocumentRequests()
            ->whereIn('status', ['resubmitted', 'fulfilled'])
            ->latest()
            ->first();

        if (!$latestResubmission || $document->created_at->lt($latestResubmission->created_at)) {
            abort(422, 'This document is not supplemental evidence for a request.');
        }

        $validated = $request->validate([
            'result' => ['required', 'in:accepted,needs_revision'],
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        SupplementalEvidenceReview::updateOrCreate(
            ['document_id' => $document->id, 'user_id' => $user->id],
            $validated,
        );

        AuditLogService::log('review_supplemental_evidence', $document, "Accreditor {$user->name} marked supplemental evidence '{$document->original_filename}' as {$validated['result']}");

        return redirect()->back()->with('success', 'Supplemental evidence review saved.');
    }
}