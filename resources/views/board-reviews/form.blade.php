@extends('layouts.app')

@section('title', $review->exists ? 'Edit Board Review' : 'New Board Review')

@section('content')
<div class="page-heading">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-bank text-primary me-2"></i>{{ $review->exists ? 'Edit Board Review' : 'New Board Review' }}</h3>
        <p class="mb-0">Record formal Board of Trustees / Accreditation Board resolutions, validity, and status actions.</p>
    </div>
    <a href="{{ route('board-reviews.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back to Board Reviews</a>
</div>

<div class="card card-custom">
    <div class="card-body p-4">
        <form action="{{ $review->exists ? route('board-reviews.update', $review) : route('board-reviews.store') }}" method="POST">
            @csrf
            @if($review->exists)
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-md-8">
                    <label for="review_title" class="form-label fw-semibold">Board Review Title <span class="text-danger">*</span></label>
                    <input type="text" name="review_title" id="review_title" class="form-control @error('review_title') is-invalid @enderror" value="{{ old('review_title', $review->review_title) }}" placeholder="e.g. Board Action on BSIT 3rd Survey Accreditation" required>
                    @error('review_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="resolution_number" class="form-label fw-semibold">Board Resolution Number</label>
                    <input type="text" name="resolution_number" id="resolution_number" class="form-control" value="{{ old('resolution_number', $review->resolution_number) }}" placeholder="e.g. BR-2026-089">
                </div>

                <div class="col-md-6">
                    <label for="area_id" class="form-label fw-semibold">Accreditation Area Target</label>
                    <select name="area_id" id="area_id" class="form-select">
                        <option value="">-- Program-wide / General Board Review --</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" @selected(old('area_id', $review->area_id) == $area->id)>{{ $area->code }} - {{ $area->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="program" class="form-label fw-semibold">Program Name</label>
                    <input type="text" name="program" id="program" class="form-control" value="{{ old('program', $review->program ?? 'BS Information Technology') }}" placeholder="e.g. BSIT">
                </div>

                <div class="col-md-3">
                    <label for="survey_visit" class="form-label fw-semibold">Survey Visit</label>
                    <input type="text" name="survey_visit" id="survey_visit" class="form-control" value="{{ old('survey_visit', $review->survey_visit ?? '3rd Survey Visit') }}" placeholder="e.g. 3rd Survey Visit">
                </div>

                <div class="col-md-4">
                    <label for="board_decision" class="form-label fw-semibold">Board Decision / Action <span class="text-danger">*</span></label>
                    <select name="board_decision" id="board_decision" class="form-select @error('board_decision') is-invalid @enderror" required>
                        <option value="under_board_review" @selected(old('board_decision', $review->board_decision) === 'under_board_review')>Under Board Review</option>
                        <option value="accredited_level_1" @selected(old('board_decision', $review->board_decision) === 'accredited_level_1')>Level 1 Accredited</option>
                        <option value="accredited_level_2" @selected(old('board_decision', $review->board_decision) === 'accredited_level_2')>Level 2 Accredited</option>
                        <option value="accredited_level_3" @selected(old('board_decision', $review->board_decision) === 'accredited_level_3')>Level 3 Accredited</option>
                        <option value="accredited_level_4" @selected(old('board_decision', $review->board_decision) === 'accredited_level_4')>Level 4 Accredited</option>
                        <option value="re_accredited" @selected(old('board_decision', $review->board_decision) === 're_accredited')>Re-Accredited</option>
                        <option value="deferred" @selected(old('board_decision', $review->board_decision) === 'deferred')>Deferred</option>
                        <option value="not_accredited" @selected(old('board_decision', $review->board_decision) === 'not_accredited')>Not Accredited</option>
                    </select>
                    @error('board_decision')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="validity_period" class="form-label fw-semibold">Accreditation Validity Period</label>
                    <input type="text" name="validity_period" id="validity_period" class="form-control" value="{{ old('validity_period', $review->validity_period) }}" placeholder="e.g. Sept 1, 2026 – Aug 31, 2031">
                </div>

                <div class="col-md-4">
                    <label for="reviewed_date" class="form-label fw-semibold">Board Review Date</label>
                    <input type="date" name="reviewed_date" id="reviewed_date" class="form-control" value="{{ old('reviewed_date', $review->reviewed_date?->format('Y-m-d') ?? date('Y-m-d')) }}">
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label fw-semibold">Record Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="under_review" @selected(old('status', $review->status) === 'under_review')>Under Review</option>
                        <option value="resolved" @selected(old('status', $review->status) === 'resolved')>Resolved (Action taken)</option>
                        <option value="approved" @selected(old('status', $review->status) === 'approved')>Approved & Confirmed</option>
                        <option value="archived" @selected(old('status', $review->status) === 'archived')>Archived</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="board_remarks" class="form-label fw-semibold">Board Remarks & Deliberation Notes</label>
                    <textarea name="board_remarks" id="board_remarks" rows="4" class="form-control" placeholder="Official remarks, justification, and board deliberation notes.">{{ old('board_remarks', $review->board_remarks) }}</textarea>
                </div>

                <div class="col-12">
                    <label for="conditions_set" class="form-label fw-semibold text-warning"><i class="bi bi-shield-exclamation me-1"></i> Special Conditions or Directives Set by Board</label>
                    <textarea name="conditions_set" id="conditions_set" rows="3" class="form-control border-warning border-opacity-50" placeholder="Specific conditions to be complied with during the accreditation period.">{{ old('conditions_set', $review->conditions_set) }}</textarea>
                </div>

                <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('board-reviews.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-apple-green px-4"><i class="bi bi-check-lg me-1"></i> Save Board Review</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
