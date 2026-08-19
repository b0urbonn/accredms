@php
    $firstPhoto = $photos->first();
    $photoCount = $photos->count();
    $pdfVersion = optional($photos->max('updated_at'))->timestamp ?? time();
@endphp
<div class="doc-file-card fs-8 p-2 mb-2 border rounded">
    <div class="d-flex align-items-start gap-2 mb-1">
        <i class="bi bi-file-earmark-image-fill text-success fs-5 flex-shrink-0 mt-0.5"></i>
        <div class="min-w-0 flex-grow-1">
            <span class="fw-semibold text-dark fs-8 d-block text-break">
                Photo evidence
            </span>
            <small class="text-muted d-block">Photo evidence &middot; {{ $photoCount }} image(s)</small>
        </div>
    </div>
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mt-1 pt-1 border-top border-light-subtle">
        <span class="badge text-bg-light text-secondary border fs-8">One PDF evidence file</span>
        <div class="d-flex align-items-center gap-1">
            <button type="button" class="btn btn-xs btn-outline-success py-0.5 px-2 fs-8"
                data-subfolder-id="{{ $firstPhoto->subfolder_id }}"
                data-stream-url="{{ route('evidence_photos.pdf', $firstPhoto) }}?layout=single-row-v4&v={{ $pdfVersion }}"
                data-download-url="{{ route('evidence_photos.pdf.download', $firstPhoto) }}?layout=single-row-v4&v={{ $pdfVersion }}"
                data-filename="{{ $firstPhoto->subfolder->code ?: 'statement' }}-photo-evidence.pdf"
                data-meta="{{ $photoCount }} photo(s) | Statement photo evidence"
                data-photo-evidence="true"
                data-can-download="{{ auth()->user()->isAccreditor() && !config('accredms.accreditor_download_allowed') ? 'false' : 'true' }}"
                onclick="openPdfModalFromBtn(this)">
                <i class="bi bi-eye"></i> View PDF
            </button>
            @if(auth()->user()->isAdmin() || auth()->user()->isFaculty())
                <form action="{{ route('evidence_photos.destroy', $firstPhoto) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete all photos under this evidence tag?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-xs btn-outline-danger py-0.5 px-1.5 fs-8" title="Delete photo evidence"><i class="bi bi-trash"></i></button>
                </form>
            @endif
        </div>
    </div>
</div>
