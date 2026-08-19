<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\ParameterCategory;
use App\Models\Subfolder;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SubfolderController extends Controller
{
    public function store(Request $request, ParameterCategory $parameterCategory)
    {
        $user = Auth::user();
        $areaId = $parameterCategory->parameter->area_id;

        // Security check
        if (!$user->isAdmin() && !$user->areas()
            ->where('areas.id', $areaId)
            ->wherePivotIn('assignment_role', ['handler', 'co-handler', 'member'])
            ->exists()) {
            abort(403, 'Unauthorized action for this Area.');
        }

        $submissionToken = $request->validate([
            'submission_token' => ['required', 'uuid'],
        ])['submission_token'];
        $submissionCacheKey = "statement-submission:{$user->id}:{$submissionToken}";

        if (!Cache::add($submissionCacheKey, true, now()->addMinutes(10))) {
            return redirect()->back()->with('success', 'Statement sub-item request was already processed successfully.');
        }

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('subfolders', 'code')
                    ->where(fn ($query) => $query
                        ->where('parameter_category_id', $parameterCategory->id)
                        ->whereNull('deleted_at')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'documents_needed' => ['nullable', 'string'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('subfolders', 'id')->where(fn ($query) => $query
                    ->where('parameter_category_id', $parameterCategory->id)
                    ->whereNull('deleted_at')),
            ],
        ], [
            'code.unique' => 'This statement code already exists in this category. Use a different code.',
        ]);

        try {
            $subfolder = Subfolder::create([
                'parameter_category_id' => $parameterCategory->id,
                'parent_id' => $validated['parent_id'] ?? null,
                'code' => $validated['code'],
                'name' => $validated['name'],
                'documents_needed' => $validated['documents_needed'] ?? null,
                'created_by' => $user->id,
                'status' => 'active',
            ]);
        } catch (QueryException $exception) {
            Cache::forget($submissionCacheKey);

            if ($exception->getCode() === '23000') {
                throw ValidationException::withMessages([
                    'code' => 'This statement code already exists in this category. Use a different code.',
                ]);
            }

            throw $exception;
        } catch (\Throwable $exception) {
            Cache::forget($submissionCacheKey);

            throw $exception;
        }

        $parentLabel = $subfolder->parent ? " under {$subfolder->parent->code}" : '';
        AuditLogService::log('create_subfolder', $subfolder, "Created statement sub-item '{$subfolder->code}'{$parentLabel} under Parameter {$parameterCategory->parameter->code}");

        return redirect()->back()->with('success', "Statement sub-item '{$subfolder->code} - {$subfolder->name}' created successfully.");
    }

    public function update(Request $request, Subfolder $subfolder)
    {
        $user = Auth::user();
        $areaId = $subfolder->parameterCategory->parameter->area_id;

        if (!$user->isAdmin() && !$user->areas()
            ->where('areas.id', $areaId)
            ->wherePivotIn('assignment_role', ['handler', 'co-handler', 'member'])
            ->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('subfolders', 'code')
                    ->ignore($subfolder->id)
                    ->where(fn ($query) => $query
                        ->where('parameter_category_id', $subfolder->parameter_category_id)
                        ->whereNull('deleted_at')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'documents_needed' => ['nullable', 'string'],
        ], [
            'code.unique' => 'This statement code already exists in this category. Use a different code.',
        ]);

        $subfolder->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'documents_needed' => $validated['documents_needed'] ?? null,
        ]);

        $subfolder->update([
            'completed_checklist_items' => $subfolder->completed_checklist_array,
        ]);
        
        AuditLogService::log('update_subfolder', $subfolder, "Updated statement sub-item '{$subfolder->name}'");

        return redirect()->back()->with('success', "Statement sub-item updated successfully.");
    }

    public function destroy(Subfolder $subfolder)
    {
        $user = Auth::user();
        $areaId = $subfolder->parameterCategory->parameter->area_id;

        if (!$user->isAdmin() && !$user->areas()
            ->where('areas.id', $areaId)
            ->wherePivotIn('assignment_role', ['handler', 'co-handler', 'member'])
            ->exists()) {
            abort(403, 'Unauthorized action.');
        }

        if ($subfolder->hasDocumentsInTree()) {
            return redirect()
                ->back()
                ->with('error', "Statement sub-item '{$subfolder->name}' cannot be deleted because it or one of its sub-items has uploaded PDF files.");
        }

        $name = $subfolder->name;
        $subfolder->deleteTree();
        AuditLogService::log('delete_subfolder', $subfolder, "Deleted statement sub-item '{$name}'");

        return redirect()->back()->with('success', "Statement sub-item '{$name}' deleted successfully.");
    }

    public function storeBatch(Request $request, ParameterCategory $parameterCategory)
    {
        $user = Auth::user();
        $areaId = $parameterCategory->parameter->area_id;

        // Security check
        if (!$user->isAdmin() && !$user->areas()
            ->where('areas.id', $areaId)
            ->wherePivotIn('assignment_role', ['handler', 'co-handler', 'member'])
            ->exists()) {
            abort(403, 'Unauthorized action for this Area.');
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('subfolders', 'code')
                    ->where(fn ($query) => $query
                        ->where('parameter_category_id', $parameterCategory->id)
                        ->whereNull('deleted_at')),
            ],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.documents_needed' => ['nullable', 'string'],
            'items.*.parent_id' => [
                'nullable',
                'integer',
                Rule::exists('subfolders', 'id')->where(fn ($query) => $query
                    ->where('parameter_category_id', $parameterCategory->id)
                    ->whereNull('deleted_at')),
            ],
        ], [
            'items.*.code.required' => 'Statement Code is required for all items.',
            'items.*.code.unique' => 'One of the statement codes already exists in this category.',
            'items.*.name.required' => 'Statement Title is required for all items.',
        ]);

        $createdCount = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $parameterCategory, $user, &$createdCount) {
            foreach ($validated['items'] as $itemData) {
                if (empty($itemData['code']) || empty($itemData['name'])) {
                    continue;
                }

                $subfolder = Subfolder::create([
                    'parameter_category_id' => $parameterCategory->id,
                    'parent_id' => !empty($itemData['parent_id']) ? $itemData['parent_id'] : null,
                    'code' => trim($itemData['code']),
                    'name' => trim($itemData['name']),
                    'documents_needed' => !empty($itemData['documents_needed']) ? trim($itemData['documents_needed']) : null,
                    'created_by' => $user->id,
                    'status' => 'active',
                ]);
                $createdCount++;
            }
        });

        if ($createdCount === 0) {
            return redirect()->back()->with('error', 'No valid statement items were submitted.');
        }

        AuditLogService::log('batch_create_subfolder', $parameterCategory, "Batch created {$createdCount} statement sub-items under Parameter {$parameterCategory->parameter->code} - {$parameterCategory->category->name}");

        return redirect()->back()->with('success', "Batch created {$createdCount} statement sub-items successfully in one save!");
    }

    public function updateChecklist(Request $request, Subfolder $subfolder)
    {
        $user = Auth::user();
        $areaId = $subfolder->parameterCategory->parameter->area_id;

        if (!$user->isAdmin() && !$user->areas()
            ->where('areas.id', $areaId)
            ->wherePivotIn('assignment_role', ['handler', 'co-handler', 'member'])
            ->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action.'], 403);
        }

        if (!$subfolder->documents()->exists() && !$subfolder->photos()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Upload at least one PDF document or photo before marking evidence items as complete.',
            ], 422);
        }

        $validated = $request->validate([
            'completed_items' => ['nullable', 'array'],
            'completed_items.*' => ['string'],
        ]);

        $subfolder->update([
            'completed_checklist_items' => array_values(array_unique($validated['completed_items'] ?? [])),
        ]);

        AuditLogService::log('update_subfolder_checklist', $subfolder, "Updated completed evidence checklist items for '{$subfolder->code}'");

        return response()->json([
            'status' => 'success',
            'message' => 'Evidence checklist status updated successfully.',
            'checklist_stats' => $subfolder->checklist_stats,
        ]);
    }

    public function updateReviewStatus(Request $request, Subfolder $subfolder)
    {
        $user = Auth::user();
        if (!$user->isAccreditor() && !$user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'review_status' => ['required', 'string', 'in:no_evidence,under_review,additional_documents_requested,resubmitted,evaluated'],
        ]);

        $subfolder->update([
            'review_status' => $validated['review_status'],
        ]);

        AuditLogService::log('update_review_status', $subfolder, "Updated review status for '{$subfolder->code}' to '{$validated['review_status']}'");

        return response()->json([
            'status' => 'success',
            'review_status' => $subfolder->review_status,
            'review_status_label' => str_replace('_', ' ', $subfolder->review_status),
        ]);
    }
}
