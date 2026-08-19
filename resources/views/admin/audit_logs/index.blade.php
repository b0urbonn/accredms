@extends('layouts.app')

@section('title', 'System Audit Logs')

@section('head')
<link href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    .audit-log-table-card .dt-container { padding: 1rem 1rem 0; }
    .audit-log-table-card .dt-layout-row { align-items: center; margin: 0 0 0.85rem; }
    .audit-log-table-card .dt-layout-cell { align-items: center; display: flex; }
    .audit-log-table-card .dt-layout-end { justify-content: flex-end; }
    .audit-log-table-card .dt-search label,
    .audit-log-table-card .dt-length label,
    .audit-log-table-card .dt-info { color: var(--text-secondary); font-size: 0.8rem; font-weight: 600; }
    .audit-log-table-card .dt-input,
    .audit-log-table-card .dt-select {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 6px;
        box-shadow: none;
        color: var(--text-primary);
        font-size: 0.8rem;
        min-height: 34px;
    }
    .audit-log-table-card .dt-search .dt-input { margin-left: 0.45rem; min-width: 220px; }
    .audit-log-table-card .dt-length .dt-select { margin: 0 0.35rem; }
    .audit-log-table-card .dt-input:focus,
    .audit-log-table-card .dt-select:focus { border-color: var(--accent); outline: 0; }
    .audit-log-table-card .dt-layout-row:last-child {
        border-top: 1px solid var(--border-color);
        margin: 0 -1rem;
        padding: 0.8rem 1rem;
    }
    .audit-log-table-card .pagination { margin: 0; }
    .audit-log-table-card .page-link { border-color: var(--border-color); color: var(--accent-text); font-size: 0.8rem; }
    .audit-log-table-card .active > .page-link { background-color: var(--accent); border-color: var(--accent); }
    .audit-log-table-card .table-responsive { overscroll-behavior-x: contain; }
    @media (max-width: 767.98px) {
        .audit-log-table-card .dt-layout-row { align-items: stretch; gap: 0.65rem; }
        .audit-log-table-card .dt-layout-cell,
        .audit-log-table-card .dt-layout-end { justify-content: flex-start; width: 100%; }
        .audit-log-table-card .dt-search .dt-input { min-width: 0; width: 100%; }
    }
</style>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold text-apple-dark mb-1">System Audit Logs & Traceability</h3>
        <p class="text-muted mb-0">Bank-grade security log tracking document uploads, previews, downloads, and user activities</p>
    </div>
</div>

<div class="card card-custom audit-log-table-card">
    <div class="table-responsive">
        <table id="auditLogsTable" class="table table-hover align-middle mb-0">
            <thead class="bg-light fs-7 text-uppercase text-secondary">
                <tr>
                    <th class="ps-3">Timestamp</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                    <th>User Agent</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="ps-3 text-nowrap fs-7 fw-semibold" data-order="{{ $log->created_at->timestamp }}">{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                        <td>
                            <div class="fw-bold text-dark fs-7">{{ $log->user->name ?? 'System' }}</div>
                            <small class="text-muted fs-8">{{ $log->user->email ?? '—' }}</small>
                        </td>
                        <td>
                            @if(in_array($log->action, ['upload', 'compress']))
                                <span class="badge bg-success">{{ $log->action }}</span>
                            @elseif(in_array($log->action, ['view', 'stream']))
                                <span class="badge bg-info text-dark">{{ $log->action }}</span>
                            @elseif($log->action === 'delete')
                                <span class="badge bg-danger">{{ $log->action }}</span>
                            @else
                                <span class="badge bg-secondary">{{ $log->action }}</span>
                            @endif
                        </td>
                        <td class="fs-7 text-dark">{{ $log->description }}</td>
                        <td><code class="fs-8">{{ $log->ip_address }}</code></td>
                        <td class="fs-8 text-secondary text-truncate" style="max-width: 200px;" title="{{ $log->user_agent }}">
                            {{ $log->user_agent }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No audit logs matching criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.min.js"></script>
<script>
    new DataTable('#auditLogsTable', {
        order: [[0, 'desc']],
        language: {
            search: 'Search',
            searchPlaceholder: 'User, action, IP, or description',
            lengthMenu: '_MENU_ per page',
            emptyTable: 'No audit logs found.',
            zeroRecords: 'No matching audit logs found.'
        },
        layout: {
            topStart: 'pageLength',
            topEnd: 'search',
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        pageLength: 20,
        lengthMenu: [10, 20, 50, 100]
    });
</script>
@endsection
