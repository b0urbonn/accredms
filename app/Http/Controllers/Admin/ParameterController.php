<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Parameter;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParameterController extends Controller
{
    public function store(Request $request, Area $area)
    {
        $this->authorizeAreaParameterManagement($request, $area);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('parameters', 'code')->where(function ($query) use ($area) {
                    return $query->where('area_id', $area->id)->whereNull('deleted_at');
                }),
            ],
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('parameters', 'title')->where(function ($query) use ($area) {
                    return $query->where('area_id', $area->id)->whereNull('deleted_at');
                }),
            ],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ], [
            'code.unique' => "Parameter code '{$request->input('code')}' already exists in this Area.",
            'title.unique' => "Parameter title '{$request->input('title')}' already exists in this Area.",
        ]);

        $validated['area_id'] = $area->id;
        $validated['status'] = 'active';

        // ParameterObserver will automatically generate the 3 parameter_categories!
        $parameter = Parameter::create($validated);
        AuditLogService::log('create_parameter', $parameter, "Created Parameter {$parameter->code} under Area {$area->code}");

        return redirect()->back()->with('success', 'Parameter created successfully. Categories auto-generated.');
    }

    public function update(Request $request, Parameter $parameter)
    {
        $this->authorizeAreaParameterManagement($request, $parameter->area);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('parameters', 'code')
                    ->ignore($parameter->id)
                    ->where(function ($query) use ($parameter) {
                        return $query->where('area_id', $parameter->area_id)->whereNull('deleted_at');
                    }),
            ],
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('parameters', 'title')
                    ->ignore($parameter->id)
                    ->where(function ($query) use ($parameter) {
                        return $query->where('area_id', $parameter->area_id)->whereNull('deleted_at');
                    }),
            ],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'code.unique' => "Parameter code '{$request->input('code')}' already exists in this Area.",
            'title.unique' => "Parameter title '{$request->input('title')}' already exists in this Area.",
        ]);

        $parameter->update($validated);
        AuditLogService::log('update_parameter', $parameter, "Updated Parameter {$parameter->code}");

        return redirect()->back()->with('success', 'Parameter updated successfully.');
    }

    public function destroy(Parameter $parameter)
    {
        $this->authorizeAreaParameterManagement(request(), $parameter->area);

        if ($parameter->parameterCategories()->whereHas('allSubfolders')->exists()) {
            return redirect()
                ->back()
                ->with('error', "Parameter {$parameter->code} cannot be deleted because it contains statements, sub-items, or uploaded documents.");
        }

        $parameter->delete();
        AuditLogService::log('delete_parameter', $parameter, "Deleted Parameter {$parameter->code}");

        return redirect()->back()->with('success', 'Parameter deleted successfully.');
    }

    private function authorizeAreaParameterManagement(Request $request, Area $area): void
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return;
        }

        if (!$user->isFaculty() || !$user->areas()
            ->where('areas.id', $area->id)
            ->wherePivotIn('assignment_role', ['handler', 'co-handler', 'member'])
            ->exists()) {
            abort(403, 'Unauthorized action for this Area.');
        }
    }
}
