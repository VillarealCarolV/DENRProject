@extends('layouts.app')

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fas fa-file-alt me-2 text-primary"></i> Application Details</h3>
        <a href="{{ route('applications.index') }}" class="btn btn-secondary fw-bold shadow-sm">
            <i class="fas fa-arrow-left me-2"></i> Back to List
        </a>
    </div>

    <!-- Application Summary Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-barcode me-2"></i> Application: {{ $application->tracking_no }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-user me-2 text-info"></i> Applicant Information</h6>
                    <p class="mb-2"><strong>Full Name:</strong> {{ $application->applicant->full_name }}</p>
                    <p class="mb-2"><strong>Address:</strong> {{ $application->applicant->address ?? 'N/A' }}</p>
                    <p class="mb-2"><strong>Contact No:</strong> {{ $application->applicant->contact_no ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-map me-2 text-success"></i> Land Record Information</h6>
                    <p class="mb-2"><strong>Survey No:</strong> {{ $application->landRecord->survey_no }}</p>
                    <p class="mb-2"><strong>Total Area:</strong> {{ number_format($application->landRecord->total_area, 2) }} sqm</p>
                    <p class="mb-2"><strong>Location:</strong> {{ $application->landRecord->location }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Details Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-tasks me-2"></i> Application Details</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-3"><strong>Date Received:</strong> {{ \Carbon\Carbon::parse($application->date_received)->format('M d, Y') }}</p>
                    <p class="mb-3"><strong>Patent Details:</strong> {{ $application->patent_details ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-3"><strong>Patent Type:</strong> {{ $application->patent_type ?? 'N/A' }}</p>
                </div>
            </div>
            
            <!-- Current Status -->
            <div class="border-top pt-3 mt-3">
                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-check-circle me-2 text-warning"></i> Current Status</h6>
                @php
                    $latestStatus = $application->statusHistories()->latest()->first();
                    $statusText = $latestStatus ? $latestStatus->status : 'Pending';
                    $badgeColor = match($statusText) {
                        'Approved' => 'bg-success',
                        'Rejected' => 'bg-danger',
                        'In Process' => 'bg-info text-dark',
                        default => 'bg-warning text-dark',
                    };
                @endphp
                <span class="badge {{ $badgeColor }} px-3 py-2 shadow-sm">{{ $statusText }}</span>
            </div>
        </div>
    </div>

    <!-- Status History Timeline -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i> Status History & Audit Trail</h5>
        </div>
        <div class="card-body">
            <div class="timeline">
                @forelse($application->statusHistories()->latest()->get() as $history)
                    <div class="timeline-item mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex">
                            <div class="timeline-marker me-3">
                                <span class="badge bg-primary rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-{{ $history->status === 'Approved' ? 'check' : ($history->status === 'Rejected' ? 'times' : 'spinner') }}"></i>
                                </span>
                            </div>
                            <div class="timeline-content flex-grow-1">
                                <h6 class="fw-bold mb-1">{{ $history->status }}</h6>
                                <p class="text-muted small mb-2">{{ $history->remarks }}</p>
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i> Updated by: <strong>{{ $history->updated_by }}</strong>
                                    <i class="fas fa-clock ms-2 me-1"></i> {{ $history->created_at->format('M d, Y \a\t h:i A') }}
                                </small>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">No status history available.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex gap-2 mb-4">
        <button class="btn btn-success fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
            <i class="fas fa-edit me-2"></i> Update Status
        </button>
        <a href="{{ route('applications.index') }}" class="btn btn-secondary fw-bold shadow-sm">
            <i class="fas fa-arrow-left me-2"></i> Back to List
        </a>
    </div>

</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-tasks me-2"></i>Update Application Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('applications.updateStatus', $application->id) }}" method="POST">
                @csrf
                <div class="modal-body text-start">
                    <p class="mb-3">Updating status for: <strong>{{ $application->tracking_no }}</strong></p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Status</label>
                        <select name="status" class="form-select" required>
                            <option value="In Process">In Process</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Remarks / Notes</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="e.g., All documents verified. Patent ready for signature."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold">Save Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
