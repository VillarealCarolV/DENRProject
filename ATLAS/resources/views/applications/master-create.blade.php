@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-lg">
            <div class="card-header bg-dark text-white p-4">
                <h3 class="mb-0 fw-bold"><i class="fas fa-file-signature me-2"></i>Master Intake Form</h3>
                <small class="text-light">Create a new application with applicant and land record information</small>
            </div>

            <form action="{{ route('applications.masterStore') }}" method="POST">
                @csrf
                <div class="card-body p-4 bg-light">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Validation Errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Applicant Information Section -->
                    <h5 class="text-primary fw-bold border-bottom pb-3 mb-4">
                        <i class="fas fa-user me-2"></i>Applicant Information
                    </h5>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control form-control-lg" required placeholder="Juan Dela Cruz" value="{{ old('full_name') }}">
                            @error('full_name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-bold">Address</label>
                            <input type="text" name="address" class="form-control form-control-lg" placeholder="Bocaue, Bulacan" value="{{ old('address') }}">
                            @error('address')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-bold">Contact No.</label>
                            <input type="text" name="contact_no" class="form-control form-control-lg" placeholder="09123456789" value="{{ old('contact_no') }}">
                            @error('contact_no')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Land Record / Mother Lot Section -->
                    <h5 class="text-success fw-bold border-bottom pb-3 mb-4">
                        <i class="fas fa-map me-2"></i>Mother Lot / Land Details
                    </h5>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-bold">Survey Number <span class="text-danger">*</span></label>
                            <input type="text" name="survey_no" class="form-control form-control-lg" required placeholder="CSD-03-012345" value="{{ old('survey_no') }}" pattern="^[A-Za-z]{3}-\d{2}-\d{6}$">
                            <small class="text-muted d-block mt-1">Format: XXX-XX-XXXXXX (e.g., CSD-03-012345)</small>
                            @error('survey_no')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-bold">Total Area (sqm) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="total_area" class="form-control form-control-lg" required placeholder="1000.50" value="{{ old('total_area') }}">
                            @error('total_area')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-bold">Location <span class="text-danger">*</span></label>
                            <input type="text" name="location" class="form-control form-control-lg" required placeholder="Brgy. Igulot" value="{{ old('location') }}">
                            @error('location')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Application Tracking Section -->
                    <h5 class="text-warning fw-bold border-bottom pb-3 mb-4">
                        <i class="fas fa-barcode me-2"></i>Application Tracking
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-bold">Tracking Number <span class="text-danger">*</span></label>
                            <input type="text" name="tracking_no" class="form-control form-control-lg" required placeholder="CENRO-2026-003" value="{{ old('tracking_no') }}">
                            @error('tracking_no')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-bold">Date Received <span class="text-danger">*</span></label>
                            <input type="date" name="date_received" class="form-control form-control-lg" required value="{{ old('date_received') }}">
                            @error('date_received')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light p-4 d-flex justify-content-between gap-2">
                    <a href="{{ route('applications.index') }}" class="btn btn-secondary btn-lg fw-bold">
                        <i class="fas fa-arrow-left me-2"></i> Back to Applications
                    </a>
                    <button type="submit" class="btn btn-dark btn-lg fw-bold">
                        <i class="fas fa-check-double me-2"></i> Process Intake
                    </button>
                </div>
            </form>
        </div>

        <!-- Help Text -->
        <div class="alert alert-info mt-4">
            <h6 class="fw-bold mb-2"><i class="fas fa-info-circle me-2"></i>What is the Master Intake Form?</h6>
            <p class="mb-0">The Master Intake Form allows you to create a complete application record in a single step. It will automatically:</p>
            <ul class="mb-0 mt-2">
                <li>Create a new Applicant record with the provided personal information</li>
                <li>Create a new Land Record (Mother Lot) with the survey details</li>
                <li>Link both records together in an Application</li>
                <li>Automatically log the initial "Pending" status</li>
            </ul>
        </div>
    </div>
</div>
@endsection
