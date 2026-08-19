@extends('layouts.app')

@section('title', 'Accreditor Portal')

@section('content')
<div class="page-heading">
    <div>
        <h3 class="fw-bold mb-1">Accreditor Review Portal</h3>
        <p class="mb-0">Review supporting evidence for your assigned accreditation areas.</p>
    </div>
    <span class="badge badge-role badge-role-accreditor py-2 px-3 fs-7">
        <i class="bi bi-shield-lock me-1"></i> View-Only Review Mode
    </span>
</div>

<div class="quiet-notice p-3 mb-4 d-flex align-items-center gap-3">
    <i class="bi bi-shield-check fs-3"></i>
    <div>
        <h6 class="fw-bold mb-1">Welcome, {{ auth()->user()->name }}</h6>
        <p class="mb-0 fs-7">
            You are logged in with Accreditor privileges. Review the assigned Areas below, coordinate with their Chairman and Members, preview evidence PDFs securely, and submit official findings for each document.
        </p>
    </div>
</div>

<h5 class="section-title mb-3"><i class="bi bi-folder2-open me-2 text-accent"></i>Assigned Areas for Audit</h5>
<div class="row g-3 mb-4">
    @forelse($assignedAreas as $area)
        <div class="col-md-6 col-lg-4">
            <div class="card card-custom area-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-apple-dark fs-7">{{ $area->code }}</span>
                    <span class="badge badge-role badge-role-accreditor">View only</span>
                </div>
                <h6 class="area-title mb-2">{{ $area->name }}</h6>
                <p class="area-description mb-3 text-truncate-2">{{ $area->description ?? 'No description.' }}</p>
                <div class="area-personnel fs-8 mb-3">
                    <div class="d-flex align-items-start gap-2 mb-1">
                        <i class="bi bi-person-badge text-success"></i>
                        <span><strong class="text-dark">Chairman:</strong> {{ $area->handlers->pluck('name')->join(', ') ?: 'Not assigned' }}</span>
                    </div>
                    <div class="d-flex align-items-start gap-2 mb-1">
                        <i class="bi bi-person-badge text-primary"></i>
                        <span><strong class="text-dark">Co-Chairman:</strong> {{ $area->coHandlers->pluck('name')->join(', ') ?: 'Not assigned' }}</span>
                    </div>
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-people text-secondary"></i>
                        <span><strong class="text-dark">Members:</strong> {{ $area->members->pluck('name')->join(', ') ?: 'No members assigned' }}</span>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-auto pt-2 border-top">
                    <span class="fs-7 text-secondary"><i class="bi bi-list-nested me-1"></i> {{ $area->parameters_count }} Parameters</span>
                    <a href="{{ route('accreditor.show_area', $area) }}" class="btn btn-sm btn-apple-green">
                        Explore Area <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-warning py-3 px-4 shadow-sm border-0">
                <i class="bi bi-exclamation-triangle me-2"></i> No Areas have been assigned to your Accreditor account yet. Please contact the Admin.
            </div>
        </div>
    @endforelse
</div>

<!-- Requests Created by Accreditor -->
<div class="card card-custom mb-4">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h5 class="section-title mb-1"><i class="bi bi-file-earmark-plus me-2 text-accent"></i>My Additional Document Requests</h5>
        <p class="text-muted fs-8 mb-0">All requests you sent to Faculty or Admin, including their current compliance status.</p>
    </div>
    @if($requestAreaTabs->isNotEmpty())
        <div class="px-3 pb-3 border-bottom bg-white">
            <div class="d-flex align-items-center gap-2 overflow-auto request-area-tabs" role="tablist" aria-label="Filter requests by Area">
                <button type="button" class="btn btn-sm btn-apple-green request-area-tab active" data-area-id="all">All <span class="badge bg-white text-success ms-1">{{ $requestedDocuments->count() }}</span></button>
                @foreach($requestAreaTabs as $areaTab)
                    <button type="button" class="btn btn-sm btn-outline-success request-area-tab text-nowrap" data-area-id="{{ $areaTab->id }}" title="{{ $areaTab->name }}">{{ $areaTab->code }} <span class="badge bg-secondary ms-1">{{ $areaTab->count }}</span></button>
                @endforeach
            </div>
        </div>
    @endif
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light fs-7 text-uppercase text-secondary"><tr><th class="ps-3">Area / Statement</th><th>Missing Documents</th><th>Instructions</th><th>Due Date</th><th>Status</th><th class="text-end pe-3">Action</th></tr></thead>
            <tbody>
                @forelse($requestedDocuments as $documentRequest)
                    <tr class="document-request-row" data-area-id="{{ $documentRequest->subfolder->parameterCategory->parameter->area_id }}">
                        <td class="ps-3"><span class="badge bg-secondary">{{ $documentRequest->subfolder->parameterCategory->parameter->area->code }}</span> {{ $documentRequest->subfolder->code }} - {{ $documentRequest->subfolder->name }}</td>
                        <td>{{ $documentRequest->requested_documents ?: 'See instructions.' }}</td>
                        <td>{{ $documentRequest->remarks }}</td>
                        <td>{{ $documentRequest->due_date?->format('M d, Y') ?? 'No due date' }}</td>
                        <td><span class="badge {{ match($documentRequest->status) { 'open' => 'text-bg-warning', 'resubmitted' => 'text-bg-primary', 'fulfilled' => 'text-bg-success', 'cancelled' => 'text-bg-secondary', default => 'text-bg-secondary' } }} text-capitalize">{{ $documentRequest->status === 'resubmitted' ? 'Complied / Uploaded' : $documentRequest->status }}</span></td>
                        <td class="text-end pe-3"><a href="{{ route('accreditor.show_area', $documentRequest->subfolder->parameterCategory->parameter->area) }}#subfolder-{{ $documentRequest->subfolder_id }}" class="btn btn-sm btn-outline-success">View Statement</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">You have not requested additional documents yet.</td></tr>
                @endforelse
                <tr id="filteredRequestsEmptyRow" class="d-none"><td colspan="6" class="text-center py-4 text-muted">No document requests for this Area.</td></tr>
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.querySelectorAll('.request-area-tab').forEach((tab) => {
    tab.addEventListener('click', () => {
        const areaId = tab.dataset.areaId;
        let visibleCount = 0;

        document.querySelectorAll('.request-area-tab').forEach((item) => {
            item.classList.toggle('active', item === tab);
            item.classList.toggle('btn-apple-green', item === tab);
            item.classList.toggle('btn-outline-success', item !== tab);
        });
        document.querySelectorAll('.document-request-row').forEach((row) => {
            const visible = areaId === 'all' || row.dataset.areaId === areaId;
            row.classList.toggle('d-none', !visible);
            if (visible) visibleCount++;
        });
        document.getElementById('filteredRequestsEmptyRow')?.classList.toggle('d-none', visibleCount > 0);
    });
});
</script>
<style>
    .request-area-tabs { scrollbar-width: thin; }
    .request-area-tab { flex: 0 0 auto; }
    .request-area-tab .badge { font-size: 0.66rem; }
</style>
@endsection
