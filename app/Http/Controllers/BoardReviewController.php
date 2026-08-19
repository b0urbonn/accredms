<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\BoardReview;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class BoardReviewController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', BoardReview::class);
        $user = $request->user();

        $query = BoardReview::with(['area', 'creator', 'updater']);

        if (!$user->isAdmin()) {
            $userAreaIds = $user->areas()->pluck('areas.id')->toArray();
            $query->where(function ($q) use ($userAreaIds) {
                $q->whereNull('area_id')->orWhereIn('area_id', $userAreaIds);
            });
        }

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->integer('area_id'));
        }

        if ($request->filled('decision')) {
            $query->where('board_decision', $request->input('decision'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('review_title', 'like', "%{$search}%")
                  ->orWhere('resolution_number', 'like', "%{$search}%")
                  ->orWhere('program', 'like', "%{$search}%")
                  ->orWhere('board_remarks', 'like', "%{$search}%");
            });
        }

        $reviews = $query->latest()->paginate(10)->withQueryString();
        $areas = $user->isAdmin() ? Area::where('status', '!=', 'inactive')->get() : $user->areas()->get();

        return view('board-reviews.index', compact('reviews', 'areas'));
    }

    public function create()
    {
        $this->authorize('create', BoardReview::class);
        $user = request()->user();
        $areas = $user->isAdmin() ? Area::where('status', '!=', 'inactive')->get() : $user->areas()->get();

        return view('board-reviews.form', [
            'review' => new BoardReview(),
            'areas' => $areas,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', BoardReview::class);

        $validated = $request->validate([
            'area_id' => ['nullable', 'exists:areas,id'],
            'resolution_number' => ['nullable', 'string', 'max:50'],
            'review_title' => ['required', 'string', 'max:255'],
            'program' => ['nullable', 'string', 'max:255'],
            'survey_visit' => ['nullable', 'string', 'max:255'],
            'board_decision' => ['required', 'string'],
            'validity_period' => ['nullable', 'string', 'max:100'],
            'board_remarks' => ['nullable', 'string'],
            'conditions_set' => ['nullable', 'string'],
            'reviewed_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:under_review,resolved,approved,archived'],
        ]);

        $review = BoardReview::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        AuditLogService::log('create_board_review', $review, "Created Board Review resolution '{$review->review_title}'");

        return redirect()->route('board-reviews.show', $review)->with('success', 'Board Review record saved successfully.');
    }

    public function show(BoardReview $boardReview)
    {
        $this->authorize('view', $boardReview);
        $boardReview->load(['area', 'creator', 'updater']);

        return view('board-reviews.show', ['review' => $boardReview]);
    }

    public function edit(BoardReview $boardReview)
    {
        $this->authorize('update', $boardReview);
        $user = request()->user();
        $areas = $user->isAdmin() ? Area::where('status', '!=', 'inactive')->get() : $user->areas()->get();

        return view('board-reviews.form', [
            'review' => $boardReview,
            'areas' => $areas,
        ]);
    }

    public function update(Request $request, BoardReview $boardReview)
    {
        $this->authorize('update', $boardReview);

        $validated = $request->validate([
            'area_id' => ['nullable', 'exists:areas,id'],
            'resolution_number' => ['nullable', 'string', 'max:50'],
            'review_title' => ['required', 'string', 'max:255'],
            'program' => ['nullable', 'string', 'max:255'],
            'survey_visit' => ['nullable', 'string', 'max:255'],
            'board_decision' => ['required', 'string'],
            'validity_period' => ['nullable', 'string', 'max:100'],
            'board_remarks' => ['nullable', 'string'],
            'conditions_set' => ['nullable', 'string'],
            'reviewed_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:under_review,resolved,approved,archived'],
        ]);

        $boardReview->update([
            ...$validated,
            'updated_by' => $request->user()->id,
        ]);

        AuditLogService::log('update_board_review', $boardReview, "Updated Board Review resolution '{$boardReview->review_title}'");

        return redirect()->route('board-reviews.show', $boardReview)->with('success', 'Board Review updated successfully.');
    }

    public function destroy(BoardReview $boardReview)
    {
        $this->authorize('delete', $boardReview);
        $title = $boardReview->review_title;
        $boardReview->delete();

        AuditLogService::log('delete_board_review', $boardReview, "Deleted Board Review resolution '{$title}'");

        return redirect()->route('board-reviews.index')->with('success', 'Board Review record deleted successfully.');
    }
}
