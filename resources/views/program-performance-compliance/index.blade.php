@extends('layouts.app')

@section('title', 'Program Performance Compliance')

@section('content')
<div class="page-heading">
    <div>
        <h3 class="fw-bold mb-1">Program Performance Compliance</h3>
        <p class="mb-0">Maintain one private Program Performance Profile PDF for each accreditation Area.</p>
    </div>
</div>

<div class="card card-custom">
    <div class="card-header bg-white border-bottom py-3 px-3 d-flex justify-content-between align-items-center">
        <div><strong>Program Performance Profile Files</strong><div class="text-muted fs-8 mt-1">Areas I through X only. PDF files up to 25 MB.</div></div>
        <span class="badge bg-apple-dark">10 Areas</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light fs-7 text-uppercase text-secondary"><tr><th class="ps-3" style="width: 38%;">Area Name</th><th class="pe-3">Uploaded File</th></tr></thead>
            <tbody>
            @foreach($rows as $row)
                <tr>
                    <td class="ps-3">
                        <div class="d-flex align-items-center gap-2"><span class="badge bg-apple-dark">Area {{ $row->number }}</span><strong>{{ $row->area?->name ?? 'Area ' . $row->number }}</strong></div>
                        @if($row->area)<small class="text-muted ms-1">{{ $row->area->code }}</small>@else<small class="text-warning ms-1">Area structure has not been created yet.</small>@endif
                    </td>
                    <td class="pe-3">
                    @if($row->file && $row->canView)
                        @php($canDownload = !auth()->user()->isAccreditor() || config('accredms.accreditor_download_allowed'))
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <i class="bi bi-file-earmark-pdf-fill text-danger fs-4"></i>
                            <div class="me-auto" style="min-width: 160px;">
                                <button type="button" class="btn btn-link text-start text-success fw-semibold p-0 text-truncate" style="max-width: 300px;" title="{{ $row->file->original_filename }}" onclick="openPdfModal('{{ route('program-performance-compliance.stream', $row->file) }}', '{{ route('program-performance-compliance.download', $row->file) }}', '{{ addslashes($row->file->original_filename) }}', 'Uploaded by {{ addslashes($row->file->uploader->name ?? 'System') }} | {{ $row->file->formatted_size }} | {{ $row->file->created_at->format('M d, Y') }}', {{ $canDownload ? 'true' : 'false' }})">{{ $row->file->original_filename }}</button>
                                <small class="d-block text-muted fs-8">PDF &middot; {{ $row->file->formatted_size }} &middot; {{ $row->file->created_at->format('M d, Y') }} by {{ $row->file->uploader->name ?? 'System' }}</small>
                            </div>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-success" title="View file" onclick="openPdfModal('{{ route('program-performance-compliance.stream', $row->file) }}', '{{ route('program-performance-compliance.download', $row->file) }}', '{{ addslashes($row->file->original_filename) }}', 'Uploaded by {{ addslashes($row->file->uploader->name ?? 'System') }} | {{ $row->file->formatted_size }} | {{ $row->file->created_at->format('M d, Y') }}', {{ $canDownload ? 'true' : 'false' }})"><i class="bi bi-eye"></i> View</button>
                                @if($canDownload)<a href="{{ route('program-performance-compliance.download', $row->file) }}" class="btn btn-sm btn-outline-secondary" title="Download file"><i class="bi bi-download"></i></a>@endif
                                @if($row->canManage)<button type="button" class="btn btn-sm btn-outline-primary" title="Replace file" onclick="openPppUploadModal('{{ route('program-performance-compliance.update', $row->file) }}', 'PUT', '{{ addslashes($row->area->code) }}', 'Replace')"><i class="bi bi-arrow-repeat"></i> Replace</button>@endif
                                @can('delete', $row->file)<form method="POST" action="{{ route('program-performance-compliance.destroy', $row->file) }}" onsubmit="return confirm('Remove the PPP file for {{ addslashes($row->area->code) }}?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" title="Delete file"><i class="bi bi-trash"></i></button></form>@endcan
                            </div>
                        </div>
                    @elseif($row->area && $row->canManage)
                        <div class="d-flex align-items-center justify-content-between gap-2"><span class="text-muted fs-7"><i class="bi bi-file-earmark-x me-1"></i>No file uploaded.</span><button type="button" class="btn btn-sm btn-apple-green" onclick="openPppUploadModal('{{ route('program-performance-compliance.store', $row->area) }}', 'POST', '{{ addslashes($row->area->code) }}', 'Upload')"><i class="bi bi-upload me-1"></i> Upload File</button></div>
                    @elseif($row->area)
                        <span class="text-muted fs-7"><i class="bi bi-lock me-1"></i>No file uploaded or you do not have access to this Area.</span>
                    @else
                        <span class="text-muted fs-7">Unavailable until this Area is added in Area Structure.</span>
                    @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="pppUploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><form id="pppUploadForm" method="POST" enctype="multipart/form-data" class="modal-content">
        @csrf <input type="hidden" id="pppMethod" name="_method">
        <div class="modal-header bg-apple-dark text-white"><h5 class="modal-title"><i class="bi bi-file-earmark-arrow-up me-1"></i><span id="pppModalAction">Upload</span> PPP File</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><p class="text-muted fs-7">Area: <strong id="pppAreaCode"></strong></p><label for="pppFile" class="form-label fw-semibold">Program Performance Profile PDF</label><input id="pppFile" name="file" type="file" class="form-control" accept="application/pdf,.pdf" required><div class="form-text">PDF only, maximum 25 MB. Replacing permanently removes the current stored file.</div></div>
        <div class="modal-footer bg-light"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-apple-green btn-sm"><i class="bi bi-upload me-1"></i><span id="pppSubmitAction">Upload</span> File</button></div>
    </form></div>
</div>
@endsection

@section('scripts')
<script>
function openPppUploadModal(action, method, areaCode, verb) {
    document.getElementById('pppUploadForm').action = action;
    document.getElementById('pppMethod').value = method === 'PUT' ? 'PUT' : '';
    document.getElementById('pppAreaCode').textContent = areaCode;
    document.getElementById('pppModalAction').textContent = verb;
    document.getElementById('pppSubmitAction').textContent = verb;
    document.getElementById('pppFile').value = '';
    new bootstrap.Modal(document.getElementById('pppUploadModal')).show();
}
</script>
@endsection