<style>
    .evidence-method-option {
        align-items: center;
        background: var(--bg-surface, #fff);
        border: 1px solid var(--border-color, #dfe5dc);
        border-radius: 8px;
        display: flex;
        gap: 0.75rem;
        min-height: 78px;
        padding: 0.85rem 1rem;
        transition: border-color 0.15s ease, background-color 0.15s ease;
    }
    .evidence-method-option:hover,
    .evidence-method-option:focus-visible {
        background: var(--accent-light, #f1f7e9);
        border-color: var(--accent, #78a22f);
        outline: none;
    }
    .evidence-method-icon {
        align-items: center;
        display: inline-flex;
        font-size: 1.65rem;
        justify-content: center;
        min-width: 2.25rem;
    }
    .capture-photo-section {
        border: 1px solid var(--border-color, #dfe5dc);
        border-radius: 8px;
        padding: 0.85rem;
    }
    .capture-photo-section-title {
        align-items: center;
        display: flex;
        font-size: 0.78rem;
        font-weight: 700;
        justify-content: space-between;
        margin-bottom: 0.6rem;
    }
    .capture-photo-camera-stage {
        background: #eef2ec;
        border: 1px dashed #b7c7ad;
        border-radius: 6px;
        min-height: 150px;
        overflow: hidden;
        padding: 0.5rem;
    }
    .capture-photo-camera-stage video {
        max-height: 260px;
        object-fit: contain;
    }
    .capture-photo-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.6rem;
    }
    .capture-photo-selected {
        background: var(--accent-light, #f1f7e9);
        border: 1px solid #d4e2c3;
        border-radius: 6px;
        margin-top: 0.6rem;
        padding: 0.6rem;
    }
    .capture-photo-selected:empty { display: none; }
</style>

<!-- Modal: Choose Evidence Upload Method -->
<div class="modal fade" id="evidenceUploadChoiceModal" tabindex="-1" aria-labelledby="evidenceUploadChoiceTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-apple-dark text-white">
                <div>
                    <h5 class="modal-title fw-bold" id="evidenceUploadChoiceTitle"><i class="bi bi-folder-plus me-1"></i> Add Evidence</h5>
                    <p class="mb-0 fs-8 text-white-50">Choose how you want to provide evidence for this statement.</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="row g-3">
                    <div class="col-12">
                        <button type="button" class="evidence-method-option w-100 text-start" onclick="chooseEvidenceUploadMethod('pdf')">
                            <span class="evidence-method-icon text-danger"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                            <span>
                                <strong class="d-block">Upload PDF Evidence</strong>
                                <small class="text-muted">Use the existing PDF data-entry flow, checklist tags, compression, and PDF preview.</small>
                            </span>
                            <i class="bi bi-chevron-right ms-auto text-muted"></i>
                        </button>
                    </div>
                    <div class="col-12">
                        <button type="button" class="evidence-method-option w-100 text-start" onclick="chooseEvidenceUploadMethod('photo')">
                            <span class="evidence-method-icon text-success"><i class="bi bi-camera-fill"></i></span>
                            <span>
                                <strong class="d-block">Capture or Upload Photos</strong>
                                <small class="text-muted">Use the camera or select multiple images, then tag them to a checklist item.</small>
                            </span>
                            <i class="bi bi-chevron-right ms-auto text-muted"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
