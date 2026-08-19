@extends('layouts.app')

@section('title', $report->exists ? 'Edit Compliance Report' : 'New Compliance Report')

@section('content')
<div class="page-heading">
    <div>
        <h3 class="fw-bold mb-1">{{ $report->exists ? 'Edit Compliance Report' : 'New Compliance Report' }}</h3>
        <p class="mb-0">Enter each recommendation separately and attach its supporting PDF evidence.</p>
    </div>
    <a href="{{ $report->exists ? route('compliance-reports.show', $report) : route('compliance-reports.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>

@if($errors->any())
    <div class="alert alert-danger"><strong>Please correct the highlighted information.</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<form method="POST" enctype="multipart/form-data" action="{{ $report->exists ? route('compliance-reports.update', $report) : route('compliance-reports.store') }}">
    @csrf
    @if($report->exists) @method('PUT') @endif
    <div class="card card-custom mb-4"><div class="card-body row g-3">
        <div class="col-md-5">
            <label class="form-label fw-semibold">Area <span class="text-danger">*</span></label>
            <select class="form-select @error('area_id') is-invalid @enderror" name="area_id" required>
                <option value="">Select an area</option>
                @foreach($areas as $area)<option value="{{ $area->id }}" @selected(old('area_id', $report->area_id) == $area->id)>{{ $area->code }} - {{ $area->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-4"><label class="form-label fw-semibold">Program / Course</label><input class="form-control" name="program" value="{{ old('program', $report->program) }}" placeholder="e.g. Bachelor of Science in Information Systems"></div>
        <div class="col-md-3"><label class="form-label fw-semibold">Survey Visit / Period</label><input class="form-control" name="survey_visit" value="{{ old('survey_visit', $report->survey_visit) }}" placeholder="e.g. 3rd Survey Visit"></div>
    </div></div>

    <div id="recommendations" class="d-grid gap-3"></div>
    <template id="recommendation-template">
        <section class="card card-custom recommendation-item"><div class="card-header bg-white d-flex align-items-center justify-content-between"><strong><i class="bi bi-list-check text-success me-1"></i> Recommendation <span class="recommendation-number"></span></strong><button type="button" class="btn btn-sm btn-outline-danger remove-recommendation" title="Remove recommendation"><i class="bi bi-trash"></i></button></div>
            <div class="card-body row g-3">
                <input type="hidden" data-field="id">
                <div class="col-12"><label class="form-label fw-semibold">Recommendation <span class="text-danger">*</span></label><textarea data-field="recommendation" class="form-control" rows="3" required></textarea></div>
                <div class="col-md-8"><label class="form-label fw-semibold">Action Taken</label><textarea data-field="action_taken" class="form-control" rows="3"></textarea></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Percentage of Compliance <span class="text-danger">*</span></label><div class="input-group"><input data-field="compliance_percentage" type="number" class="form-control" min="0" max="100" required><span class="input-group-text">%</span></div></div>
                <div class="col-12"><label class="form-label fw-semibold">Add Evidence PDFs</label><input data-field="files" type="file" class="form-control" accept="application/pdf,.pdf" multiple><div class="form-text">PDF files only, up to 25 MB each. New files are added without replacing existing evidence.</div><div class="existing-evidence mt-2"></div></div>
            </div>
        </section>
    </template>
    <button type="button" id="add-recommendation" class="btn btn-outline-success align-self-start"><i class="bi bi-plus-circle me-1"></i> Add Recommendation</button>
    <div class="mt-4 d-flex gap-2"><button class="btn btn-apple-green" type="submit"><i class="bi bi-check-lg me-1"></i> Save Compliance Report</button><a href="{{ route('compliance-reports.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
</form>
@endsection

@section('scripts')
@php
    $existingRecommendations = old('recommendations');

    if ($existingRecommendations === null) {
        $existingRecommendations = $report->recommendations->map(function ($item) {
            return [
                'id' => $item->id,
                'recommendation' => $item->recommendation,
                'action_taken' => $item->action_taken,
                'compliance_percentage' => $item->compliance_percentage,
                'evidences' => $item->evidences->map(function ($evidence) {
                    return [
                        'name' => $evidence->original_filename,
                        'size' => $evidence->formatted_size,
                    ];
                })->values(),
            ];
        })->values();
    }
@endphp
<script>
const existingRecommendations = @json($existingRecommendations);
const container = document.getElementById('recommendations');
const template = document.getElementById('recommendation-template');
function addRecommendation(data = {}) {
    const node = template.content.cloneNode(true);
    const item = node.querySelector('.recommendation-item');
    item.querySelectorAll('[data-field]').forEach((field) => { field.name = field.dataset.field === 'files' ? `recommendations[${container.children.length}][files][]` : `recommendations[${container.children.length}][${field.dataset.field}]`; if (field.dataset.field !== 'files') field.value = data[field.dataset.field] ?? ''; });
    const evidenceList = item.querySelector('.existing-evidence');
    (data.evidences || []).forEach((evidence) => { evidenceList.insertAdjacentHTML('beforeend', `<span class="badge bg-light text-dark border me-1"><i class="bi bi-file-earmark-pdf text-danger me-1"></i>${evidence.name} (${evidence.size})</span>`); });
    item.querySelector('.remove-recommendation').addEventListener('click', () => { item.remove(); renumber(); });
    container.appendChild(node); renumber();
}
function renumber() { [...container.children].forEach((item, index) => { item.querySelector('.recommendation-number').textContent = index + 1; item.querySelectorAll('[data-field]').forEach((field) => { field.name = field.dataset.field === 'files' ? `recommendations[${index}][files][]` : `recommendations[${index}][${field.dataset.field}]`; }); }); }
document.getElementById('add-recommendation').addEventListener('click', () => addRecommendation());
(existingRecommendations.length ? existingRecommendations : [{}]).forEach(addRecommendation);
</script>
@endsection