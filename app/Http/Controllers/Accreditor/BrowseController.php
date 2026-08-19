<?php

namespace App\Http\Controllers\Accreditor;

use App\Http\Controllers\Controller;
use App\Models\AccreditorEvaluation;
use App\Models\Area;
use App\Models\Document;
use App\Models\DocumentRemark;
use App\Models\Subfolder;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrowseController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $areas = $user->isAdmin()
            ? Area::where('status', '!=', 'inactive')->with(['handlers', 'coHandlers', 'members'])->withCount('parameters')->get()
            : $user->areas()->where('areas.status', '!=', 'inactive')->with(['handlers', 'coHandlers', 'members'])->withCount('parameters')->get();

        return view('accreditor.index', compact('areas'));
    }

    public function showArea(Area $area)
    {
        $user = Auth::user();
        if ($area->status === 'inactive') {
            abort(403, 'This Area is inactive and cannot be accessed.');
        }

        if (!$user->isAdmin() && !$user->areas()->where('areas.id', $area->id)->exists()) {
            abort(403, 'Access denied for this Area.');
        }

        $area->load([
            'parameters.parameterCategories.category',
            'parameters.parameterCategories.subfolders.documents.uploader',
            'parameters.parameterCategories.subfolders.documents.remarks.user',
            'parameters.parameterCategories.subfolders.documents.supplementalEvidenceReviews.reviewer',
            'parameters.parameterCategories.subfolders.photos.uploader',
            'parameters.parameterCategories.subfolders.evaluations',
            'parameters.parameterCategories.subfolders.additionalDocumentRequests.requester',
            'parameters.parameterCategories.subfolders.creator',
            'parameters.parameterCategories.subfolders.children.documents.uploader',
            'parameters.parameterCategories.subfolders.children.documents.remarks.user',
            'parameters.parameterCategories.subfolders.children.documents.supplementalEvidenceReviews.reviewer',
            'parameters.parameterCategories.subfolders.children.photos.uploader',
            'parameters.parameterCategories.subfolders.children.evaluations',
            'parameters.parameterCategories.subfolders.children.additionalDocumentRequests.requester',
            'parameters.parameterCategories.subfolders.children.creator',
            'parameters.parameterCategories.subfolders.children.children.documents.uploader',
            'parameters.parameterCategories.subfolders.children.children.documents.remarks.user',
            'parameters.parameterCategories.subfolders.children.children.documents.supplementalEvidenceReviews.reviewer',
            'parameters.parameterCategories.subfolders.children.children.photos.uploader',
            'parameters.parameterCategories.subfolders.children.children.evaluations',
            'parameters.parameterCategories.subfolders.children.children.additionalDocumentRequests.requester',
            'parameters.parameterCategories.subfolders.children.children.creator',
        ]);

        $statementCounts = Subfolder::query()
            ->where('status', 'active')
            ->whereHas('parameterCategory.parameter', function ($query) use ($area) {
                $query->where('area_id', $area->id);
            })
            ->withCount(['documents', 'photos'])
            ->get()
            ->reduce(function (array $counts, Subfolder $subfolder) {
                $counts['total']++;

                if ($subfolder->documents_count > 0 || $subfolder->photos_count > 0) {
                    $counts['complete']++;
                }

                return $counts;
            }, ['total' => 0, 'complete' => 0]);

        $totalStatements = $statementCounts['total'];
        $completedStatements = $statementCounts['complete'];
        $missingStatements = $totalStatements - $completedStatements;
        $evidenceCompletionPercent = $totalStatements > 0
            ? (int) round(($completedStatements / $totalStatements) * 100)
            : 0;

        return view('accreditor.show_area', compact(
            'area',
            'totalStatements',
            'completedStatements',
            'missingStatements',
            'evidenceCompletionPercent',
        ));
    }

    public function storeRemark(Request $request, Document $document)
    {
        $user = Auth::user();
        $areaId = $document->subfolder->parameterCategory->parameter->area_id;

        if (!$user->isAccreditor() || !$user->areas()->where('areas.id', $areaId)->exists()) {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'remark' => ['required', 'string', 'max:2000'],
            'rating' => ['required', 'numeric', 'min:0', 'max:5'],
            'evaluation' => ['nullable', 'string', 'max:5000'],
        ]);

        $remark = DocumentRemark::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'remark' => $validated['remark'],
        ]);

        AccreditorEvaluation::updateOrCreate(
            ['subfolder_id' => $document->subfolder_id, 'user_id' => $user->id],
            [
                'rating' => $validated['rating'],
                'evaluation' => $validated['evaluation'] ?? $validated['remark'],
            ],
        );

        AuditLogService::log('evaluate_document', $document, "Accreditor {$user->name} rated and evaluated document '{$document->original_filename}'");

        return redirect()->back()->with('success', 'Finding, rating, and evaluation saved successfully.');
    }

    public function updateRemark(Request $request, Document $document, DocumentRemark $remark)
    {
        $user = Auth::user();
        $areaId = $document->subfolder->parameterCategory->parameter->area_id;

        if ($remark->document_id !== $document->id || !$user->isAccreditor() || $remark->user_id !== $user->id || !$user->areas()->where('areas.id', $areaId)->exists()) {
            abort(403, 'You can only edit your own evaluation in an assigned Area.');
        }

        $validated = $request->validate([
            'remark' => ['required', 'string', 'max:2000'],
            'rating' => ['required', 'numeric', 'min:0', 'max:5'],
            'evaluation' => ['nullable', 'string', 'max:5000'],
        ]);

        $remark->update(['remark' => $validated['remark']]);
        AccreditorEvaluation::updateOrCreate(
            ['subfolder_id' => $document->subfolder_id, 'user_id' => $user->id],
            [
                'rating' => $validated['rating'],
                'evaluation' => $validated['evaluation'] ?? $validated['remark'],
            ],
        );

        AuditLogService::log('update_document_evaluation', $document, "Accreditor {$user->name} updated the evaluation of document '{$document->original_filename}'");

        return redirect()->back()->with('success', 'Finding, rating, and evaluation updated successfully.');
    }
}
