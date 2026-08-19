@extends('layouts.app')

@section('title', 'Compliance Report - ' . $report->area->code)

@section('head')
<style>
.compliance-report-paper { border-color: #dce4d5; overflow: hidden; }
.compliance-report-header { border-bottom: 2px solid #22351c; color: #172312; }
.compliance-report-header h4 { font-size: 1.45rem; letter-spacing: 0.04em; }
.report-area { font-size: 0.92rem; letter-spacing: 0.02em; }
.report-meta { border-bottom: 1px solid #e2e8dd; color: #687562; font-size: 0.78rem; }
.compliance-report-table { border-color: #d7ded1; font-size: 0.83rem; table-layout: fixed; }
.compliance-report-table thead th { background: #edf3e8; border-color: #cfd9c7; color: #405337; font-size: 0.67rem; letter-spacing: 0.06em; padding: 0.82rem 0.7rem; text-transform: uppercase; vertical-align: middle; }
.compliance-report-table td { border-color: #dfe6da; line-height: 1.55; padding: 1rem 0.85rem; vertical-align: middle; }
.recommendation-number { color: #829179; font-size: 0.72rem; font-weight: 700; margin-bottom: 0.35rem; text-transform: uppercase; }
.compliance-score { color: #365c1e; font-size: 1.1rem; font-variant-numeric: tabular-nums; }
.evidence-file-card { align-items: center; background: #f7faf5; border: 1px solid #dfe8d9; border-radius: 5px; display: flex; gap: 0.55rem; margin-bottom: 0.45rem; min-width: 0; padding: 0.5rem 0.55rem; }
.evidence-file-card:last-child { margin-bottom: 0; }
.evidence-file-icon { color: #d9534f; flex: 0 0 auto; font-size: 1.15rem; }
.evidence-file-details { flex: 1 1 auto; min-width: 0; }
.evidence-file-name { color: #2b591e; display: block; font-size: 0.75rem; font-weight: 700; overflow: hidden; text-decoration: none; text-overflow: ellipsis; white-space: nowrap; }
.evidence-file-name:hover { color: #1e4115; text-decoration: underline; }
.evidence-file-meta { color: #778271; display: block; font-size: 0.65rem; margin-top: 0.1rem; }
.evidence-file-actions { align-items: center; display: flex; flex: 0 0 auto; gap: 0.2rem; }
.evidence-file-actions .btn { align-items: center; border-radius: 4px; display: inline-flex; height: 26px; justify-content: center; padding: 0; width: 26px; }
.empty-evidence { background: #fafbf9; border: 1px dashed #d4ddd0; border-radius: 5px; color: #899487; font-size: 0.72rem; padding: 0.7rem; text-align: center; }
@media print { .sidebar, .top-navbar, .site-footer, .no-print { display: none !important; } .main-wrapper { margin-left: 0 !important; } .content-body { padding: 0 !important; } .card, .card-custom { border: 0 !important; box-shadow: none !important; } body { background: #fff !important; } .compliance-report-paper { border: 0; } .evidence-file-card { background: transparent; border: 0; padding: 0.15rem 0; } .evidence-file-name { color: #111; white-space: normal; } thead { display: table-header-group; } tr { break-inside: avoid; } }
</style>
@endsection

@section('content')
<div class="page-heading no-print">
    <div><h3 class="fw-bold mb-1">Compliance Report</h3><p class="mb-0">{{ $report->area->code }} - {{ $report->area->name }}</p></div>
    <div class="d-flex gap-2"><button type="button" onclick="window.print()" class="btn btn-outline-dark"><i class="bi bi-printer me-1"></i> Print / Save PDF</button>@can('update', $report)<a href="{{ route('compliance-reports.edit', $report) }}" class="btn btn-apple-green"><i class="bi bi-pencil me-1"></i> Edit</a>@endcan</div>
</div>

<section class="card card-custom compliance-report-paper">
    <div class="card-body p-md-4">
        <header class="compliance-report-header text-center pb-3 mb-3">
            <h4 class="fw-bold mb-1">COMPLIANCE REPORT</h4>
            <div class="report-area fw-semibold">{{ $report->area->code }} - {{ strtoupper($report->area->name) }}</div>
            @if($report->program)<div class="mt-1 small">{{ $report->program }}</div>@endif
            @if($report->survey_visit)<div class="small">{{ $report->survey_visit }}</div>@endif
        </header>
        <div class="report-meta d-flex flex-wrap justify-content-between gap-2 pb-3 mb-3"><span>Report Status: <span class="text-success fw-semibold text-capitalize">{{ $report->status }}</span></span><span>Last updated {{ $report->updated_at->format('F d, Y') }} by {{ $report->updater->name ?? $report->creator->name ?? 'System' }}</span></div>
        <div class="table-responsive"><table class="table table-bordered compliance-report-table mb-0">
            <thead class="text-center"><tr><th style="width:30%">Recommendations For</th><th style="width:32%">Action Taken</th><th style="width:25%">Evidence Files</th><th style="width:13%">% of Compliance</th></tr></thead>
            <tbody>@forelse($report->recommendations as $recommendation)<tr>
                <td><div class="recommendation-number">Recommendation {{ $loop->iteration }}</div>{{ $recommendation->recommendation }}</td><td>{{ $recommendation->action_taken ?: 'No action recorded.' }}</td>
                <td>@forelse($recommendation->evidences as $evidence)@php($canDownload = !auth()->user()->isAccreditor() || config('accredms.accreditor_download_allowed'))<div class="evidence-file-card"><i class="bi bi-file-earmark-pdf-fill evidence-file-icon"></i><div class="evidence-file-details"><button type="button" class="evidence-file-name border-0 bg-transparent p-0 text-start w-100" onclick="openPdfModal('{{ route('compliance-evidences.stream', $evidence) }}', '{{ route('compliance-evidences.download', $evidence) }}', '{{ addslashes($evidence->original_filename) }}', 'Uploaded {{ $evidence->created_at->format('M d, Y') }} | Size: {{ $evidence->formatted_size }}', {{ $canDownload ? 'true' : 'false' }})" title="View {{ $evidence->original_filename }}">{{ $evidence->original_filename }}</button><span class="evidence-file-meta">{{ $evidence->formatted_size }} &middot; Uploaded {{ $evidence->created_at->format('M d, Y') }}</span></div><div class="evidence-file-actions no-print"><button type="button" class="btn btn-sm btn-outline-success" onclick="openPdfModal('{{ route('compliance-evidences.stream', $evidence) }}', '{{ route('compliance-evidences.download', $evidence) }}', '{{ addslashes($evidence->original_filename) }}', 'Uploaded {{ $evidence->created_at->format('M d, Y') }} | Size: {{ $evidence->formatted_size }}', {{ $canDownload ? 'true' : 'false' }})" title="View evidence"><i class="bi bi-eye"></i></button>@if($canDownload)<a class="btn btn-sm btn-outline-secondary" href="{{ route('compliance-evidences.download', $evidence) }}" title="Download evidence"><i class="bi bi-download"></i></a>@endif @can('update', $report)<form method="POST" action="{{ route('compliance-evidences.destroy', $evidence) }}" onsubmit="return confirm('Remove this evidence file?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" title="Remove evidence"><i class="bi bi-trash"></i></button></form>@endcan</div></div>@empty<div class="empty-evidence"><i class="bi bi-file-earmark-x me-1"></i>No evidence uploaded</div>@endforelse</td>
                <td class="text-center"><strong class="compliance-score">{{ $recommendation->compliance_percentage }}%</strong></td>
            </tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">No recommendations recorded.</td></tr>@endforelse</tbody>
        </table></div>
    </div>
</section>
@can('delete', $report)<form method="POST" action="{{ route('compliance-reports.destroy', $report) }}" class="no-print mt-3" onsubmit="return confirm('Delete this compliance report and all its evidence?')">@csrf @method('DELETE')<button class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i> Delete Report</button></form>@endcan
@endsection