@extends('layouts.app')

@section('title', 'Certificate of Program Compliance')

@section('content')
<div class="page-heading">
    <div>
        <h3 class="fw-bold mb-1">Certificate of Program Compliance</h3>
        <p class="mb-0">Official COPC document for viewing and authorized administration.</p>
    </div>
    @can('create', App\Models\CopcFile::class)
        <button type="button" class="btn btn-apple-green" onclick="openCopcUploadModal('{{ $copcFile ? 'Replace' : 'Upload' }}')"><i class="bi {{ $copcFile ? 'bi-arrow-repeat' : 'bi-upload' }} me-1"></i>{{ $copcFile ? 'Replace PDF' : 'Upload COPC PDF' }}</button>
    @endcan
</div>

<div class="card card-custom" style="max-width: 860px;">
    <div class="card-header bg-white border-bottom py-3 px-4"><strong><i class="bi bi-award text-success me-2"></i>COPC Document</strong></div>
    <div class="card-body p-4">
    @if($copcFile)
        @php($canDownload = !auth()->user()->isAccreditor() || config('accredms.accreditor_download_allowed'))
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex align-items-center justify-content-center rounded" style="width: 48px; height: 48px; background: #fcebea;"><i class="bi bi-file-earmark-pdf-fill text-danger fs-3"></i></div>
            <div class="flex-grow-1" style="min-width: 220px;"><button type="button" class="btn btn-link p-0 text-success fw-bold text-start" onclick="openPdfModal('{{ route('copc.stream', $copcFile) }}', '{{ route('copc.download', $copcFile) }}', '{{ addslashes($copcFile->original_filename) }}', 'Uploaded by {{ addslashes($copcFile->uploader->name ?? 'System') }} | {{ $copcFile->formatted_size }} | {{ $copcFile->created_at->format('M d, Y') }}', {{ $canDownload ? 'true' : 'false' }})">{{ $copcFile->original_filename }}</button><div class="text-muted fs-7 mt-1">PDF &middot; {{ $copcFile->formatted_size }} &middot; Uploaded {{ $copcFile->created_at->format('F d, Y') }} by {{ $copcFile->uploader->name ?? 'System' }}</div></div>
            <div class="d-flex gap-2"><button type="button" class="btn btn-outline-success" onclick="openPdfModal('{{ route('copc.stream', $copcFile) }}', '{{ route('copc.download', $copcFile) }}', '{{ addslashes($copcFile->original_filename) }}', 'Uploaded by {{ addslashes($copcFile->uploader->name ?? 'System') }} | {{ $copcFile->formatted_size }} | {{ $copcFile->created_at->format('M d, Y') }}', {{ $canDownload ? 'true' : 'false' }})"><i class="bi bi-eye me-1"></i> View PDF</button>@if($canDownload)<a class="btn btn-outline-secondary" href="{{ route('copc.download', $copcFile) }}" title="Download COPC PDF"><i class="bi bi-download"></i></a>@endif @can('update', $copcFile)<button type="button" class="btn btn-outline-primary" onclick="openCopcUploadModal('Replace')"><i class="bi bi-arrow-repeat me-1"></i> Replace</button>@endcan @can('delete', $copcFile)<form method="POST" action="{{ route('copc.destroy', $copcFile) }}" onsubmit="return confirm('Remove the current COPC PDF?');">@csrf @method('DELETE')<button class="btn btn-outline-danger" title="Delete COPC PDF"><i class="bi bi-trash"></i></button></form>@endcan</div>
        </div>
    @else
        <div class="text-center py-4 text-muted"><i class="bi bi-file-earmark-x fs-2 d-block mb-2"></i><strong class="d-block text-dark">No COPC file uploaded yet</strong><span class="fs-7">The Administrator can upload the official Certificate of Program Compliance PDF.</span></div>
    @endif
    </div>
</div>

@can('create', App\Models\CopcFile::class)
<div class="modal fade" id="copcUploadModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><form method="POST" action="{{ route('copc.store') }}" enctype="multipart/form-data" class="modal-content" onsubmit="return confirm(document.getElementById('copcModalAction').textContent === 'Replace' ? 'Replace the current COPC PDF?' : 'Upload this COPC PDF?');">@csrf
    <div class="modal-header bg-apple-dark text-white"><h5 class="modal-title"><i class="bi bi-file-earmark-arrow-up me-1"></i><span id="copcModalAction">Upload</span> COPC PDF</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><label for="copcFile" class="form-label fw-semibold">Certificate of Program Compliance PDF</label><input type="file" id="copcFile" name="file" class="form-control" accept="application/pdf,.pdf" required><div class="form-text">PDF only, maximum 25 MB. Uploading a new file replaces the current COPC document.</div></div>
    <div class="modal-footer bg-light"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-apple-green btn-sm"><i class="bi bi-upload me-1"></i><span id="copcSubmitAction">Upload</span> PDF</button></div>
</form></div></div>
@endcan
@endsection

@section('scripts')
<script>
function openCopcUploadModal(action) {
    document.getElementById('copcModalAction').textContent = action;
    document.getElementById('copcSubmitAction').textContent = action;
    document.getElementById('copcFile').value = '';
    new bootstrap.Modal(document.getElementById('copcUploadModal')).show();
}
</script>
@endsection