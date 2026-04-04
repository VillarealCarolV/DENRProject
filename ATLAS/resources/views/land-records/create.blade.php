@extends('layouts.app')

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fas fa-map me-2 text-success"></i> Create Land Record</h3>
        <a href="{{ route('land-records.index') }}" class="btn btn-secondary fw-bold shadow-sm">
            <i class="fas fa-arrow-left me-2"></i> Back
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-plus me-2"></i> Add Mother Lot</h5>
        </div>
        <div class="card-body p-4">
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <strong>Validation Errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('land-records.store') }}" method="POST">
                @csrf 

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold"><i class="fas fa-barcode me-2 text-info"></i>Survey Number</label>
                            <input 
                                type="text" 
                                name="survey_no" 
                                class="form-control @error('survey_no') is-invalid @enderror" 
                                value="{{ old('survey_no') }}"
                                placeholder="e.g., CSD-03-012345"
                                required>
                            @error('survey_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">Format: XXX-XX-XXXXXX</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold"><i class="fas fa-ruler me-2 text-warning"></i>Total Area (sq. meters)</label>
                            <input 
                                type="number" 
                                step="0.01" 
                                name="total_area" 
                                class="form-control @error('total_area') is-invalid @enderror"
                                value="{{ old('total_area') }}"
                                placeholder="e.g., 1000.50"
                                required>
                            @error('total_area')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold"><i class="fas fa-map-marker-alt me-2 text-danger"></i>Location</label>
                    <input 
                        type="text" 
                        name="location" 
                        class="form-control @error('location') is-invalid @enderror"
                        value="{{ old('location') }}"
                        placeholder="e.g., Brgy. Igulot, Bocaue, Bulacan"
                        required>
                    @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success fw-bold shadow-sm">
                        <i class="fas fa-save me-2"></i> Save Land Record
                    </button>
                    <a href="{{ route('land-records.index') }}" class="btn btn-secondary fw-bold">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection