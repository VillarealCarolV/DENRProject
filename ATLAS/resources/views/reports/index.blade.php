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
                    {{-- <div class="row mb-4">
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
                    </div> --}}

                    <!-- Pending Applications Table -->
                    <div class="card border-0 shadow-sm border-top">
                        <!-- Export Button -->
                        <div style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-bottom: 1px solid #f0f0f0;">
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm fw-bold" type="button" onclick="document.getElementById('pendingBacklogExportMenu').classList.toggle('show')">
                                    <i class="fas fa-download me-2"></i> Export <i class="fas fa-caret-down ms-1"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" id="pendingBacklogExportMenu" style="position: absolute; right: 0;">
                                    <li><a class="dropdown-item" href="{{ route('reports.exportPendingBacklog', ['format' => 'csv']) }}"><i class="fas fa-file-csv me-2 text-success"></i>CSV</a></li>
                                    <li><a class="dropdown-item" href="{{ route('reports.exportPendingBacklog', ['format' => 'excel']) }}"><i class="fas fa-file-excel me-2 text-success"></i>Excel</a></li>
                                    <li><a class="dropdown-item" href="{{ route('reports.exportPendingBacklog', ['format' => 'pdf']) }}"><i class="fas fa-file-pdf me-2 text-danger"></i>PDF</a></li>
                                </ul>
                            </div>
                        </div>

                        <style>
                            .dropdown-menu {
                                display: none;
                                position: absolute;
                                background-color: #f9fafb;
                                min-width: 160px;
                                box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
                                padding: 12px 0;
                                z-index: 1;
                                right: 0;
                                border-radius: 4px;
                                border: 1px solid #e5e7eb;
                            }

                            .dropdown-menu.show {
                                display: block;
                            }

                            .dropdown-menu .dropdown-item {
                                color: #374151;
                                padding: 8px 16px;
                                text-decoration: none;
                                display: block;
                                cursor: pointer;
                            }

                            .dropdown-menu .dropdown-item:hover {
                                background-color: #f3f4f6;
                            }
                        </style>

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
                                                    $daysPending = (int)$application->date_received->diffInDays(now());
                                                    $urgency = $daysPending > 30 ? 'danger' : ($daysPending > 14 ? 'warning' : 'info');
                                                @endphp
                                                <tr>
                                                    <td class="px-4 py-3">
                                                        <strong>{{ $application->tracking_no }}</strong>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        {{ $application->applicant->full_name ?? 'N/A' }}
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
                                    <td class="px-4 py-3">{{ $app->applicant->full_name ?? 'N/A' }}</td>
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
