@extends('layouts.app')

@section('title', 'Board Reviews')

@section('content')
<div class="page-heading">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-bank text-primary me-2"></i>Board Review & Action</h3>
        <p class="mb-0">Formal Accreditation Board decisions, resolution records, status validity, and review meetings.</p>
    </div>
    @can('create', App\Models\BoardReview::class)
        <a href="{{ route('board-reviews.create') }}" class="btn btn-apple-green"><i class="bi bi-plus-lg me-1"></i> New Board Review</a>
    @endcan
</div>

<div class="card card-custom mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="area_id" class="form-label fs-7 fw-semibold">Area Target</label>
                <select name="area_id" id="area_id" class="form-select form-select-sm">
                    <option value="">All assigned areas</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}" @selected(request('area_id') == $area->id)>{{ $area->code }} - {{ $area->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="decision" class="form-label fs-7 fw-semibold">Board Decision</label>
                <select name="decision" id="decision" class="form-select form-select-sm">
                    <option value="">All decisions</option>
                    <option value="accredited_level_1" @selected(request('decision') === 'accredited_level_1')>Level 1 Accredited</option>
                    <option value="accredited_level_2" @selected(request('decision') === 'accredited_level_2')>Level 2 Accredited</option>
                    <option value="accredited_level_3" @selected(request('decision') === 'accredited_level_3')>Level 3 Accredited</option>
                    <option value="accredited_level_4" @selected(request('decision') === 'accredited_level_4')>Level 4 Accredited</option>
                    <option value="re_accredited" @selected(request('decision') === 're_accredited')>Re-Accredited</option>
                    <option value="under_board_review" @selected(request('decision') === 'under_board_review')>Under Board Review</option>
                    <option value="deferred" @selected(request('decision') === 'deferred')>Deferred</option>
                    <option value="not_accredited" @selected(request('decision') === 'not_accredited')>Not Accredited</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="search" class="form-label fs-7 fw-semibold">Search Keywords</label>
                <input type="search" name="search" id="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Resolution #, title, or program...">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-sm btn-apple-green flex-grow-1" type="submit"><i class="bi bi-funnel me-1"></i> Filter</button>
                <a href="{{ route('board-reviews.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    @forelse($reviews as $review)
        <div class="col-md-6 col-lg-4">
            <div class="card card-custom h-100 border-top border-4 border-primary">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge {{ $review->decision_badge }} text-uppercase fs-8">{{ $review->formatted_decision }}</span>
                        @if($review->resolution_number)
                            <span class="badge bg-dark text-light font-monospace fs-8">{{ $review->resolution_number }}</span>
                        @endif
                    </div>

                    <h5 class="fw-bold mb-1">{{ $review->review_title }}</h5>
                    <p class="text-muted fs-8 mb-2">
                        <i class="bi bi-folder2 me-1"></i>{{ $review->area ? $review->area->code . ' — ' . $review->area->name : 'Program-wide Board Review' }}
                    </p>

                    @if($review->validity_period)
                        <div class="mb-2 fs-8">
                            <span class="text-muted me-1">Validity Period:</span>
                            <span class="fw-bold text-success"><i class="bi bi-calendar-check me-1"></i>{{ $review->validity_period }}</span>
                        </div>
                    @endif

                    @if($review->board_remarks)
                        <p class="fs-8 text-secondary text-truncate-2 mb-3 flex-grow-1">
                            {{ Str::limit($review->board_remarks, 120) }}
                        </p>
                    @endif

                    <div class="pt-2 border-top d-flex align-items-center justify-content-between mt-auto fs-8">
                        <small class="text-muted"><i class="bi bi-person me-1"></i>{{ $review->creator->name ?? 'Board Secretary' }}</small>
                        <div class="d-flex gap-1">
                            <a href="{{ route('board-reviews.show', $review) }}" class="btn btn-xs btn-outline-primary"><i class="bi bi-eye"></i> View</a>
                            @can('update', $review)
                                <a href="{{ route('board-reviews.edit', $review) }}" class="btn btn-xs btn-outline-dark"><i class="bi bi-pencil"></i> Edit</a>
                            @endcan
                            @can('delete', $review)
                                <form action="{{ route('board-reviews.destroy', $review) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this Board Review record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete Record"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card card-custom py-5 text-center">
                <i class="bi bi-journal-x display-4 text-muted mb-2"></i>
                <h5 class="fw-bold">No Board Review Records Found</h5>
                <p class="text-muted fs-8 mb-3">No board resolutions match your current filter criteria.</p>
                @can('create', App\Models\BoardReview::class)
                    <div>
                        <a href="{{ route('board-reviews.create') }}" class="btn btn-sm btn-apple-green"><i class="bi bi-plus-lg me-1"></i> Create First Resolution</a>
                    </div>
                @endcan
            </div>
        </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $reviews->links() }}
</div>
@endsection
