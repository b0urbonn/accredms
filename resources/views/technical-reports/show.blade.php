@extends('layouts.app')

@section('title', $report->title)

@section('content')
<div class="page-heading">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge {{ $report->status_badge }} text-uppercase fs-8">{{ str_replace('_', ' ', $report->status) }}</span>
            @if($report->report_number)
                <span class="badge bg-secondary font-monospace fs-8">{{ $report->report_number }}</span>
            @endif
        </div>
        <h3 class="fw-bold mb-1">{{ $report->title }}</h3>
        <p class="mb-0 text-muted fs-7">
            <i class="bi bi-folder2 me-1"></i>{{ $report->area ? $report->area->code . ' — ' . $report->area->name : 'Program-wide Technical Report' }}
            @if($report->survey_visit)
                &bull; {{ $report->survey_visit }}
            @endif
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('technical-reports.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
        @can('update', $report)
            <a href="{{ route('technical-reports.edit', $report) }}" class="btn btn-outline-dark"><i class="bi bi-pencil me-1"></i> Edit</a>
        @endcan
        <button onclick="window.print()" class="btn btn-apple-green"><i class="bi bi-printer me-1"></i> Print Report</button>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        @if($report->summary_findings)
            <div class="card card-custom mb-4">
                <div class="card-header bg-light fw-bold fs-7 py-3">
                    <i class="bi bi-card-text text-primary me-2"></i>Executive Summary Findings
                </div>
                <div class="card-body">
                    <p class="mb-0 style-whitespace">{!! nl2br(e($report->summary_findings)) !!}</p>
                </div>
            </div>
        @endif

        @if($report->technical_evaluation)
            <div class="card card-custom mb-4">
                <div class="card-header bg-light fw-bold fs-7 py-3">
                    <i class="bi bi-cpu text-info me-2"></i>Detailed Technical Evaluation
                </div>
                <div class="card-body">
                    <p class="mb-0 style-whitespace">{!! nl2br(e($report->technical_evaluation)) !!}</p>
                </div>
            </div>
        @endif

        <div class="row g-3 mb-4">
            @if($report->strengths)
                <div class="col-md-6">
                    <div class="card card-custom h-100 border-start border-4 border-success">
                        <div class="card-header bg-success bg-opacity-10 fw-bold fs-7 text-success py-2">
                            <i class="bi bi-check-circle me-1"></i> Commendable Strengths
                        </div>
                        <div class="card-body fs-8">
                            {!! nl2br(e($report->strengths)) !!}
                        </div>
                    </div>
                </div>
            @endif

            @if($report->areas_for_improvement)
                <div class="col-md-6">
                    <div class="card card-custom h-100 border-start border-4 border-warning">
                        <div class="card-header bg-warning bg-opacity-10 fw-bold fs-7 text-warning py-2">
                            <i class="bi bi-exclamation-triangle me-1"></i> Areas for Improvement
                        </div>
                        <div class="card-body fs-8">
                            {!! nl2br(e($report->areas_for_improvement)) !!}
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if($report->recommendations)
            <div class="card card-custom mb-4 border-start border-4 border-primary">
                <div class="card-header bg-primary bg-opacity-10 fw-bold fs-7 text-primary py-3">
                    <i class="bi bi-lightbulb me-2"></i> Evaluator Recommendations
                </div>
                <div class="card-body fs-8">
                    {!! nl2br(e($report->recommendations)) !!}
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card card-custom mb-4">
            <div class="card-header bg-light fw-bold fs-7 py-3">
                <i class="bi bi-info-circle text-success me-2"></i>Report Details
            </div>
            <ul class="list-group list-group-flush fs-8">
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Target Area:</span>
                    <strong class="text-dark">{{ $report->area ? $report->area->code : 'Program-wide' }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Program:</span>
                    <strong>{{ $report->program ?? 'N/A' }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Survey Visit:</span>
                    <strong>{{ $report->survey_visit ?? 'N/A' }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Overall Technical Score:</span>
                    <strong class="font-monospace text-success fs-7">{{ $report->overall_score ? number_format($report->overall_score, 2) . ' / 5.0' : 'Not Rated' }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Prepared By:</span>
                    <strong>{{ $report->creator->name ?? 'System' }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Last Updated:</span>
                    <strong>{{ $report->updated_at->format('M d, Y H:i') }}</strong>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
