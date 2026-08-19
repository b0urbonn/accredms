@extends('layouts.app')

@section('title', 'User Accounts Management')

@section('head')
<link href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    .users-table-card .dt-container { padding: 1rem 1rem 0; }
    .users-table-card .dt-layout-row { align-items: center; margin: 0 0 0.85rem; }
    .users-table-card .dt-layout-cell { display: flex; align-items: center; }
    .users-table-card .dt-layout-end { justify-content: flex-end; }
    .users-table-card .dt-search label,
    .users-table-card .dt-length label,
    .users-table-card .dt-info {
        color: var(--text-secondary);
        font-size: 0.8rem;
        font-weight: 600;
    }
    .users-table-card .dt-input,
    .users-table-card .dt-select {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 6px;
        box-shadow: none;
        color: var(--text-primary);
        font-size: 0.8rem;
        min-height: 34px;
    }
    .users-table-card .dt-search .dt-input { margin-left: 0.45rem; min-width: 220px; }
    .users-table-card .dt-length .dt-select { margin: 0 0.35rem; }
    .users-table-card .dt-input:focus,
    .users-table-card .dt-select:focus { border-color: var(--accent); outline: 0; }
    .users-table-card .dt-layout-row:last-child {
        border-top: 1px solid var(--border-color);
        margin: 0 -1rem;
        padding: 0.8rem 1rem;
    }
    .users-table-card .pagination { margin: 0; }
    .users-table-card .page-link { border-color: var(--border-color); color: var(--accent-text); font-size: 0.8rem; }
    .users-table-card .active > .page-link { background-color: var(--accent); border-color: var(--accent); }
    .users-table-card td:last-child { white-space: nowrap; }
    @media (max-width: 767.98px) {
        .users-table-card .dt-layout-row { align-items: stretch; gap: 0.65rem; }
        .users-table-card .dt-layout-cell,
        .users-table-card .dt-layout-end { justify-content: flex-start; width: 100%; }
        .users-table-card .dt-search .dt-input { min-width: 0; width: 100%; }
    }
</style>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold text-apple-dark mb-1">User Accounts Management</h3>
        <p class="text-muted mb-0">Manage Faculty, Accreditor, and Administrator user credentials and access states</p>
    </div>
    <button type="button" class="btn btn-apple-green shadow-sm" data-bs-toggle="modal" data-bs-target="#createUserModal">
        <i class="bi bi-person-plus me-1"></i> Add New User
    </button>
</div>

<div class="card card-custom users-table-card">
    <div class="table-responsive">
        <table id="usersTable" class="table table-hover align-middle mb-0">
            <thead class="bg-light fs-7 text-uppercase text-secondary">
                <tr>
                    <th class="ps-3">User / Employee</th>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th>System Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-apple-dark text-white fw-bold d-flex align-items-center justify-content-center" style="width:36px; height:36px; font-size:0.85rem;">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <code class="fw-bold text-dark fs-8 d-block">{{ $user->employee_id ?? 'N/A' }}</code>
                                </div>
                            </div>
                        </td>
                        <td class="fw-bold text-dark">{{ $user->name }}</td>
                        <td class="text-secondary fs-7">{{ $user->email }}</td>
                        <td>
                            @if($user->isAdmin())
                                <span class="badge badge-role badge-role-admin">Administrator</span>
                            @elseif($user->isFaculty())
                                <span class="badge badge-role badge-role-faculty">Faculty</span>
                            @elseif($user->isAccreditor())
                                <span class="badge badge-role badge-role-accreditor">Accreditor</span>
                            @else
                                <span class="badge badge-role bg-secondary">User</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.users.toggle_status', $user) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-xs {{ $user->status === 'active' ? 'btn-success' : 'btn-secondary' }} py-1 px-2 fs-8 fw-semibold rounded-pill" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                    <i class="bi {{ $user->status === 'active' ? 'bi-check-circle-fill' : 'bi-pause-circle-fill' }} me-1"></i>
                                    {{ ucfirst($user->status) }}
                                </button>
                            </form>
                        </td>
                        <td class="fs-7 text-muted">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</td>
                        <td class="text-end pe-3">
                            <!-- Edit User Modal Trigger -->
                            <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                onclick="openEditUserModal(
                                    {{ $user->id }},
                                    '{{ addslashes($user->employee_id ?? '') }}',
                                    '{{ addslashes($user->name) }}',
                                    '{{ addslashes($user->email) }}',
                                    '{{ $user->roles->first()->name ?? 'faculty' }}',
                                    '{{ $user->status }}'
                                )">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>

                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete user account {{ addslashes($user->name) }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" {{ $user->id === auth()->id() ? 'disabled' : '' }}><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create User -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.users.store') }}" class="modal-content">
            @csrf
            <div class="modal-header bg-apple-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-1"></i> Create User Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Employee / User ID</label>
                    <input type="text" name="employee_id" class="form-control" placeholder="e.g. FAC-002" value="{{ old('employee_id') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Prof. Maria Clara" value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="e.g. mclara@cics.marsu.edu.ph" value="{{ old('email') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">System Role</label>
                    <select name="role" class="form-select" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7" for="createUserPassword">Password</label>
                    <div class="input-group">
                        <input type="password" id="createUserPassword" name="password" class="form-control" autocomplete="new-password" aria-describedby="passwordStrengthHelp" required>
                        <button class="btn btn-outline-secondary" type="button" id="toggleCreatePassword" aria-label="Show password" title="Show password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div id="passwordStrengthHelp" class="password-strength mt-2" aria-live="polite">
                        <div class="progress mb-2"><div id="passwordStrengthBar" class="progress-bar" style="width: 0%"></div></div>
                        <div class="d-flex flex-wrap gap-2 fs-8 text-muted">
                            <span data-password-rule="length"><i class="bi bi-circle me-1"></i>8+ characters</span>
                            <span data-password-rule="case"><i class="bi bi-circle me-1"></i>Upper & lowercase</span>
                            <span data-password-rule="number"><i class="bi bi-circle me-1"></i>Number</span>
                            <span data-password-rule="symbol"><i class="bi bi-circle me-1"></i>Special character</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" id="createUserSubmit" class="btn btn-sm btn-apple-green" disabled>Save User</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editUserForm" method="POST" action="" class="modal-content">
            @csrf
            @method('PUT')
            <input type="hidden" name="form_context" value="edit">
            <input type="hidden" name="editing_user_id" id="editingUserId" value="">
            <div class="modal-header bg-apple-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-1"></i> Edit User Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Employee / User ID</label>
                    <input type="text" id="editEmployeeId" name="employee_id" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Full Name</label>
                    <input type="text" id="editName" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Email Address</label>
                    <input type="email" id="editEmail" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">System Role</label>
                    <select id="editRole" name="role" class="form-select" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7">Status</label>
                    <select id="editStatus" name="status" class="form-select" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7" for="editUserPassword">New Password <span class="text-muted fw-normal">(leave blank to keep current)</span></label>
                    <div class="input-group">
                        <input type="password" id="editUserPassword" name="password" class="form-control" autocomplete="new-password" aria-describedby="editPasswordStrengthHelp">
                        <button class="btn btn-outline-secondary" type="button" id="toggleEditPassword" aria-label="Show password" title="Show password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div id="editPasswordStrengthHelp" class="password-strength mt-2 d-none" aria-live="polite">
                        <div class="progress mb-2"><div id="editPasswordStrengthBar" class="progress-bar" style="width: 0%"></div></div>
                        <div class="d-flex flex-wrap gap-2 fs-8 text-muted">
                            <span data-edit-password-rule="length"><i class="bi bi-circle me-1"></i>8+ characters</span>
                            <span data-edit-password-rule="case"><i class="bi bi-circle me-1"></i>Upper & lowercase</span>
                            <span data-edit-password-rule="number"><i class="bi bi-circle me-1"></i>Number</span>
                            <span data-edit-password-rule="symbol"><i class="bi bi-circle me-1"></i>Special character</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-apple-green">Update User</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.min.js"></script>
<script>
    new DataTable('#usersTable', {
        columnDefs: [{ orderable: false, targets: 6 }],
        language: {
            search: 'Search',
            searchPlaceholder: 'Name, email, or employee ID',
            lengthMenu: '_MENU_ per page',
            emptyTable: 'No users found.',
            zeroRecords: 'No matching users found.'
        },
        layout: {
            topStart: 'pageLength',
            topEnd: 'search',
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        pageLength: 10,
        lengthMenu: [10, 25, 50]
    });

    const createPasswordInput = document.getElementById('createUserPassword');
    const createUserSubmit = document.getElementById('createUserSubmit');
    const passwordStrengthBar = document.getElementById('passwordStrengthBar');
    const passwordRuleElements = document.querySelectorAll('[data-password-rule]');
    const editPasswordInput = document.getElementById('editUserPassword');
    const editPasswordStrengthHelp = document.getElementById('editPasswordStrengthHelp');
    const editPasswordStrengthBar = document.getElementById('editPasswordStrengthBar');
    const editPasswordRuleElements = document.querySelectorAll('[data-edit-password-rule]');

    function updatePasswordStrength() {
        const password = createPasswordInput.value;
        const rules = {
            length: password.length >= 8,
            case: /[a-z]/.test(password) && /[A-Z]/.test(password),
            number: /\d/.test(password),
            symbol: /[^A-Za-z0-9]/.test(password)
        };
        const passedRules = Object.values(rules).filter(Boolean).length;
        const isStrong = passedRules === Object.keys(rules).length;

        passwordStrengthBar.style.width = (passedRules / 4 * 100) + '%';
        passwordStrengthBar.className = 'progress-bar ' + (isStrong ? 'bg-success' : passedRules > 1 ? 'bg-warning' : 'bg-danger');
        createUserSubmit.disabled = !isStrong;

        passwordRuleElements.forEach((element) => {
            const passes = rules[element.dataset.passwordRule];
            element.classList.toggle('text-success', passes);
            element.classList.toggle('text-muted', !passes);
            element.querySelector('i').className = passes ? 'bi bi-check-circle-fill me-1' : 'bi bi-circle me-1';
        });
    }

    createPasswordInput.addEventListener('input', updatePasswordStrength);
    document.getElementById('toggleCreatePassword').addEventListener('click', function () {
        const showPassword = createPasswordInput.type === 'password';
        createPasswordInput.type = showPassword ? 'text' : 'password';
        this.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
        this.setAttribute('title', showPassword ? 'Hide password' : 'Show password');
        this.querySelector('i').className = showPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
    });

    function updateEditPasswordStrength() {
        const password = editPasswordInput.value;
        editPasswordStrengthHelp.classList.toggle('d-none', password.length === 0);
        if (password.length === 0) return;

        const rules = {
            length: password.length >= 8,
            case: /[a-z]/.test(password) && /[A-Z]/.test(password),
            number: /\d/.test(password),
            symbol: /[^A-Za-z0-9]/.test(password)
        };
        const passedRules = Object.values(rules).filter(Boolean).length;
        const isStrong = passedRules === Object.keys(rules).length;
        editPasswordStrengthBar.style.width = (passedRules / 4 * 100) + '%';
        editPasswordStrengthBar.className = 'progress-bar ' + (isStrong ? 'bg-success' : passedRules > 1 ? 'bg-warning' : 'bg-danger');

        editPasswordRuleElements.forEach((element) => {
            const passes = rules[element.dataset.editPasswordRule];
            element.classList.toggle('text-success', passes);
            element.classList.toggle('text-muted', !passes);
            element.querySelector('i').className = passes ? 'bi bi-check-circle-fill me-1' : 'bi bi-circle me-1';
        });
    }

    editPasswordInput.addEventListener('input', updateEditPasswordStrength);
    document.getElementById('toggleEditPassword').addEventListener('click', function () {
        const showPassword = editPasswordInput.type === 'password';
        editPasswordInput.type = showPassword ? 'text' : 'password';
        this.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
        this.setAttribute('title', showPassword ? 'Hide password' : 'Show password');
        this.querySelector('i').className = showPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
    });

    @if($errors->any() && old('form_context') === 'edit')
        openEditUserModal(
            {{ (int) old('editing_user_id') }},
            @json(old('employee_id')),
            @json(old('name')),
            @json(old('email')),
            @json(old('role')),
            @json(old('status'))
        );
    @elseif($errors->any())
        new bootstrap.Modal(document.getElementById('createUserModal')).show();
    @endif

    function openEditUserModal(id, employeeId, name, email, role, status) {
        document.getElementById('editUserForm').action = "/admin/users/" + id;
        document.getElementById('editingUserId').value = id;
        document.getElementById('editEmployeeId').value = employeeId;
        document.getElementById('editName').value = name;
        document.getElementById('editEmail').value = email;
        document.getElementById('editRole').value = role;
        document.getElementById('editStatus').value = status;
        editPasswordInput.value = '';
        editPasswordStrengthHelp.classList.add('d-none');
        new bootstrap.Modal(document.getElementById('editUserModal')).show();
    }
</script>
@endsection
