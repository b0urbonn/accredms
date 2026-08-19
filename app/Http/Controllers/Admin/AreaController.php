<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Subfolder;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AreaController extends Controller
{
    public function index()
    {
        $areas = Area::with(['creator', 'users', 'handlers', 'coHandlers', 'members'])
            ->withCount('parameters')
            ->orderBy('code')
            ->get();
        $assignableUsers = User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['faculty', 'admin']))
            ->orderBy('name')
            ->get();

        return view('admin.areas.index', compact('areas', 'assignableUsers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:20'],
        ]);

        $deletedArea = Area::onlyTrashed()->where('code', $request->input('code'))->first();
        if ($deletedArea && $deletedArea->parameters()->exists()) {
            $deletedArea->restore();
            AuditLogService::log('restore_area', $deletedArea, "Restored Area {$deletedArea->code} because it contains parameters and documentary data.");

            return redirect()
                ->route('admin.areas.index')
                ->with('warning', "Area {$deletedArea->code} was restored because it contains parameters and documentary data.");
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:areas,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'chairman_id' => ['nullable', 'exists:users,id'],
            'co_chairman_ids' => ['nullable', 'array'],
            'co_chairman_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'co_chairman_id' => ['nullable', 'exists:users,id'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);

        $chairmanId = $validated['chairman_id'] ?? null;
        $coChairmanIds = collect($validated['co_chairman_ids'] ?? []);
        if (!empty($validated['co_chairman_id'])) {
            $coChairmanIds->push($validated['co_chairman_id']);
        }
        $coChairmanIds = $coChairmanIds
            ->reject(fn ($userId) => (int) $userId === (int) $chairmanId)
            ->unique()
            ->values();

        $memberIds = collect($validated['member_ids'] ?? [])
            ->reject(fn ($userId) => (int) $userId === (int) $chairmanId || $coChairmanIds->contains((int) $userId))
            ->unique()
            ->values();

        $personnelIds = (clone $memberIds)
            ->when($chairmanId, fn ($ids) => $ids->push($chairmanId))
            ->merge($coChairmanIds)
            ->unique()
            ->values();

        $eligiblePersonnelCount = User::query()
            ->whereIn('id', $personnelIds)
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['faculty', 'admin']))
            ->count();

        if ($eligiblePersonnelCount !== $personnelIds->count()) {
            throw ValidationException::withMessages([
                'chairman_id' => 'Area chairman, co-chairman, and members must be Faculty or Administrator accounts.',
            ]);
        }
        $areaAttributes = collect($validated)->except(['chairman_id', 'co_chairman_id', 'co_chairman_ids', 'member_ids'])->all();
        $areaAttributes['created_by'] = Auth::id();
        $areaAttributes['status'] = 'active';

        $area = DB::transaction(function () use ($areaAttributes, $chairmanId, $coChairmanIds, $memberIds) {
            $area = Area::create($areaAttributes);
            $assignments = [];

            if ($chairmanId) {
                $assignments[$chairmanId] = [
                    'assignment_role' => 'handler',
                    'assigned_by' => Auth::id(),
                    'assigned_at' => now(),
                ];
            }

            foreach ($coChairmanIds as $coChairmanId) {
                $assignments[$coChairmanId] = [
                    'assignment_role' => 'co-handler',
                    'assigned_by' => Auth::id(),
                    'assigned_at' => now(),
                ];
            }

            foreach ($memberIds as $memberId) {
                $assignments[$memberId] = [
                    'assignment_role' => 'member',
                    'assigned_by' => Auth::id(),
                    'assigned_at' => now(),
                ];
            }

            if ($assignments) {
                $area->users()->attach($assignments);
            }

            return $area;
        });
        AuditLogService::log('create_area', $area, "Created Area {$area->code} - {$area->name}");

        return redirect()->route('admin.areas.index')->with('success', 'Area created successfully.');
    }

    public function show(Area $area)
    {
        if ($area->status === 'inactive') {
            abort(403, 'This Area is inactive and cannot be accessed.');
        }

        $area->load([
            'parameters.parameterCategories.category',
            'parameters.parameterCategories.subfolders.documents',
            'parameters.parameterCategories.subfolders.photos',
            'parameters.parameterCategories.subfolders.additionalDocumentRequests.requester',
            'parameters.parameterCategories.subfolders.creator',
            'parameters.parameterCategories.subfolders.children.documents',
            'parameters.parameterCategories.subfolders.children.photos',
            'parameters.parameterCategories.subfolders.children.additionalDocumentRequests.requester',
            'parameters.parameterCategories.subfolders.children.creator',
            'parameters.parameterCategories.subfolders.children.children.documents',
            'parameters.parameterCategories.subfolders.children.children.photos',
            'parameters.parameterCategories.subfolders.children.children.additionalDocumentRequests.requester',
            'parameters.parameterCategories.subfolders.children.children.creator',
            'users',
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

        return view('admin.areas.show', compact(
            'area',
            'totalStatements',
            'completedStatements',
            'missingStatements',
            'evidenceCompletionPercent',
        ));
    }

    public function update(Request $request, Area $area)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:areas,code,' . $area->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $area->update($validated);
        AuditLogService::log('update_area', $area, "Updated Area {$area->code}");

        return redirect()->back()->with('success', 'Area updated successfully.');
    }

    public function destroy(Area $area)
    {
        if ($area->parameters()->exists()) {
            return redirect()
                ->route('admin.areas.index')
                ->with('error', "Area {$area->code} cannot be deleted because it contains parameters and documentary data.");
        }

        $area->forceDelete();
        AuditLogService::log('delete_area', $area, "Deleted Area {$area->code}");

        return redirect()->route('admin.areas.index')->with('success', 'Area deleted successfully.');
    }
}
