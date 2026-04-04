@extends('layouts.app')

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fas fa-map me-2 text-success"></i> Land Record Details</h3>
        <a href="{{ route('land-records.index') }}" class="btn btn-secondary fw-bold shadow-sm">
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
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-barcode me-2"></i> {{ $landRecord->survey_no }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-3">
                        <strong><i class="fas fa-barcode me-2 text-info"></i> Survey Number:</strong><br>
                        <span class="text-primary fw-bold">{{ $landRecord->survey_no }}</span>
                    </p>
                    <p class="mb-3">
                        <strong><i class="fas fa-map-marker-alt me-2 text-danger"></i> Location:</strong><br>
                        {{ $landRecord->location }}
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-3">
                        <strong><i class="fas fa-ruler me-2 text-warning"></i> Total Area:</strong><br>
                        {{ number_format($landRecord->total_area, 2) }} sqm
                    </p>
                    <p class="mb-3">
                        <strong><i class="fas fa-check-circle me-2 text-success"></i> Subdivided:</strong><br>
                        @if($landRecord->is_subdivided)
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </p>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6">
                    <p class="small text-muted mb-0">
                        <i class="fas fa-calendar me-1"></i> Created: {{ $landRecord->created_at->format('M d, Y h:i A') }}
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="small text-muted mb-0">
                        <i class="fas fa-sync me-1"></i> Last Updated: {{ $landRecord->updated_at->format('M d, Y h:i A') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex gap-2">
        <a href="{{ route('land-records.edit', $landRecord->id) }}" class="btn btn-primary fw-bold shadow-sm">
            <i class="fas fa-edit me-2"></i> Edit
        </a>
        <a href="{{ route('land-records.index') }}" class="btn btn-secondary fw-bold shadow-sm">
            <i class="fas fa-arrow-left me-2"></i> Back to List
        </a>
    </div>

</div>
@endsection
