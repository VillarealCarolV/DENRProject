@extends('layouts.app')

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fas fa-user me-2 text-primary"></i> Applicant Details</h3>
        <a href="{{ route('applicants.index') }}" class="btn btn-secondary fw-bold shadow-sm">
            <i class="fas fa-arrow-left me-2"></i> Back to List
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-user me-2"></i> {{ $applicant->full_name }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-3">
                        <strong><i class="fas fa-user me-2 text-info"></i> Full Name:</strong><br>
                        {{ $applicant->full_name }}
                    </p>
                    <p class="mb-3">
                        <strong><i class="fas fa-map-marker-alt me-2 text-danger"></i> Address:</strong><br>
                        {{ $applicant->address ?? '—' }}
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-3">
                        <strong><i class="fas fa-phone me-2 text-success"></i> Contact Number:</strong><br>
                        {{ $applicant->contact_no ?? '—' }}
                    </p>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6">
                    <p class="small text-muted mb-0">
                        <i class="fas fa-calendar me-1"></i> Created: {{ $applicant->created_at->format('M d, Y h:i A') }}
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="small text-muted mb-0">
                        <i class="fas fa-sync me-1"></i> Last Updated: {{ $applicant->updated_at->format('M d, Y h:i A') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex gap-2">
        <a href="{{ route('applicants.edit', $applicant->id) }}" class="btn btn-primary fw-bold shadow-sm">
            <i class="fas fa-edit me-2"></i> Edit
        </a>
        <a href="{{ route('applicants.index') }}" class="btn btn-secondary fw-bold shadow-sm">
            <i class="fas fa-arrow-left me-2"></i> Back to List
        </a>
    </div>

</div>
@endsection
