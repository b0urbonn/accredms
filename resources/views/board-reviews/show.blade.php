@extends('layouts.app')

@section('title', $review->review_title)

@section('content')
<div class="page-heading">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge {{ $review->decision_badge }} text-uppercase fs-8">{{ $review->formatted_decision }}</span>
            @if($review->resolution_number)
                <span class="badge bg-dark font-monospace fs-8">{{ $review->resolution_number }}</span>
            @endif
        </div>
        <h3 class="fw-bold mb-1">{{ $review->review_title }}</h3>
        <p class="mb-0 text-muted fs-7">
            <i class="bi bi-folder2 me-1"></i>{{ $review->area ? $review->area->code . ' — ' . $review->area->name : 'Program-wide Board Review' }}
            @if($review->survey_visit)
                &bull; {{ $review->survey_visit }}
            @endif
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('board-reviews.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
        @can('update', $review)
            <a href="{{ route('board-reviews.edit', $review) }}" class="btn btn-outline-dark"><i class="bi bi-pencil me-1"></i> Edit</a>
        @endcan
        <button onclick="window.print()" class="btn btn-apple-green"><i class="bi bi-printer me-1"></i> Print Resolution</button>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        @if($review->board_remarks)
            <div class="card card-custom mb-4">
                <div class="card-header bg-light fw-bold fs-7 py-3">
                    <i class="bi bi-bank text-primary me-2"></i>Board Deliberations & Remarks
                </div>
                <div class="card-body">
                    <p class="mb-0 style-whitespace">{!! nl2br(e($review->board_remarks)) !!}</p>
                </div>
            </div>
        @endif

        @if($review->conditions_set)
            <div class="card card-custom mb-4 border-start border-4 border-warning">
                <div class="card-header bg-warning bg-opacity-10 fw-bold fs-7 text-warning py-3">
                    <i class="bi bi-shield-exclamation me-2"></i> Special Directives & Conditions
                </div>
                <div class="card-body fs-8">
                    {!! nl2br(e($review->conditions_set)) !!}
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card card-custom mb-4">
            <div class="card-header bg-light fw-bold fs-7 py-3">
                <i class="bi bi-info-circle text-primary me-2"></i>Board Action Record
            </div>
            <ul class="list-group list-group-flush fs-8">
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Target Area:</span>
                    <strong class="text-dark">{{ $review->area ? $review->area->code : 'Program-wide' }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Board Action:</span>
                    <span class="badge {{ $review->decision_badge }} text-uppercase">{{ $review->formatted_decision }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Validity Period:</span>
                    <strong class="text-success"><i class="bi bi-calendar-check me-1"></i>{{ $review->validity_period ?? 'N/A' }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Board Review Date:</span>
                    <strong>{{ $review->reviewed_date ? $review->reviewed_date->format('M d, Y') : 'N/A' }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Created By:</span>
                    <strong>{{ $review->creator->name ?? 'Board Secretary' }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Record Status:</span>
                    <strong class="text-capitalize">{{ $review->status }}</strong>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
