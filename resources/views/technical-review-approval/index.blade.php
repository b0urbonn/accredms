@extends('layouts.app')

@section('title', 'Technical Review & Approval')

@php
    $canDownload = !auth()->user()->isAccreditor() || config('accredms.accreditor_download_allowed', false);
@endphp

@section('content')
<div class="page-heading d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="bi bi-file-earmark-check text-success me-2"></i>Technical Review & Board Approval
        </h3>
        <p class="mb-0 text-muted">Upload and manage official technical evaluation reports and Accreditation Board resolution documents.</p>
    </div>
    @can('create', App\Models\TechnicalReviewApproval::class)
        <button type="button" class="btn btn-apple-green" data-bs-toggle="modal" data-bs-target="#uploadReportModal">
            <i class="bi bi-cloud-arrow-up me-1"></i> Upload Reports
        </button>
    @endcan
</div>

<!-- Filter Card -->
<div class="card card-custom mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="area_id" class="form-label fs-7 fw-semibold">Area Target</label>
                <select name="area_id" id="area_id" class="form-select form-select-sm">
                    <option value="">All assigned areas / General</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}" @selected(request('area_id') == $area->id)>{{ $area->code }} — {{ $area->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="category" class="form-label fs-7 fw-semibold">Category</label>
                <select name="category" id="category" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    <option value="general" @selected(request('category') === 'general')>Technical Review & Board Approval</option>
                    <option value="technical_review" @selected(request('category') === 'technical_review')>Technical Review</option>
                    <option value="board_approval" @selected(request('category') === 'board_approval')>Board Approval</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="search" class="form-label fs-7 fw-semibold">Search Keywords</label>
                <input type="search" name="search" id="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Filename or keyword...">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-sm btn-apple-green flex-grow-1" type="submit"><i class="bi bi-funnel me-1"></i> Filter</button>
                <a href="{{ route('technical-review-approval.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Uploaded Reports Table -->
<div class="card card-custom">
    <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="fw-bold mb-0 fs-6"><i class="bi bi-journal-text me-2 text-success"></i>Report Documents Directory</h5>
        <span class="badge bg-secondary fs-8">{{ $reports->total() }} File(s) Recorded</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-matrix-dark align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th>Document / File Name</th>
                        <th style="width: 180px;">Category</th>
                        <th style="width: 220px;">Target Area</th>
                        <th style="width: 180px;">Uploaded By</th>
                        <th style="width: 110px;">Size</th>
                        <th style="width: 150px;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $index => $report)
                        @php
                            $isPdf = str_contains(strtolower($report->mime_type ?? ''), 'pdf') || str_ends_with(strtolower($report->original_filename), '.pdf');
                            $streamUrl = route('technical-review-approval.stream', $report);
                            $downloadUrl = route('technical-review-approval.download', $report);
                            $title = addslashes($report->original_filename);
                            $meta = addslashes('Category: ' . $report->category_label . ' | Uploaded by ' . ($report->uploader->name ?? 'System') . ' | ' . $report->formatted_size . ' | ' . $report->created_at->format('M d, Y'));
                        @endphp
                        <tr>
                            <td class="text-center text-muted fw-bold">{{ $reports->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($isPdf)
                                        <i class="bi bi-file-earmark-pdf-fill text-danger fs-5"></i>
                                        <div>
                                            <button type="button" class="btn btn-link p-0 text-start text-dark fw-semibold text-truncate d-block" style="max-width: 320px;" title="{{ $report->original_filename }}" onclick="openPdfModal('{{ $streamUrl }}', '{{ $downloadUrl }}', '{{ $title }}', '{{ $meta }}', {{ $canDownload ? 'true' : 'false' }})">
                                                {{ $report->original_filename }}
                                            </button>
                                            <small class="text-muted fs-8">{{ $report->created_at->format('M d, Y • h:i A') }}</small>
                                        </div>
                                    @elseif(str_contains(strtolower($report->mime_type ?? ''), 'word') || str_ends_with(strtolower($report->original_filename), '.doc') || str_ends_with(strtolower($report->original_filename), '.docx'))
                                        <i class="bi bi-file-earmark-word-fill text-primary fs-5"></i>
                                        <div>
                                            <span class="fw-semibold d-block text-truncate" style="max-width: 320px;" title="{{ $report->original_filename }}">
                                                {{ $report->original_filename }}
                                            </span>
                                            <small class="text-muted fs-8">{{ $report->created_at->format('M d, Y • h:i A') }}</small>
                                        </div>
                                    @else
                                        <i class="bi bi-file-earmark-text-fill text-success fs-5"></i>
                                        <div>
                                            <span class="fw-semibold d-block text-truncate" style="max-width: 320px;" title="{{ $report->original_filename }}">
                                                {{ $report->original_filename }}
                                            </span>
                                            <small class="text-muted fs-8">{{ $report->created_at->format('M d, Y • h:i A') }}</small>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $report->category_badge }} fs-8">{{ $report->category_label }}</span>
                            </td>
                            <td>
                                @if($report->area)
                                    <span class="badge bg-light text-dark border fs-8">
                                        <i class="bi bi-folder2 me-1"></i>{{ $report->area->code }}
                                    </span>
                                    <small class="d-block text-muted text-truncate fs-8" style="max-width: 200px;">{{ $report->area->name }}</small>
                                @else
                                    <span class="badge bg-secondary text-white fs-8">
                                        <i class="bi bi-globe me-1"></i>Program-wide / General
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="fs-8">
                                    <span class="fw-semibold d-block">{{ $report->uploader->name ?? 'Administrator' }}</span>
                                    <small class="text-muted">{{ $report->uploader->role->name ?? 'User' }}</small>
                                </div>
                            </td>
                            <td class="font-monospace fs-8 text-muted">{{ $report->formatted_size }}</td>
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    @if($isPdf)
                                        <button type="button" class="btn btn-xs btn-outline-primary" title="View PDF" onclick="openPdfModal('{{ $streamUrl }}', '{{ $downloadUrl }}', '{{ $title }}', '{{ $meta }}', {{ $canDownload ? 'true' : 'false' }})">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                    @endif
                                    @if($canDownload)
                                        <a href="{{ $downloadUrl }}" class="btn btn-xs btn-outline-success" title="Download File">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    @endif
                                    @can('delete', $report)
                                        <form action="{{ route('technical-review-approval.destroy', $report) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this report file?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete File">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-file-earmark-x display-4 d-block mb-2 text-secondary"></i>
                                <h6 class="fw-bold mb-1">No Report Documents Found</h6>
                                <p class="fs-8 mb-3">No Technical Review or Board Approval reports match your criteria.</p>
                                @can('create', App\Models\TechnicalReviewApproval::class)
                                    <button type="button" class="btn btn-sm btn-apple-green" data-bs-toggle="modal" data-bs-target="#uploadReportModal">
                                        <i class="bi bi-cloud-arrow-up me-1"></i> Upload First Report
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($reports->hasPages())
        <div class="card-footer py-3">
            {{ $reports->links() }}
        </div>
    @endif
</div>

<!-- Upload Report Modal -->
@can('create', App\Models\TechnicalReviewApproval::class)
<div class="modal fade" id="uploadReportModal" tabindex="-1" aria-labelledby="uploadReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title fw-bold fs-6" id="uploadReportModalLabel">
                    <i class="bi bi-cloud-arrow-up me-2"></i>Upload Technical Review & Board Approval Report(s)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('technical-review-approval.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 fs-8 mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill fs-6"></i>
                        <div>Select single or multiple files (PDF, DOCX, XLSX, ZIP) to upload directly. No verbose text forms required.</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="modal_area_id" class="form-label fs-7 fw-semibold">Target Area (Optional)</label>
                            <select name="area_id" id="modal_area_id" class="form-select form-select-sm">
                                <option value="">Program-wide / General</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->code }} — {{ $area->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted fs-8">Select an area or leave blank for program-wide reports.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="modal_category" class="form-label fs-7 fw-semibold">Report Category</label>
                            <select name="category" id="modal_category" class="form-select form-select-sm" required>
                                <option value="general">Technical Review & Board Approval (General)</option>
                                <option value="technical_review">Technical Review Report</option>
                                <option value="board_approval">Board Approval Resolution</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="report_files" class="form-label fs-7 fw-semibold">Select Report File(s)</label>
                            <input type="file" name="files[]" id="report_files" class="form-control" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.zip">
                            <div class="form-text fs-8">You can select **single or multiple files** at once. Supported formats: PDF, DOC, DOCX, XLS, XLSX, ZIP (Max 30MB per file).</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-apple-green px-4">
                        <i class="bi bi-upload me-1"></i> Upload Files
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
