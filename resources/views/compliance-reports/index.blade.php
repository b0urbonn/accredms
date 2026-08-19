@extends('layouts.app')

@section('title', 'Compliance Reports')

@section('content')
<div class="page-heading">
    <div>
        <h3 class="fw-bold mb-1">Compliance Reports</h3>
        <p class="mb-0">Document recommendations, completed actions, and supporting evidence by accreditation area.</p>
    </div>
    @can('create', App\Models\ComplianceReport::class)
        <a href="{{ route('compliance-reports.create') }}" class="btn btn-apple-green"><i class="bi bi-plus-lg me-1"></i> New Report</a>
    @endcan
</div>

<div class="card card-custom mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="area_id" class="form-label fs-7 fw-semibold">Area</label>
                <select name="area_id" id="area_id" class="form-select form-select-sm">
                    <option value="">All assigned areas</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}" @selected(request('area_id') == $area->id)>{{ $area->code }} - {{ $area->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="survey_visit" class="form-label fs-7 fw-semibold">Survey Visit</label>
                <select name="survey_visit" id="survey_visit" class="form-select form-select-sm">
                    <option value="">All visits</option>
                    @foreach($surveyVisits as $surveyVisit)
                        <option value="{{ $surveyVisit }}" @selected(request('survey_visit') === $surveyVisit)>{{ $surveyVisit }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label fs-7 fw-semibold">Status</label>
                <select name="status" id="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    <option value="uploaded" @selected(request('status') === 'uploaded')>Uploaded</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="search" class="form-label fs-7 fw-semibold">Search</label>
                <input type="search" name="search" id="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Area, program, or visit">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-sm btn-apple-green" type="submit"><i class="bi bi-funnel me-1"></i> Filter</button>
                <a href="{{ route('compliance-reports.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light fs-7 text-uppercase text-secondary">
                <tr>
                    <th class="ps-3">Area</th>
                    <th>Program / Course</th>
                    <th>Survey Visit</th>
                    <th>Recommendations</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    <tr>
                        <td class="ps-3"><span class="badge bg-apple-dark">{{ $report->area->code }}</span><div class="fs-7 mt-1">{{ $report->area->name }}</div></td>
                        <td>{{ $report->program ?: '—' }}</td>
                        <td>{{ $report->survey_visit ?: '—' }}</td>
                        <td><span class="badge bg-secondary">{{ $report->recommendations_count }}</span></td>
                        <td><span class="badge bg-success text-capitalize">{{ $report->status }}</span></td>
                        <td class="fs-7">{{ $report->updated_at->format('M d, Y') }}<br><span class="text-muted">{{ $report->creator->name ?? 'System' }}</span></td>
                        <td class="text-end pe-3 text-nowrap">
                            <a href="{{ route('compliance-reports.show', $report) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-eye"></i> View</a>
                            @can('update', $report)
                                <a href="{{ route('compliance-reports.edit', $report) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-5">No compliance reports match the current filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reports->hasPages())<div class="card-footer bg-white">{{ $reports->links() }}</div>@endif
</div>
@endsection