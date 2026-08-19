@extends('layouts.app')

@section('title', 'Area Assignments')

@section('head')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold text-apple-dark mb-1">Accreditation Area Assignments</h3>
        <p class="text-muted mb-0">Assign Faculty Handlers/Members and Accreditors to specific Accreditation Areas</p>
    </div>
    <button type="button" class="btn btn-apple-green shadow-sm" data-bs-toggle="modal" data-bs-target="#assignUserModal">
        <i class="bi bi-person-check me-1"></i> Assign User to Area
    </button>
</div>

<div class="row g-4">
    @foreach($areas as $area)
        <div class="col-md-6 col-lg-4">
            <div class="card card-custom h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <span class="badge bg-apple-dark fs-7">{{ $area->code }}</span>
                    <h6 class="fw-bold text-dark mb-0 text-truncate" style="max-width: 180px;">{{ $area->name }}</h6>
                </div>
                <div class="card-body p-3">
                    <h6 class="fs-8 text-uppercase fw-bold text-secondary mb-2">Assigned Personnel</h6>
                    <ul class="list-group list-group-flush fs-7">
                        @php
                            $roleWeights = [
                                'handler' => 1,
                                'co-handler' => 2,
                                'member' => 3,
                                'accreditor' => 4,
                            ];
                            $sortedUsers = $area->users->sortBy(function ($u) use ($roleWeights) {
                                return $roleWeights[$u->pivot->assignment_role] ?? 99;
                            });
                        @endphp
                        @forelse($sortedUsers as $u)
                            <li class="list-group-item px-0 py-2 d-flex align-items-center justify-content-between bg-transparent">
                                <div>
                                    <span class="fw-bold text-dark d-block mb-1">{{ $u->name }}</span>
                                    @if($u->pivot->assignment_role === 'handler')
                                        <span class="badge bg-success"><i class="bi bi-person-badge me-1"></i>Chairman</span>
                                    @elseif($u->pivot->assignment_role === 'co-handler')
                                        <span class="badge bg-primary"><i class="bi bi-person-badge me-1"></i>Co-Chairman</span>
                                    @elseif($u->pivot->assignment_role === 'accreditor')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-shield-check me-1"></i>Accreditor</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="bi bi-person me-1"></i>Member</span>
                                    @endif
                                </div>
                                <form action="{{ route('admin.assignments.destroy', [$area, $u, $u->pivot->assignment_role]) }}" method="POST" onsubmit="return confirm('Remove assignment of {{ addslashes($u->name) }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger py-1 px-2" title="Remove assignment"><i class="bi bi-trash"></i></button>
                                </form>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted py-3 fs-8 border-0">No users assigned to this area.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Assign User Modal -->
<div class="modal fade" id="assignUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.assignments.store') }}" class="modal-content">
            @csrf
            <div class="modal-header bg-apple-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-check me-1"></i> Assign Area Personnel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Select Area</label>
                    <select id="assignmentAreaSelect" name="area_id" class="form-select" required>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}">{{ $area->code }} — {{ $area->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Area Chairman</label>
                    <select id="assignmentChairmanSelect" name="chairman_id" class="form-select">
                        <option value="">Assign later</option>
                        @foreach($facultyUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted fs-8">The chairman is assigned as the Area Handler/Lead.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Area Co-Chairman</label>
                    <select id="assignmentCoChairmanSelect" name="co_chairman_ids[]" class="form-select" multiple>
                        @foreach($facultyUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted fs-8">Search, then click a name to add co-chairmen. Click the x on a selected name to remove it.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Area Members</label>
                    <select id="assignmentMembersSelect" name="member_ids[]" class="form-select" multiple>
                        @foreach($facultyUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted fs-8">Search, then click a name to add it. Click the x on a selected name to remove it.</small>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold fs-7">Area Accreditors</label>
                    <select id="assignmentAccreditorsSelect" name="accreditor_ids[]" class="form-select" multiple>
                        @foreach($accreditorUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted fs-8">Search, then click an accreditor's name to add them with view-only audit permissions.</small>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-apple-green">Save Area</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const areaAssignments = {
        @foreach($areas as $area)
            {{ $area->id }}: {
                chairman_id: {{ $area->handlers->first()->id ?? 'null' }},
                co_chairman_ids: @json($area->coHandlers->pluck('id')),
                member_ids: @json($area->members->pluck('id')),
                accreditor_ids: @json($area->accreditors->pluck('id'))
            },
        @endforeach
    };

    $(function () {
        const assignmentModal = $('#assignUserModal');
        const areaSelect = $('#assignmentAreaSelect');
        const chairmanSelect = $('#assignmentChairmanSelect');
        const coChairmanSelect = $('#assignmentCoChairmanSelect');
        const membersSelect = $('#assignmentMembersSelect');
        const accreditorsSelect = $('#assignmentAccreditorsSelect');

        function populateAreaAssignments(areaId) {
            const data = areaAssignments[areaId] || { chairman_id: null, co_chairman_ids: [], member_ids: [], accreditor_ids: [] };
            chairmanSelect.val(data.chairman_id || '').trigger('change');
            coChairmanSelect.val(data.co_chairman_ids || []).trigger('change');
            membersSelect.val(data.member_ids || []).trigger('change');
            accreditorsSelect.val(data.accreditor_ids || []).trigger('change');
        }

        assignmentModal.on('shown.bs.modal', function () {
            if (!chairmanSelect.hasClass('select2-hidden-accessible')) {
                areaSelect.select2({ dropdownParent: assignmentModal, width: '100%' });
                chairmanSelect.select2({ dropdownParent: assignmentModal, placeholder: 'Search and select chairman', allowClear: true, width: '100%' });
                coChairmanSelect.select2({ dropdownParent: assignmentModal, placeholder: 'Search and select co-chairmen', closeOnSelect: false, width: '100%' });
                membersSelect.select2({ dropdownParent: assignmentModal, placeholder: 'Search and select members', closeOnSelect: false, width: '100%' });
                accreditorsSelect.select2({ dropdownParent: assignmentModal, placeholder: 'Search and select accreditors', closeOnSelect: false, width: '100%' });
            }
            populateAreaAssignments(areaSelect.val());
        });

        areaSelect.on('change', function () {
            populateAreaAssignments($(this).val());
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
