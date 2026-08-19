<?php

namespace App\Http\Controllers\Accreditor;

use App\Http\Controllers\Controller;
use App\Models\AdditionalDocumentRequest;
use App\Models\Subfolder;
use App\Models\User;
use App\Notifications\AdditionalDocumentsRequested;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class AdditionalDocumentRequestController extends Controller
{
    public function store(Request $request, Subfolder $subfolder)
    {
        $user = $request->user();
        $area = $subfolder->parameterCategory->parameter->area;

        if (!$user->isAccreditor() || !$user->areas()
            ->where('areas.id', $area->id)
            ->wherePivot('assignment_role', 'accreditor')
            ->exists()) {
            abort(403, 'Only an assigned Accreditor can request additional documents.');
        }

        $validated = $request->validate([
            'requested_documents' => ['nullable', 'string', 'max:5000'],
            'remarks' => ['required', 'string', 'max:5000'],
            'due_date' => ['nullable', 'date'],
        ]);

        $documentRequest = AdditionalDocumentRequest::create([
            'subfolder_id' => $subfolder->id,
            'requested_by' => $user->id,
            'requested_documents' => $validated['requested_documents'] ?? null,
            'remarks' => $validated['remarks'],
            'due_date' => $validated['due_date'] ?? null,
            'status' => 'open',
        ]);

        $subfolder->update(['review_status' => 'additional_documents_requested']);
        AuditLogService::log(
            'request_additional_documents',
            $documentRequest,
            "Accreditor {$user->name} requested additional documents for statement {$subfolder->code} in Area {$area->code}"
        );

        $recipients = $area->users()
            ->wherePivotIn('assignment_role', ['handler', 'co-handler', 'member'])
            ->get()
            ->merge(User::role('admin')->get())
            ->unique('id');
        Notification::send($recipients, new AdditionalDocumentsRequested($documentRequest->load('subfolder'), $area->id));

        return redirect()->back()->with('success', "Additional document request sent for statement {$subfolder->code}.");
    }
}