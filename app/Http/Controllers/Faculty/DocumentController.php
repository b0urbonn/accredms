<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Subfolder;
use App\Models\User;
use App\Notifications\EvidenceResubmitted;
use App\Services\AuditLogService;
use App\Services\DocumentUploadService;
use App\Services\PdfCompressionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function store(Request $request, Subfolder $subfolder, DocumentUploadService $uploadService)
    {
        $user = Auth::user();
        $areaId = $subfolder->parameterCategory->parameter->area_id;

        if (!$user->isAdmin() && !$user->areas()->where('areas.id', $areaId)->exists()) {
            abort(403, 'Unauthorized upload attempt for this Area.');
        }

        $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'mimes:pdf', 'max:102400'], // 100MB intake limit; stored files must finish at 25MB or less
            'force_compress' => ['nullable', 'boolean'],
            'completed_items' => ['nullable', 'array'],
            'completed_items.*' => ['string'],
        ]);

        if ($request->has('completed_items')) {
            $completedItems = array_values(array_unique((array) $request->input('completed_items')));
            $subfolder->update([
                'completed_checklist_items' => $completedItems,
            ]);
        }

        $forceCompress = $request->boolean('force_compress');
        $coveredEvidences = array_values(array_unique((array) $request->input('completed_items', [])));
        $uploadedCount = 0;
        $errors = [];

        foreach ($request->file('files') as $file) {
            try {
                $uploadService->upload($file, $subfolder, $forceCompress, $coveredEvidences);
                $uploadedCount++;
            } catch (\Exception $e) {
                $errors[] = $file->getClientOriginalName() . ': ' . $e->getMessage();
            }
        }

        if ($uploadedCount > 0) {
            $subfolder->unsetRelation('documents');
            $subfolder->update([
                'completed_checklist_items' => $subfolder->completed_checklist_array,
            ]);
            if ($user->isFaculty() || $user->isAdmin()) {
                $this->recordEvidenceResubmission($subfolder, $user);
            }
        }

        if ($uploadedCount > 0 && empty($errors)) {
            return $this->uploadResponse($request, 'success', "Successfully uploaded {$uploadedCount} PDF document(s).");
        } elseif ($uploadedCount > 0 && !empty($errors)) {
            return $this->uploadResponse($request, 'warning', "Uploaded {$uploadedCount} document(s), but some failed: " . implode(', ', $errors));
        }

        return $this->uploadResponse($request, 'error', 'Upload failed: ' . implode(', ', $errors));
    }

    private function uploadResponse(Request $request, string $status, string $message)
    {
        if ($request->ajax()) {
            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        }

        return redirect()->back()->with($status, $message);
    }

    private function recordEvidenceResubmission(Subfolder $subfolder, User $user): void
    {
        $area = $subfolder->parameterCategory->parameter->area;
        $openRequests = $subfolder->additionalDocumentRequests()
            ->where('status', 'open')
            ->with('requester')
            ->get();

        if ($openRequests->isEmpty()) {
            if ($subfolder->review_status === 'no_evidence') {
                $subfolder->update(['review_status' => 'under_review']);
            }

            return;
        }

        $subfolder->additionalDocumentRequests()
            ->where('status', 'open')
            ->update(['status' => 'resubmitted']);
        $subfolder->update(['review_status' => 'resubmitted']);
        AuditLogService::log(
            'resubmit_requested_documents',
            $subfolder,
            "{$user->name} resubmitted requested evidence for statement {$subfolder->code} in Area {$area->code}"
        );

        $recipients = $openRequests->pluck('requester')
            ->filter()
            ->merge(User::role('admin')->get())
            ->unique('id');
        foreach ($openRequests as $documentRequest) {
            Notification::send($recipients, new EvidenceResubmitted($documentRequest, $area->id));
        }
    }

    public function compress(Document $document, PdfCompressionService $compressionService)
    {
        $user = Auth::user();
        $areaId = $document->subfolder->parameterCategory->parameter->area_id;

        if (!$user->isAdmin() && !$user->areas()->where('areas.id', $areaId)->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $compressed = $compressionService->compress($document);

        if ($compressed) {
            return redirect()->back()->with('success', 'Document compressed successfully.');
        }

        return redirect()->back()->with('error', 'Document compression failed or file size could not be reduced.');
    }

    public function destroy(Document $document)
    {
        $user = Auth::user();
        $subfolder = $document->subfolder;
        $areaId = $subfolder->parameterCategory->parameter->area_id;

        if (!$user->isAdmin() && (!$user->isFaculty() || !$user->areas()->where('areas.id', $areaId)->exists())) {
            abort(403, 'Unauthorized action.');
        }

        $filename = $document->original_filename;

        try {
            if (Storage::disk($document->disk)->exists($document->file_path)) {
                Storage::disk($document->disk)->delete($document->file_path);
            }

            $document->forceDelete();

            $subfolder->unsetRelation('documents');
            $subfolder->unsetRelation('photos');
            $updatedChecklist = $subfolder->completed_checklist_array;
            if (!$subfolder->documents()->exists() && !$subfolder->photos()->exists()) {
                $subfolder->update([
                    'review_status' => 'no_evidence',
                    'completed_checklist_items' => [],
                ]);
            } else {
                $subfolder->update([
                    'completed_checklist_items' => $updatedChecklist,
                ]);
            }
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->back()->with('error', "Could not delete '{$filename}'. The PDF file was kept unchanged.");
        }

        AuditLogService::log('delete_document', $document, "Deleted document '{$filename}'");

        $redirectUrl = preg_replace('/#.*/', '', url()->previous()) . '#subfolder-' . $subfolder->id;
        return redirect($redirectUrl)->with('success', "Document '{$filename}' and its stored PDF file were deleted successfully.");
    }

    public function updateEvidences(Request $request, Document $document)
    {
        $user = Auth::user();
        $subfolder = $document->subfolder;
        $areaId = $subfolder->parameterCategory->parameter->area_id;

        if (!$user->isAdmin() && (!$user->isFaculty() || !$user->areas()->where('areas.id', $areaId)->exists())) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'covered_evidences' => ['nullable', 'array'],
            'covered_evidences.*' => ['string'],
        ]);

        $evidences = array_values(array_unique($validated['covered_evidences'] ?? []));
        $document->update([
            'covered_evidences' => $evidences,
        ]);

        $subfolder->unsetRelation('documents');
        $subfolder->update([
            'completed_checklist_items' => $subfolder->completed_checklist_array,
        ]);

        AuditLogService::log('update_document_evidences', $document, "Updated covered evidence items for document '{$document->original_filename}'");

        return response()->json([
            'status' => 'success',
            'message' => 'Document evidence tags updated successfully.',
            'covered_evidences' => $evidences,
            'checklist_stats' => $subfolder->checklist_stats,
        ]);
    }
}
