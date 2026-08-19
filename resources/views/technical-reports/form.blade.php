@extends('layouts.app')

@section('title', $report->exists ? 'Edit Technical Report' : 'New Technical Report')

@section('content')
<div class="page-heading">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-file-earmark-text text-success me-2"></i>{{ $report->exists ? 'Edit Technical Report' : 'New Technical Report' }}</h3>
        <p class="mb-0">Fill out technical evaluation details, findings, score ratings, and recommendations.</p>
    </div>
    <a href="{{ route('technical-reports.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back to Reports</a>
</div>

<div class="card card-custom">
    <div class="card-body p-4">
        <form action="{{ $report->exists ? route('technical-reports.update', $report) : route('technical-reports.store') }}" method="POST">
            @csrf
            @if($report->exists)
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-md-8">
                    <label for="title" class="form-label fw-semibold">Report Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $report->title) }}" placeholder="e.g. Area I Technical Evaluation Synthesis Report" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="report_number" class="form-label fw-semibold">Report Number / Code</label>
                    <input type="text" name="report_number" id="report_number" class="form-control" value="{{ old('report_number', $report->report_number) }}" placeholder="e.g. TR-2026-AREA1">
                </div>

                <div class="col-md-6">
                    <label for="area_id" class="form-label fw-semibold">Accreditation Area Target</label>
                    <select name="area_id" id="area_id" class="form-select">
                        <option value="">-- Program-wide / General Report --</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" @selected(old('area_id', $report->area_id) == $area->id)>{{ $area->code }} - {{ $area->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="program" class="form-label fw-semibold">Program Name</label>
                    <input type="text" name="program" id="program" class="form-control" value="{{ old('program', $report->program ?? 'BS Information Technology') }}" placeholder="e.g. BSIT">
                </div>

                <div class="col-md-3">
                    <label for="survey_visit" class="form-label fw-semibold">Survey Visit</label>
                    <input type="text" name="survey_visit" id="survey_visit" class="form-control" value="{{ old('survey_visit', $report->survey_visit ?? '3rd Survey Visit') }}" placeholder="e.g. 3rd Survey Visit">
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label fw-semibold">Publication Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="draft" @selected(old('status', $report->status) === 'draft')>Draft (Editing in progress)</option>
                        <option value="under_review" @selected(old('status', $report->status) === 'under_review')>Under Review (Submitted to lead evaluator)</option>
                        <option value="approved" @selected(old('status', $report->status) === 'approved')>Approved (Technical review passed)</option>
                        <option value="published" @selected(old('status', $report->status) === 'published')>Published (Final report available)</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="overall_score" class="form-label fw-semibold">Overall Technical Rating Score (0.0 - 5.0)</label>
                    <input type="number" step="0.01" min="0" max="5" name="overall_score" id="overall_score" class="form-control" value="{{ old('overall_score', $report->overall_score) }}" placeholder="e.g. 4.25">
                </div>

                <div class="col-12">
                    <label for="summary_findings" class="form-label fw-semibold">Summary Findings</label>
                    <textarea name="summary_findings" id="summary_findings" rows="3" class="form-control" placeholder="Executive summary of findings for this technical evaluation.">{{ old('summary_findings', $report->summary_findings) }}</textarea>
                </div>

                <div class="col-12">
                    <label for="technical_evaluation" class="form-label fw-semibold">Detailed Technical Evaluation Notes</label>
                    <textarea name="technical_evaluation" id="technical_evaluation" rows="4" class="form-control" placeholder="Specific technical compliance metrics, criteria notes, and evaluation comments.">{{ old('technical_evaluation', $report->technical_evaluation) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label for="strengths" class="form-label fw-semibold text-success"><i class="bi bi-check-circle me-1"></i> Commendable Strengths</label>
                    <textarea name="strengths" id="strengths" rows="3" class="form-control border-success border-opacity-50" placeholder="List strengths and commendable practices.">{{ old('strengths', $report->strengths) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label for="areas_for_improvement" class="form-label fw-semibold text-warning"><i class="bi bi-exclamation-triangle me-1"></i> Areas for Improvement</label>
                    <textarea name="areas_for_improvement" id="areas_for_improvement" rows="3" class="form-control border-warning border-opacity-50" placeholder="List gaps, unfulfilled criteria, or areas needing action.">{{ old('areas_for_improvement', $report->areas_for_improvement) }}</textarea>
                </div>

                <div class="col-12">
                    <label for="recommendations" class="form-label fw-semibold text-primary"><i class="bi bi-lightbulb me-1"></i> Evaluator Recommendations</label>
                    <textarea name="recommendations" id="recommendations" rows="3" class="form-control border-primary border-opacity-50" placeholder="Concrete recommendations for compliance enhancement.">{{ old('recommendations', $report->recommendations) }}</textarea>
                </div>

                <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('technical-reports.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-apple-green px-4"><i class="bi bi-check-lg me-1"></i> Save Technical Report</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
