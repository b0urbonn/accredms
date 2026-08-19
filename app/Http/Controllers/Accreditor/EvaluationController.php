<?php

namespace App\Http\Controllers\Accreditor;

use App\Http\Controllers\Controller;
use App\Models\AccreditorEvaluation;
use App\Models\Area;
use App\Models\Subfolder;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    public function show(Area $area)
    {
        $user = request()->user();
        $this->authorizeAreaAccess($user, $area);

        $area->load([
            'parameters.parameterCategories.category',
            'parameters.parameterCategories.allSubfolders.documents',
            'parameters.parameterCategories.allSubfolders.evaluations.user',
            'users',
        ]);

        $rows = collect();
        foreach ($area->parameters as $parameter) {
            foreach ($parameter->parameterCategories as $parameterCategory) {
                $statements = $parameterCategory->allSubfolders;
                $childrenByParent = $statements->groupBy('parent_id');
                $appendRows = function ($parentId, int $depth) use (&$appendRows, $childrenByParent, $parameter, $parameterCategory, $rows): void {
                    $children = $childrenByParent->get($parentId, collect())->sort(function ($a, $b) {
                        return strnatcasecmp($a->code ?? '', $b->code ?? '');
                    });
                    foreach ($children as $statement) {
                        $rows->push([
                            'parameter' => $parameter,
                            'category' => $parameterCategory,
                            'statement' => $statement,
                            'depth' => $depth,
                        ]);
                        $appendRows($statement->id, $depth + 1);
                    }
                };
                $appendRows(null, 0);
            }
        }

        $ratings = $rows->flatMap(fn (array $row) => $row['statement']->evaluations)
            ->pluck('rating')
            ->map(fn ($rating) => (float) $rating);
        $areaMean = $ratings->isNotEmpty() ? round($ratings->avg(), 2) : null;
        $categoryMeans = [];
        $parameterMeans = [];

        foreach ($area->parameters as $parameter) {
            $parameterRatings = collect();
            foreach ($parameter->parameterCategories as $parameterCategory) {
                $categoryRatings = $parameterCategory->allSubfolders
                    ->flatMap(fn (Subfolder $statement) => $statement->evaluations)
                    ->pluck('rating')
                    ->map(fn ($rating) => (float) $rating);
                $categoryMeans[$parameterCategory->id] = $categoryRatings->isNotEmpty()
                    ? round($categoryRatings->avg(), 2)
                    : null;
                $parameterRatings = $parameterRatings->concat($categoryRatings);
            }
            $parameterMeans[$parameter->id] = $parameterRatings->isNotEmpty()
                ? round($parameterRatings->avg(), 2)
                : null;
        }
        $ratedParameterMeans = array_filter($parameterMeans, fn ($mean) => $mean !== null);
        $parameterRatingTotal = array_sum($ratedParameterMeans);
        $ratedParameterCount = count($ratedParameterMeans);
        $canRate = $user->isAccreditor() && $user->areas()->where('areas.id', $area->id)->exists();

        return view('accreditor.evaluation_report', compact(
            'area',
            'rows',
            'areaMean',
            'categoryMeans',
            'parameterMeans',
            'parameterRatingTotal',
            'ratedParameterCount',
            'canRate',
        ));
    }

    public function store(Request $request, Subfolder $subfolder)
    {
        $user = $request->user();
        $area = $subfolder->parameterCategory->parameter->area;

        if (!$user->isAccreditor() || !$user->areas()->where('areas.id', $area->id)->exists()) {
            abort(403, 'Only an assigned Accreditor can rate this indicator.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'numeric', 'min:0', 'max:5'],
            'compliance_result' => ['required', 'in:complied,partially_complied,not_complied'],
            'evaluation' => ['required', 'string', 'max:5000'],
        ]);

        AccreditorEvaluation::updateOrCreate(
            ['subfolder_id' => $subfolder->id, 'user_id' => $user->id],
            $validated,
        );

        $subfolder->update(['review_status' => 'evaluated']);
        $subfolder->additionalDocumentRequests()
            ->where('status', 'resubmitted')
            ->update(['status' => 'fulfilled', 'fulfilled_at' => now()]);

        AuditLogService::log('evaluate_indicator', $subfolder, "Accreditor {$user->name} evaluated indicator {$subfolder->code} in Area {$area->code}");

        return redirect()->back()->with('success', "Evaluation for indicator {$subfolder->code} saved.");
    }

    private function authorizeAreaAccess($user, Area $area): void
    {
        if (!$user->isAdmin() && !$user->areas()->where('areas.id', $area->id)->exists()) {
            abort(403, 'Unauthorized access to this evaluation report.');
        }
    }
}