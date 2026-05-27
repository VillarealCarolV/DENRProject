@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 text-dark">Reports</h1>
        <p class="text-muted">Access and manage your reports</p>
    </div>
</div>

<!-- Land Management Officer Tabbed Reports -->
@if(Auth::user()->role === 'land_officer')
    <div class="card border-0 shadow-sm">
        <!-- Navigation Tabs -->
        <div class="card-header bg-white border-bottom">
            <ul class="nav nav-tabs card-header-tabs" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pending-backlog-tab" data-bs-toggle="tab" data-bs-target="#pending-backlog-content" type="button" role="tab" aria-controls="pending-backlog-content" aria-selected="true">
                        <i class="bi bi-list-task"></i> My Pending Backlog
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="subdivision-tab" data-bs-toggle="tab" data-bs-target="#subdivision-content" type="button" role="tab" aria-controls="subdivision-content" aria-selected="false">
                        <i class="bi bi-graph-up"></i> Land Subdivision Report
                    </button>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div class="tab-content" id="reportTabsContent">
            <!-- My Pending Backlog Tab -->
            <div class="tab-pane fade show active" id="pending-backlog-content" role="tabpanel" aria-labelledby="pending-backlog-tab">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-12">
                            <p class="text-muted mb-0">Applications waiting for your processing (oldest first)</p>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <div class="card border-left-primary h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="text-muted small text-uppercase">Pending Applications</span>
                                            <h3 class="text-primary font-weight-bold mt-2">{{ $totalPending }}</h3>
                                        </div>
                                        <div class="text-primary opacity-50">
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card border-left-success h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="text-muted small text-uppercase">Total Assigned</span>
                                            <h3 class="text-success font-weight-bold mt-2">{{ $totalAssigned }}</h3>
                                        </div>
                                        <div class="text-success opacity-50">
                                            <i class="fas fa-inbox fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card border-left-info h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="text-muted small text-uppercase">Processed</span>
                                            <h3 class="text-info font-weight-bold mt-2">{{ $totalProcessed }}</h3>
                                        </div>
                                        <div class="text-info opacity-50">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Applications Table -->
                    <div class="card border-0 shadow-sm border-top">
                        <div class="card-body p-0">
                            @if($pendingApplications->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="px-4 py-3">Tracking No</th>
                                                <th class="px-4 py-3">Applicant Name</th>
                                                <th class="px-4 py-3">Survey No</th>
                                                <th class="px-4 py-3">Date Received</th>
                                                <th class="px-4 py-3">Lot Type</th>
                                                <th class="px-4 py-3">Days Pending</th>
                                                <th class="px-4 py-3">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pendingApplications as $application)
                                                @php
                                                    $daysPending = $application->date_received->diffInDays(now());
                                                    $urgency = $daysPending > 30 ? 'danger' : ($daysPending > 14 ? 'warning' : 'info');
                                                @endphp
                                                <tr>
                                                    <td class="px-4 py-3">
                                                        <strong>{{ $application->tracking_no }}</strong>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        {{ $application->applicant->name ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        {{ $application->landRecord->survey_no ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <small class="text-muted">{{ $application->date_received->format('M d, Y') }}</small>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        @if($application->lot_type)
                                                            <span class="badge bg-light text-dark">{{ $application->lot_type }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <span class="badge bg-{{ $urgency }}">
                                                            {{ $daysPending }} day{{ $daysPending !== 1 ? 's' : '' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <a href="{{ route('applications.show', $application->id) }}" 
                                                           class="btn btn-sm btn-outline-primary" 
                                                           title="View Details">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="{{ route('applications.edit', $application->id) }}" 
                                                           class="btn btn-sm btn-outline-secondary" 
                                                           title="Process Application">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-success m-4" role="alert">
                                    <i class="bi bi-check-circle"></i> 
                                    <strong>Great!</strong> You have no pending applications. All your work is up to date.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Land Subdivision Report Tab -->
            <div class="tab-pane fade" id="subdivision-content" role="tabpanel" aria-labelledby="subdivision-tab">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-12">
                            <p class="text-muted mb-0">Monthly performance review - {{ now()->format('F Y') }}</p>
                        </div>
                    </div>

                    <!-- Key Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card border-left-success h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="text-muted small text-uppercase">Total Area Subdivided</span>
                                            <h3 class="text-success font-weight-bold mt-2">{{ number_format($totalAreaSubdivided, 2) }}</h3>
                                            <small class="text-muted">hectares</small>
                                        </div>
                                        <div class="text-success opacity-50">
                                            <i class="fas fa-chart-line fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card border-left-primary h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="text-muted small text-uppercase">Avg Area per Application</span>
                                            <h3 class="text-primary font-weight-bold mt-2">{{ number_format($averageAreaPerApplication, 2) }}</h3>
                                            <small class="text-muted">hectares</small>
                                        </div>
                                        <div class="text-primary opacity-50">
                                            <i class="fas fa-calculator fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card border-left-info h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="text-muted small text-uppercase">Applications Approved</span>
                                            <h3 class="text-info font-weight-bold mt-2">{{ $totalApplicationsApproved }}</h3>
                                            <small class="text-muted">this month</small>
                                        </div>
                                        <div class="text-info opacity-50">
                                            <i class="fas fa-check-double fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card border-left-warning h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="text-muted small text-uppercase">Classifications</span>
                                            <h3 class="text-warning font-weight-bold mt-2">{{ $classificationBreakdown->count() }}</h3>
                                            <small class="text-muted">lot types</small>
                                        </div>
                                        <div class="text-warning opacity-50">
                                            <i class="fas fa-tags fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Classification Breakdown & Monthly Trend -->
                    <div class="row mb-4">
                        <div class="col-lg-6 mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h5 class="mb-0">
                                        <i class="bi bi-tags"></i> Classification Breakdown
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($classificationBreakdown->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Lot Type</th>
                                                        <th>Count</th>
                                                        <th>Total Area</th>
                                                        <th>Avg Area</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($classificationBreakdown as $type => $data)
                                                        <tr>
                                                            <td>
                                                                <strong>{{ $type ?? 'Unclassified' }}</strong>
                                                            </td>
                                                            <td>{{ $data['count'] }}</td>
                                                            <td>{{ number_format($data['total_area'], 2) }} ha</td>
                                                            <td>{{ number_format($data['average_area'], 2) }} ha</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted text-center py-3">No approved applications this month</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Monthly Trend -->
                        <div class="col-lg-6 mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h5 class="mb-0">
                                        <i class="bi bi-calendar3"></i> Monthly Trend (Year-to-Date)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="monthlyTrendChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approved Applications List -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-list-check"></i> Approved Applications This Month
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @if($subdividedApplications->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="px-4 py-3">Tracking No</th>
                                                <th class="px-4 py-3">Applicant</th>
                                                <th class="px-4 py-3">Survey No</th>
                                                <th class="px-4 py-3">Lot Type</th>
                                                <th class="px-4 py-3">Subdivided Area</th>
                                                <th class="px-4 py-3">Remaining Area</th>
                                                <th class="px-4 py-3">Approval Date</th>
                                                <th class="px-4 py-3">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($subdividedApplications as $application)
                                                <tr>
                                                    <td class="px-4 py-3">
                                                        <strong>{{ $application->tracking_no }}</strong>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        {{ $application->applicant->name ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        {{ $application->landRecord->survey_no ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        @if($application->lot_type)
                                                            <span class="badge bg-light text-dark">{{ $application->lot_type }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        @if($application->subdivided_area)
                                                            <strong>{{ number_format($application->subdivided_area, 2) }} ha</strong>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        @if($application->remaining_area)
                                                            {{ number_format($application->remaining_area, 2) }} ha
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <small class="text-muted">
                                                            {{ $application->statusHistories->first()?->created_at->format('M d, Y') ?? 'N/A' }}
                                                        </small>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <a href="{{ route('applications.show', $application->id) }}" 
                                                           class="btn btn-sm btn-outline-primary" 
                                                           title="View Details">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info m-4" role="alert">
                                    <i class="bi bi-info-circle"></i> 
                                    <strong>No Data Available</strong> - No approved applications this month yet.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .border-left-primary {
            border-left: 4px solid #0d6efd !important;
        }
        .border-left-success {
            border-left: 4px solid #198754 !important;
        }
        .border-left-info {
            border-left: 4px solid #0dcaf0 !important;
        }
        .border-left-warning {
            border-left: 4px solid #ffc107 !important;
        }

        /* Tab styling */
        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
        }

        .nav-tabs .nav-link {
            color: #6c757d;
            border: none;
            border-bottom: 3px solid transparent;
            font-weight: 500;
        }

        .nav-tabs .nav-link:hover {
            border-bottom-color: #0d6efd;
            color: #0d6efd;
        }

        .nav-tabs .nav-link.active {
            color: #0d6efd;
            background-color: transparent;
            border-bottom-color: #0d6efd;
        }
    </style>

    <!-- Chart.js for Monthly Trend -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script>
        // Initialize chart when the subdivision tab is shown
        let chartInitialized = false;
        
        document.getElementById('subdivision-tab').addEventListener('shown.bs.tab', function() {
            if (!chartInitialized) {
                const ctx = document.getElementById('monthlyTrendChart').getContext('2d');
                
                const monthlyData = @json($monthlyTrend);
                const labels = Object.keys(monthlyData);
                const data = Object.values(monthlyData);

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Area Subdivided (hectares)',
                            data: data,
                            backgroundColor: 'rgba(25, 135, 84, 0.6)',
                            borderColor: 'rgba(25, 135, 84, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Area (hectares)'
                                }
                            }
                        }
                    }
                });

                chartInitialized = true;
            }
        });

        // Trigger chart initialization if subdivision tab is already active
        if (document.getElementById('subdivision-tab').classList.contains('active')) {
            document.getElementById('subdivision-tab').dispatchEvent(new Event('shown.bs.tab'));
        }
    </script>
@else
    <!-- General Summary for Other Roles -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-center">
                        <i class="bi bi-file-earmark-text text-primary" style="font-size: 2rem;"></i>
                        <h6 class="mt-3">Total Applications</h6>
                        <h3 class="text-primary">{{ $totalApplications }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-center">
                        <i class="bi bi-people text-success" style="font-size: 2rem;"></i>
                        <h6 class="mt-3">Total Applicants</h6>
                        <h3 class="text-success">{{ $totalApplicants }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-center">
                        <i class="bi bi-map text-info" style="font-size: 2rem;"></i>
                        <h6 class="mt-3">Land Records</h6>
                        <h3 class="text-info">{{ $totalLandRecords }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Applications -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0">Recent Applications</h5>
        </div>
        <div class="card-body p-0">
            @if($recentApplications->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3">Tracking No</th>
                                <th class="px-4 py-3">Applicant</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Date Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentApplications as $app)
                                <tr>
                                    <td class="px-4 py-3"><strong>{{ $app->tracking_no }}</strong></td>
                                    <td class="px-4 py-3">{{ $app->applicant->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $status = $app->statusHistories->first()?->status ?? 'Unknown';
                                            $badgeClass = match($status) {
                                                'Pending' => 'warning',
                                                'Processing' => 'info',
                                                'Approved' => 'success',
                                                'Rejected' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }}">{{ $status }}</span>
                                    </td>
                                    <td class="px-4 py-3"><small class="text-muted">{{ $app->date_received->format('M d, Y') }}</small></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center py-4">No applications found</p>
            @endif
        </div>
    </div>
@endif
@endsection
