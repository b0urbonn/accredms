<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\AdditionalDocumentRequest;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\Subfolder;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $totalAreas = Area::count();
            $totalUsers = User::count();
            $totalDocuments = Document::count();
            $totalSizeBytes = Document::sum('file_size_bytes') ?? 0;

            $recentActivities = AuditLog::with('user')->orderBy('created_at', 'desc')->take(10)->get();
            $areas = Area::withCount(['parameters', 'users'])->orderBy('code')->get();
            $openDocumentRequests = AdditionalDocumentRequest::where('status', 'open')
                ->with(['subfolder.parameterCategory.parameter.area', 'requester'])
                ->latest()
                ->get();
            $adminAreaTasks = $this->buildAreaTasks($areas, $openDocumentRequests);

            $categoryCounts = \Illuminate\Support\Facades\DB::table('documents')
                ->join('subfolders', 'documents.subfolder_id', '=', 'subfolders.id')
                ->join('parameter_categories', 'subfolders.parameter_category_id', '=', 'parameter_categories.id')
                ->join('categories', 'parameter_categories.category_id', '=', 'categories.id')
                ->whereNull('documents.deleted_at')
                ->select('categories.name', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                ->groupBy('categories.name')
                ->pluck('count', 'name');

            $categoryDistribution = [
                'system_input' => (int) ($categoryCounts['System Input and Process'] ?? 0),
                'outcomes' => (int) ($categoryCounts['Outcomes'] ?? 0),
                'implementation' => (int) ($categoryCounts['Implementation'] ?? 0),
            ];

            return view('dashboards.admin', compact(
                'totalAreas', 'totalUsers', 'totalDocuments', 'totalSizeBytes',
                'recentActivities', 'areas', 'openDocumentRequests', 'adminAreaTasks', 'categoryDistribution'
            ));
        }

        if ($user->isFaculty()) {
            $assignedAreas = $user->areas()->withCount('parameters')->get();
            $assignedAreaIds = $assignedAreas->pluck('id');

            $assignedDocuments = Document::whereHas('subfolder.parameterCategory.parameter', function ($query) use ($assignedAreaIds) {
                $query->whereIn('area_id', $assignedAreaIds);
            });

            $totalDocuments = (clone $assignedDocuments)->count();
            $totalSizeBytes = (clone $assignedDocuments)->sum('file_size_bytes');

            $recentDocuments = $assignedDocuments
                ->with('subfolder.parameterCategory.parameter.area')
                ->orderBy('created_at', 'desc')
                ->take(8)
                ->get();

            $openDocumentRequests = AdditionalDocumentRequest::where('status', 'open')
                ->whereHas('subfolder.parameterCategory.parameter', fn ($query) => $query->whereIn('area_id', $assignedAreaIds))
                ->with(['subfolder.parameterCategory.parameter.area', 'requester'])
                ->latest()
                ->get();

            $assignedStatements = Subfolder::where('status', 'active')
                ->whereHas('parameterCategory.parameter', fn ($query) => $query->whereIn('area_id', $assignedAreaIds))
                ->with('parameterCategory.parameter:id,area_id')
                ->withCount('documents')
                ->get()
                ->groupBy(fn (Subfolder $subfolder) => $subfolder->parameterCategory->parameter->area_id);
            $requestsByArea = $openDocumentRequests->groupBy(
                fn (AdditionalDocumentRequest $documentRequest) => $documentRequest->subfolder->parameterCategory->parameter->area_id
            );
            $today = now()->startOfDay();
            $facultyAreaTasks = $assignedAreas->map(function (Area $area) use ($assignedStatements, $requestsByArea, $today) {
                $statements = $assignedStatements->get($area->id, collect());
                $requests = $requestsByArea->get($area->id, collect());
                $deadline = $requests->filter(fn (AdditionalDocumentRequest $request) => $request->due_date)
                    ->sortBy('due_date')
                    ->first();
                $totalStatements = $statements->count();
                $completedStatements = $statements->where('documents_count', '>', 0)->count();

                return (object) [
                    'area' => $area,
                    'totalStatements' => $totalStatements,
                    'completedStatements' => $completedStatements,
                    'missingEvidenceCount' => $totalStatements - $completedStatements,
                    'returnedForRevisionCount' => $requests->count(),
                    'progressPercent' => $totalStatements > 0 ? (int) round(($completedStatements / $totalStatements) * 100) : 0,
                    'nextDeadline' => $deadline?->due_date,
                    'hasOverdueDeadline' => $deadline?->due_date?->lt($today) ?? false,
                ];
            });
            $missingEvidenceCount = $facultyAreaTasks->sum('missingEvidenceCount');
            $returnedForRevisionCount = $facultyAreaTasks->sum('returnedForRevisionCount');

            return view('dashboards.faculty', compact('assignedAreas', 'totalDocuments', 'totalSizeBytes', 'recentDocuments', 'openDocumentRequests', 'facultyAreaTasks', 'missingEvidenceCount', 'returnedForRevisionCount'));
        }

        // Accreditor
        $assignedAreas = $user->areas()
            ->with(['handlers', 'coHandlers', 'members'])
            ->withCount('parameters')
            ->get();
        $assignedAreaIds = $assignedAreas->pluck('id');

        $recentDocuments = Document::whereHas('subfolder.parameterCategory.parameter', function ($q) use ($assignedAreaIds) {
            $q->whereIn('area_id', $assignedAreaIds);
        })->with('subfolder.parameterCategory.parameter.area')->orderBy('created_at', 'desc')->take(8)->get();
        $requestedDocuments = AdditionalDocumentRequest::where('requested_by', $user->id)
            ->with(['subfolder.parameterCategory.parameter.area', 'assignee'])
            ->latest()
            ->get();
        $requestAreaTabs = $requestedDocuments
            ->groupBy(fn (AdditionalDocumentRequest $request) => $request->subfolder->parameterCategory->parameter->area_id)
            ->map(function ($requests) {
                $area = $requests->first()->subfolder->parameterCategory->parameter->area;

                return (object) [
                    'id' => $area->id,
                    'code' => $area->code,
                    'name' => $area->name,
                    'count' => $requests->count(),
                ];
            })
            ->sortBy('code')
            ->values();

        return view('dashboards.accreditor', compact('assignedAreas', 'recentDocuments', 'requestedDocuments', 'requestAreaTabs'));
    }

    private function buildAreaTasks($areas, $openDocumentRequests)
    {
        $areaIds = $areas->pluck('id');
        $statementsByArea = Subfolder::where('status', 'active')
            ->whereHas('parameterCategory.parameter', fn ($query) => $query->whereIn('area_id', $areaIds))
            ->with('parameterCategory.parameter:id,area_id')
            ->withCount('documents')
            ->get()
            ->groupBy(fn (Subfolder $subfolder) => $subfolder->parameterCategory->parameter->area_id);
        $requestsByArea = $openDocumentRequests->groupBy(
            fn (AdditionalDocumentRequest $documentRequest) => $documentRequest->subfolder->parameterCategory->parameter->area_id
        );
        $today = now()->startOfDay();

        return $areas->map(function (Area $area) use ($statementsByArea, $requestsByArea, $today) {
            $statements = $statementsByArea->get($area->id, collect());
            $requests = $requestsByArea->get($area->id, collect());
            $deadline = $requests->filter(fn (AdditionalDocumentRequest $request) => $request->due_date)->sortBy('due_date')->first();
            $totalStatements = $statements->count();
            $completedStatements = $statements->where('documents_count', '>', 0)->count();

            return (object) [
                'area' => $area,
                'totalStatements' => $totalStatements,
                'completedStatements' => $completedStatements,
                'missingEvidenceCount' => $totalStatements - $completedStatements,
                'returnedForRevisionCount' => $requests->count(),
                'progressPercent' => $totalStatements > 0 ? (int) round(($completedStatements / $totalStatements) * 100) : 0,
                'nextDeadline' => $deadline?->due_date,
                'hasOverdueDeadline' => $deadline?->due_date?->lt($today) ?? false,
            ];
        });
    }
}
