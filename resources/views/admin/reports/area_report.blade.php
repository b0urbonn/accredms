<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Area Compliance Report - {{ $area->code }} | MarSU CICS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,300;0,400;0,700;0,900;1,400&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Lato', 'Helvetica Neue', Arial, sans-serif;
            background: #f4f6f8;
            color: #111;
            font-size: 0.88rem;
            line-height: 1.5;
        }

        .report-paper {
            background: #ffffff;
            max-width: 920px;
            margin: 2rem auto;
            padding: 3rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-radius: 4px;
        }

        /* CICS Apple Green Theme Accents */
        .text-success, .text-apple-green {
            color: #78a22f !important;
        }
        .bg-success, .bg-apple-green {
            background-color: #78a22f !important;
        }

        /* Institutional Brand Header */
        .report-header {
            margin-bottom: 1.5rem;
        }
        .header-logo-img {
            height: 72px;
            width: auto;
            object-fit: contain;
        }
        .university-title {
            font-family: 'Lato', sans-serif;
            font-weight: 700;
            font-size: 1.65rem;
            letter-spacing: 0.03em;
            color: #111111;
            line-height: 1.15;
        }
        .text-maroon {
            color: #800000;
            font-weight: 900;
        }
        .college-title {
            font-family: 'Lato', sans-serif;
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: 0.04em;
            color: #222222;
            margin-top: 3px;
        }
        .header-line-bar {
            display: flex;
            align-items: center;
            height: 6px;
            width: 100%;
            margin-top: 0.75rem;
        }
        .header-line-bar .line-gold {
            width: 25%;
            height: 5px;
            background: #d4af37;
        }
        .header-line-bar .line-maroon {
            width: 75%;
            height: 5px;
            background: #800000;
        }

        .report-title-block {
            margin-top: 1.25rem;
        }

        @media print {
            body { background: #fff; font-size: 12pt; }
            .report-paper { box-shadow: none; margin: 0; padding: 0; width: 100%; max-width: 100%; }
            .no-print { display: none !important; }
            .page-break-before { break-before: page; }
            thead { display: table-header-group; }
            tr { break-inside: avoid; }
        }
    </style>
</head>
<body>

<div class="no-print text-center py-3 bg-dark text-white border-bottom">
    <div class="d-inline-flex gap-2">
        <button onclick="window.print()" class="btn btn-success font-weight-bold"><i class="bi bi-printer me-1"></i> Print / Save as PDF Report</button>
        <a href="{{ auth()->user()->isAdmin() ? route('admin.areas.show', $area) : route('accreditor.show_area', $area) }}" class="btn btn-outline-light">&larr; Back to Hierarchy</a>
    </div>
</div>

<div class="report-paper">
    <!-- Institutional Header -->
    <div class="report-header mb-4">
        <div class="d-flex align-items-center justify-content-start gap-3">
            <div class="d-flex align-items-center gap-2">
                <img src="{{ asset('images/logos/MARSU LOGO.png') }}" alt="MARSU Logo" class="header-logo-img">
                <img src="{{ asset('images/logos/cics_logo.png') }}" alt="CICS Logo" class="header-logo-img">
            </div>
            <div class="header-text-block ms-2">
                <h2 class="university-title mb-0">
                    <span class="text-maroon">MAR</span>INDUQUE <span class="text-maroon">S</span>TATE <span class="text-maroon">U</span>NIVERSITY
                </h2>
                <h5 class="college-title mb-0">
                    COLLEGE OF INFORMATION AND COMPUTING SCIENCES
                </h5>
            </div>
        </div>
        <div class="header-line-bar">
            <div class="line-gold"></div>
            <div class="line-maroon"></div>
        </div>
        <div class="report-title-block text-center">
            <h5 class="text-uppercase fw-bold mb-1">Survey of Unit Compliance (SUC) Accreditation Report</h5>
            <div class="fs-6 fw-bold text-success">{{ $area->code }} — {{ strtoupper($area->name) }}</div>
        </div>
    </div>

    <!-- Executive Summary Card -->
    <div class="row g-3 mb-4">
        <div class="col-6">
            <table class="table table-sm table-bordered fs-7 mb-0">
                <tr><th class="bg-light w-40">Report Date:</th><td>{{ date('F d, Y') }}</td></tr>
                <tr><th class="bg-light">Area Code:</th><td>{{ $area->code }}</td></tr>
                <tr><th class="bg-light">Status:</th><td class="fw-bold text-uppercase">{{ str_replace('_', ' ', $area->status) }}</td></tr>
            </table>
        </div>
        <div class="col-6">
            <table class="table table-sm table-bordered fs-7 mb-0">
                <tr><th class="bg-light w-50">Total Parameters:</th><td class="fw-bold">{{ $totalParameters }}</td></tr>
                <tr><th class="bg-light">Total Indicators:</th><td class="fw-bold">{{ $totalSubfolders }}</td></tr>
                <tr><th class="bg-light">Uploaded PDF Documents:</th><td class="fw-bold text-success">{{ $totalDocuments }}</td></tr>
            </table>
        </div>
    </div>

    <div class="border border-dark p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong class="text-uppercase fs-7">Evidence Completion Summary</strong>
            <strong>{{ $evidenceCompletionPercent }}%</strong>
        </div>
        <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-success" style="width: {{ $evidenceCompletionPercent }}%"></div>
        </div>
        <small class="d-block mt-2">{{ $completedStatements }} of {{ $totalSubfolders }} indicators have at least one uploaded PDF; {{ $missingStatements }} still require evidence.</small>
    </div>

    <!-- Assigned Personnel Matrix -->
    <h6 class="fw-bold text-uppercase border-bottom pb-1 mb-2 fs-7">I. Assigned Accreditation Committee & Accreditors</h6>
    <table class="table table-bordered table-sm fs-7 mb-4">
        <thead class="bg-light">
            <tr>
                <th>Personnel Name</th>
                <th>Institutional Email</th>
                <th>Role in Area</th>
            </tr>
        </thead>
        <tbody>
            @forelse($area->users as $user)
                <tr>
                    <td class="fw-bold">{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td class="text-capitalize">{{ $user->pivot->assignment_role }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted">No personnel assigned to this area.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- Detailed Survey Indicator Matrix -->
    <h6 class="fw-bold text-uppercase border-bottom pb-1 mb-2 fs-7">II. Survey Indicator and Documentary Evidence Matrix</h6>
    <table class="table table-bordered table-sm fs-7 mb-4">
        <thead class="bg-light text-center">
            <tr>
                <th width="35%">Indicators / Requirements</th>
                <th width="44%">Document Evidences</th>
                <th width="10%">Evidence Status</th>
                <th width="11%">Accreditor Findings</th>
            </tr>
        </thead>
        <tbody>
            <?php $previousParameterId = null; $previousCategoryId = null; ?>
            @forelse($reportRows as $row)
                @if($previousParameterId !== $row['parameter']->id)
                    <tr class="table-secondary"><td colspan="4" class="fw-bold text-uppercase">Parameter {{ $row['parameter']->code }}: {{ $row['parameter']->title }}</td></tr>
                    <?php $previousParameterId = $row['parameter']->id; $previousCategoryId = null; ?>
                @endif
                @if($previousCategoryId !== $row['category']->id)
                    <tr class="table-light"><td colspan="4" class="fw-bold text-uppercase">{{ $row['category']->category->name }}</td></tr>
                    <?php $previousCategoryId = $row['category']->id; ?>
                @endif
                <tr>
                    <td style="padding-left: {{ 0.55 + ($row['depth'] * 1.2) }}rem;">
                        <strong>{{ $row['statement']->code ?? 'N/A' }}</strong> {{ $row['statement']->name }}
                    </td>
                    <td>
                        @if($row['statement']->documents_needed)
                            {!! \App\Helpers\ChecklistFormatter::formatForReport($row['statement']->documents_needed, $row['statement']->completed_checklist_array, $row['statement']->documents->isNotEmpty() || $row['statement']->photos->isNotEmpty()) !!}
                        @else
                            <span class="text-muted fst-italic fs-8">None specified</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($row['statement']->documents->isNotEmpty() || $row['statement']->photos->isNotEmpty())
                            <span class="fw-bold text-success">Provided</span>
                        @else
                            <span class="fw-bold text-danger">Missing</span>
                        @endif
                    </td>
                    <td>
                        <?php $statementFindings = $row['statement']->documents->flatMap->remarks; ?>
                        @if($statementFindings->isNotEmpty())
                            {{ $statementFindings->count() }} finding(s)
                        @else
                            <span class="text-muted">None</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">No survey indicators have been entered for this Area.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- Accreditor Findings -->
    <section class="page-break-before mt-5">
        <h6 class="fw-bold text-uppercase border-bottom pb-1 mb-2 fs-7">III. Accreditor Findings and Recommendations</h6>
        <p class="fs-7">Findings below are the official remarks recorded against each documentary evidence file.</p>
        <table class="table table-bordered table-sm fs-7 mb-4">
            <thead class="bg-light">
                <tr>
                    <th width="18%">Parameter / Indicator</th>
                    <th width="22%">Document</th>
                    <th>Finding / Recommendation</th>
                    <th width="18%">Accreditor / Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($findings as $finding)
                    <tr>
                        <td>
                            <strong>Parameter {{ $finding->document->subfolder->parameterCategory->parameter->code }}</strong><br>
                            {{ $finding->document->subfolder->code ?? 'N/A' }} - {{ $finding->document->subfolder->name }}
                        </td>
                        <td>{{ $finding->document->original_filename }}</td>
                        <td>{{ $finding->remark }}</td>
                        <td>{{ $finding->user->name }}<br><small class="text-muted">{{ $finding->created_at->format('M d, Y') }}</small></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No accreditor findings have been recorded for this Area.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <!-- Official Sign-off & Verification Block -->
    <?php
        $hasCoChairman = $area->coHandlers->isNotEmpty();
        $colClass = $hasCoChairman ? 'col-3' : 'col-4';
    ?>
    <div class="mt-5 pt-4">
        <h6 class="fw-bold text-uppercase border-bottom pb-1 mb-4 fs-7">IV. Sign-Off & Verification Approval</h6>
        <div class="row text-center fs-7 mt-4 justify-content-center">
            <!-- Area Handler / Chairperson -->
            <div class="{{ $colClass }}">
                <div class="border-bottom border-dark pb-1 mb-1 fw-bold text-uppercase">
                    {{ $area->handlers->first()->name ?? 'Area Handler / Chairperson' }}
                </div>
                <small class="text-dark d-block fw-semibold">Area Handler / Chairperson</small>
                <small class="text-muted">Date Signed: _______________</small>
            </div>

            <!-- Co-Chairman (Only shown if Co-Chairman exists!) -->
            @if($hasCoChairman)
                <div class="{{ $colClass }}">
                    <div class="border-bottom border-dark pb-1 mb-1 fw-bold text-uppercase">
                        {{ $area->coHandlers->first()->name }}
                    </div>
                    <small class="text-dark d-block fw-semibold">Area Co-Chairman</small>
                    <small class="text-muted">Date Signed: _______________</small>
                </div>
            @endif

            <!-- Dean of CICS -->
            <div class="{{ $colClass }}">
                <div class="border-bottom border-dark pb-1 mb-1 fw-bold text-uppercase">
                    Dr. Ronjie Mar L. Malinao, DIT
                </div>
                <small class="text-dark d-block fw-semibold">Dean, CICS</small>
                <small class="text-muted">Date Signed: _______________</small>
            </div>

            <!-- Lead Accreditor -->
            <div class="{{ $colClass }}">
                <div class="border-bottom border-dark pb-1 mb-1 fw-bold text-uppercase">
                    {{ $area->accreditors->first()->name ?? 'Lead Accreditor Signature' }}
                </div>
                <small class="text-dark d-block fw-semibold">Lead AACCUP Accreditor</small>
                <small class="text-muted">Date Signed: _______________</small>
            </div>
        </div>
    </div>

</div>

</body>
</html>
