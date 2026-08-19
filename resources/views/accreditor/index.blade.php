@extends('layouts.app')

@section('title', auth()->user()->isFaculty() ? 'My Areas' : 'Browse Accreditation Areas')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold text-apple-dark mb-1">{{ auth()->user()->isFaculty() ? 'My Assigned Areas' : 'Accreditation Areas Repository' }}</h3>
        <p class="text-muted mb-0">{{ auth()->user()->isFaculty() ? 'Manage statements and documentary evidence in your assigned Areas.' : 'Select an Area to explore Parameters, fixed Categories, and documentary evidence.' }}</p>
    </div>
</div>

<div class="row g-4">
    @forelse($areas as $area)
        <div class="col-md-6 col-lg-4">
            <div class="card card-custom h-100 p-4 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="badge bg-apple-dark fs-6">{{ $area->code }}</span>
                    <span class="badge bg-light text-dark border fs-7"><i class="bi bi-list-nested me-1 text-secondary"></i>{{ $area->parameters_count }} Parameters</span>
                </div>

                <h5 class="fw-bold area-title mb-2">{{ $area->name }}</h5>
                <p class="text-secondary fs-7 mb-3 text-truncate-2">{{ $area->description ?? 'Accreditation documentary folder repository.' }}</p>
                <div class="area-personnel fs-8 mb-3">
                    <div class="d-flex align-items-start gap-2 mb-1"><i class="bi bi-person-badge text-success"></i><span><strong class="text-dark">Chairman:</strong> {{ $area->handlers->pluck('name')->join(', ') ?: 'Not assigned' }}</span></div>
                    <div class="d-flex align-items-start gap-2 mb-1"><i class="bi bi-person-badge text-primary"></i><span><strong class="text-dark">Co-Chairman:</strong> {{ $area->coHandlers->pluck('name')->join(', ') ?: 'Not assigned' }}</span></div>
                    <div class="d-flex align-items-start gap-2"><i class="bi bi-people text-secondary"></i><span><strong class="text-dark">Members:</strong> {{ $area->members->pluck('name')->join(', ') ?: 'No members assigned' }}</span></div>
                </div>

                <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-between">
                    <span class="fs-8 text-secondary"><i class="bi bi-folder-check me-1 text-success"></i> Active</span>
                    <a href="{{ route('accreditor.show_area', $area) }}" class="btn btn-sm btn-apple-green">
                        {{ auth()->user()->isFaculty() ? 'Manage Area' : 'Explore Hierarchy' }} <i class="bi bi-chevron-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info p-4 text-center">
                {{ auth()->user()->isFaculty() ? 'No Areas have been assigned to you yet.' : 'No Accreditation Areas accessible.' }}
            </div>
        </div>
    @endforelse
</div>
@endsection
