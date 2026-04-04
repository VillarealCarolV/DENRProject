@extends('layouts.app')

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fas fa-edit me-2 text-primary"></i> Edit Applicant</h3>
        <a href="{{ route('applicants.index') }}" class="btn btn-secondary fw-bold shadow-sm">
            <i class="fas fa-arrow-left me-2"></i> Back
        </a>
    </div>

    <div class="card shadow-sm border-0" style="max-width: 600px;">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-pencil-alt me-2"></i> Update Applicant Information</h5>
        </div>
        <div class="card-body p-4">
            
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

            <form action="{{ route('applicants.update', $applicant->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-bold"><i class="fas fa-user me-2 text-info"></i>Full Name</label>
                    <input 
                        type="text" 
                        name="full_name" 
                        class="form-control @error('full_name') is-invalid @enderror" 
                        value="{{ old('full_name', $applicant->full_name) }}"
                        placeholder="e.g., Juan Dela Cruz"
                        required>
                    @error('full_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold"><i class="fas fa-map-marker-alt me-2 text-danger"></i>Address</label>
                    <input 
                        type="text" 
                        name="address" 
                        class="form-control @error('address') is-invalid @enderror"
                        value="{{ old('address', $applicant->address) }}"
                        placeholder="e.g., Bocaue, Bulacan">
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold"><i class="fas fa-phone me-2 text-success"></i>Contact Number</label>
                    <input 
                        type="text" 
                        name="contact_no" 
                        class="form-control @error('contact_no') is-invalid @enderror"
                        value="{{ old('contact_no', $applicant->contact_no) }}"
                        placeholder="e.g., 09123456789">
                    @error('contact_no')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-bold shadow-sm">
                        <i class="fas fa-save me-2"></i> Update Applicant
                    </button>
                    <a href="{{ route('applicants.show', $applicant->id) }}" class="btn btn-secondary fw-bold">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
