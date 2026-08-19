@extends('layouts.app')

@section('title', $area->code . ' - Hierarchy Explorer')

@section('content')
<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb breadcrumb-custom">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.areas.index') }}">Areas</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $area->code }}</li>
    </ol>
</nav>

<!-- Page Header & Action Bar -->
<div class="area-detail-header mb-4">
    <div class="area-detail-heading">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="area-code-badge"><i class="bi bi-folder-fill me-1"></i>{{ $area->code }}</span>
            <h3 class="mb-0">{{ $area->name }}</h3>
        </div>
        <p class="mb-0 fs-7">{{ $area->description ?? 'Accreditation documentary requirements compliance matrix repository.' }}</p>
        <div class="evidence-progress" aria-label="Evidence completion status">
            <div class="evidence-progress-summary">
                <span><i class="bi bi-pie-chart me-1"></i>Evidence completion</span>
                <strong>{{ $evidenceCompletionPercent }}%</strong>
            </div>
            <div class="progress" role="progressbar" aria-label="{{ $evidenceCompletionPercent }} percent of statements have uploaded evidence" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $evidenceCompletionPercent }}">
                <div class="progress-bar" style="width: {{ $evidenceCompletionPercent }}%"></div>
            </div>
            <div class="evidence-progress-detail">
                {{ $completedStatements }} of {{ $totalStatements }} statements with uploaded evidence
                @if($missingStatements > 0)
                    <span>{{ $missingStatements }} missing</span>
                @endif
            </div>
        </div>
    </div>

    <div class="area-detail-actions">
        <a href="{{ route('admin.areas.report', $area) }}" class="btn btn-outline-dark" target="_blank">
            <i class="bi bi-file-earmark-bar-graph me-1"></i> Official Report
        </a>

        @if(auth()->user()->isAdmin() || (auth()->user()->isFaculty() && auth()->user()->areas()->where('areas.id', $area->id)->wherePivotIn('assignment_role', ['handler', 'co-handler'])->exists()))
            <form action="{{ route('admin.areas.toggle_submission', $area) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn {{ $area->status === 'submission_ready' ? 'btn-success' : 'btn-warning' }}">
                    <i class="bi {{ $area->status === 'submission_ready' ? 'bi-check-circle-fill' : 'bi-shield-check' }} me-1"></i>
                    {{ $area->status === 'submission_ready' ? 'Submission Ready' : 'Mark Submission Ready' }}
                </button>
            </form>
        @endif

        @if(auth()->user()->isAdmin())
            <button type="button" class="btn btn-apple-green" data-bs-toggle="modal" data-bs-target="#createParameterModal">
                <i class="bi bi-plus-circle me-1"></i> Add Parameter
            </button>
        @endif
    </div>
</div>

<!-- Parameter Select / Filter Navigation Bar -->
@if($area->parameters->count() > 0)
<div class="area-filter-bar mb-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="pill-group parameter-filter-pills d-flex align-items-center flex-wrap gap-1">
            <span class="fs-8 fw-bold text-dark me-2 ms-1 text-uppercase"><i class="bi bi-funnel-fill text-success me-1"></i> Parameter:</span>
            <button class="btn btn-pill-filter active" data-param-id="all" onclick="filterParameter('all', this)">
                <i class="bi bi-grid-fill me-1"></i> All Parameters ({{ $area->parameters->count() }})
            </button>
            @foreach($area->parameters as $param)
                <button class="btn btn-pill-filter" data-param-id="{{ $param->id }}" onclick="filterParameter({{ $param->id }}, this)" title="{{ $param->title }}">
                    <i class="bi bi-folder-fill me-1 text-warning"></i> Parameter {{ $param->code }}
                </button>
            @endforeach
        </div>
        <div class="text-muted fs-8 fst-italic me-2 d-none d-lg-block">
            <i class="bi bi-info-circle me-1"></i> Select a Parameter to focus your view.
        </div>
    </div>
</div>
@endif

<!-- Top Pill Category Filter Navigation Bar -->
<div class="area-filter-bar mb-4">
    <div class="pill-group">
        <button class="btn btn-pill-filter active" onclick="filterCategory('all', this)">
            <i class="bi bi-grid-fill me-1"></i> All Categories
        </button>
        <button class="btn btn-pill-filter" onclick="filterCategory('system_input_and_process', this)">
            <i class="bi bi-cpu me-1"></i> System - Inputs and Processes
        </button>
        <button class="btn btn-pill-filter" onclick="filterCategory('implementation', this)">
            <i class="bi bi-gear-wide-connected me-1"></i> Implementation
        </button>
        <button class="btn btn-pill-filter" onclick="filterCategory('outcomes', this)">
            <i class="bi bi-trophy me-1"></i> Outcomes
        </button>
    </div>
    <div class="search-box-wrapper">
        <label class="visually-hidden" for="statementSearch">Search statements</label>
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="search" id="statementSearch" class="form-control border-start-0 ps-0" placeholder="Search statement or sub-item..." oninput="searchStatements(this.value)">
        </div>
    </div>
</div>

<!-- Parameters Stack -->
<div id="parametersContainer">
    @forelse($area->parameters as $index => $param)
        @php
            $parameterHasData = $param->parameterCategories->contains(
                fn ($parameterCategory) => $parameterCategory->allSubfolders()->exists()
            );
        @endphp
        @php
            $paramProgress = $param->progress;
        @endphp
        <section class="criterion-card-dark parameter-card mb-4" id="parameter-{{ $param->id }}">
            <!-- Parameter Title Header -->
            <div class="parameter-card-header d-flex flex-wrap align-items-center justify-content-between gap-3 p-3">
                <!-- Left Column: Parameter Eyebrow, Title & Description -->
                <div class="min-w-0" style="flex: 1 1 240px;">
                    <div class="parameter-eyebrow">Parameter {{ $param->code }}</div>
                    <h4 class="mb-1 parameter-title fw-bold">
                        {{ $param->title }}
                    </h4>
                    @if($param->description)
                        <p class="text-muted fs-7 mb-0">{{ $param->description }}</p>
                    @endif
                </div>

                <!-- Middle Column: Repositioned UI/UX Professional Progress Widget -->
                <div class="parameter-progress-widget px-3 py-2 rounded-3 border bg-white shadow-sm flex-shrink-0" style="min-width: 270px; max-width: 320px;">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="d-flex align-items-center gap-1.5">
                            <span class="badge rounded-circle p-1 bg-success-subtle text-success d-inline-flex align-items-center justify-content-center" style="width: 20px; height: 20px;">
                                <i class="bi bi-pie-chart-fill fs-9"></i>
                            </span>
                            <span class="fw-semibold text-secondary fs-8">Evidence Progress</span>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace fw-bold fs-8 px-2 py-0.5">
                            {{ $paramProgress['percent'] }}%
                        </span>
                    </div>
                    <div class="progress rounded-pill bg-light" style="height: 6px; overflow: hidden;" role="progressbar" aria-valuenow="{{ $paramProgress['percent'] }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar bg-success rounded-pill" style="width: {{ $paramProgress['percent'] }}%; transition: width 0.4s ease;"></div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-1 fs-8 text-muted" style="font-size: 0.75rem;">
                        <span class="fw-medium text-secondary"><i class="bi bi-file-earmark-check me-1 text-success"></i>{{ $paramProgress['completed'] }}/{{ $paramProgress['total'] }} statements</span>
                        @if($paramProgress['missing'] > 0)
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle py-0.5 px-1.5 fw-semibold" style="font-size: 0.7rem;">{{ $paramProgress['missing'] }} missing</span>
                        @else
                            <span class="badge bg-success-subtle text-success border border-success-subtle py-0.5 px-1.5 fw-semibold" style="font-size: 0.7rem;"><i class="bi bi-check2 me-1"></i>Complete</span>
                        @endif
                    </div>
                </div>
                <div class="parameter-actions">
                    <button type="button" class="btn btn-xs btn-outline-dark parameter-toggle"
                        onclick="toggleParameter(this)" aria-controls="parameter-{{ $param->id }}" aria-expanded="true"
                        title="Hide parameter categories">
                        <i class="bi bi-chevron-up"></i>
                    </button>
                    @if(auth()->user()->isAdmin())
                        <button type="button" class="btn btn-xs btn-outline-dark"
                            onclick="openEditParameterModal(
                                {{ $param->id }},
                                '{{ addslashes($param->code) }}',
                                '{{ addslashes($param->title) }}',
                                '{{ addslashes($param->description ?? '') }}',
                                {{ $param->sort_order }}
                            )">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                        @if($parameterHasData)
                            <button type="button" class="btn btn-xs btn-outline-danger py-1 px-2 fs-8" disabled
                                title="Delete is unavailable while this parameter has statements, sub-items, or uploaded documents.">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        @else
                            <form action="{{ route('admin.parameters.destroy', $param) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete empty Parameter {{ addslashes($param->code) }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger py-1 px-2 fs-8"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        @endif
                    @endif
                    <span class="file-count-badge">3 categories</span>
                </div>
            </div>

            <!-- Categories Loop inside Parameter -->
            @foreach($param->parameterCategories as $paramCat)
                @php
                    $catSlug = Str::slug($paramCat->category->name, '_');
                @endphp
                <div class="category-block mb-4 category-section-{{ $catSlug }}">
                    <!-- Category Title Header -->
                    <div class="category-header mb-2">
                        <h6 class="mb-0 text-uppercase d-flex align-items-center">
                            <i class="bi bi-folder2-open me-2"></i>{{ $paramCat->category->name }}
                        </h6>
                        @if(auth()->user()->isAdmin() || auth()->user()->isFaculty())
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn btn-xs category-add-button"
                                    data-param-cat-id="{{ $paramCat->id }}"
                                    data-category-name="{{ $paramCat->category->name }}"
                                    onclick="openAddSubfolderModal(this)">
                                    <i class="bi bi-plus-lg me-1"></i> Add Item
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-light category-batch-button"
                                    data-param-cat-id="{{ $paramCat->id }}"
                                    data-category-name="{{ $paramCat->category->name }}"
                                    data-param-code="{{ $paramCat->parameter->code }}"
                                    data-param-id="{{ $param->id }}"
                                    onclick="openBatchSubfolderModal(this)">
                                    <i class="bi bi-stack me-1"></i> Bulk Add (One Save)
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- 3-Column AACCUP Survey Table Layout (Matching Scanned Image) -->
                    <div class="table-responsive matrix-shell">
                        <table class="table table-matrix-dark align-middle mb-0">
                            <thead class="text-uppercase fs-8">
                                <tr>
                                    <th style="width: 33%; min-width: 330px;" class="ps-3 border-end border-secondary border-opacity-25">
                                        STATEMENT / SUB-ITEM REQUIREMENT
                                    </th>
                                    <th style="width: 35%; min-width: 350px;" class="border-end border-secondary border-opacity-25">
                                        DOCUMENTS NEEDED (Checklist)
                                    </th>
                                    <th style="width: 32%; min-width: 340px;" class="pe-3">
                                        AVAILABLE DOCUMENTS PROVIDED (PDF Files)
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paramCat->subfolders->sort(fn($a, $b) => strnatcasecmp($a->code ?? '', $b->code ?? '')) as $subfolder)
                                    @include('admin.areas._subfolder_row', ['subfolder' => $subfolder, 'depth' => 0, 'paramCat' => $paramCat])
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-3 text-muted fs-8">No statements created under {{ $paramCat->category->name }} yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </section>
    @empty
        <div class="card card-custom p-4 text-center text-muted">
            No parameters found for this area.
        </div>
    @endforelse
</div>

<!-- Modal: Edit Statement Sub-Item -->
<div class="modal fade" id="editSubfolderModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editSubfolderForm" method="POST" action="" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header bg-apple-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-1"></i> Edit Statement Sub-Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Statement Code</label>
                    <input type="text" id="editSubfolderCode" name="code" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Statement Title / Requirement</label>
                    <input type="text" id="editSubfolderName" name="name" class="form-control" required>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold fs-7">Documents Needed (Checklist)</label>
                    <textarea id="editSubfolderDocumentsNeeded" name="documents_needed" class="form-control" rows="4"></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-apple-green">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: View Additional Documents Request -->
<div class="modal fade" id="documentRequestDetailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-apple-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-chat-square-text me-1"></i> Additional Documents Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body fs-7">
                <p class="mb-3">Statement: <strong id="requestDetailsStatement"></strong></p>
                <div class="mb-3"><span class="fw-semibold d-block">Missing documents</span><span id="requestDetailsDocuments" class="text-secondary"></span></div>
                <div class="mb-3"><span class="fw-semibold d-block">Accreditor instructions</span><span id="requestDetailsRemarks" class="text-secondary"></span></div>
                <div class="text-muted fs-8">Requested by <span id="requestDetailsRequester"></span> &middot; <span id="requestDetailsDueDate"></span></div>
            </div>
            <div class="modal-footer bg-light py-2"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>

<!-- Modal: Create Parameter -->
<div class="modal fade" id="createParameterModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.parameters.store', $area) }}" class="modal-content">
            @csrf
            <div class="modal-header bg-apple-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-node-plus me-1"></i> Add Parameter to {{ $area->code }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Parameter Code</label>
                    <input type="text" name="code" class="form-control" placeholder="e.g. 1.1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Parameter Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Statement of Vision, Mission, Goals, and Objectives" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="alert alert-info fs-8 mb-0">
                    <i class="bi bi-magic me-1"></i> The 3 fixed accreditation categories (<code>System Input and Process</code>, <code>Outcomes</code>, <code>Implementation</code>) will be created automatically.
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-apple-green">Save Parameter</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Parameter -->
<div class="modal fade" id="editParameterModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editParameterForm" method="POST" action="" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header bg-apple-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-1"></i> Edit Parameter</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Parameter Code</label>
                    <input type="text" id="editParamCode" name="code" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Parameter Title</label>
                    <input type="text" id="editParamTitle" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Description</label>
                    <textarea id="editParamDescription" name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Sort Order</label>
                    <input type="number" id="editParamSort" name="sort_order" class="form-control" value="0">
                </div>
                <input type="hidden" name="status" value="active">
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-apple-green">Update Parameter</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Create Subfolder -->
<div class="modal fade" id="createSubfolderModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="createSubfolderForm" method="POST" action="" class="modal-content">
            @csrf
            <div class="modal-header bg-apple-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-folder-plus me-1"></i> Add Statement Sub-Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted fs-7 mb-1">Category: <strong id="modalCategoryTitle"></strong></p>
                <p class="text-muted fs-7 mb-2" id="modalParentLabel" style="display:none;">Parent Sub-Item: <strong id="modalParentCode"></strong></p>
                <input type="hidden" name="parent_id" id="subfolderParentId" value="">
                <input type="hidden" name="submission_token" id="subfolderSubmissionToken" value="">
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Statement Code</label>
                    <input type="text" name="code" id="subfolderCodeInput" class="form-control" placeholder="e.g. S.1 or I.1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Statement Title / Requirement</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. The institution has a system of determining VM." required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Documents Needed (Checklist)</label>
                    <textarea name="documents_needed" class="form-control" rows="4" placeholder="• Notices of Meeting&#10;• Minutes of Meeting&#10;• Action Photos"></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-apple-green" data-submit-label="Save Statement">Save Statement</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Bulk Add Statement Sub-Items (One Save) -->
<div class="modal fade" id="batchSubfolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form id="batchSubfolderForm" method="POST" action="" class="modal-content">
            @csrf
            <div class="modal-header bg-apple-dark text-white py-3">
                <div>
                    <h5 class="modal-title fw-bold" id="batchSubfolderModalLabel">
                        <i class="bi bi-stack me-2"></i> Bulk Add Statement Sub-Items (One Save)
                    </h5>
                    <p class="mb-0 fs-8 text-white-50">Parameter: <strong id="batchModalParamCode"></strong> &middot; Category: <strong id="batchModalCatName"></strong></p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3">
                <div class="alert alert-apple-green py-2 px-3 fs-8 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <i class="bi bi-info-circle-fill me-1 text-success"></i> Input multiple statement requirements at once. All items will be created together in one click!
                    </div>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-xs btn-apple-green shadow-sm" id="autoGeneratePresetBtn" onclick="generatePresetRows()">
                            <i class="bi bi-lightning-charge-fill me-1"></i> Auto-Generate 1 - 5
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-success" onclick="addBatchRow()">
                            <i class="bi bi-plus-circle me-1"></i> Add Single Row
                        </button>
                    </div>
                </div>

                <div class="table-responsive border rounded">
                    <table class="table table-bordered align-middle mb-0" id="batchTable">
                        <thead class="bg-light fs-8 text-uppercase">
                            <tr>
                                <th style="width: 18%;">Code <span class="text-danger">*</span></th>
                                <th style="width: 32%;">Statement / Requirement Title <span class="text-danger">*</span></th>
                                <th style="width: 28%;">Documents Needed (Checklist)</th>
                                <th style="width: 16%;">Parent Item</th>
                                <th style="width: 6%;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="batchTableBody">
                            <!-- Dynamic Rows -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer bg-light py-2 justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-success" onclick="addBatchRow()">
                    <i class="bi bi-plus-lg me-1"></i> Add Another Row
                </button>
                <div>
                    <button type="button" class="btn btn-sm btn-secondary me-1" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-apple-green px-4">
                        <i class="bi bi-check-all me-1"></i> Save All Items (One Save)
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@include('components.evidence-upload-choice-modal')

<!-- Modal: Upload PDF Documents -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="uploadDocumentForm" method="POST" action="" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header bg-apple-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-cloud-upload me-1"></i> Upload PDF Documents</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted fs-7 mb-3">Uploading into Statement: <strong id="modalSubfolderTitle"></strong></p>
                <div id="uploadRequestDetails" class="alert alert-warning border-warning-subtle d-none fs-8">
                    <div class="fw-semibold mb-1"><i class="bi bi-exclamation-circle me-1"></i>Additional documents requested by Accreditor</div>
                    <div id="uploadRequestedDocuments" class="mb-1"></div>
                    <div id="uploadRequestRemarks" class="mb-1"></div>
                    <div id="uploadRequestDueDate" class="text-muted"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Select PDF Files (Drag & Drop or Multi-Select)</label>
                    <input type="file" name="files[]" class="form-control" accept="application/pdf" multiple required>
                    <small class="text-muted fs-8">Only <code>.pdf</code> files accepted. Files up to 100 MB are accepted; files over 25 MB are compressed before storage and must finish at 25 MB or less.</small>
                </div>

                <div id="uploadChecklistSection" class="mb-3 p-3 border rounded bg-light d-none">
                    <label class="form-label fw-bold fs-7 d-block mb-1 text-dark" style="color: #000000 !important;">
                        <i class="bi bi-card-checklist text-success me-1"></i> Evidences Included in this Upload / Document:
                    </label>
                    <small class="text-dark fw-semibold fs-8 d-block mb-2" style="color: #111827 !important;">Check off the required evidence items that are included in your PDF document(s):</small>
                    <div id="uploadChecklistItems" class="d-flex flex-column gap-2 ms-1"></div>
                </div>

                <div class="compression-option mb-2">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="force_compress" value="1" id="forceCompressCheck">
                        <label class="form-check-label fw-semibold fs-7" for="forceCompressCheck">
                            <i class="bi bi-file-zip text-success me-1"></i> Compress PDF size on server (Ghostscript optimization)
                        </label>
                    </div>
                    <small class="d-block text-muted fs-8 mt-1">Files over 25 MB are always compressed before storage. Smaller files are optimized when this is enabled.</small>
                </div>
                <div id="uploadProgressPanel" class="upload-progress-panel d-none" aria-live="polite">
                    <div class="d-flex justify-content-between align-items-center gap-3 mb-1"><span id="uploadProgressLabel">Preparing upload...</span><strong id="uploadProgressValue">0%</strong></div>
                    <div class="progress"><div id="uploadProgressBar" class="progress-bar" role="progressbar" style="width: 0%" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div></div>
                    <small class="text-muted d-block mt-1">Please keep this window open while the upload finishes.</small>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-apple-green" data-submit-label="Start Upload"><i class="bi bi-upload me-1"></i> Start Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Tag Evidences for Uploaded Document -->
<div class="modal fade" id="tagEvidencesModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="tagEvidencesForm" method="POST" action="" onsubmit="saveDocumentEvidences(event)" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header bg-apple-dark text-white py-2">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-tags me-1"></i> Tag Evidences for PDF File</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body fs-7">
                <p class="text-muted fs-8 mb-2">File: <strong id="tagEvidencesFilename"></strong></p>
                <div class="alert alert-light border fs-8 py-2 mb-3">
                    <i class="bi bi-info-circle text-success me-1"></i> Select which required evidence items are covered inside this specific PDF document:
                </div>
                <div id="tagEvidencesContainer" class="d-flex flex-column gap-2 ms-1"></div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-apple-green" id="saveTagEvidencesBtn"><i class="bi bi-check-lg me-1"></i> Save Evidence Tags</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Capture Photo Evidence -->
<div class="modal fade" id="capturePhotoModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="capturePhotoForm" method="POST" action="" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header bg-apple-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-camera-fill me-1"></i> Capture Photo Evidence</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted fs-7 mb-3">Statement: <strong id="capturePhotoSubfolderTitle"></strong></p>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Tag to Checklist / Document-Needed Item</label>
                    <select name="checklist_item" id="capturePhotoChecklistItem" class="form-select" required></select>
                    <small class="text-muted fs-8">Captured photos are saved only under this specific evidence tag.</small>
                </div>
                <div class="capture-photo-section mb-3">
                    <div class="capture-photo-section-title"><span><i class="bi bi-camera-video me-1 text-success"></i>Camera</span><span class="text-muted fw-normal">Up to 2,000px / JPEG</span></div>
                    <div class="capture-photo-camera-stage">
                        <video id="capturePhotoVideo" class="w-100 rounded d-none" autoplay muted playsinline></video>
                        <div id="capturePhotoCameraMessage" class="text-center text-muted fs-8 py-3">Start the camera to take one or more photos, or choose multiple existing photos.</div>
                    </div>
                    <div class="capture-photo-actions">
                        <button type="button" class="btn btn-sm btn-outline-success" id="startEvidenceCameraBtn" onclick="startEvidenceCamera()"><i class="bi bi-camera-video me-1"></i> Start Camera</button>
                        <button type="button" class="btn btn-sm btn-apple-green" id="takeEvidencePhotoBtn" onclick="takeEvidencePhoto()" disabled><i class="bi bi-camera-fill me-1"></i> Take Photo</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="stopEvidenceCameraBtn" onclick="stopEvidenceCamera()"><i class="bi bi-camera-video-off me-1"></i> Stop</button>
                    </div>
                </div>
                <div class="capture-photo-section mb-3">
                    <div class="capture-photo-section-title"><span><i class="bi bi-images me-1 text-success"></i>Gallery / Device</span><span class="text-muted fw-normal">Multiple selection</span></div>
                    <input id="capturePhotoFiles" type="file" name="photos[]" class="form-control" accept="image/*" multiple required>
                    <small class="text-muted fs-8 d-block mt-1">Choose one or more photos. They will be resized and compressed before storage.</small>
                </div>
                <div class="capture-photo-selected">
                    <div class="capture-photo-section-title mb-0"><span><i class="bi bi-check2-square me-1 text-success"></i>Selected Photos</span><span class="text-muted fw-normal">Remove any before saving</span></div>
                    <div id="capturePhotoSelection" class="row row-cols-2 g-2 mt-1"></div>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold fs-7">Caption <span class="text-muted fw-normal">(optional)</span></label>
                    <input type="text" name="caption" class="form-control" maxlength="255" placeholder="e.g. General Assembly, August 2026">
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-apple-green"><i class="bi bi-camera me-1"></i> Save Photo Evidence</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<style>
    .area-detail-header {
        align-items: center;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        display: flex;
        gap: 1.5rem;
        justify-content: space-between;
        padding: 1.35rem 1.5rem;
        box-shadow: var(--card-shadow);
    }
    .area-detail-heading h3 { color: var(--text-primary); font-size: 1.4rem; font-weight: 700; }
    .evidence-progress { margin-top: 0.9rem; max-width: 330px; }
    .parameter-progress-widget {
        background-color: var(--bg-surface) !important;
        border-color: var(--border-color) !important;
    }
    .evidence-progress-summary { align-items: center; color: var(--text-secondary); display: flex; font-size: 0.72rem; font-weight: 600; justify-content: space-between; margin-bottom: 0.35rem; }
    .evidence-progress-summary strong { color: var(--accent-text); font-size: 0.85rem; }
    .evidence-progress .progress { background: var(--border-color); border-radius: 99px; height: 6px; }
    .evidence-progress .progress-bar { background: var(--accent); border-radius: inherit; }
    .evidence-progress-detail { color: var(--text-secondary); font-size: 0.72rem; margin-top: 0.35rem; }
    .evidence-progress-detail span { color: #9a6a13; font-weight: 600; margin-left: 0.35rem; }
    .area-code {
        background: var(--accent-light);
        border: 1px solid #cfe1b9;
        border-radius: 4px;
        color: var(--accent-text);
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.25rem 0.45rem;
    }
    .area-detail-actions, .parameter-actions { align-items: center; display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .area-filter-bar {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        box-shadow: var(--card-shadow);
        padding: 0.6rem;
    }
    .parameter-card { overflow: hidden; padding: 0; }
    .parameter-card.is-collapsed .category-block { display: none !important; }
    .parameter-card-header {
        align-items: flex-start;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        padding: 1.25rem 1.35rem;
    }
    .parameter-card-header h4,
    .parameter-card-header h5 { color: var(--text-primary); font-size: 1.2rem; font-weight: 800; margin-bottom: 0.25rem; }
    .parameter-eyebrow { color: var(--accent-text); font-size: 0.78rem; font-weight: 800; letter-spacing: 0.08em; margin-bottom: 0.3rem; text-transform: uppercase; }
    .category-block { margin: 1.1rem 1.35rem !important; }
    .category-header { align-items: center; display: flex; gap: 0.75rem; justify-content: flex-start; }
    .category-header h6 { flex: 0 1 auto; }
    .matrix-shell {
        border: 1px solid var(--table-border);
        border-radius: 8px;
        overflow-x: auto !important;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }
    .matrix-shell::-webkit-scrollbar {
        height: 6px;
    }
    .matrix-shell::-webkit-scrollbar-track {
        background: var(--bg-surface);
        border-radius: 4px;
    }
    .matrix-shell::-webkit-scrollbar-thumb {
        background: rgba(100, 116, 139, 0.35);
        border-radius: 4px;
    }
    .matrix-shell::-webkit-scrollbar-thumb:hover {
        background: rgba(100, 116, 139, 0.65);
    }
    .matrix-shell .table-matrix-dark {
        min-width: 1050px;
        table-layout: fixed;
    }
    .matrix-shell .table-matrix-dark td,
    .matrix-shell .table-matrix-dark th {
        word-break: break-word !important;
        overflow-wrap: break-word !important;
    }
    .matrix-shell .btn { white-space: nowrap; }
    .statement-search-hidden { display: none !important; }
    .upload-progress-panel { background: var(--doc-card-bg); border: 1px solid var(--doc-card-border); border-radius: 6px; font-size: 0.78rem; margin-top: 1rem; padding: 0.8rem; }
    .upload-progress-panel .progress { background: var(--border-color); height: 8px; }
    .upload-progress-panel .progress-bar { background: var(--accent); }
    @media (max-width: 767.98px) {
        .area-detail-header, .parameter-card-header { align-items: stretch; flex-direction: column; }
        .area-detail-actions { width: 100%; }
        .area-detail-actions .btn, .area-detail-actions form { flex: 1 1 auto; }
        .category-block { margin: 0.9rem !important; }
        .category-header { align-items: flex-start; flex-direction: column; }
    }
    .transition-rotate {
        transition: transform 0.25s ease;
    }
    .transition-rotate.rotated {
        transform: rotate(90deg);
    }
    .child-of-expanded {
        animation: fadeSlideIn 0.25s ease forwards;
    }
    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateY(-6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .subfolder-child-row td {
        border-top: 1px solid rgba(108, 117, 125, 0.15) !important;
    }
</style>
<script>
    function filterCategory(catSlug, btn) {
        document.querySelectorAll('.btn-pill-filter').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        document.querySelectorAll('.category-block').forEach(block => {
            if (catSlug === 'all') {
                block.style.display = 'block';
            } else {
                if (block.classList.contains('category-section-' + catSlug)) {
                    block.style.display = 'block';
                } else {
                    block.style.display = 'none';
                }
            }
        });
    }

    function searchStatements(query) {
        const normalizedQuery = query.trim().toLowerCase();
        const statementRows = [...document.querySelectorAll('tr[id^="subfolder-"]')];

        statementRows.forEach((row) => row.classList.remove('statement-search-hidden'));
        document.querySelectorAll('.subfolder-child-row').forEach((row) => {
            if (normalizedQuery) row.style.display = 'none';
        });

        if (!normalizedQuery) {
            document.querySelectorAll('.parameter-card').forEach((card) => card.classList.remove('statement-search-hidden'));
            return;
        }

        statementRows.forEach((row) => {
            const matches = row.textContent.toLowerCase().includes(normalizedQuery);
            row.classList.toggle('statement-search-hidden', !matches);

            if (matches) {
                let container = row.closest('tr.subfolder-child-row');
                while (container) {
                    container.style.display = '';
                    container = container.parentElement.closest('tr.subfolder-child-row');
                }
            }
        });

        document.querySelectorAll('.parameter-card').forEach((card) => {
            card.classList.toggle('statement-search-hidden', !card.querySelector('tr[id^="subfolder-"]:not(.statement-search-hidden)'));
        });
    }

    function filterParameter(paramId, btnEl) {
        const container = document.querySelector('.parameter-filter-pills');
        if (container) {
            container.querySelectorAll('.btn-pill-filter').forEach(btn => btn.classList.remove('active'));
        }

        if (btnEl) {
            btnEl.classList.add('active');
        } else if (container) {
            const targetBtn = container.querySelector(`[data-param-id="${paramId}"]`);
            if (targetBtn) targetBtn.classList.add('active');
        }

        const cards = document.querySelectorAll('.parameter-card');
        cards.forEach(card => {
            if (paramId === 'all' || card.id === 'parameter-' + paramId) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });

        const areaId = {{ $area->id }};
        if (paramId === 'all') {
            localStorage.removeItem('active_parameter_area_' + areaId);
        } else {
            localStorage.setItem('active_parameter_area_' + areaId, paramId);
        }

        if (window.history && window.history.replaceState) {
            const url = new URL(window.location);
            if (paramId === 'all') {
                url.searchParams.delete('param_id');
            } else {
                url.searchParams.set('param_id', paramId);
            }
            window.history.replaceState(null, '', url);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const areaId = {{ $area->id }};
        const urlParams = new URLSearchParams(window.location.search);
        let targetParamId = urlParams.get('param_id') || localStorage.getItem('active_parameter_area_' + areaId);

        if (targetParamId && targetParamId !== 'all') {
            const exists = document.getElementById('parameter-' + targetParamId);
            if (exists) {
                filterParameter(targetParamId);
            }
        }
    });

    function toggleParameter(button) {
        const parameterCard = button.closest('.parameter-card');
        const isCollapsed = parameterCard.classList.toggle('is-collapsed');
        const icon = button.querySelector('i');

        button.setAttribute('aria-expanded', String(!isCollapsed));
        button.setAttribute('title', isCollapsed ? 'Show parameter categories' : 'Hide parameter categories');
        icon.classList.toggle('bi-chevron-down', isCollapsed);
        icon.classList.toggle('bi-chevron-up', !isCollapsed);
    }

    // Toggle expand/collapse of child sub-item rows
    function toggleChildren(btn) {
        const targetSelector = btn.getAttribute('data-target');
        const subfolderId = targetSelector.replace('#children-', '');
        const childRows = document.querySelectorAll('.child-of-' + subfolderId);
        const icon = btn.querySelector('i');
        const isExpanded = icon.classList.contains('rotated');

        if (isExpanded) {
            // Collapse: hide all children (and their nested children)
            childRows.forEach(row => {
                row.style.display = 'none';
                // Also collapse any nested toggles inside
                row.querySelectorAll('.subfolder-toggle i.rotated').forEach(nestedIcon => {
                    nestedIcon.classList.remove('rotated');
                });
                // Hide nested children too
                const nestedId = row.querySelector('[data-target]');
                if (nestedId) {
                    const nestedSubfolderId = nestedId.getAttribute('data-target').replace('#children-', '');
                    document.querySelectorAll('.child-of-' + nestedSubfolderId).forEach(nr => {
                        nr.style.display = 'none';
                    });
                }
            });
            icon.classList.remove('rotated');
        } else {
            // Expand: show direct children only
            childRows.forEach(row => {
                row.style.display = '';
                row.classList.add('child-of-expanded');
            });
            icon.classList.add('rotated');
        }
    }

    // Open modal to add a top-level subfolder (no parent)
    function openAddSubfolderModal(paramCatId, categoryName) {
        if (paramCatId && typeof paramCatId === 'object' && paramCatId.dataset) {
            const btn = paramCatId;
            paramCatId = btn.dataset.paramCatId;
            categoryName = btn.dataset.categoryName;
        }
        resetSubfolderSubmitButton();
        generateSubfolderSubmissionToken();
        document.getElementById('modalCategoryTitle').innerText = categoryName || '';
        document.getElementById('createSubfolderForm').action = "/parameter-categories/" + paramCatId + "/subfolders";
        document.getElementById('subfolderParentId').value = '';
        document.getElementById('subfolderCodeInput').placeholder = 'e.g. S.1 or I.1';
        document.getElementById('modalParentLabel').style.display = 'none';
        new bootstrap.Modal(document.getElementById('createSubfolderModal')).show();
    }

    // Open modal to add a child sub-item under a parent subfolder
    function openAddChildSubfolderModal(paramCatId, parentId, parentCode, categoryName) {
        if (paramCatId && typeof paramCatId === 'object' && paramCatId.dataset) {
            const btn = paramCatId;
            paramCatId = btn.dataset.paramCatId;
            parentId = btn.dataset.parentId;
            parentCode = btn.dataset.parentCode;
            categoryName = btn.dataset.categoryName;
        }
        resetSubfolderSubmitButton();
        generateSubfolderSubmissionToken();
        document.getElementById('modalCategoryTitle').innerText = categoryName || '';
        document.getElementById('createSubfolderForm').action = "/parameter-categories/" + paramCatId + "/subfolders";
        document.getElementById('subfolderParentId').value = parentId;
        document.getElementById('subfolderCodeInput').placeholder = (parentCode ? parentCode + '.1' : '');
        document.getElementById('subfolderCodeInput').value = '';
        document.getElementById('modalParentLabel').style.display = 'block';
        document.getElementById('modalParentCode').innerText = parentCode || '';
        new bootstrap.Modal(document.getElementById('createSubfolderModal')).show();
    }

    const createSubfolderFormEl = document.getElementById('createSubfolderForm');
    if (createSubfolderFormEl) {
        createSubfolderFormEl.addEventListener('submit', function () {
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Saving...';
            }
        });
    }

    function resetSubfolderSubmitButton() {
        const form = document.getElementById('createSubfolderForm');
        if (form) {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = false;
                if (submitButton.dataset && submitButton.dataset.submitLabel) {
                    submitButton.textContent = submitButton.dataset.submitLabel;
                } else {
                    submitButton.textContent = 'Save Statement Item';
                }
            }
        }
    }

    function generateSubfolderSubmissionToken() {
        const tokenEl = document.getElementById('subfolderSubmissionToken');
        if (tokenEl) {
            tokenEl.value = (window.crypto && window.crypto.randomUUID) ? window.crypto.randomUUID() : (Date.now() + '_' + Math.random());
        }
    }

    let pendingEvidenceUploadButton = null;

    function openEvidenceUploadChoice(btn) {
        pendingEvidenceUploadButton = btn;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('evidenceUploadChoiceModal')).show();
    }

    function chooseEvidenceUploadMethod(method) {
        const button = pendingEvidenceUploadButton;
        const modalElement = document.getElementById('evidenceUploadChoiceModal');
        if (!button || !modalElement) return;

        modalElement.addEventListener('hidden.bs.modal', function openSelectedUploadModal() {
            if (method === 'photo') {
                openCapturePhotoModal(button);
            } else {
                openUploadModal(button);
            }
        }, { once: true });

        bootstrap.Modal.getOrCreateInstance(modalElement).hide();
    }

    function openUploadModal(subfolderId, subfolderName, requestButton = null) {
        if (subfolderId && typeof subfolderId === 'object' && subfolderId.dataset) {
            const btn = subfolderId;
            requestButton = btn;
            subfolderId = btn.dataset.subfolderId;
            subfolderName = btn.dataset.subfolderName;
        }
        document.getElementById('modalSubfolderTitle').innerText = subfolderName || '';
        document.getElementById('uploadDocumentForm').action = "/subfolders/" + subfolderId + "/documents";
        const requestPanel = document.getElementById('uploadRequestDetails');
        const hasOpenRequest = requestButton && requestButton.dataset && requestButton.dataset.openRequestId;
        requestPanel.classList.toggle('d-none', !hasOpenRequest);

        if (hasOpenRequest) {
            document.getElementById('uploadRequestedDocuments').textContent = 'Missing documents: ' + (requestButton.dataset.requestedDocuments || 'See accreditor instructions.');
            document.getElementById('uploadRequestRemarks').textContent = 'Instructions: ' + requestButton.dataset.requestRemarks;
            document.getElementById('uploadRequestDueDate').textContent = requestButton.dataset.requestDueDate ? 'Due date: ' + requestButton.dataset.requestDueDate : 'No due date set.';
        }

        // Populate evidence checklist items in upload modal
        let documentsNeeded = '';
        let completedItems = [];
        if (requestButton && requestButton.dataset) {
            documentsNeeded = requestButton.dataset.documentsNeeded || '';
            try {
                completedItems = JSON.parse(requestButton.dataset.completedItems || '[]');
            } catch (e) {
                completedItems = [];
            }
        }

        const checklistSection = document.getElementById('uploadChecklistSection');
        const checklistContainer = document.getElementById('uploadChecklistItems');
        if (checklistContainer) {
            checklistContainer.innerHTML = '';
            if (documentsNeeded.trim() !== '') {
                const rawLines = documentsNeeded.split(/\r\n|\r|\n|•/);
                const items = [];
                rawLines.forEach(line => {
                    let trimmed = line.trim().replace(/^[•\t\-\s*]+/, '');
                    if (trimmed !== '' && !items.includes(trimmed)) {
                        items.push(trimmed);
                    }
                });

                if (items.length > 0) {
                    items.forEach((item, index) => {
                        const isChecked = completedItems.includes(item);
                        const chkId = 'modal-chk-' + index;
                        const div = document.createElement('div');
                        div.className = 'form-check fs-8';
                        div.innerHTML = `
                            <input class="form-check-input mt-1" type="checkbox" name="completed_items[]" value="${escapeHtml(item)}" id="${chkId}" ${isChecked ? 'checked' : ''}>
                            <label class="form-check-label text-dark fw-bold cursor-pointer" for="${chkId}" style="color: #000000 !important;">
                                ${escapeHtml(item)}
                            </label>
                        `;
                        checklistContainer.appendChild(div);
                    });
                    checklistSection.classList.remove('d-none');
                } else {
                    checklistSection.classList.add('d-none');
                }
            } else {
                checklistSection.classList.add('d-none');
            }
        }

        resetUploadProgress();
        new bootstrap.Modal(document.getElementById('uploadDocumentModal')).show();
    }

    function toggleSubfolderChecklistItem(checkbox) {
        const subfolderId = checkbox.dataset.subfolderId;
        const cell = document.getElementById('checklist-cell-' + subfolderId);
        if (!cell) return;

        const checkedBoxes = cell.querySelectorAll('.checklist-item-toggle:checked');
        const completedItems = Array.from(checkedBoxes).map(cb => cb.dataset.itemName);

        const csrfTokenEl = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenEl ? csrfTokenEl.getAttribute('content') : '';

        fetch('/subfolders/' + subfolderId + '/checklist', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ completed_items: completedItems })
        })
        .then(async response => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok || data.status === 'error') {
                checkbox.checked = !checkbox.checked;
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'warning',
                    title: 'Action Unavailable',
                    text: data.message || 'Please upload a PDF document before selecting evidence checklist items.',
                    showConfirmButton: false,
                    timer: 2800
                });
                return;
            }
            if (data.status === 'success') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Evidence Checklist Updated',
                    text: 'Status: ' + data.checklist_stats.completed + '/' + data.checklist_stats.total + ' complete',
                    showConfirmButton: false,
                    timer: 1800
                });
                window.setTimeout(() => window.location.reload(), 1500);
            }
        })
        .catch(err => {
            checkbox.checked = !checkbox.checked;
            console.error('Checklist update failed', err);
        });
    }

    function escapeHtml(text) {
        return (text || '').replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function expandAndScrollToStatement(subfolderId) {
        if (!subfolderId) return;
        const targetRow = document.getElementById('subfolder-' + subfolderId);
        if (!targetRow) return;

        // 1. Un-collapse any parent parameter container if collapsed
        const parameterCard = targetRow.closest('.parameter-card');
        if (parameterCard && parameterCard.classList.contains('is-collapsed')) {
            parameterCard.classList.remove('is-collapsed');
            const pToggleBtn = parameterCard.querySelector('.parameter-toggle i');
            if (pToggleBtn) {
                pToggleBtn.classList.remove('bi-chevron-down');
                pToggleBtn.classList.add('bi-chevron-up');
            }
        }

        // 2. Expand parent child rows if nested
        let current = targetRow;
        while (current) {
            const childRow = current.closest('tr.subfolder-child-row');
            if (childRow) {
                childRow.style.display = '';
                const parentClass = Array.from(childRow.classList).find(c => c.startsWith('child-of-'));
                if (parentClass) {
                    const parentId = parentClass.replace('child-of-', '');
                    const parentRow = document.getElementById('subfolder-' + parentId);
                    if (parentRow) {
                        const toggleBtnIcon = parentRow.querySelector('.subfolder-toggle i');
                        if (toggleBtnIcon) toggleBtnIcon.classList.add('rotated');
                    }
                    current = parentRow;
                } else {
                    break;
                }
            } else {
                break;
            }
        }

        // 3. Scroll to target statement and highlight
        setTimeout(() => {
            targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            targetRow.style.transition = 'background-color 0.5s ease';
            const originalBg = targetRow.style.backgroundColor;
            targetRow.style.backgroundColor = 'var(--accent-light)';
            setTimeout(() => {
                targetRow.style.backgroundColor = originalBg || '';
            }, 2500);
        }, 200);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const areaId = {{ $area->id }};
        const urlParams = new URLSearchParams(window.location.search);
        let targetParamId = urlParams.get('param_id') || localStorage.getItem('active_parameter_area_' + areaId);

        if (targetParamId && targetParamId !== 'all') {
            const exists = document.getElementById('parameter-' + targetParamId);
            if (exists) {
                filterParameter(targetParamId);
            }
        }

        const hash = window.location.hash;
        if (hash && hash.startsWith('#subfolder-')) {
            const subfolderId = hash.replace('#subfolder-', '');
            expandAndScrollToStatement(subfolderId);
        }
    });

    function openTagEvidencesModal(btn) {
        const docId = btn.dataset.documentId;
        const subfolderId = btn.dataset.subfolderId;
        const filename = btn.dataset.filename;
        const documentsNeeded = btn.dataset.documentsNeeded || '';
        let coveredEvidences = [];
        try {
            coveredEvidences = JSON.parse(btn.dataset.coveredEvidences || '[]');
        } catch(e) {
            coveredEvidences = [];
        }

        const modalEl = document.getElementById('tagEvidencesModal');
        if (modalEl) {
            modalEl.dataset.subfolderId = subfolderId || '';
        }

        document.getElementById('tagEvidencesFilename').textContent = filename || '';
        document.getElementById('tagEvidencesForm').action = "/documents/" + docId + "/evidences";

        const container = document.getElementById('tagEvidencesContainer');
        container.innerHTML = '';

        if (documentsNeeded.trim() !== '') {
            const rawLines = documentsNeeded.split(/\r\n|\r|\n|•/);
            const items = [];
            rawLines.forEach(line => {
                let trimmed = line.trim().replace(/^[•\t\-\s*]+/, '');
                if (trimmed !== '' && !items.includes(trimmed)) {
                    items.push(trimmed);
                }
            });

            if (items.length > 0) {
                items.forEach((item, index) => {
                    const isChecked = coveredEvidences.includes(item);
                    const chkId = 'file-tag-chk-' + docId + '-' + index;
                    const div = document.createElement('div');
                    div.className = 'form-check fs-8';
                    div.innerHTML = `
                        <input class="form-check-input mt-1" type="checkbox" name="covered_evidences[]" value="${escapeHtml(item)}" id="${chkId}" ${isChecked ? 'checked' : ''}>
                        <label class="form-check-label text-dark fw-bold cursor-pointer" for="${chkId}" style="color: #000000 !important;">
                            ${escapeHtml(item)}
                        </label>
                    `;
                    container.appendChild(div);
                });
            } else {
                container.innerHTML = '<span class="text-muted fs-8 fst-italic">No specific evidence checklist defined for this statement.</span>';
            }
        } else {
            container.innerHTML = '<span class="text-muted fs-8 fst-italic">No specific evidence checklist defined for this statement.</span>';
        }

        new bootstrap.Modal(document.getElementById('tagEvidencesModal')).show();
    }

    function saveDocumentEvidences(event) {
        event.preventDefault();
        const form = document.getElementById('tagEvidencesForm');
        const submitBtn = document.getElementById('saveTagEvidencesBtn');
        const formData = new FormData(form);
        const coveredEvidences = formData.getAll('covered_evidences[]');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

        const csrfTokenEl = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenEl ? csrfTokenEl.getAttribute('content') : '';

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-HTTP-Method-Override': 'PUT'
            },
            body: JSON.stringify({ covered_evidences: coveredEvidences })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const modalEl = document.getElementById('tagEvidencesModal');
                const subfolderId = modalEl ? modalEl.dataset.subfolderId : '';
                if (subfolderId) {
                    window.location.hash = '#subfolder-' + subfolderId;
                }
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Evidence Tags Updated',
                    text: 'Statement progress: ' + data.checklist_stats.completed + '/' + data.checklist_stats.total + ' complete',
                    showConfirmButton: false,
                    timer: 1800
                });
                window.setTimeout(() => window.location.reload(), 1200);
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save Evidence Tags';
                Swal.fire({ icon: 'error', title: 'Update failed', text: data.message || 'Could not update evidence tags.' });
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save Evidence Tags';
            console.error(err);
        });
    }

    let evidencePhotoFiles = [];
    let evidencePhotoStream = null;

    function syncEvidencePhotoFiles() {
        const input = document.getElementById('capturePhotoFiles');
        const transfer = new DataTransfer();
        evidencePhotoFiles.forEach(file => transfer.items.add(file));
        input.files = transfer.files;

        const selection = document.getElementById('capturePhotoSelection');
        selection.innerHTML = '';
        evidencePhotoFiles.forEach((file, index) => {
            const column = document.createElement('div');
            column.className = 'col';
            const card = document.createElement('div');
            card.className = 'border rounded p-1 h-100 bg-white';
            const image = document.createElement('img');
            image.src = URL.createObjectURL(file);
            image.alt = file.name;
            image.className = 'w-100 rounded';
            image.style.height = '100px';
            image.style.objectFit = 'cover';
            const details = document.createElement('div');
            details.className = 'd-flex align-items-center justify-content-between gap-1 mt-1';
            const label = document.createElement('small');
            label.className = 'text-truncate';
            label.textContent = file.name;
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-xs btn-outline-danger';
            remove.title = 'Remove photo';
            remove.innerHTML = '<i class="bi bi-x-lg"></i>';
            remove.addEventListener('click', () => {
                evidencePhotoFiles.splice(index, 1);
                syncEvidencePhotoFiles();
            });
            details.append(label, remove);
            card.append(image, details);
            column.appendChild(card);
            selection.appendChild(column);
        });
    }

    function addEvidencePhotoFiles(files) {
        Array.from(files).forEach(file => {
            const duplicate = evidencePhotoFiles.some(existing => existing.name === file.name && existing.size === file.size && existing.lastModified === file.lastModified);
            if (!duplicate) evidencePhotoFiles.push(file);
        });
        syncEvidencePhotoFiles();
    }

    async function startEvidenceCamera() {
        const video = document.getElementById('capturePhotoVideo');
        const message = document.getElementById('capturePhotoCameraMessage');
        try {
            evidencePhotoStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false });
            video.srcObject = evidencePhotoStream;
            video.classList.remove('d-none');
            message.classList.add('d-none');
            document.getElementById('takeEvidencePhotoBtn').disabled = false;
            document.getElementById('stopEvidenceCameraBtn').classList.remove('d-none');
        } catch (error) {
            message.textContent = 'Camera access was unavailable. Allow camera permission, or choose photos from your device.';
            message.classList.remove('d-none');
        }
    }

    function takeEvidencePhoto() {
        const video = document.getElementById('capturePhotoVideo');
        if (!video.videoWidth) return;
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        canvas.toBlob(blob => {
            if (!blob) return;
            addEvidencePhotoFiles([new File([blob], 'evidence-photo-' + Date.now() + '.jpg', { type: 'image/jpeg', lastModified: Date.now() })]);
        }, 'image/jpeg', 0.9);
    }

    function stopEvidenceCamera() {
        evidencePhotoStream?.getTracks().forEach(track => track.stop());
        evidencePhotoStream = null;
        const video = document.getElementById('capturePhotoVideo');
        video.srcObject = null;
        video.classList.add('d-none');
        document.getElementById('capturePhotoCameraMessage').classList.remove('d-none');
        document.getElementById('takeEvidencePhotoBtn').disabled = true;
        document.getElementById('stopEvidenceCameraBtn').classList.add('d-none');
    }

    document.getElementById('capturePhotoFiles').addEventListener('change', function () {
        addEvidencePhotoFiles(this.files);
    });
    document.getElementById('capturePhotoModal').addEventListener('hidden.bs.modal', stopEvidenceCamera);

    function openCapturePhotoModal(btn) {
        const subfolderId = btn.dataset.subfolderId;
        const subfolderName = btn.dataset.subfolderName;
        const documentsNeeded = btn.dataset.documentsNeeded || '';

        document.getElementById('capturePhotoSubfolderTitle').innerText = subfolderName || '';
        document.getElementById('capturePhotoForm').action = "/subfolders/" + subfolderId + "/evidence-photos";
        evidencePhotoFiles = [];
        document.getElementById('capturePhotoFiles').value = '';
        syncEvidencePhotoFiles();

        const select = document.getElementById('capturePhotoChecklistItem');
        select.innerHTML = '';

        const rawLines = documentsNeeded.split(/\r\n|\r|\n|•/);
        const items = [];
        rawLines.forEach(line => {
            let trimmed = line.trim().replace(/^[•\t\-\s*]+/, '');
            if (trimmed !== '' && !items.includes(trimmed)) {
                items.push(trimmed);
            }
        });

        if (items.length > 0) {
            items.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item;
                opt.textContent = item;
                select.appendChild(opt);
            });
        } else {
            const opt = document.createElement('option');
            opt.value = 'General Evidence';
            opt.textContent = 'General Evidence (no checklist defined)';
            select.appendChild(opt);
        }

        new bootstrap.Modal(document.getElementById('capturePhotoModal')).show();
    }

    function openDocumentRequestDetailsModal(button) {
        document.getElementById('requestDetailsStatement').textContent = button.dataset.statement;
        document.getElementById('requestDetailsDocuments').textContent = button.dataset.requestedDocuments || 'See accreditor instructions.';
        document.getElementById('requestDetailsRemarks').textContent = button.dataset.remarks;
        document.getElementById('requestDetailsRequester').textContent = button.dataset.requester;
        document.getElementById('requestDetailsDueDate').textContent = button.dataset.dueDate ? 'Due: ' + button.dataset.dueDate : 'No due date set.';
        new bootstrap.Modal(document.getElementById('documentRequestDetailsModal')).show();
    }

    const isAccreditorUser = {{ auth()->user()->isAccreditor() ? 'true' : 'false' }};

    function openPdfModalFromBtn(btn) {
        let streamUrl = btn.dataset.streamUrl;
        const downloadUrl = btn.dataset.downloadUrl;
        const filename = btn.dataset.filename;
        const meta = btn.dataset.meta;
        const canDownload = btn.dataset.canDownload === 'true';
        const subfolderId = btn.dataset.subfolderId;

        if (btn.dataset.photoEvidence === 'true') {
            streamUrl += (streamUrl.includes('?') ? '&' : '?') + 'layout=single-row-v4&refresh=' + Date.now();
        }

        if (subfolderId && typeof isAccreditorUser !== 'undefined' && isAccreditorUser) {
            markStatementAsEvaluated(subfolderId);
        }

        openPdfModal(streamUrl, downloadUrl, filename, meta, canDownload);
    }

    function markStatementAsEvaluated(subfolderId) {
        const csrfTokenEl = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenEl ? csrfTokenEl.getAttribute('content') : '';

        fetch('/subfolders/' + subfolderId + '/review-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ review_status: 'evaluated' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const row = document.getElementById('subfolder-' + subfolderId);
                if (row) {
                    const badge = row.querySelector('.badge');
                    if (badge) {
                        badge.className = 'badge text-bg-success text-capitalize';
                        badge.textContent = 'evaluated';
                    }
                }
            }
        })
        .catch(err => console.error('Failed to update review status', err));
    }

    function openEditSubfolderModal(id, code, name, documentsNeeded) {
        if (id && typeof id === 'object' && id.dataset) {
            const btn = id;
            id = btn.dataset.id;
            code = btn.dataset.code;
            name = btn.dataset.name;
            documentsNeeded = btn.dataset.documentsNeeded;
        }
        document.getElementById('editSubfolderForm').action = '/subfolders/' + id;
        document.getElementById('editSubfolderCode').value = code || '';
        document.getElementById('editSubfolderName').value = name || '';
        const docNeededEl = document.getElementById('editSubfolderDocumentsNeeded');
        if (docNeededEl) {
            docNeededEl.value = documentsNeeded || '';
        }
        new bootstrap.Modal(document.getElementById('editSubfolderModal')).show();
    }

    initializeUploadProgress();

    function initializeUploadProgress() {
        const form = document.getElementById('uploadDocumentForm');

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            const submitButton = form.querySelector('button[type="submit"]');
            const panel = document.getElementById('uploadProgressPanel');
            const progressBar = document.getElementById('uploadProgressBar');
            const progressValue = document.getElementById('uploadProgressValue');
            const progressLabel = document.getElementById('uploadProgressLabel');
            const request = new XMLHttpRequest();

            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Uploading...';
            panel.classList.remove('d-none');
            progressLabel.textContent = 'Uploading files...';

            request.upload.addEventListener('progress', function (progressEvent) {
                if (!progressEvent.lengthComputable) {
                    return;
                }

                const percentage = Math.round((progressEvent.loaded / progressEvent.total) * 100);
                progressBar.style.width = percentage + '%';
                progressBar.setAttribute('aria-valuenow', percentage);
                progressValue.textContent = percentage + '%';
            });

            request.addEventListener('load', function () {
                const response = JSON.parse(request.responseText || '{}');

                if (request.status < 200 || request.status >= 300) {
                    finishUploadWithToast('error', 'Upload failed', response.message || 'The upload could not be completed.');
                    return;
                }

                progressBar.style.width = '100%';
                progressBar.setAttribute('aria-valuenow', '100');
                progressValue.textContent = '100%';
                finishUploadWithToast(
                    response.status === 'warning' ? 'warning' : 'success',
                    response.status === 'warning' ? 'Upload completed with warnings' : 'Upload complete',
                    response.message || 'PDF upload completed successfully.'
                );
            });

            request.addEventListener('error', function () {
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="bi bi-upload me-1"></i>' + submitButton.dataset.submitLabel;
                progressLabel.textContent = 'Upload failed. Please try again.';
            });

            request.open('POST', form.action);
            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            request.send(new FormData(form));
        });
    }

    function resetUploadProgress() {
        const panel = document.getElementById('uploadProgressPanel');
        const progressBar = document.getElementById('uploadProgressBar');
        const progressValue = document.getElementById('uploadProgressValue');
        const progressLabel = document.getElementById('uploadProgressLabel');
        const submitButton = document.querySelector('#uploadDocumentForm button[type="submit"]');

        panel.classList.add('d-none');
        progressBar.style.width = '0%';
        progressBar.setAttribute('aria-valuenow', '0');
        progressValue.textContent = '0%';
        progressLabel.textContent = 'Preparing upload...';
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="bi bi-upload me-1"></i>' + submitButton.dataset.submitLabel;
    }

    function finishUploadWithToast(icon, title, text) {
        Swal.fire({ toast: true, position: 'top-end', icon, title, text, showConfirmButton: false, timer: 2800, timerProgressBar: true });
        window.setTimeout(() => window.location.reload(), 3000);
    }

    function openEditParameterModal(id, code, title, description, sortOrder) {
        document.getElementById('editParameterForm').action = "/admin/parameters/" + id;
        document.getElementById('editParamCode').value = code;
        document.getElementById('editParamTitle').value = title;
        document.getElementById('editParamDescription').value = description;
        document.getElementById('editParamSort').value = sortOrder;
        new bootstrap.Modal(document.getElementById('editParameterModal')).show();
    }

    // ==================== BATCH / BULK SUBFOLDER CREATION (ONE SAVE) ====================
    let currentBatchCategoryParents = [];
    let currentBatchCategoryPrefix = 'S.';

    function getCategoryPrefix(categoryName) {
        const lower = (categoryName || '').toLowerCase();
        if (lower.includes('implementation')) {
            return 'I.';
        } else if (lower.includes('outcome')) {
            return 'O.';
        } else {
            return 'S.';
        }
    }

    function openBatchSubfolderModal(paramCatId, categoryName, paramCode, paramId) {
        let btn = null;
        if (paramCatId && typeof paramCatId === 'object') {
            btn = paramCatId;
            if (btn.dataset) {
                paramCatId = btn.dataset.paramCatId;
                categoryName = btn.dataset.categoryName;
                paramCode = btn.dataset.paramCode;
                paramId = btn.dataset.paramId;
            }
        }
        document.getElementById('batchModalCatName').innerText = categoryName || '';
        document.getElementById('batchModalParamCode').innerText = paramCode || '';
        document.getElementById('batchSubfolderForm').action = "/parameter-categories/" + paramCatId + "/subfolders/batch";

        currentBatchCategoryPrefix = getCategoryPrefix(categoryName);
        const presetBtn = document.getElementById('autoGeneratePresetBtn');
        if (presetBtn) {
            presetBtn.innerHTML = `<i class="bi bi-magic me-1"></i> Auto-Generate ${currentBatchCategoryPrefix}1 - ${currentBatchCategoryPrefix}5`;
        }

        currentBatchCategoryParents = [];
        let parameterCard = paramId ? document.getElementById('parameter-' + paramId) : null;
        if (!parameterCard && btn) {
            parameterCard = btn.closest('.parameter-card');
        }

        const categorySection = parameterCard 
            ? parameterCard.querySelector('.category-section-' + slugify(categoryName || ''))
            : document.querySelector('.category-section-' + slugify(categoryName || ''));

        if (categorySection) {
            categorySection.querySelectorAll('tr[id^="subfolder-"]').forEach(row => {
                const subfolderId = row.id.replace('subfolder-', '');
                const codeBadge = row.querySelector('.code-badge');
                const titleSpan = row.querySelector('.fw-bold.fs-7');
                if (codeBadge) {
                    currentBatchCategoryParents.push({
                        id: subfolderId,
                        code: codeBadge.textContent.trim(),
                        name: titleSpan ? titleSpan.textContent.trim() : ''
                    });
                }
            });
        }

        const tbody = document.getElementById('batchTableBody');
        if (tbody) {
            tbody.innerHTML = '';
            addBatchRow('', '', '');
            addBatchRow('', '', '');
            addBatchRow('', '', '');
        }

        new bootstrap.Modal(document.getElementById('batchSubfolderModal')).show();
    }

    function slugify(text) {
        return (text || '').toString().toLowerCase()
            .replace(/\s+/g, '_')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '_')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
    }

    function addBatchRow(code = '', name = '', docsNeeded = '', parentId = '') {
        const tbody = document.getElementById('batchTableBody');
        const rowIndex = tbody.children.length;

        let parentOptionsHtml = '<option value="">Top Level (No Parent)</option>';
        currentBatchCategoryParents.forEach(p => {
            const selected = (parentId && parentId == p.id) ? 'selected' : '';
            parentOptionsHtml += `<option value="${p.id}" ${selected}>Under ${p.code} - ${p.name.substring(0, 18)}...</option>`;
        });

        const tr = document.createElement('tr');
        tr.className = 'batch-row';
        tr.innerHTML = `
            <td>
                <input type="text" name="items[${rowIndex}][code]" class="form-control form-control-sm font-monospace fw-bold" placeholder="e.g. ${currentBatchCategoryPrefix}1" value="${code}" required>
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][name]" class="form-control form-control-sm" placeholder="e.g. Statement Title / Requirement" value="${name}" required>
            </td>
            <td>
                <textarea name="items[${rowIndex}][documents_needed]" class="form-control form-control-sm" rows="2" placeholder="• Notices of Meeting&#10;• Minutes of Meeting&#10;• Action Photos">${docsNeeded}</textarea>
            </td>
            <td>
                <select name="items[${rowIndex}][parent_id]" class="form-select form-select-sm" onchange="onBatchParentChange(this)">
                    ${parentOptionsHtml}
                </select>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-xs btn-outline-danger" onclick="removeBatchRow(this)" title="Remove Row">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        reindexBatchRows();
    }

    function onBatchParentChange(selectElem) {
        const row = selectElem.closest('tr');
        const codeInput = row.querySelector('input[name*="[code]"]');
        const parentId = selectElem.value;

        if (!parentId) {
            return;
        }

        const selectedParent = currentBatchCategoryParents.find(p => p.id == parentId);
        if (selectedParent) {
            const parentCode = selectedParent.code;
            const rows = document.querySelectorAll('#batchTableBody tr.batch-row');
            let childCount = 0;
            rows.forEach(r => {
                const s = r.querySelector('select[name*="[parent_id]"]');
                if (s && s.value == parentId) {
                    childCount++;
                }
            });
            codeInput.value = `${parentCode}.${childCount}`;
        }
    }

    function removeBatchRow(btn) {
        const tbody = document.getElementById('batchTableBody');
        if (tbody.children.length <= 1) {
            Swal.fire({ icon: 'warning', title: 'At least one row required', text: 'You must submit at least one item.', timer: 1500, showConfirmButton: false });
            return;
        }
        btn.closest('tr').remove();
        reindexBatchRows();
    }

    function reindexBatchRows() {
        const rows = document.querySelectorAll('#batchTableBody tr.batch-row');
        rows.forEach((row, index) => {
            row.querySelectorAll('input, select, textarea').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/items\[\d+\]/, `items[${index}]`));
                }
            });
        });
    }

    function generatePresetRows() {
        const tbody = document.getElementById('batchTableBody');
        tbody.innerHTML = '';
        for (let i = 1; i <= 5; i++) {
            addBatchRow(`${currentBatchCategoryPrefix}${i}`, '', '');
        }
    }

    const batchForm = document.getElementById('batchSubfolderForm');
    if (batchForm) {
        batchForm.addEventListener('submit', function () {
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Saving All...';
        });
    }
</script>
@endsection
