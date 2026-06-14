@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 text-dark">My Pending Backlog</h1>
                <p class="text-muted">Applications waiting for your processing (oldest first)</p>
            </div>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Reports
            </a>
        </div>
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
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0">
            <i class="bi bi-list-task"></i> Pending Applications
        </h5>
    </div>
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
</style>
@endsection
