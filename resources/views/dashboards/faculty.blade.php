@extends('layouts.app')

@section('title', 'Faculty Dashboard')

@section('content')
<div class="page-heading">
    <div>
        <h3 class="fw-bold mb-1">Welcome, {{ auth()->user()->name }}</h3>
        <p class="mb-0">Prioritize evidence gaps, returned requests, and deadlines across your assigned areas.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom metric-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="metric-label">Uploaded PDFs</span>
                    <div class="metric-value mt-1">{{ $totalDocuments }}</div>
                </div>
                <div class="metric-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom metric-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="metric-label">Total Storage</span>
                    <div class="metric-value mt-1">
                        @if($totalSizeBytes >= 1073741824)
                            {{ number_format($totalSizeBytes / 1073741824, 2) }} GB
                        @elseif($totalSizeBytes >= 1048576)
                            {{ number_format($totalSizeBytes / 1048576, 2) }} MB
                        @else
                            {{ number_format($totalSizeBytes / 1024, 2) }} KB
                        @endif
                    </div>
                </div>
                <div class="metric-icon">
                    <i class="bi bi-device-ssd"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom metric-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="metric-label">Missing Evidence</span>
                    <div class="metric-value mt-1">{{ $missingEvidenceCount }}</div>
                </div>
                <div class="metric-icon text-warning"><i class="bi bi-file-earmark-x"></i></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom metric-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="metric-label">Returned for Revision</span>
                    <div class="metric-value mt-1">{{ $returnedForRevisionCount }}</div>
                </div>
                <div class="metric-icon text-danger"><i class="bi bi-arrow-return-left"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Area Task Board -->
<div class="card card-custom mb-4">
    <div class="card-header bg-white py-3 border-bottom-0 d-flex align-items-center justify-content-between">
        <div>
            <h5 class="section-title mb-1"><i class="bi bi-list-check me-2 text-accent"></i>Area Task Board</h5>
            <p class="text-muted fs-8 mb-0">Evidence progress and actions for your assigned areas.</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light fs-7 text-uppercase text-secondary">
                <tr><th class="ps-3">Area</th><th>Progress</th><th>Missing Evidence</th><th>Returned for Revision</th><th>Nearest Deadline</th><th class="text-end pe-3">Action</th></tr>
            </thead>
            <tbody>
                @forelse($facultyAreaTasks as $task)
                    <tr>
                        <td class="ps-3"><span class="badge bg-apple-dark me-1">{{ $task->area->code }}</span><span class="fw-semibold">{{ $task->area->name }}</span></td>
                        <td style="min-width: 150px;">
                            <div class="d-flex justify-content-between fs-8 mb-1"><span>{{ $task->completedStatements }}/{{ $task->totalStatements }} statements</span><strong>{{ $task->progressPercent }}%</strong></div>
                            <div class="progress" style="height: 7px;"><div class="progress-bar bg-success" style="width: {{ $task->progressPercent }}%"></div></div>
                        </td>
                        <td><span class="badge {{ $task->missingEvidenceCount ? 'text-bg-warning' : 'text-bg-success' }}">{{ $task->missingEvidenceCount }} {{ Str::plural('item', $task->missingEvidenceCount) }}</span></td>
                        <td><span class="badge {{ $task->returnedForRevisionCount ? 'text-bg-danger' : 'text-bg-secondary' }}">{{ $task->returnedForRevisionCount }} {{ Str::plural('request', $task->returnedForRevisionCount) }}</span></td>
                        <td>
                            @if($task->nextDeadline)
                                <span class="{{ $task->hasOverdueDeadline ? 'text-danger fw-semibold' : 'text-dark' }}"><i class="bi bi-calendar-event me-1"></i>{{ $task->nextDeadline->format('M d, Y') }}</span>
                                @if($task->hasOverdueDeadline)<small class="d-block text-danger">Overdue</small>@endif
                            @else
                                <span class="text-muted fs-7">No deadline</span>
                            @endif
                        </td>
                        <td class="text-end pe-3"><a href="{{ route('accreditor.show_area', $task->area) }}" class="btn btn-sm btn-apple-green"><i class="bi bi-box-arrow-up-right me-1"></i>Open Area</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">You have not been assigned to any Accreditation Areas yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Open Additional Document Requests -->
<div class="card card-custom mb-4">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h5 class="section-title mb-0"><i class="bi bi-exclamation-circle me-2 text-warning"></i>Requests Requiring Your Compliance</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light fs-7 text-uppercase text-secondary">
                <tr><th class="ps-3">Statement</th><th>Request Details</th><th>Requester</th><th>Due Date</th><th>Status</th><th class="text-end pe-3">Action</th></tr>
            </thead>
            <tbody>
                @forelse($openDocumentRequests as $documentRequest)
                    <tr>
                        <td class="ps-3"><span class="fw-semibold d-block">{{ $documentRequest->subfolder->code }} - {{ $documentRequest->subfolder->name }}</span><small class="text-muted">{{ $documentRequest->subfolder->parameterCategory->parameter->area->code }}</small></td>
                        <td><span class="d-block">{{ $documentRequest->requested_documents ?: 'See instructions below.' }}</span><small class="text-muted">{{ $documentRequest->remarks }}</small></td>
                        <td>{{ $documentRequest->requester->name }}</td>
                        <td>{{ $documentRequest->due_date?->format('M d, Y') ?? 'No due date' }}</td>
                        <td><span class="badge text-bg-warning"><i class="bi bi-exclamation-circle me-1"></i>Needs your compliance</span></td>
                        <td class="text-end pe-3"><a href="{{ route('accreditor.show_area', $documentRequest->subfolder->parameterCategory->parameter->area) }}?request_id={{ $documentRequest->id }}#subfolder-{{ $documentRequest->subfolder_id }}" class="btn btn-sm btn-apple-green"><i class="bi bi-upload me-1"></i> View Request / Upload</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No additional document requests require your compliance.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Documents Table -->
<div class="card card-custom">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h5 class="section-title mb-0"><i class="bi bi-file-earmark-text me-2 text-accent"></i>Recent Documents</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light fs-7 text-uppercase text-secondary">
                <tr>
                    <th class="ps-3">Document Title</th>
                    <th>Area / Parameter</th>
                    <th>Subfolder</th>
                    <th>File Size</th>
                    <th>Compression</th>
                    <th class="text-end pe-3">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentDocuments as $doc)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-pdf fs-4 text-danger"></i>
                                <div>
                                    <span class="fw-bold text-dark d-block">{{ $doc->original_filename }}</span>
                                    <small class="text-muted fs-8">Uploaded {{ $doc->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $doc->subfolder->parameterCategory->parameter->area->code }}</span>
                            <span class="fs-7 text-muted">Param {{ $doc->subfolder->parameterCategory->parameter->code }}</span>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $doc->subfolder->name }}</span></td>
                        <td class="fw-semibold fs-7">{{ $doc->formatted_size }}</td>
                        <td>
                            @if($doc->is_compressed)
                                <span class="badge bg-success"><i class="bi bi-file-zip me-1"></i> Compressed</span>
                            @elseif($doc->compression_status === 'processing' || $doc->compression_status === 'pending')
                                <span class="badge bg-warning text-dark"><i class="bi bg-spin me-1"></i> Queued</span>
                            @else
                                <span class="badge bg-light text-secondary border">Original</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <button type="button" class="btn btn-sm btn-outline-success"
                                onclick="openPdfModal(
                                    '{{ route('documents.stream', $doc) }}',
                                    '{{ route('documents.download', $doc) }}',
                                    '{{ addslashes($doc->original_filename) }}',
                                    'Size: {{ $doc->formatted_size }} | Uploaded: {{ $doc->created_at->format('M d, Y H:i') }}',
                                    true
                                )">
                                <i class="bi bi-eye me-1"></i> Preview
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No documents uploaded in your assigned areas yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
