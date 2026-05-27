@extends('layouts.app')

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fas fa-pen-to-square me-2 text-success"></i> Assess Application</h3>
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
                    <p class="mb-2"><strong>Total Area (Mother Lot):</strong> <span class="badge bg-info text-dark">{{ number_format($application->landRecord->total_area, 2) }} sqm</span></p>
                    <p class="mb-2"><strong>Location:</strong> {{ $application->landRecord->location }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Land Officer Assessment Form -->
    <form action="{{ route('applications.update', $application->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-tasks me-2"></i> Physical Land Assessment</h5>
            </div>
            <div class="card-body">
                
                <!-- Step 1: Lot Type Selection -->
                <div class="mb-4 p-3 bg-light rounded border-left border-warning" style="border-left: 4px solid #ffc107;">
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="fas fa-step-backward me-2 text-warning"></i> Step 1: Lot Classification
                    </h6>
                    <p class="text-muted mb-3">Determine if this is an existing lot or a subdivision of the mother lot.</p>
                    
                    <div class="form-check mb-3">
                        <input 
                            class="form-check-input lot-type-radio" 
                            type="radio" 
                            name="lot_type" 
                            id="existing_lot" 
                            value="existing_lot"
                            {{ $application->lot_type === 'existing_lot' ? 'checked' : '' }}>
                        <label class="form-check-label" for="existing_lot">
                            <strong>Existing Lot</strong>
                            <small class="text-muted d-block">No new measurements needed. Use current cadastral data.</small>
                        </label>
                    </div>

                    <div class="form-check">
                        <input 
                            class="form-check-input lot-type-radio" 
                            type="radio" 
                            name="lot_type" 
                            id="subdivision" 
                            value="subdivision"
                            {{ $application->lot_type === 'subdivision' ? 'checked' : '' }}>
                        <label class="form-check-label" for="subdivision">
                            <strong>Subdivision</strong>
                            <small class="text-muted d-block">Lot is subdivided from the mother lot. Requires new lot number and area measurements.</small>
                        </label>
                    </div>
                </div>

                <!-- Step 2: Subdivision Details (Conditional) -->
                <div id="subdivisionFields" class="mb-4 p-3 bg-light rounded border-left border-info" style="border-left: 4px solid #0dcaf0; display: {{ $application->lot_type === 'subdivision' ? 'block' : 'none' }};">
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="fas fa-ruler me-2 text-info"></i> Step 2: Subdivision Details
                    </h6>
                    <p class="text-muted mb-3">Enter the new lot information. The system will automatically calculate the remaining area of the mother lot.</p>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_lot_number" class="form-label fw-bold">New Lot Number <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                class="form-control @error('new_lot_number') is-invalid @enderror" 
                                id="new_lot_number" 
                                name="new_lot_number" 
                                placeholder="e.g., 001-A, 2B-North"
                                value="{{ $application->new_lot_number ?? old('new_lot_number') }}"
                                required>
                            @error('new_lot_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="subdivided_area" class="form-label fw-bold">Subdivided Area (sqm) <span class="text-danger">*</span></label>
                            <input 
                                type="number" 
                                class="form-control @error('subdivided_area') is-invalid @enderror" 
                                id="subdivided_area" 
                                name="subdivided_area" 
                                placeholder="0.00"
                                step="0.01"
                                min="0.01"
                                value="{{ $application->subdivided_area ?? old('subdivided_area') }}"
                                required
                                onchange="calculateRemainingArea()">
                            @error('subdivided_area')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted"><small><strong>Mother Lot Total:</strong> {{ number_format($application->landRecord->total_area, 2) }} sqm</small></p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-success"><small><strong>Remaining Area:</strong> <span id="remainingAreaDisplay">{{ number_format($application->remaining_area ?? ($application->landRecord->total_area - ($application->subdivided_area ?? 0)), 2) }}</span> sqm</small></p>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Final Decision -->
                <div class="mb-4 p-3 bg-light rounded border-left border-success" style="border-left: 4px solid #198754;">
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="fas fa-check-circle me-2 text-success"></i> Step 3: Final Decision & Remarks
                    </h6>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label fw-bold">Application Status <span class="text-danger">*</span></label>
                        <select 
                            class="form-select @error('status') is-invalid @enderror" 
                            id="status" 
                            name="status" 
                            required>
                            <option value="" disabled>-- Select Status --</option>
                            <option value="In Process" {{ $application->status === 'In Process' ? 'selected' : '' }}>
                                <i class="fas fa-hourglass-half"></i> In Process
                            </option>
                            <option value="Approved" {{ $application->status === 'Approved' ? 'selected' : '' }}>
                                <i class="fas fa-check"></i> Approved
                            </option>
                            <option value="Rejected" {{ $application->status === 'Rejected' ? 'selected' : '' }}>
                                <i class="fas fa-times"></i> Rejected
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="land_officer_remarks" class="form-label fw-bold">Official Remarks <span class="text-danger">*</span></label>
                        <textarea 
                            class="form-control @error('land_officer_remarks') is-invalid @enderror" 
                            id="land_officer_remarks" 
                            name="land_officer_remarks" 
                            rows="4" 
                            placeholder="e.g., 'Approved based on Cadastral survey and physical verification. All documents are complete.' or 'Rejected due to discrepancies in survey data.'"
                            required>{{ $application->land_officer_remarks ?? old('land_officer_remarks') }}</textarea>
                        <small class="text-muted">Be specific about the decision rationale.</small>
                        @error('land_officer_remarks')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="patent_details" class="form-label fw-bold">Patent Details</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="patent_details" 
                                name="patent_details" 
                                placeholder="e.g., Agricultural Free Patent"
                                value="{{ $application->patent_details ?? old('patent_details') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="patent_type" class="form-label fw-bold">Patent Type</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="patent_type" 
                                name="patent_type" 
                                placeholder="e.g., Residential, Agricultural"
                                value="{{ $application->patent_type ?? old('patent_type') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="d-flex gap-2 justify-content-end mb-5">
            <a href="{{ route('applications.index') }}" class="btn btn-outline-secondary fw-bold">
                <i class="fas fa-times me-2"></i> Cancel
            </a>
            <button type="submit" class="btn btn-success fw-bold shadow-sm">
                <i class="fas fa-save me-2"></i> Save Assessment
            </button>
        </div>
    </form>

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
                    <p class="text-muted">No status history available yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
console.log('🔧 Land Officer Assessment Form Initialized');
console.log('📋 Application ID:', {{ $application->id }});
console.log('📍 Tracking Number:', '{{ $application->tracking_no }}');

document.addEventListener('DOMContentLoaded', function() {
    console.log('✓ DOM Content Loaded - Land Officer Workflow Script Active');
    
    const lotTypeRadios = document.querySelectorAll('.lot-type-radio');
    const subdivisionFields = document.getElementById('subdivisionFields');
    
    console.log('📌 Found lot type radios:', lotTypeRadios.length);
    console.log('📌 Subdivision fields element:', subdivisionFields ? 'Found' : 'NOT FOUND');

    lotTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            console.log('🔄 LOT TYPE CHANGED:', this.value);
            console.log('   Details:', {
                lotType: this.value,
                timestamp: new Date().toLocaleTimeString(),
                isSubdivision: this.value === 'subdivision'
            });

            if (this.value === 'subdivision') {
                console.log('✅ SUBDIVISION Selected - Showing subdivision fields');
                subdivisionFields.style.display = 'block';
                document.getElementById('new_lot_number').required = true;
                document.getElementById('subdivided_area').required = true;
                console.log('   ✓ New Lot Number field - REQUIRED');
                console.log('   ✓ Subdivided Area field - REQUIRED');
            } else {
                console.log('✅ EXISTING LOT Selected - Hiding subdivision fields');
                subdivisionFields.style.display = 'none';
                document.getElementById('new_lot_number').required = false;
                document.getElementById('subdivided_area').required = false;
                console.log('   ✓ Subdivision fields hidden');
                console.log('   ✓ Subdivision input fields - NOT REQUIRED');
            }
        });
    });

    // Add listener to subdivided area input
    const subdividedAreaInput = document.getElementById('subdivided_area');
    if (subdividedAreaInput) {
        console.log('✓ Subdivided Area input listener attached');
        subdividedAreaInput.addEventListener('input', function() {
            calculateRemainingArea();
        });
    }

    // Add listener to status dropdown for automatic remarks
    const statusSelect = document.getElementById('status');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            console.log('📊 STATUS CHANGED:', this.value);
            
            const remarksTextarea = document.getElementById('land_officer_remarks');
            if (this.value === 'Approved') {
                console.log('✅ APPROVED STATUS DETECTED - Auto-filling remarks with "Patent"');
                remarksTextarea.value = 'Patent';
                remarksTextarea.classList.add('is-valid');
                remarksTextarea.classList.remove('is-invalid');
                console.log('   ✓ Remarks field auto-filled');
            } else {
                console.log('   Status is:', this.value, '- remarks field NOT auto-filled');
            }
        });
    }

    // Attach form submission listener
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            console.log('📝 FORM SUBMISSION INITIATED');
            const lotType = document.querySelector('input[name="lot_type"]:checked');
            const status = document.querySelector('select[name="status"]');
            const remarks = document.querySelector('textarea[name="land_officer_remarks"]');
            
            console.log('📤 Form Data Summary:', {
                lotType: lotType ? lotType.value : 'NOT SELECTED',
                status: status ? status.value : 'NOT SELECTED',
                remarksLength: remarks ? remarks.value.length : 0,
            });

            // Submit form via AJAX
            const formData = new FormData(form);
            const actionUrl = form.getAttribute('action');
            
            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('✅ ASSESSMENT SUBMITTED SUCCESSFULLY:', data);
                
                // Refresh pending count and notification bell
                if (typeof refreshPendingCount === 'function') {
                    console.log('📋 Refreshing pending count...');
                    refreshPendingCount();
                }
                if (typeof refreshNotificationBell === 'function') {
                    console.log('🔔 Refreshing notification bell...');
                    refreshNotificationBell();
                }
                
                // Show success message
                alert('✅ Application assessment completed successfully!');
                
                // Redirect after 1 second
                setTimeout(() => {
                    window.location.href = '{{ route("applications.index") }}';
                }, 1000);
            })
            .catch(error => {
                console.error('❌ ASSESSMENT SUBMISSION FAILED:', error);
                alert('❌ Error submitting assessment: ' + error.message);
            });
        });
    }

    console.log('✓ Land Officer Workflow Setup Complete');
});

function calculateRemainingArea() {
    const totalArea = {{ $application->landRecord->total_area }};
    const subdividedAreaInput = document.getElementById('subdivided_area');
    const subdividedArea = parseFloat(subdividedAreaInput.value) || 0;
    const remainingArea = totalArea - subdividedArea;

    console.log('🧮 AREA CALCULATION TRIGGERED');
    console.log('   Total Area (Mother Lot):', totalArea + ' sqm');
    console.log('   Subdivided Area:', subdividedArea + ' sqm');
    console.log('   Remaining Area:', remainingArea.toFixed(2) + ' sqm');

    if (remainingArea < 0) {
        console.log('   ❌ ERROR: Subdivided area exceeds total area!');
        document.getElementById('subdivided_area').classList.add('is-invalid');
        document.getElementById('remainingAreaDisplay').textContent = 'Invalid (exceeds total)';
        document.getElementById('remainingAreaDisplay').parentElement.classList.remove('text-success');
        document.getElementById('remainingAreaDisplay').parentElement.classList.add('text-danger');
    } else {
        console.log('   ✅ Valid subdivision - remaining area calculated');
        document.getElementById('subdivided_area').classList.remove('is-invalid');
        document.getElementById('remainingAreaDisplay').textContent = remainingArea.toFixed(2);
        document.getElementById('remainingAreaDisplay').parentElement.classList.remove('text-danger');
        document.getElementById('remainingAreaDisplay').parentElement.classList.add('text-success');
    }
}
</script>

<style>
.timeline {
    position: relative;
    padding-left: 0;
}

.timeline-item {
    position: relative;
    padding-left: 0;
}

.timeline-marker {
    display: flex;
    align-items: flex-start;
}

.border-left {
    border-left: 4px solid;
}
</style>
@endsection
