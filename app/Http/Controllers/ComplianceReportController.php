<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\ComplianceEvidence;
use App\Models\ComplianceRecommendation;
use App\Models\ComplianceReport;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ComplianceReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ComplianceReport::class);
        $user = $request->user();
        $reports = ComplianceReport::query()->with(['area', 'creator'])->withCount('recommendations');

        if (!$user->isAdmin()) {
            $reports->whereIn('area_id', $user->areas()->select('areas.id'));
        }

        $reports->when($request->filled('area_id'), fn ($query) => $query->where('area_id', $request->integer('area_id')))
            ->when($request->filled('survey_visit'), fn ($query) => $query->where('survey_visit', $request->input('survey_visit')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($nested) use ($search) {
                    $nested->where('program', 'like', "%{$search}%")
                        ->orWhere('survey_visit', 'like', "%{$search}%")
                        ->orWhereHas('area', fn ($area) => $area->where('code', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
                });
            });

        $areas = $this->availableAreas($user);
        $surveyVisits = (clone $reports)->whereNotNull('survey_visit')->distinct()->orderBy('survey_visit')->pluck('survey_visit');
        $reports = $reports->latest()->paginate(12)->withQueryString();

        return view('compliance-reports.index', compact('reports', 'areas', 'surveyVisits'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', ComplianceReport::class);

        return view('compliance-reports.form', [
            'report' => new ComplianceReport(),
            'areas' => $this->availableAreas($request->user(), true),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', ComplianceReport::class);
        $data = $this->validatedData($request);
        $this->ensureManageableArea($request->user(), $data['area_id']);

        $report = DB::transaction(function () use ($request, $data) {
            $report = ComplianceReport::create([
                ...collect($data)->except('recommendations')->all(),
                'status' => 'uploaded',
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);
            $this->syncRecommendations($report, $data['recommendations'], $request);

            return $report;
        });

        AuditLogService::log('create_compliance_report', $report, "Created compliance report for Area {$report->area->code}");
        return redirect()->route('compliance-reports.show', $report)->with('success', 'Compliance report created successfully.');
    }

    public function show(ComplianceReport $complianceReport)
    {
        $this->authorize('view', $complianceReport);
        $complianceReport->load(['area', 'creator', 'updater', 'recommendations.evidences.uploader']);

        return view('compliance-reports.show', ['report' => $complianceReport]);
    }

    public function edit(ComplianceReport $complianceReport)
    {
        $this->authorize('update', $complianceReport);
        $complianceReport->load('recommendations.evidences');

        return view('compliance-reports.form', [
            'report' => $complianceReport,
            'areas' => $this->availableAreas(request()->user(), true),
        ]);
    }

    public function update(Request $request, ComplianceReport $complianceReport)
    {
        $this->authorize('update', $complianceReport);
        $data = $this->validatedData($request);
        $this->ensureManageableArea($request->user(), $data['area_id']);

        DB::transaction(function () use ($request, $data, $complianceReport) {
            $complianceReport->update([
                ...collect($data)->except('recommendations')->all(),
                'updated_by' => $request->user()->id,
            ]);
            $this->syncRecommendations($complianceReport, $data['recommendations'], $request);
        });

        AuditLogService::log('update_compliance_report', $complianceReport, "Updated compliance report for Area {$complianceReport->area->code}");
        return redirect()->route('compliance-reports.show', $complianceReport)->with('success', 'Compliance report updated successfully.');
    }

    public function destroy(ComplianceReport $complianceReport)
    {
        $this->authorize('delete', $complianceReport);
        $this->deleteRecommendationFiles($complianceReport->recommendations()->with('evidences')->get());
        $complianceReport->delete();

        AuditLogService::log('delete_compliance_report', $complianceReport, 'Deleted compliance report');
        return redirect()->route('compliance-reports.index')->with('success', 'Compliance report deleted.');
    }

    public function streamEvidence(ComplianceEvidence $evidence)
    {
        $this->authorizeEvidence($evidence, 'view');
        $disk = Storage::disk($evidence->disk);
        abort_unless($disk->exists($evidence->file_path), 404, 'Evidence file not found.');

        return response()->file($disk->path($evidence->file_path), ['Content-Type' => $evidence->mime_type]);
    }

    public function downloadEvidence(ComplianceEvidence $evidence)
    {
        $this->authorizeEvidence($evidence, 'view');
        abort_if(request()->user()->isAccreditor() && !config('accredms.accreditor_download_allowed', false), 403, 'Downloading evidence is restricted for Accreditor accounts.');
        $disk = Storage::disk($evidence->disk);
        abort_unless($disk->exists($evidence->file_path), 404, 'Evidence file not found.');

        return $disk->download($evidence->file_path, $evidence->original_filename);
    }

    public function destroyEvidence(ComplianceEvidence $evidence)
    {
        $this->authorizeEvidence($evidence, 'update');
        Storage::disk($evidence->disk)->delete($evidence->file_path);
        $evidence->delete();

        AuditLogService::log('delete_compliance_evidence', $evidence, "Deleted evidence '{$evidence->original_filename}'");
        return back()->with('success', 'Evidence file removed.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'area_id' => ['required', 'exists:areas,id'],
            'program' => ['nullable', 'string', 'max:255'],
            'survey_visit' => ['nullable', 'string', 'max:255'],
            'recommendations' => ['required', 'array', 'min:1'],
            'recommendations.*.id' => ['nullable', 'integer'],
            'recommendations.*.recommendation' => ['required', 'string'],
            'recommendations.*.action_taken' => ['nullable', 'string'],
            'recommendations.*.compliance_percentage' => ['required', 'integer', 'between:0,100'],
            'recommendations.*.files' => ['nullable', 'array'],
            'recommendations.*.files.*' => ['file', 'mimes:pdf', 'max:25600'],
        ]);
    }

    private function syncRecommendations(ComplianceReport $report, array $recommendations, Request $request): void
    {
        $existing = $report->recommendations()->with('evidences')->get()->keyBy('id');
        $keptIds = collect($recommendations)->pluck('id')->filter()->map(fn ($id) => (int) $id);

        $this->deleteRecommendationFiles($existing->whereNotIn('id', $keptIds));
        $report->recommendations()->whereNotIn('id', $keptIds)->delete();

        foreach ($recommendations as $index => $item) {
            $recommendation = isset($item['id']) && $existing->has((int) $item['id'])
                ? $existing[(int) $item['id']]
                : new ComplianceRecommendation(['compliance_report_id' => $report->id]);

            $recommendation->fill([
                'recommendation' => $item['recommendation'],
                'action_taken' => $item['action_taken'] ?? null,
                'compliance_percentage' => $item['compliance_percentage'],
                'sort_order' => $index,
            ])->save();

            foreach ($request->file("recommendations.{$index}.files", []) as $file) {
                $this->storeEvidence($file, $recommendation, $request->user()->id);
            }
        }
    }

    private function storeEvidence($file, ComplianceRecommendation $recommendation, int $userId): void
    {
        $handle = fopen($file->getRealPath(), 'rb');
        $header = fread($handle, 5);
        fclose($handle);
        abort_unless($header === '%PDF-', 422, 'Evidence files must be valid PDF documents.');

        $storedFilename = (string) Str::uuid() . '.pdf';
        $path = "compliance-evidence/{$recommendation->report->area_id}/{$recommendation->id}/{$storedFilename}";
        Storage::disk('local_private')->put($path, fopen($file->getRealPath(), 'rb'));

        $recommendation->evidences()->create([
            'uploaded_by' => $userId,
            'original_filename' => $file->getClientOriginalName(),
            'stored_filename' => $storedFilename,
            'disk' => 'local_private',
            'file_path' => $path,
            'mime_type' => 'application/pdf',
            'file_size_bytes' => $file->getSize(),
            'checksum_sha256' => hash_file('sha256', $file->getRealPath()),
        ]);
    }

    private function availableAreas($user, bool $manageableOnly = false)
    {
        $areas = Area::query()->where('status', '!=', 'inactive')->orderBy('code');
        if (!$user->isAdmin()) {
            $areas->whereIn('id', $user->areas()->select('areas.id'));
            if ($manageableOnly) {
                $areas->whereIn('id', $user->areas()->wherePivotIn('assignment_role', ['handler', 'co-handler', 'member'])->select('areas.id'));
            }
        }
        return $areas->get();
    }

    private function ensureManageableArea($user, int $areaId): void
    {
        if ($user->isAdmin()) {
            return;
        }

        abort_unless($user->areas()->whereKey($areaId)->wherePivotIn('assignment_role', ['handler', 'co-handler', 'member'])->exists(), 403);
    }

    private function authorizeEvidence(ComplianceEvidence $evidence, string $ability): void
    {
        $this->authorize($ability, $evidence->recommendation->report);
    }

    private function deleteRecommendationFiles($recommendations): void
    {
        foreach ($recommendations as $recommendation) {
            foreach ($recommendation->evidences as $evidence) {
                Storage::disk($evidence->disk)->delete($evidence->file_path);
            }
        }
    }
}