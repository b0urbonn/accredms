{{-- Recursive subfolder row with expand/collapse child sub-items --}}
@php
    $hasChildren = $subfolder->children->count() > 0;
    $hasDocuments = $subfolder->documents->isNotEmpty();
    $hasPhotos = $subfolder->photos->isNotEmpty();
    $hasEvidence = $hasDocuments || $hasPhotos;
    $canDelete = !$subfolder->hasDocumentsInTree();
    $reviewStatus = ($subfolder->evidence_status === 'approved')
        ? 'approved'
        : ($subfolder->review_status ?? ($hasEvidence ? 'under_review' : 'no_evidence'));
    if ($reviewStatus === 'draft') {
        $reviewStatus = 'no_evidence';
    }
    $reviewStatusClass = match ($reviewStatus) {
        'approved' => 'text-bg-success',
        'under_review' => 'text-bg-info',
        'additional_documents_requested' => 'text-bg-warning',
        'resubmitted' => 'text-bg-primary',
        'evaluated' => 'text-bg-success',
        default => 'text-bg-secondary',
    };
    $reviewStatusLabel = $reviewStatus === 'resubmitted'
        ? 'Complied / Uploaded'
        : str_replace('_', ' ', $reviewStatus);
    $latestDocumentRequest = $subfolder->additionalDocumentRequests->sortByDesc('created_at')->first();
    $latestResubmission = $subfolder->additionalDocumentRequests
        ->whereIn('status', ['resubmitted', 'fulfilled'])
        ->sortByDesc('created_at')
        ->first();
    $openDocumentRequest = $subfolder->additionalDocumentRequests->firstWhere('status', 'open');
    $rowId = 'subfolder-' . $subfolder->id;
    $childrenId = 'children-' . $subfolder->id;
    $indentPx = $depth * 28;
@endphp

<tr id="{{ $rowId }}" class="{{ $depth > 0 ? 'subfolder-child-row' : '' }}">
    <!-- Statement / Sub-item Code & Title Column -->
    <td class="ps-3 align-top py-3">
        <div class="d-flex align-items-start gap-2" style="padding-left: {{ $indentPx }}px;">
            {{-- Expand/Collapse Toggle --}}
            @if($hasChildren)
                <button type="button" class="btn btn-xs btn-link text-apple-green p-0 me-1 subfolder-toggle"
                    data-target="#{{ $childrenId }}" onclick="toggleChildren(this)"
                    title="Expand/Collapse sub-items" style="min-width: 20px;">
                    <i class="bi bi-chevron-right fs-6 transition-rotate"></i>
                </button>
            @else
                @if($depth > 0)
                    <span class="text-secondary opacity-50 me-1" style="min-width: 20px; text-align:center;">
                        <i class="bi bi-dash"></i>
                    </span>
                @endif
            @endif

            <span class="code-badge {{ $hasDocuments ? 'has-evidence' : 'missing-evidence' }}">{{ $subfolder->code ?? 'N/A' }}</span>
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex flex-wrap align-items-center gap-1.5 mb-1">
                    <strong class="fs-7 text-break me-1">{{ $subfolder->name }}</strong>
                    <span class="badge {{ $reviewStatusClass }} text-capitalize flex-shrink-0">{{ $reviewStatusLabel }}</span>
                </div>
                <small class="text-muted fs-8 d-block mb-1">Added by {{ $subfolder->creator->name ?? 'Faculty' }}</small>

                {{-- Action buttons: Add Child Sub-Item --}}
                <div class="mt-1 d-flex flex-wrap align-items-center gap-1">
                    @if(auth()->user()->isAdmin() || auth()->user()->isFaculty())
                        <button type="button" class="btn btn-xs btn-outline-dark py-0.5 px-2 fs-8"
                            data-id="{{ $subfolder->id }}"
                            data-code="{{ $subfolder->code ?? '' }}"
                            data-name="{{ $subfolder->name }}"
                            data-documents-needed="{{ $subfolder->documents_needed ?? '' }}"
                            onclick="openEditSubfolderModal(this)">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-success py-0.5 px-2 fs-8"
                            data-param-cat-id="{{ $paramCat->id }}"
                            data-parent-id="{{ $subfolder->id }}"
                            data-parent-code="{{ $subfolder->code ?? '' }}"
                            data-category-name="{{ $paramCat->category->name ?? '' }}"
                            onclick="openAddChildSubfolderModal(this)">
                            <i class="bi bi-plus-circle me-1"></i> Add Sub-Item
                        </button>
                        @if($canDelete)
                            <form action="{{ route('subfolders.destroy', $subfolder) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete sub-item {{ $subfolder->code ?? 'item' }} and its empty sub-items?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger py-0.5 px-2 fs-8" title="Delete sub-item">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn btn-xs btn-outline-danger py-0.5 px-2 fs-8" disabled
                                title="Delete is unavailable while this sub-item or one of its sub-items has uploaded PDF files.">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        @endif
                    @endif
                    @if($hasChildren)
                        <span class="file-count-badge">
                            <i class="bi bi-diagram-3 me-1"></i>{{ $subfolder->children->count() }} sub-items
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </td>

    <!-- Documents Needed (Checklist) Column -->
    <td class="checklist-cell align-top py-3 fs-7" id="checklist-cell-{{ $subfolder->id }}">
        @if($subfolder->documents_needed)
            {!! \App\Helpers\ChecklistFormatter::format($subfolder->documents_needed, $subfolder->completed_checklist_array, (auth()->user()->isAdmin() || auth()->user()->isFaculty()), $subfolder->id, $hasEvidence) !!}
        @else
            <span class="text-muted fs-8 fst-italic">• Standard documentary evidence required for accreditation compliance.</span>
        @endif
    </td>

    <!-- Available Documents Provided (PDF Files & Actions) Column -->
    <td class="pe-3 align-top py-3">
        @if($latestDocumentRequest)
            <button type="button" class="btn btn-xs btn-outline-warning mb-2 fs-8"
                data-statement="{{ $subfolder->code }} - {{ $subfolder->name }}"
                data-requested-documents="{{ $latestDocumentRequest->requested_documents }}"
                data-remarks="{{ $latestDocumentRequest->remarks }}"
                data-requester="{{ $latestDocumentRequest->requester->name ?? 'Accreditor' }}"
                data-due-date="{{ $latestDocumentRequest->due_date?->format('M d, Y') }}"
                onclick="openDocumentRequestDetailsModal(this)">
                <i class="bi bi-chat-square-text me-1"></i> View Request
            </button>
        @endif
        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-1">
            <span class="file-count-badge">
                <i class="bi bi-file-earmark-pdf text-danger me-1"></i> {{ $subfolder->documents->count() }} Files
            </span>
            @if(auth()->user()->isAdmin() || auth()->user()->isFaculty())
                <button type="button" class="btn btn-xs {{ $openDocumentRequest ? 'btn-warning' : 'btn-apple-green' }} py-1 px-2 fs-8 fw-semibold"
                    data-subfolder-id="{{ $subfolder->id }}"
                    data-subfolder-name="{{ $subfolder->name }}"
                    data-documents-needed="{{ $subfolder->documents_needed ?? '' }}"
                    data-completed-items="{{ json_encode($subfolder->completed_checklist_array) }}"
                    data-open-request-id="{{ $openDocumentRequest?->id }}"
                    data-requested-documents="{{ $openDocumentRequest?->requested_documents }}"
                    data-request-remarks="{{ $openDocumentRequest?->remarks }}"
                    data-request-due-date="{{ $openDocumentRequest?->due_date?->format('M d, Y') }}"
                    onclick="openEvidenceUploadChoice(this)">
                    <i class="bi {{ $openDocumentRequest ? 'bi-exclamation-circle' : 'bi-folder-plus' }} me-1"></i> {{ $openDocumentRequest ? 'Comply / Add Evidence' : 'Add Evidence' }}
                </button>
            @endif
        </div>

        @forelse($subfolder->documents as $doc)
            @php
                $isSupplementalEvidence = $latestResubmission && $doc->created_at->greaterThanOrEqualTo($latestResubmission->created_at);
            @endphp
            <div class="doc-file-card fs-8 p-2 mb-2 border rounded {{ $isSupplementalEvidence ? 'border-success' : '' }}" @if($isSupplementalEvidence) style="background: var(--accent-light);" @endif>
                <!-- Top Row: PDF Icon & Full Title -->
                <div class="d-flex align-items-start gap-2 mb-1">
                    <i class="bi bi-file-earmark-pdf-fill {{ $isSupplementalEvidence ? 'text-success' : 'text-danger' }} fs-5 flex-shrink-0 mt-0.5"></i>
                    <div class="min-w-0 flex-grow-1">
                        <span class="fw-semibold text-dark fs-8 d-block text-break" style="line-height: 1.3;" title="{{ $doc->original_filename }}">
                            {{ $doc->original_filename }}
                        </span>
                    </div>
                </div>

                <!-- Bottom Row: File Badges & Action Buttons -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mt-1 pt-1 border-top border-light-subtle">
                    <div class="d-flex align-items-center flex-wrap gap-1">
                        <span class="badge text-bg-light text-secondary border fs-8 font-monospace">{{ $doc->formatted_size }}</span>
                        @if($isSupplementalEvidence)
                            <span class="badge bg-success text-white fs-8 d-inline-flex align-items-center gap-1">
                                <i class="bi bi-plus-circle"></i> Supplemental evidence
                            </span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-1 flex-shrink-0 ms-auto">
                        <button type="button" class="btn btn-xs btn-outline-success py-0.5 px-2 fs-8"
                            data-stream-url="{{ route('documents.stream', $doc) }}"
                            data-download-url="{{ route('documents.download', $doc) }}"
                            data-filename="{{ $doc->original_filename }}"
                            data-meta="Uploaded by {{ $doc->uploader->name ?? 'Faculty' }} | Size: {{ $doc->formatted_size }}"
                            data-can-download="{{ auth()->user()->isAccreditor() && !config('accredms.accreditor_download_allowed') ? 'false' : 'true' }}"
                            onclick="openPdfModalFromBtn(this)">
                            <i class="bi bi-eye"></i> View
                        </button>
                        @php
                            $coveredCount = count($doc->covered_evidences_array);
                            $tooltipText = $coveredCount > 0 
                                ? "Covered Evidences:\n• " . implode("\n• ", $doc->covered_evidences_array) 
                                : "No evidence requirements tagged yet. Click to tag evidences.";
                        @endphp
                        @if(auth()->user()->isAdmin() || (auth()->user()->isFaculty() && $doc->uploaded_by === auth()->id()))
                            <button type="button" class="btn btn-xs {{ $coveredCount > 0 ? 'btn-outline-success fw-semibold px-2' : 'btn-outline-secondary px-1' }} py-0.5 fs-8"
                                title="{{ $tooltipText }}"
                                data-document-id="{{ $doc->id }}"
                                data-subfolder-id="{{ $subfolder->id }}"
                                data-filename="{{ $doc->original_filename }}"
                                data-documents-needed="{{ $subfolder->documents_needed ?? '' }}"
                                data-covered-evidences="{{ json_encode($doc->covered_evidences_array) }}"
                                onclick="openTagEvidencesModal(this)">
                                <i class="bi {{ $coveredCount > 0 ? 'bi-tags-fill me-1' : 'bi-tags' }}"></i>{{ $coveredCount > 0 ? "Tag Evidences ($coveredCount)" : '' }}
                            </button>
                            <form action="{{ route('documents.destroy', $doc) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this uploaded PDF file?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger py-0.5 px-1.5 fs-8" title="Delete file"><i class="bi bi-trash"></i></button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            @if($subfolder->photos->isEmpty())
                <div class="p-2 text-center text-muted fs-8 border rounded" style="border-color: var(--doc-card-border) !important; background: var(--doc-card-bg);">
                    No evidence uploaded for this statement yet.
                </div>
            @endif
        @endforelse

        @if($subfolder->photos->isNotEmpty())
            @include('components.evidence-photo-card', ['photos' => $subfolder->photos])
        @endif
    </td>
</tr>

{{-- Render child sub-items recursively (hidden by default, toggled via JS) --}}
@if($hasChildren)
    @foreach($subfolder->children->sort(fn($a, $b) => strnatcasecmp($a->code ?? '', $b->code ?? '')) as $child)
        <tr class="subfolder-child-row child-of-{{ $subfolder->id }}" style="display: none;">
            <td colspan="3" class="p-0 border-0">
                <table class="table table-matrix-dark align-middle mb-0 w-100">
                    <tbody>
                        @include('admin.areas._subfolder_row', ['subfolder' => $child, 'depth' => $depth + 1, 'paramCat' => $paramCat])
                    </tbody>
                </table>
            </td>
        </tr>
    @endforeach
@endif
