@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-2">
            <i class="fas fa-briefcase me-2 text-primary"></i>Intake Workstation
        </h2>
        <p class="text-muted">Process applications and manage land records efficiently</p>
    </div>

    <!-- THE WORKSTATION: Master Intake Form (Compact & Embedded) -->
    <div class="card border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-file-signature me-2 text-primary"></i>Process Intake
            </h5>
        </div>
        <div class="card-body">
            <!-- Alert Container for AJAX Messages -->
            <div id="formAlertContainer"></div>

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Success Messages -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Master Intake Form -->
            <form id="intakeForm" action="{{ route('applications.masterStore') }}" method="POST">
                @csrf
                <div class="row g-3 mb-4">
                    <!-- Applicant Name -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary">Applicant Name <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            name="full_name" 
                            class="form-control form-control-sm @error('full_name') is-invalid @enderror" 
                            required 
                            placeholder="Full Name"
                            value="{{ old('full_name') }}">
                        @error('full_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Survey Number -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary">Survey Number <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            id="surveyNoInput"
                            name="survey_no" 
                            class="form-control form-control-sm @error('survey_no') is-invalid @enderror" 
                            required 
                            placeholder="CSD-03-012345"
                            pattern="^[A-Za-z]{3}-\d{2,}-\d{4,}$"
                            value="{{ old('survey_no') }}"
                            autocomplete="off">
                        @error('survey_no')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-1">Format: XXX-00-000000</small>
                    </div>

                    <!-- Total Area -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-secondary">Total Area (sqm) <span class="text-danger">*</span></label>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="total_area" 
                            class="form-control form-control-sm @error('total_area') is-invalid @enderror" 
                            required 
                            placeholder="0.00"
                            value="{{ old('total_area') }}">
                        @error('total_area')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Location -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-secondary">Location <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            name="location" 
                            class="form-control form-control-sm @error('location') is-invalid @enderror" 
                            required 
                            placeholder="e.g., Brgy. Igulot"
                            value="{{ old('location') }}">
                        @error('location')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Address (Optional) -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-secondary">Address</label>
                        <input 
                            type="text" 
                            name="address" 
                            class="form-control form-control-sm @error('address') is-invalid @enderror" 
                            placeholder="Applicant Address"
                            value="{{ old('address') }}">
                        @error('address')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Contact No. (Optional) -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-secondary">Contact No.</label>
                        <input 
                            type="text" 
                            name="contact_no" 
                            class="form-control form-control-sm @error('contact_no') is-invalid @enderror" 
                            placeholder="09123456789"
                            value="{{ old('contact_no') }}">
                        @error('contact_no')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Hidden Fields for Tracking -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-secondary">Tracking No. <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            id="trackingNoInput"
                            name="tracking_no" 
                            class="form-control form-control-sm @error('tracking_no') is-invalid @enderror" 
                            required 
                            placeholder="CENRO-2026-001"
                            value="{{ old('tracking_no') }}"
                            readonly>
                        @error('tracking_no')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-1">Auto-generated</small>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold text-secondary">Date Received <span class="text-danger">*</span></label>
                        <input 
                            type="date" 
                            id="dateReceivedInput"
                            name="date_received" 
                            class="form-control form-control-sm @error('date_received') is-invalid @enderror" 
                            required 
                            value="{{ old('date_received') ?? date('Y-m-d') }}"
                            readonly>
                        @error('date_received')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-1">Today's date</small>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 align-items-center">
                    <button 
                        type="submit" 
                        id="submitBtn"
                        class="btn btn-primary">
                        <i class="fas fa-check-circle me-1"></i>Process Intake
                    </button>
                    
                    <!-- Secondary Action Buttons (Ghost Style) -->
                    <button type="button" class="btn btn-ghost btn-sm" onclick="openNewApplicantModal()">
                        <i class="fas fa-user-plus me-1"></i>New Applicant
                    </button>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="openAddLandRecordModal()">
                        <i class="fas fa-map-marker-alt me-1"></i>Add Land Record
                    </button>

                    <!-- Loading Indicator -->
                    <span id="loadingIndicator" class="spinner-border spinner-border-sm ms-2" style="display: none;" role="status" aria-hidden="true"></span>
                </div>
            </form>
        </div>
    </div>

    <!-- THE LIVE TABLE: Applications List -->
    <div class="card border-0">
        <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-folder-open me-2 text-primary"></i>Applications
            </h5>
            <div class="d-flex gap-2">
                <input 
                    type="text" 
                    id="tableSearchInput" 
                    class="form-control form-control-sm" 
                    style="width: 200px;" 
                    placeholder="Search..."
                    onkeyup="filterApplicationsTable()">
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tracking No.</th>
                            <th>Applicant Name</th>
                            <th>Survey No.</th>
                            <th>Total Area</th>
                            <th>Date Received</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="applicationsTableBody">
                        @forelse($applications as $app)
                            <tr class="application-row">
                                <td class="fw-bold">{{ $app->tracking_no }}</td>
                                <td>{{ $app->applicant->full_name }}</td>
                                <td>{{ $app->landRecord->survey_no }}</td>
                                <td>{{ number_format($app->landRecord->total_area, 2) }} sqm</td>
                                <td>{{ \Carbon\Carbon::parse($app->date_received)->format('M d, Y') }}</td>
                                <td class="text-center">
                                    @php
                                        $latestStatus = $app->statusHistories()->latest()->first();
                                        $statusText = $latestStatus ? $latestStatus->status : 'Pending';
                                        $statusClass = match($statusText) {
                                            'Approved' => 'status-approved',
                                            'Rejected' => 'status-rejected',
                                            'In Process' => 'status-in-process',
                                            default => 'status-pending',
                                        };
                                    @endphp
                                    <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('applications.show', $app->id) }}" class="action-link" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('applications.edit', $app->id) }}" class="action-link" title="Edit">
                                            <i class="fas fa-pen-to-square"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 text-light"></i>
                                    <p class="mb-0">No applications yet. Use the form above to create one.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: New Applicant (Placeholder) -->
<div class="modal fade" id="newApplicantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New Applicant</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>This feature allows you to create a new applicant separately before creating an application.</p>
                <p class="text-muted small">Coming soon - For now, use the main form above.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Land Record (Placeholder) -->
<div class="modal fade" id="addLandRecordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-map-marker-alt me-2"></i>Add Land Record</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>This feature allows you to create a new land record separately.</p>
                <p class="text-muted small">Coming soon - For now, use the main form above.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// AJAX Form Submission for Master Intake
document.getElementById('intakeForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const loadingIndicator = document.getElementById('loadingIndicator');
    const submitBtn = document.getElementById('submitBtn');

    // Show loading state
    loadingIndicator.style.display = 'inline-block';
    submitBtn.disabled = true;

    fetch('{{ route("applications.masterStore") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert('success', data.message || 'Application processed successfully!');

            // Reset the form
            document.getElementById('intakeForm').reset();
            document.querySelector('input[name="date_received"]').value = new Date().toISOString().split('T')[0];

            // Refresh the applications table
            refreshApplicationsTable();
            
            // Refresh pending count and notification bell
            if (typeof refreshPendingCount === 'function') {
                refreshPendingCount();
            }
            if (typeof refreshNotificationBell === 'function') {
                refreshNotificationBell();
            }

            // Scroll to the table
            document.getElementById('applicationsTableBody').scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            showAlert('danger', data.message || 'An error occurred while processing the intake.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred. Please try again.');
    })
    .finally(() => {
        // Hide loading state
        loadingIndicator.style.display = 'none';
        submitBtn.disabled = false;
    });
});

// Function to refresh the applications table via AJAX
function refreshApplicationsTable() {
    fetch('{{ route("applications.getTableData") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.html) {
            // Replace table body with fresh data
            document.getElementById('applicationsTableBody').innerHTML = data.html;
        }
    })
    .catch(error => console.error('Error refreshing table:', error));
}

// Function to display alerts
function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${type === 'success' ? '<i class="fas fa-check-circle me-2"></i>' : '<i class="fas fa-exclamation-circle me-2"></i>'}
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    // Get the alert container
    const container = document.getElementById('formAlertContainer');
    if (!container) {
        console.error('Alert container not found');
        return;
    }

    const alertDiv = document.createElement('div');
    alertDiv.innerHTML = alertHtml;
    container.appendChild(alertDiv);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = alertDiv.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Filter the applications table
function filterApplicationsTable() {
    const searchInput = document.getElementById('tableSearchInput').value.toLowerCase();
    const rows = document.querySelectorAll('.application-row');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchInput) ? '' : 'none';
    });
}

// Modal functions
function openNewApplicantModal() {
    new bootstrap.Modal(document.getElementById('newApplicantModal')).show();
}

function openAddLandRecordModal() {
    new bootstrap.Modal(document.getElementById('addLandRecordModal')).show();
}

// ============================================
// INPUT MASKING & AUTO-POPULATION
// ============================================

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set today's date in the date field
    const dateInput = document.getElementById('dateReceivedInput');
    if (dateInput && !dateInput.value) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }

    // Load next tracking number
    loadNextTrackingNumber();

    // Initialize Survey Number input mask
    const surveyInput = document.getElementById('surveyNoInput');
    if (surveyInput) {
        surveyInput.addEventListener('input', function(e) {
            applyInputMask(e);
        });
        
        // Also handle paste events
        surveyInput.addEventListener('paste', function(e) {
            setTimeout(() => applyInputMask(e), 10);
        });
    }
});

// Apply input mask to Survey Number (XXX-00-000000)
function applyInputMask(event) {
    const input = event.target;
    let value = input.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
    
    // Build the formatted value according to pattern XXX-00-000000
    let formatted = '';
    let position = 0;
    const pattern = 'XXX-00-000000';
    
    for (let i = 0; i < pattern.length && position < value.length; i++) {
        const patternChar = pattern[i];
        const valueChar = value[position];
        
        if (patternChar === '-') {
            formatted += '-';
            if (valueChar === '-') {
                position++;
            }
        } else if (patternChar === 'X') {
            // Letters only
            if (/[A-Z]/.test(valueChar)) {
                formatted += valueChar;
                position++;
            } else {
                break;
            }
        } else if (patternChar === '0') {
            // Digits only
            if (/[0-9]/.test(valueChar)) {
                formatted += valueChar;
                position++;
            } else {
                break;
            }
        }
    }
    
    input.value = formatted;
}

// Load next tracking number from server
function loadNextTrackingNumber() {
    const trackingInput = document.getElementById('trackingNoInput');
    
    // Only load if field is empty (first load)
    if (!trackingInput.value || trackingInput.value.startsWith('CENRO-2026')) {
        fetch('{{ route("applications.nextTrackingNumber") }}')
            .then(response => response.json())
            .then(data => {
                trackingInput.value = data.tracking_no;
            })
            .catch(error => {
                console.error('Error loading tracking number:', error);
                // Fallback to default format
                trackingInput.value = 'CENRO-' + new Date().getFullYear() + '-001';
            });
    }
}

console.log('✓ Intake Workstation Page Ready');
</script>
@endsection
