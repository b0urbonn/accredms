<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\DocumentRemark;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function showAreaReport(Area $area)
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->areas()->where('areas.id', $area->id)->exists()) {
            abort(403, 'Unauthorized access to this Area report.');
        }

        $area->load([
            'parameters.parameterCategories.category',
            'parameters.parameterCategories.allSubfolders.documents.uploader',
            'parameters.parameterCategories.allSubfolders.documents.remarks',
            'parameters.parameterCategories.allSubfolders.photos',
            'users',
        ]);

        $totalParameters = $area->parameters->count();
        $totalSubfolders = 0;
        $totalDocuments = 0;
        $completedStatements = 0;
        $reportRows = collect();

        foreach ($area->parameters as $param) {
            foreach ($param->parameterCategories as $paramCat) {
                $statements = $paramCat->allSubfolders;
                $totalSubfolders += $statements->count();

                $childrenByParent = $statements->groupBy('parent_id');
                $appendRows = function ($parentId, int $depth) use (&$appendRows, $childrenByParent, $param, $paramCat, $reportRows): void {
                    $children = $childrenByParent->get($parentId, collect())->sort(function ($a, $b) {
                        return strnatcasecmp($a->code ?? '', $b->code ?? '');
                    });
                    foreach ($children as $statement) {
                        $reportRows->push([
                            'parameter' => $param,
                            'category' => $paramCat,
                            'statement' => $statement,
                            'depth' => $depth,
                        ]);
                        $appendRows($statement->id, $depth + 1);
                    }
                };
                $appendRows(null, 0);

                foreach ($statements as $sub) {
                    $totalDocuments += $sub->documents->count();
                    if ($sub->documents->isNotEmpty() || $sub->photos->isNotEmpty()) {
                        $completedStatements++;
                    }
                }
            }
        }

        $missingStatements = $totalSubfolders - $completedStatements;
        $evidenceCompletionPercent = $totalSubfolders > 0
            ? (int) round(($completedStatements / $totalSubfolders) * 100)
            : 0;

        $findings = DocumentRemark::query()
            ->whereHas('document.subfolder.parameterCategory.parameter', function ($query) use ($area) {
                $query->where('area_id', $area->id);
            })
            ->with('user', 'document.subfolder.parameterCategory.parameter')
            ->latest()
            ->get();

        AuditLogService::log('generate_report', $area, "Generated official compliance report for Area {$area->code}");

        return view('admin.reports.area_report', compact(
            'area',
            'totalParameters',
            'totalSubfolders',
            'totalDocuments',
            'completedStatements',
            'missingStatements',
            'evidenceCompletionPercent',
            'reportRows',
            'findings',
        ));
    }

    public function toggleAreaSubmission(Request $request, Area $area)
    {
        $user = Auth::user();

        // Only Admin or assigned Area Handler can toggle submission status
        $isHandler = $user->areas()
            ->where('areas.id', $area->id)
            ->wherePivotIn('assignment_role', ['handler', 'co-handler'])
            ->exists();

        if (!$user->isAdmin() && !$isHandler) {
            abort(403, 'Only assigned Handlers or Admins can mark an Area as submission-ready.');
        }

        $newStatus = ($area->status === 'submission_ready') ? 'active' : 'submission_ready';
        $area->update(['status' => $newStatus]);

        $statusText = ($newStatus === 'submission_ready') ? 'MARKED AS SUBMISSION-READY' : 'REOPENED FOR EDITING';
        AuditLogService::log('toggle_submission', $area, "Area {$area->code} was {$statusText} by {$user->name}");

        return redirect()->back()->with('success', "Area {$area->code} status updated to: " . str_replace('_', ' ', strtoupper($newStatus)));
    }
}
