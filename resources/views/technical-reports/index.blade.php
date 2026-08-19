@extends('layouts.app')

@section('title', 'Technical Reports')

@section('content')
<div class="page-heading">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-file-earmark-text text-success me-2"></i>Technical Reports</h3>
        <p class="mb-0">Technical evaluation findings, score summaries, and official accreditation recommendations.</p>
    </div>
    @can('create', App\Models\TechnicalReport::class)
        <a href="{{ route('technical-reports.create') }}" class="btn btn-apple-green"><i class="bi bi-plus-lg me-1"></i> New Technical Report</a>
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
                <label for="status" class="form-label fs-7 fw-semibold">Report Status</label>
                <select name="status" id="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="under_review" @selected(request('status') === 'under_review')>Under Review</option>
                    <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                    <option value="published" @selected(request('status') === 'published')>Published</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="search" class="form-label fs-7 fw-semibold">Search Keywords</label>
                <input type="search" name="search" id="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Title, report number, or program...">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-sm btn-apple-green flex-grow-1" type="submit"><i class="bi bi-funnel me-1"></i> Filter</button>
                <a href="{{ route('technical-reports.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    @forelse($reports as $report)
        <div class="col-md-6 col-lg-4">
            <div class="card card-custom h-100 border-top border-4 border-success">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge {{ $report->status_badge }} text-uppercase fs-8">{{ str_replace('_', ' ', $report->status) }}</span>
                        @if($report->overall_score)
                            <span class="badge bg-dark text-warning font-monospace fs-8"><i class="bi bi-star-fill text-warning me-1"></i>Score: {{ number_format($report->overall_score, 2) }}</span>
                        @endif
                    </div>

                    <h5 class="fw-bold mb-1">{{ $report->title }}</h5>
                    <p class="text-muted fs-8 mb-2">
                        <i class="bi bi-folder2 me-1"></i>{{ $report->area ? $report->area->code . ' — ' . $report->area->name : 'Program-wide Technical Report' }}
                    </p>

                    @if($report->summary_findings)
                        <p class="fs-8 text-secondary text-truncate-2 mb-3 flex-grow-1">
                            {{ Str::limit($report->summary_findings, 120) }}
                        </p>
                    @endif

                    <div class="pt-2 border-top d-flex align-items-center justify-content-between mt-auto fs-8">
                        <small class="text-muted"><i class="bi bi-person me-1"></i>{{ $report->creator->name ?? 'System' }}</small>
                        <div class="d-flex gap-1">
                            <a href="{{ route('technical-reports.show', $report) }}" class="btn btn-xs btn-outline-primary"><i class="bi bi-eye"></i> View</a>
                            @can('update', $report)
                                <a href="{{ route('technical-reports.edit', $report) }}" class="btn btn-xs btn-outline-dark"><i class="bi bi-pencil"></i> Edit</a>
                            @endcan
                            @can('delete', $report)
                                <form action="{{ route('technical-reports.destroy', $report) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this Technical Report?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete Report"><i class="bi bi-trash"></i></button>
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
                <i class="bi bi-file-earmark-x display-4 text-muted mb-2"></i>
                <h5 class="fw-bold">No Technical Reports Found</h5>
                <p class="text-muted fs-8 mb-3">No technical evaluation reports match your current filter selection.</p>
                @can('create', App\Models\TechnicalReport::class)
                    <div>
                        <a href="{{ route('technical-reports.create') }}" class="btn btn-sm btn-apple-green"><i class="bi bi-plus-lg me-1"></i> Create First Report</a>
                    </div>
                @endcan
            </div>
        </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $reports->links() }}
</div>
@endsection
