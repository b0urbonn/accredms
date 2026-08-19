<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    public function index()
    {
        $areas = Area::with(['users', 'handlers', 'coHandlers', 'members', 'accreditors'])->orderBy('code')->get();
        $facultyUsers = User::whereHas('roles', fn($q) => $q->whereIn('name', ['faculty', 'admin']))->orderBy('name')->get();
        $accreditorUsers = User::whereHas('roles', fn($q) => $q->where('name', 'accreditor'))->orderBy('name')->get();

        return view('admin.assignments.index', compact('areas', 'facultyUsers', 'accreditorUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'area_id' => ['required', 'exists:areas,id'],
            'chairman_id' => ['nullable', 'exists:users,id'],
            'co_chairman_ids' => ['nullable', 'array'],
            'co_chairman_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'co_chairman_id' => ['nullable', 'exists:users,id'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'accreditor_ids' => ['nullable', 'array'],
            'accreditor_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'assignment_role' => ['nullable', 'in:handler,co-handler,member,accreditor'],
        ]);

        $area = Area::findOrFail($validated['area_id']);

        if (!empty($validated['user_id']) && !empty($validated['assignment_role'])) {
            $user = User::findOrFail($validated['user_id']);
            $role = $validated['assignment_role'];

            if ($role === 'handler') {
                $area->users()->wherePivot('assignment_role', 'handler')->detach();
            }

            $area->users()->syncWithoutDetaching([
                $user->id => [
                    'assignment_role' => $role,
                    'assigned_by' => Auth::id(),
                    'assigned_at' => now(),
                ]
            ]);

            AuditLogService::log('assign_area', $area, "Updated assignment for Area {$area->code}");
            return redirect()->back()->with('success', "Assigned {$user->name} to Area {$area->code} successfully.");
        }

        $chairmanId = $validated['chairman_id'] ?? null;
        $coChairmanIds = collect($validated['co_chairman_ids'] ?? []);
        if (!empty($validated['co_chairman_id'])) {
            $coChairmanIds->push($validated['co_chairman_id']);
        }
        $coChairmanIds = $coChairmanIds
            ->reject(fn($id) => (int)$id === (int)$chairmanId)
            ->unique()
            ->values();

        $memberIds = collect($validated['member_ids'] ?? [])
            ->reject(fn($id) => (int)$id === (int)$chairmanId || $coChairmanIds->contains((int)$id))
            ->unique()
            ->values();

        $accreditorIds = collect($validated['accreditor_ids'] ?? [])->unique()->values();

        $assignments = [];

        if ($chairmanId) {
            $assignments[$chairmanId] = [
                'assignment_role' => 'handler',
                'assigned_by' => Auth::id(),
                'assigned_at' => now(),
            ];
        }

        foreach ($coChairmanIds as $coId) {
            $assignments[$coId] = [
                'assignment_role' => 'co-handler',
                'assigned_by' => Auth::id(),
                'assigned_at' => now(),
            ];
        }

        foreach ($memberIds as $mId) {
            $assignments[$mId] = [
                'assignment_role' => 'member',
                'assigned_by' => Auth::id(),
                'assigned_at' => now(),
            ];
        }

        foreach ($accreditorIds as $accId) {
            $assignments[$accId] = [
                'assignment_role' => 'accreditor',
                'assigned_by' => Auth::id(),
                'assigned_at' => now(),
            ];
        }

        $area->users()->sync($assignments);

        AuditLogService::log('assign_area', $area, "Updated personnel assignments for Area {$area->code}");

        return redirect()->back()->with('success', "Personnel assignments for Area {$area->code} updated successfully.");
    }

    public function destroy(Area $area, User $user, string $role)
    {
        $area->users()->wherePivot('assignment_role', $role)->detach($user->id);
        AuditLogService::log('unassign_area', $area, "Removed {$user->name} ({$role}) from Area {$area->code}");

        return redirect()->back()->with('success', 'Area assignment removed.');
    }
}
