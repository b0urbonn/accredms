<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\EvidencePhoto;
use App\Models\Subfolder;
use App\Services\AuditLogService;
use App\Services\EvidencePhotoUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EvidencePhotoController extends Controller
{
    public function store(Request $request, Subfolder $subfolder, EvidencePhotoUploadService $uploadService)
    {
        $user = Auth::user();
        $areaId = $subfolder->parameterCategory->parameter->area_id;

        if (!$user->isAdmin() && !$user->areas()->where('areas.id', $areaId)->exists()) {
            abort(403, 'Unauthorized upload attempt for this Area.');
        }

        $validated = $request->validate([
            'photos' => ['required', 'array', 'min:1'],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:15360'],
            'checklist_item' => ['required', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $checklistItems = $subfolder->parsed_checklist;
        if ($checklistItems && !in_array($validated['checklist_item'], $checklistItems, true)) {
            return redirect()->back()->withErrors([
                'checklist_item' => 'Choose a valid evidence item from this statement checklist.',
            ]);
        }

        $uploadedCount = 0;
        $errors = [];

        foreach ($request->file('photos') as $photo) {
            try {
                $uploadService->upload($photo, $subfolder, $validated['checklist_item'], $validated['caption'] ?? null);
                $uploadedCount++;
            } catch (\Exception $e) {
                $errors[] = $photo->getClientOriginalName() . ': ' . $e->getMessage();
            }
        }

        if ($uploadedCount > 0) {
            $subfolder->unsetRelation('documents');
            $subfolder->unsetRelation('photos');
            $subfolder->update([
                'completed_checklist_items' => $subfolder->completed_checklist_array,
                'review_status' => $subfolder->review_status === 'no_evidence' ? 'under_review' : $subfolder->review_status,
            ]);
        }

        if ($uploadedCount > 0 && empty($errors)) {
            return redirect()->back()->with('success', "Successfully captured {$uploadedCount} photo(s) as evidence for \"{$validated['checklist_item']}\".");
        } elseif ($uploadedCount > 0 && !empty($errors)) {
            return redirect()->back()->with('warning', "Uploaded {$uploadedCount} photo(s), but some failed: " . implode(', ', $errors));
        }

        return redirect()->back()->with('error', 'Photo upload failed: ' . implode(', ', $errors));
    }

    public function destroy(EvidencePhoto $evidencePhoto)
    {
        $user = Auth::user();
        $subfolder = $evidencePhoto->subfolder;
        $areaId = $subfolder->parameterCategory->parameter->area_id;

        if (!$user->isAdmin() && (!$user->isFaculty() || !$user->areas()->where('areas.id', $areaId)->exists())) {
            abort(403, 'Unauthorized action.');
        }

        $photos = EvidencePhoto::query()
            ->where('subfolder_id', $evidencePhoto->subfolder_id)
            ->where('checklist_item', $evidencePhoto->checklist_item)
            ->get();
        $filename = $evidencePhoto->original_filename;

        try {
            foreach ($photos as $photo) {
                if (Storage::disk($photo->disk)->exists($photo->file_path)) {
                    Storage::disk($photo->disk)->delete($photo->file_path);
                }

                $photo->forceDelete();
            }

            $subfolder->unsetRelation('documents');
            $subfolder->unsetRelation('photos');
            $hasEvidence = $subfolder->documents()->exists() || $subfolder->photos()->exists();
            $subfolder->update([
                'completed_checklist_items' => $subfolder->completed_checklist_array,
                'review_status' => $hasEvidence ? $subfolder->review_status : 'no_evidence',
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->back()->with('error', "Could not delete photo '{$filename}'.");
        }

        AuditLogService::log('delete_evidence_photo', $evidencePhoto, "Deleted photo evidence group '{$filename}'");

        return redirect()->back()->with('success', "Photo '{$filename}' deleted successfully.");
    }
}
