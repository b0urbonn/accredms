@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="page-heading">
    <div>
        <h3 class="fw-bold mb-1">System Overview</h3>
        <p class="mb-0">A concise view of your accreditation workspace and recent activity.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.areas.index') }}" class="btn btn-apple-green">
            <i class="bi bi-plus-circle me-1"></i> Add New Area
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-dark">
            <i class="bi bi-person-plus me-1"></i> Add User
        </a>
    </div>
</div>

<!-- Stat Cards Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-custom metric-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="metric-label">Accreditation Areas</span>
                    <div class="metric-value mt-1">{{ $totalAreas }}</div>
                </div>
                <div class="metric-icon">
                    <i class="bi bi-folder2-open"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-custom metric-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="metric-label">System Users</span>
                    <div class="metric-value mt-1">{{ $totalUsers }}</div>
                </div>
                <div class="metric-icon">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-custom metric-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="metric-label">Uploaded PDFs</span>
                    <div class="metric-value mt-1">{{ $totalDocuments }}</div>
                </div>
                <div class="metric-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-custom metric-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="metric-label">Total Storage</span>
                    <div class="metric-value mt-1">
                        @if($totalSizeBytes >= 1073741824)
                            {{ number_format($totalSizeBytes / 1073741824, 2) }} GB
                        @elseif($totalSizeBytes >= 1048576)
                            {{ number_format($totalSizeBytes / 1048576, 2) }} MB
                        @else
                            {{ number_format($totalSizeBytes / 1024, 2) }} KB
                        @endif
                    </div>
                </div>
                <div class="metric-icon">
                    <i class="bi bi-device-ssd"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Visual Analytics Charts Section -->
<div class="row g-4 mb-4">
    <!-- Area Progress Bar Chart -->
    <div class="col-lg-8">
        <div class="card card-custom h-100">
            <div class="card-header bg-white py-3 border-bottom-0 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="section-title mb-1"><i class="bi bi-bar-chart-line-fill me-2 text-accent"></i>Area Compliance Progress</h5>
                    <p class="text-muted fs-8 mb-0">Statement compliance completion percentage per accreditation area.</p>
                </div>
                <a href="{{ route('admin.areas.index') }}" class="section-link">Manage Areas <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="card-body pt-0 pb-3" style="position: relative; min-height: 260px;">
                <canvas id="areaProgressChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Category Doughnut Chart -->
    <div class="col-lg-4">
        <div class="card card-custom h-100">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="section-title mb-1"><i class="bi bi-pie-chart-fill me-2 text-accent"></i>Evidence by Category</h5>
                <p class="text-muted fs-8 mb-0">Distribution of uploaded files across fixed categories.</p>
            </div>
            <div class="card-body pt-0 pb-3 d-flex align-items-center justify-content-center" style="position: relative; min-height: 260px;">
                <canvas id="categoryDoughnutChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Area Task Board -->
<div class="card card-custom mb-4">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h5 class="section-title mb-1"><i class="bi bi-list-check me-2 text-accent"></i>Area Task Board</h5>
        <p class="text-muted fs-8 mb-0">System-wide evidence progress, returned requests, and deadlines.</p>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light fs-7 text-uppercase text-secondary">
                <tr><th class="ps-3">Area</th><th>Progress</th><th>Missing Evidence</th><th>Returned for Revision</th><th>Nearest Deadline</th><th class="text-end pe-3">Action</th></tr>
            </thead>
            <tbody>
                @forelse($adminAreaTasks as $task)
                    <tr>
                        <td class="ps-3"><span class="badge bg-apple-dark me-1">{{ $task->area->code }}</span><span class="fw-semibold">{{ $task->area->name }}</span></td>
                        <td style="min-width: 150px;"><div class="d-flex justify-content-between fs-8 mb-1"><span>{{ $task->completedStatements }}/{{ $task->totalStatements }} statements</span><strong>{{ $task->progressPercent }}%</strong></div><div class="progress" style="height: 7px;"><div class="progress-bar bg-success" style="width: {{ $task->progressPercent }}%"></div></div></td>
                        <td><span class="badge {{ $task->missingEvidenceCount ? 'text-bg-warning' : 'text-bg-success' }}">{{ $task->missingEvidenceCount }} {{ Str::plural('item', $task->missingEvidenceCount) }}</span></td>
                        <td><span class="badge {{ $task->returnedForRevisionCount ? 'text-bg-danger' : 'text-bg-secondary' }}">{{ $task->returnedForRevisionCount }} {{ Str::plural('request', $task->returnedForRevisionCount) }}</span></td>
                        <td>@if($task->nextDeadline)<span class="{{ $task->hasOverdueDeadline ? 'text-danger fw-semibold' : 'text-dark' }}"><i class="bi bi-calendar-event me-1"></i>{{ $task->nextDeadline->format('M d, Y') }}</span>@if($task->hasOverdueDeadline)<small class="d-block text-danger">Overdue</small>@endif @else <span class="text-muted fs-7">No deadline</span>@endif</td>
                        <td class="text-end pe-3"><a href="{{ route('admin.areas.show', $task->area) }}" class="btn btn-sm btn-apple-green"><i class="bi bi-box-arrow-up-right me-1"></i>Open Area</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No accreditation areas created yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card card-custom mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom-0">
        <h5 class="section-title mb-0"><i class="bi bi-file-earmark-plus me-2 text-accent"></i>Open Additional Document Requests</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light fs-7 text-uppercase text-secondary"><tr><th class="ps-3">Area / Statement</th><th>Missing Documents</th><th>Accreditor Instructions</th><th>Accreditor</th><th>Due Date</th><th class="text-end pe-3">View</th></tr></thead>
            <tbody>
                @forelse($openDocumentRequests as $documentRequest)
                    <tr>
                        <td class="ps-3"><span class="badge bg-secondary">{{ $documentRequest->subfolder->parameterCategory->parameter->area->code }}</span> {{ $documentRequest->subfolder->code }} - {{ $documentRequest->subfolder->name }}</td>
                        <td>{{ $documentRequest->requested_documents ?: 'See instructions.' }}</td>
                        <td>{{ $documentRequest->remarks }}</td>
                        <td>{{ $documentRequest->requester->name }}</td>
                        <td>{{ $documentRequest->due_date?->format('M d, Y') ?? 'No due date' }}</td>
                        <td class="text-end pe-3"><a href="{{ route('admin.areas.show', $documentRequest->subfolder->parameterCategory->parameter->area) }}" class="btn btn-sm btn-outline-success">View Statement</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No open additional document requests.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="row g-4">
    <!-- Areas List Card -->
    <div class="col-lg-7">
        <div class="card card-custom h-100">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom-0">
                <h5 class="section-title mb-0"><i class="bi bi-diagram-3 me-2 text-accent"></i>Accreditation Areas</h5>
                <a href="{{ route('admin.areas.index') }}" class="section-link">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light fs-7 text-uppercase text-secondary">
                        <tr>
                            <th class="ps-3">Code</th>
                            <th>Area Name</th>
                            <th>Parameters</th>
                            <th>Assigned Users</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($areas as $area)
                            <tr>
                                <td class="ps-3"><span class="badge bg-apple-dark">{{ $area->code }}</span></td>
                                <td class="fw-semibold">{{ $area->name }}</td>
                                <td><span class="badge bg-secondary">{{ $area->parameters_count }} parameters</span></td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                        {{ $area->users_count }} assigned
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('admin.areas.show', $area) }}" class="btn btn-sm btn-outline-success">Manage</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No accreditation areas created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Audit Activity Feed -->
    <div class="col-lg-5">
        <div class="card card-custom h-100">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom-0">
                <h5 class="section-title mb-0"><i class="bi bi-journal-text me-2 text-accent"></i>Audit Activity</h5>
                <a href="{{ route('admin.audit_logs.index') }}" class="section-link">Full logs <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush fs-7">
                    @forelse($recentActivities as $log)
                        <li class="list-group-item py-3 px-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="fw-bold text-dark">{{ $log->user->name ?? 'System' }}</span>
                                    <span class="badge bg-light text-dark border ms-1">{{ $log->action }}</span>
                                </div>
                                <small class="text-muted" style="font-size: 0.72rem;">{{ $log->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="text-secondary mb-0 mt-1 fs-7">{{ $log->description }}</p>
                        </li>
                    @empty
                        <li class="list-group-item text-center py-4 text-muted">No activity logs recorded.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const textColor = isDark ? '#e4e6ea' : '#495057';
        const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';

        // 1. Area Progress Bar Chart
        const areaCodes = @json($adminAreaTasks->pluck('area.code'));
        const areaProgresses = @json($adminAreaTasks->pluck('progressPercent'));

        const barColors = areaProgresses.map(p => {
            if (p >= 80) return '#40916c';
            if (p >= 40) return '#d4b44c';
            return '#e63946';
        });

        const ctxArea = document.getElementById('areaProgressChart');
        if (ctxArea) {
            new Chart(ctxArea, {
                type: 'bar',
                data: {
                    labels: areaCodes,
                    datasets: [{
                        label: 'Completion Progress (%)',
                        data: areaProgresses,
                        backgroundColor: barColors,
                        borderRadius: 6,
                        barThickness: 22
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Progress: ' + context.raw + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: 'transparent' },
                            ticks: { color: textColor, font: { family: 'DM Sans', weight: '600' } }
                        },
                        y: {
                            min: 0,
                            max: 100,
                            grid: { color: gridColor },
                            ticks: {
                                color: textColor,
                                font: { family: 'DM Sans' },
                                callback: function(value) { return value + '%'; }
                            }
                        }
                    }
                }
            });
        }

        // 2. Category Doughnut Chart
        const categoryData = @json(array_values($categoryDistribution));
        const totalCategoryDocs = categoryData.reduce((a, b) => a + b, 0);
        const displayData = totalCategoryDocs > 0 ? categoryData : [1, 1, 1];
        const displayColors = totalCategoryDocs > 0
            ? ['#40916c', '#2a6f97', '#d4b44c']
            : [isDark ? '#334155' : '#e2e8f0', isDark ? '#334155' : '#e2e8f0', isDark ? '#334155' : '#e2e8f0'];

        const ctxCategory = document.getElementById('categoryDoughnutChart');
        if (ctxCategory) {
            new Chart(ctxCategory, {
                type: 'doughnut',
                data: {
                    labels: ['System Input & Process', 'Outcomes', 'Implementation'],
                    datasets: [{
                        data: displayData,
                        backgroundColor: displayColors,
                        borderWidth: 2,
                        borderColor: isDark ? '#1a1d24' : '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: textColor,
                                font: { family: 'DM Sans', size: 11, weight: '500' },
                                padding: 15
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (totalCategoryDocs === 0) {
                                        return context.label + ': 0 files uploaded';
                                    }
                                    return context.label + ': ' + context.raw + ' files';
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }
    });
</script>
@endsection
