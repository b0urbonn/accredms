@extends('layouts.app')

@section('title', 'Manage Areas')

@section('head')
<link href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
    .areas-table-card .dt-container { padding: 1rem 1rem 0; }
    .areas-table-card .dt-layout-row { align-items: center; margin: 0 0 0.85rem; }
    .areas-table-card .dt-layout-cell { display: flex; align-items: center; }
    .areas-table-card .dt-layout-end { justify-content: flex-end; }
    .areas-table-card .dt-search label,
    .areas-table-card .dt-length label,
    .areas-table-card .dt-info {
        color: var(--text-secondary);
        font-size: 0.8rem;
        font-weight: 600;
    }
    .areas-table-card .dt-input,
    .areas-table-card .dt-select {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 6px;
        box-shadow: none;
        color: var(--text-primary);
        font-size: 0.8rem;
        min-height: 34px;
    }
    .areas-table-card .dt-search .dt-input { margin-left: 0.45rem; min-width: 220px; }
    .areas-table-card .dt-length .dt-select { margin: 0 0.35rem; }
    .areas-table-card .dt-input:focus,
    .areas-table-card .dt-select:focus { border-color: var(--accent); outline: 0; }
    .areas-table-card .dt-layout-row:last-child {
        border-top: 1px solid var(--border-color);
        margin: 0 -1rem;
        padding: 0.8rem 1rem;
    }
    .areas-table-card .pagination { margin: 0; }
    .areas-table-card .page-link { border-color: var(--border-color); color: var(--accent-text); font-size: 0.8rem; }
    .areas-table-card .active > .page-link { background-color: var(--accent); border-color: var(--accent); }
    .areas-table-card td:last-child { white-space: nowrap; }
    @media (max-width: 767.98px) {
        .areas-table-card .dt-layout-row { align-items: stretch; gap: 0.65rem; }
        .areas-table-card .dt-layout-cell,
        .areas-table-card .dt-layout-end { justify-content: flex-start; width: 100%; }
        .areas-table-card .dt-search .dt-input { min-width: 0; width: 100%; }
    }
</style>
@endsection

@section('content')
<div class="page-heading">
    <div>
        <h3 class="fw-bold mb-1">Accreditation Areas</h3>
        <p class="mb-0">Define areas and maintain their parameter structures.</p>
    </div>
    <button type="button" class="btn btn-apple-green" data-bs-toggle="modal" data-bs-target="#createAreaModal">
        <i class="bi bi-plus-lg me-1"></i> Create New Area
    </button>
</div>

<div class="card card-custom areas-table-card">
    <div class="table-responsive">
        <table id="areasTable" class="table table-hover align-middle mb-0" style="width: 100%;">
            <thead class="bg-light fs-7 text-uppercase text-secondary">
                <tr>
                    <th class="ps-3">Code</th>
                    <th>Area Name</th>
                    <th>Assigned Personnel</th>
                    <th>Parameters</th>
                    <th>Status</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($areas as $area)
                    <tr>
                        <td class="ps-3"><span class="badge bg-apple-dark fs-7">{{ $area->code }}</span></td>
                        <td class="fw-bold text-dark">{{ $area->name }}</td>
                        <td>
                            <div class="fs-8">
                                <div class="mb-1 text-nowrap"><i class="bi bi-person-badge text-success me-1"></i><strong class="text-dark">Chair:</strong> {{ $area->handlers->pluck('name')->first() ?? 'Not assigned' }}</div>
                                <div class="text-nowrap"><i class="bi bi-person-badge text-primary me-1"></i><strong class="text-dark">Co-Chair:</strong> {{ $area->coHandlers->pluck('name')->first() ?? 'Not assigned' }}</div>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark border fs-8 fw-semibold"><i class="bi bi-list-nested me-1 text-secondary"></i>{{ $area->parameters_count }} parameters</span></td>
                        <td>
                            @if($area->status === 'submission_ready')
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Submission Ready</span>
                            @elseif($area->status === 'active')
                                <span class="badge bg-primary">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <a href="{{ route('admin.areas.show', $area) }}" class="btn btn-sm btn-outline-success me-1">
                                <i class="bi bi-diagram-3 me-1"></i> Parameters
                            </a>

                            <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                onclick="openEditAreaModal(
                                    {{ $area->id }},
                                    '{{ addslashes($area->code) }}',
                                    '{{ addslashes($area->name) }}',
                                    '{{ addslashes($area->description ?? '') }}',
                                    '{{ $area->status }}'
                                )">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>

                            @if($area->parameters_count > 0)
                                <button type="button" class="btn btn-sm btn-outline-danger" disabled
                                    title="Delete is unavailable while this area contains parameters.">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            @else
                                <form action="{{ route('admin.areas.destroy', $area) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete Area {{ addslashes($area->code) }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No areas created yet. Click 'Create New Area' to start.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create Area -->
<div class="modal fade" id="createAreaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.areas.store') }}" class="modal-content">
            @csrf
            <div class="modal-header bg-apple-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-folder-plus me-1"></i> Create Accreditation Area</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Area Code</label>
                    <input type="text" name="code" class="form-control" placeholder="e.g. AREA-IV" required>
                    <small class="text-muted fs-8">Unique identifier for the area.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Area Title/Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Support to Students" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Brief summary of requirements for this area..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Area Chairman</label>
                    <select id="areaChairmanSelect" name="chairman_id" class="form-select">
                        <option value="">Assign later</option>
                        @foreach($assignableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted fs-8">The chairman is assigned as the Area Handler/Lead.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Area Co-Chairman</label>
                    <select id="areaCoChairmanSelect" name="co_chairman_ids[]" class="form-select" multiple>
                        @foreach($assignableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted fs-8">Search, then click a name to add co-chairmen. Click the x on a selected name to remove it.</small>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold fs-7">Area Members</label>
                    <select id="areaMembersSelect" name="member_ids[]" class="form-select" multiple>
                        @foreach($assignableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted fs-8">Search, then click a name to add it. Click the x on a selected name to remove it.</small>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-apple-green">Save Area</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Area -->
<div class="modal fade" id="editAreaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editAreaForm" method="POST" action="" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header bg-apple-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-1"></i> Edit Accreditation Area</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Area Code</label>
                    <input type="text" id="editAreaCode" name="code" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Area Title/Name</label>
                    <input type="text" id="editAreaName" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Description</label>
                    <textarea id="editAreaDescription" name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Status</label>
                    <select id="editAreaStatus" name="status" class="form-select" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="submission_ready">Submission Ready</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-apple-green">Update Area</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(function () {
        if ($('#areasTable').length) {
            new DataTable('#areasTable', {
                language: {
                    search: 'Search',
                    searchPlaceholder: 'Search code, area name, personnel...',
                    lengthMenu: '_MENU_ per page',
                    emptyTable: 'No accreditation areas found.',
                    zeroRecords: 'No matching accreditation areas found.'
                },
                layout: {
                    topStart: 'pageLength',
                    topEnd: 'search',
                    bottomStart: 'info',
                    bottomEnd: 'paging'
                },
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                columnDefs: [
                    { orderable: false, targets: [2, 5] }
                ]
            });
        }
        const createAreaModal = $('#createAreaModal');
        const chairmanSelect = $('#areaChairmanSelect');
        const coChairmanSelect = $('#areaCoChairmanSelect');
        const membersSelect = $('#areaMembersSelect');

        createAreaModal.on('shown.bs.modal', function () {
            if (!chairmanSelect.hasClass('select2-hidden-accessible')) {
                chairmanSelect.select2({
                    dropdownParent: createAreaModal,
                    placeholder: 'Search and select chairman',
                    allowClear: true,
                    width: '100%'
                });
                coChairmanSelect.select2({
                    dropdownParent: createAreaModal,
                    placeholder: 'Search and select co-chairmen',
                    closeOnSelect: false,
                    width: '100%'
                });
                membersSelect.select2({
                    dropdownParent: createAreaModal,
                    placeholder: 'Search and select members',
                    closeOnSelect: false,
                    width: '100%'
                });
            }
        });

        // When Chairman changes, remove that person from Co-Chairman and Members
        chairmanSelect.on('change', function () {
            const chairmanId = $(this).val();
            if (chairmanId) {
                const selectedCo = coChairmanSelect.val() || [];
                coChairmanSelect.val(selectedCo.filter((id) => id !== chairmanId)).trigger('change');
                const selectedMembers = membersSelect.val() || [];
                membersSelect.val(selectedMembers.filter((id) => id !== chairmanId)).trigger('change');
            }
        });

        // When Co-Chairmen change, remove selected co-chairmen from Members
        coChairmanSelect.on('change', function () {
            const coIds = (coChairmanSelect.val() || []).map(id => String(id));
            const selectedMembers = membersSelect.val() || [];
            membersSelect.val(selectedMembers.filter((id) => !coIds.includes(String(id)))).trigger('change');
        });
    });

    function openEditAreaModal(id, code, name, description, status) {
        document.getElementById('editAreaForm').action = "/admin/areas/" + id;
        document.getElementById('editAreaCode').value = code;
        document.getElementById('editAreaName').value = name;
        document.getElementById('editAreaDescription').value = description;
        document.getElementById('editAreaStatus').value = status;
        new bootstrap.Modal(document.getElementById('editAreaModal')).show();
    }
</script>
<style>
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        border: 1px solid var(--border-subtle);
        border-radius: 6px;
        min-height: 38px;
    }
    .select2-container--default .select2-selection--single { padding: 0.2rem 0.15rem; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--text-primary); font-size: 0.875rem; line-height: 28px; padding-left: 0.55rem; }
    .select2-container--default .select2-selection--multiple .select2-selection__choice { background: var(--accent-light); border: 1px solid #cfe1b9; border-radius: 4px; color: var(--accent-text); font-size: 0.76rem; font-weight: 600; margin-top: 5px; padding: 1px 5px; }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove { border-right: 0; color: var(--accent-text); margin-right: 3px; }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover { background: transparent; color: #b02a37; }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--focus .select2-selection--multiple { border-color: var(--accent); box-shadow: 0 0 0 0.22rem rgba(120, 162, 47, 0.18); }
    .select2-results__option { font-size: 0.82rem; padding: 0.5rem 0.7rem; }
    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable { background: var(--accent); }
</style>
@endsection
