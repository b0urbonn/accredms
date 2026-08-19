@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="page-heading">
    <div>
        <h3 class="fw-bold mb-1">Notifications</h3>
        <p class="mb-0">Recent accreditation workflow updates and opened notifications.</p>
    </div>
</div>

<div class="card card-custom">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h5 class="section-title mb-0"><i class="bi bi-bell me-2 text-accent"></i>Notification History</h5>
    </div>
    <div class="list-group list-group-flush">
        @forelse($notifications as $notification)
            @php
                $destination = isset($notification->data['area_id'])
                    ? route('accreditor.show_area', $notification->data['area_id'])
                    : route('dashboard');
                $documentRequest = $documentRequests->get($notification->data['request_id'] ?? null);
            @endphp
            <div class="list-group-item py-3 {{ is_null($notification->read_at) ? 'bg-light' : '' }}">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <a href="{{ $destination }}" class="notification-item text-decoration-none text-reset flex-grow-1" data-notification-id="{{ $notification->id }}">
                        <span class="fw-semibold d-block">{{ $notification->data['title'] ?? 'Notification' }}</span>
                        <span class="text-secondary fs-7">{{ $notification->data['message'] ?? '' }}</span>
                    </a>
                    <div class="text-end text-nowrap">
                        @if(is_null($notification->read_at))
                            <span class="badge text-bg-primary">Unread</span>
                        @else
                            <span class="badge text-bg-secondary">Opened</span>
                        @endif
                        <small class="d-block text-muted fs-8 mt-1">{{ $notification->created_at->diffForHumans() }}</small>
                        @if($documentRequest)
                            <button type="button" class="btn btn-xs btn-outline-success mt-2"
                                data-statement="{{ $documentRequest->subfolder->code }} - {{ $documentRequest->subfolder->name }}"
                                data-requested-documents="{{ $documentRequest->requested_documents }}"
                                data-remarks="{{ $documentRequest->remarks }}"
                                data-requester="{{ $documentRequest->requester->name }}"
                                data-due-date="{{ $documentRequest->due_date?->format('M d, Y') }}"
                                onclick="openNotificationRequestModal(this)">
                                <i class="bi bi-eye me-1"></i> View Details
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5">No notifications yet.</div>
        @endforelse
    </div>
    @if($notifications->hasPages())
        <div class="card-footer bg-white border-top-0">{{ $notifications->links() }}</div>
    @endif
</div>

<div class="modal fade" id="notificationRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-apple-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-chat-square-text me-1"></i> Additional Documents Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body fs-7">
                <p class="mb-3">Statement: <strong id="notificationRequestStatement"></strong></p>
                <div class="mb-3"><span class="fw-semibold d-block">Missing documents</span><span id="notificationRequestedDocuments" class="text-secondary"></span></div>
                <div class="mb-3"><span class="fw-semibold d-block">Accreditor instructions</span><span id="notificationRequestRemarks" class="text-secondary"></span></div>
                <div class="text-muted fs-8">Requested by <span id="notificationRequester"></span> &middot; <span id="notificationDueDate"></span></div>
            </div>
            <div class="modal-footer bg-light py-2"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openNotificationRequestModal(button) {
        document.getElementById('notificationRequestStatement').textContent = button.dataset.statement;
        document.getElementById('notificationRequestedDocuments').textContent = button.dataset.requestedDocuments || 'See accreditor instructions.';
        document.getElementById('notificationRequestRemarks').textContent = button.dataset.remarks;
        document.getElementById('notificationRequester').textContent = button.dataset.requester;
        document.getElementById('notificationDueDate').textContent = button.dataset.dueDate ? 'Due: ' + button.dataset.dueDate : 'No due date set.';
        new bootstrap.Modal(document.getElementById('notificationRequestModal')).show();
    }
</script>
@endsection
