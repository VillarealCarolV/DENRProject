@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-10 mt-2">
        
        @if(session('success'))
            <div class="alert alert-success fw-bold shadow-sm">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger shadow-sm">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow border-0">
            <div class="card-header bg-dark text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-file-signature me-2"></i> Master Intake Form</h5>
                <small class="text-light">Complete Lane 1 of the processing flowchart in a single step.</small>
            </div>
            <div class="card-body p-4 bg-light">
                
                <form action="{{ route('applications.masterStore') }}" method="POST">
                    @csrf 

                    <h6 class="text-primary fw-bold border-bottom pb-2 mb-3"><i class="fas fa-user me-2"></i>1. Applicant Information</h6>
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-secondary">Full Name</label>
                            <input type="text" name="full_name" class="form-control" required placeholder="Juan Dela Cruz" value="{{ old('full_name') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-secondary">Address</label>
                            <input type="text" name="address" class="form-control" placeholder="Bocaue, Bulacan" value="{{ old('address') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-secondary">Contact No.</label>
                            <input type="text" name="contact_no" class="form-control" placeholder="09123456789" value="{{ old('contact_no') }}">
                        </div>
                    </div>

                    <h6 class="text-success fw-bold border-bottom pb-2 mb-3"><i class="fas fa-map me-2"></i>2. Mother Lot / Land Details</h6>
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-secondary">Survey Number</label>
                            <input type="text" name="survey_no" class="form-control" required placeholder="CSD-03-012345" value="{{ old('survey_no') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-secondary">Total Area (sqm)</label>
                            <input type="number" step="0.01" name="total_area" class="form-control" required placeholder="1000" value="{{ old('total_area') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-secondary">Location</label>
                            <input type="text" name="location" class="form-control" required placeholder="Brgy. Igulot" value="{{ old('location') }}">
                        </div>
                    </div>

                    <h6 class="text-warning fw-bold border-bottom pb-2 mb-3"><i class="fas fa-barcode me-2"></i>3. Application Tracking</h6>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary">Tracking Number</label>
                            <input type="text" name="tracking_no" class="form-control border-warning" required placeholder="CENRO-2026-003" value="{{ old('tracking_no') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary">Date Received</label>
                            <input type="date" name="date_received" class="form-control" required value="{{ old('date_received') }}">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-dark w-100 fw-bold py-3 fs-5 shadow-sm">
                        <i class="fas fa-check-double me-2"></i> Process Complete Intake
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection