<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\TechnicalReport;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class TechnicalReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', TechnicalReport::class);
        $user = $request->user();

        $query = TechnicalReport::with(['area', 'creator', 'updater']);

        if (!$user->isAdmin()) {
            $userAreaIds = $user->areas()->pluck('areas.id')->toArray();
            $query->where(function ($q) use ($userAreaIds) {
                $q->whereNull('area_id')->orWhereIn('area_id', $userAreaIds);
            });
        }

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->integer('area_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('report_number', 'like', "%{$search}%")
                  ->orWhere('program', 'like', "%{$search}%")
                  ->orWhere('survey_visit', 'like', "%{$search}%");
            });
        }

        $reports = $query->latest()->paginate(10)->withQueryString();
        $areas = $user->isAdmin() ? Area::where('status', '!=', 'inactive')->get() : $user->areas()->get();

        return view('technical-reports.index', compact('reports', 'areas'));
    }

    public function create()
    {
        $this->authorize('create', TechnicalReport::class);
        $user = request()->user();
        $areas = $user->isAdmin() ? Area::where('status', '!=', 'inactive')->get() : $user->areas()->get();

        return view('technical-reports.form', [
            'report' => new TechnicalReport(),
            'areas' => $areas,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', TechnicalReport::class);

        $validated = $request->validate([
            'area_id' => ['nullable', 'exists:areas,id'],
            'report_number' => ['nullable', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'program' => ['nullable', 'string', 'max:255'],
            'survey_visit' => ['nullable', 'string', 'max:255'],
            'summary_findings' => ['nullable', 'string'],
            'technical_evaluation' => ['nullable', 'string'],
            'strengths' => ['nullable', 'string'],
            'areas_for_improvement' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'overall_score' => ['nullable', 'numeric', 'between:0,5'],
            'status' => ['required', 'string', 'in:draft,under_review,approved,published'],
        ]);

        $report = TechnicalReport::create([
            ...$validated,
            'prepared_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        AuditLogService::log('create_technical_report', $report, "Created Technical Report '{$report->title}'");

        return redirect()->route('technical-reports.show', $report)->with('success', 'Technical Report created successfully.');
    }

    public function show(TechnicalReport $technicalReport)
    {
        $this->authorize('view', $technicalReport);
        $technicalReport->load(['area', 'creator', 'updater']);

        return view('technical-reports.show', ['report' => $technicalReport]);
    }

    public function edit(TechnicalReport $technicalReport)
    {
        $this->authorize('update', $technicalReport);
        $user = request()->user();
        $areas = $user->isAdmin() ? Area::where('status', '!=', 'inactive')->get() : $user->areas()->get();

        return view('technical-reports.form', [
            'report' => $technicalReport,
            'areas' => $areas,
        ]);
    }

    public function update(Request $request, TechnicalReport $technicalReport)
    {
        $this->authorize('update', $technicalReport);

        $validated = $request->validate([
            'area_id' => ['nullable', 'exists:areas,id'],
            'report_number' => ['nullable', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'program' => ['nullable', 'string', 'max:255'],
            'survey_visit' => ['nullable', 'string', 'max:255'],
            'summary_findings' => ['nullable', 'string'],
            'technical_evaluation' => ['nullable', 'string'],
            'strengths' => ['nullable', 'string'],
            'areas_for_improvement' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'overall_score' => ['nullable', 'numeric', 'between:0,5'],
            'status' => ['required', 'string', 'in:draft,under_review,approved,published'],
        ]);

        $technicalReport->update([
            ...$validated,
            'updated_by' => $request->user()->id,
        ]);

        AuditLogService::log('update_technical_report', $technicalReport, "Updated Technical Report '{$technicalReport->title}'");

        return redirect()->route('technical-reports.show', $technicalReport)->with('success', 'Technical Report updated successfully.');
    }

    public function destroy(TechnicalReport $technicalReport)
    {
        $this->authorize('delete', $technicalReport);
        $title = $technicalReport->title;
        $technicalReport->delete();

        AuditLogService::log('delete_technical_report', $technicalReport, "Deleted Technical Report '{$title}'");

        return redirect()->route('technical-reports.index')->with('success', 'Technical Report deleted successfully.');
    }
}
