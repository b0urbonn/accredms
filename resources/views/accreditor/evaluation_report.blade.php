<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accreditor Evaluation Report - {{ $area->code }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f2; color: #111; font-family: Arial, sans-serif; }
        .report-paper { background: #fff; box-shadow: 0 2px 16px rgba(0, 0, 0, .12); margin: 2rem auto; max-width: 1100px; padding: 2.5rem; }
        .report-title { padding-bottom: 1rem; }
        .survey-intro { font-size: .78rem; margin: 1.5rem auto 2.25rem; max-width: 760px; }
        .survey-intro h5, .survey-intro h6 { font-weight: 700; text-align: center; text-transform: uppercase; }
        .parameter-list { margin: 1.5rem 0; }
        .survey-details { max-width: 520px; }
        .survey-details th { padding-right: 1rem; vertical-align: top; white-space: nowrap; }
        .survey-details td { border-bottom: 1px solid #111; min-width: 300px; }
        .rating-scale td, .rating-scale th { font-size: .7rem; text-align: center; vertical-align: top; }
        .evaluation-table { font-size: .76rem; }
        .evaluation-table th { background: #eee; text-align: center; vertical-align: middle; }
        .parameter-row td { background: #dfe7d8; font-weight: 700; text-transform: uppercase; }
        .category-row td { background: #f2f2f2; font-weight: 700; text-transform: uppercase; }
        .mean-row td { background: #fafafa; font-weight: 700; }
        .statement-title { line-height: 1.35; }
        .evaluation-note { font-size: .72rem; white-space: pre-line; }
        @media print {
            body { background: #fff; }
            .report-paper { box-shadow: none; margin: 0; max-width: none; padding: 0; }
            .no-print { display: none !important; }
            thead { display: table-header-group; }
            tr { break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="no-print bg-dark py-3 text-center">
    <a href="{{ route('accreditor.show_area', $area) }}" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left"></i> Back to Area</a>
    <button type="button" onclick="window.print()" class="btn btn-success btn-sm"><i class="bi bi-printer"></i> Print / Save PDF</button>
</div>

<main class="report-paper">
    <header class="report-title survey-intro">
        <h5 class="mb-3">{{ strtoupper($area->code) }}</h5>
        <h6 class="mb-4">{{ strtoupper($area->name) }}</h6>
        <div class="parameter-list">
            <strong>PARAMETERS</strong>
            <ol type="A" class="mb-0 ps-4">
                @forelse($area->parameters as $parameter)
                    <li>{{ $parameter->title }}</li>
                @empty
                    <li>No Parameters available</li>
                @endforelse
            </ol>
        </div>
        <table class="survey-details">
            <tr><th>Program:</th><td>Not specified</td></tr>
            <tr><th>Level:</th><td>Not specified</td></tr>
            <tr><th>SUC:</th><td>Marinduque State University</td></tr>
            <tr><th>Campus:</th><td>Not specified</td></tr>
            <tr><th>Date of Actual Survey:</th><td>{{ now()->format('M. d, Y') }}</td></tr>
        </table>
    </header>

    <section class="mb-4">
        <h6 class="fw-bold">RATING SCALE</h6>
        <table class="table table-bordered rating-scale mb-0">
            <thead><tr><th>0</th><th>1</th><th>2</th><th>3</th><th>4</th><th>5</th></tr></thead>
            <tbody><tr><td>Missing</td><td>Poor</td><td>Fair</td><td>Satisfactory</td><td>Very Satisfactory</td><td>Excellent</td></tr></tbody>
        </table>
    </section>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">INDICATOR EVALUATIONS</h6>
        <strong>Area Mean: {{ $areaMean !== null ? number_format($areaMean, 2) : 'Not rated' }}</strong>
    </div>
    <table class="table table-bordered evaluation-table">
        <thead>
            <tr>
                <th style="width: 68%;">Indicators</th>
                <th style="width: 10%;">Item Rating (IR)</th>
                <th style="width: 11%;">System - Implementation - Outcome Mean (SIOM)</th>
                <th style="width: 11%;">Parameter Mean (PM)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $previousParameterId = null;
                $previousCategoryId = null;
            @endphp
            @forelse($rows as $row)
                @php
                    $statement = $row['statement'];
                    $meanRating = $statement->evaluations->isNotEmpty() ? number_format($statement->evaluations->avg('rating'), 2) : null;
                @endphp
                @if($previousParameterId !== $row['parameter']->id)
                    <tr class="parameter-row"><td colspan="4">Parameter {{ $row['parameter']->code }}: {{ $row['parameter']->title }}</td></tr>
                    @php
                        $previousParameterId = $row['parameter']->id;
                        $previousCategoryId = null;
                    @endphp
                @endif
                @if($previousCategoryId !== $row['category']->id)
                    <tr class="category-row"><td colspan="4">{{ $row['category']->category->name }}</td></tr>
                    @php
                        $previousCategoryId = $row['category']->id;
                    @endphp
                @endif
                <tr>
                    <td class="statement-title" style="padding-left: {{ .65 + ($row['depth'] * 1.15) }}rem;"><strong>{{ $statement->code ?? 'N/A' }}</strong> {{ $statement->name }}</td>
                    <td class="text-center"><strong>{{ $meanRating ?? '—' }}</strong></td>
                    <td></td>
                    <td></td>
                </tr>
                @php
                    $nextRow = $rows->get($loop->index + 1);
                    $isLastCategoryRow = !$nextRow || $nextRow['category']->id !== $row['category']->id;
                    $isLastParameterRow = !$nextRow || $nextRow['parameter']->id !== $row['parameter']->id;
                @endphp
                @if($isLastCategoryRow)
                    <tr class="mean-row">
                        <td class="text-end">{{ strtoupper($row['category']->category->name) }} MEAN</td>
                        <td></td>
                        <td class="text-center">{{ $categoryMeans[$row['category']->id] !== null ? number_format($categoryMeans[$row['category']->id], 2) : '—' }}</td>
                        <td></td>
                    </tr>
                @endif
                @if($isLastParameterRow)
                    <tr class="mean-row">
                        <td class="text-end">PARAMETER {{ $row['parameter']->code }} MEAN</td>
                        <td></td><td></td>
                        <td class="text-center">{{ $parameterMeans[$row['parameter']->id] !== null ? number_format($parameterMeans[$row['parameter']->id], 2) : '—' }}</td>
                    </tr>
                @endif
            @empty
                <tr><td colspan="4" class="text-center text-muted py-3">No indicators are available for evaluation.</td></tr>
            @endforelse
        </tbody>
    </table>

    <section class="mt-5">
        <h6 class="fw-bold">ACCREDITOR EVALUATIONS AND RECOMMENDATIONS</h6>
        <table class="table table-bordered table-sm evaluation-note">
            <thead><tr><th style="width: 28%;">Indicator</th><th>Evaluation / Recommendation</th><th style="width: 20%;">Accreditor</th></tr></thead>
            <tbody>
                @php($hasSubmittedEvaluations = false)
                @foreach($rows as $row)
                    @foreach($row['statement']->evaluations as $evaluation)
                        @php($hasSubmittedEvaluations = true)
                        <tr><td><strong>{{ $row['statement']->code ?? 'N/A' }}</strong> {{ $row['statement']->name }}</td><td>{{ $evaluation->evaluation ?: 'No written evaluation.' }}</td><td>{{ $evaluation->user->name }}</td></tr>
                    @endforeach
                @endforeach
                @if(!$hasSubmittedEvaluations)
                    <tr><td colspan="3" class="text-center text-muted">No evaluations have been submitted.</td></tr>
                @endif
            </tbody>
        </table>
    </section>

    <section class="mt-5" style="max-width: 720px;">
        <h6 class="fw-bold">SUMMARY OF RATINGS</h6>
        <table class="table table-bordered table-sm">
            <thead><tr><th>Parameters</th><th>Numerical Rating</th><th>Descriptive Rating</th></tr></thead>
            <tbody>
                @forelse($area->parameters as $parameter)
                    @php($parameterMean = $parameterMeans[$parameter->id])
                    <tr>
                        <td><strong>{{ $parameter->code }}.</strong> {{ $parameter->title }}</td>
                        <td class="text-center">{{ $parameterMean !== null ? number_format($parameterMean, 2) : '—' }}</td>
                        <td>
                            @if($parameterMean === null) Not rated
                            @elseif($parameterMean >= 5) Excellent
                            @elseif($parameterMean >= 4) Very Satisfactory
                            @elseif($parameterMean >= 3) Satisfactory
                            @elseif($parameterMean >= 2) Fair
                            @else Poor
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted">No Parameters are available.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="text-end" style="max-width: 520px;">
            <div><strong>Total</strong><span class="ms-4 fw-bold">{{ $ratedParameterCount > 0 ? number_format($parameterRatingTotal, 2) : '—' }}</span></div>
            <div><strong>Mean</strong><span class="ms-4 fw-bold">{{ $areaMean !== null ? number_format($areaMean, 2) : 'Not rated' }}</span>
                <span class="ms-3">
                    @if($areaMean === null) Not rated
                    @elseif($areaMean >= 5) Excellent
                    @elseif($areaMean >= 4) Very Satisfactory
                    @elseif($areaMean >= 3) Satisfactory
                    @elseif($areaMean >= 2) Fair
                    @else Poor
                    @endif
                </span>
            </div>
        </div>
        <div class="mt-5 ms-5" style="max-width: 360px;">
            <strong class="d-block mb-3">LEAD ACCREDITOR/S</strong>
            <div class="border-bottom border-dark pb-1">{{ $area->accreditors->first()->name ?? '____________________________' }}</div>
            <div class="border-bottom border-dark mt-4"></div>
        </div>
    </section>
</main>

</body>
</html>