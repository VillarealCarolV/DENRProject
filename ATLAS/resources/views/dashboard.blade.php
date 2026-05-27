@extends('layouts.app')

@section('content')
<!-- Quick Actions Bar - Only for Records Officers & Admins -->
@if(auth()->user()->role !== 'land_management_officer' && auth()->user()->role !== 'processing')
<div class="mb-4 p-3 bg-light border border-light rounded-3 shadow-sm">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h6 class="mb-0 text-muted fw-bold"><i class="fa-solid fa-plus-circle me-2 text-warning"></i>Create New</h6>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            
                <button class="btn btn-outline-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#newApplicationModal" title="Create a new application">
                    <i class="fas fa-file-alt me-1"></i> New Application
                </button>
            @endif
            @if(auth()->user()->role === 'records_officer' || auth()->user()->role === 'admin' || auth()->user()->role === 'input')
                <button class="btn btn-outline-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#newLandRecordModal" title="Add a new land record">
                    <i class="fas fa-map me-1"></i> Add Land Record
                </button>
            @endif
            @if(auth()->user()->role === 'records_officer' || auth()->user()->role === 'admin' || auth()->user()->role === 'input')
                <button class="btn btn-outline-info btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#newApplicantModal" title="Register a new applicant">
                    <i class="fas fa-user-plus me-1"></i> New Applicant
                </button>
            @endif
        </div>
    </div>
</div>

<!-- SUMMARY STATS: Single Row of Compact Metric Tiles -->
<div class="row g-2 mb-4">
    <!-- Stat 1: Pending Applications -->
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Pending</p>
                        <h4 class="mb-0 fw-bold text-primary">{{ $pendingCount }}</h4>
                    </div>
                    <i class="bi bi-file-earmark-text text-primary" style="font-size: 2rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 2: Approved This Month -->
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Approved</p>
                        <h4 class="mb-0 fw-bold text-success">{{ $approvedThisMonth }}</h4>
                    </div>
                    <i class="bi bi-check-circle text-success" style="font-size: 2rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 3: Land Records -->
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Land Records</p>
                        <h4 class="mb-0 fw-bold text-info">{{ $landRecordsCount }}</h4>
                    </div>
                    <i class="bi bi-map text-info" style="font-size: 2rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 4: Active Applicants -->
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Active</p>
                        <h4 class="mb-0 fw-bold text-warning">{{ $activeApplicants }}</h4>
                    </div>
                    <i class="bi bi-people text-warning" style="font-size: 2rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- APPLICATION SUMMARY CHART: Compact Height -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom p-3">
                <h6 class="mb-0 fw-bold">Application Summary</h6>
                <small class="text-muted">Monthly breakdown of submissions and approvals</small>
            </div>
            <div class="card-body p-3">
                <div id="chart-monthly-velocity" style="height: 250px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Applications Table (Full Width Below) -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Applications</h5>
                <a href="{{ route('applications.index') }}" class="btn btn-sm btn-outline-primary fw-bold">View All <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3 py-3">Tracking No.</th>
                                <th class="px-3 py-3">Applicant</th>
                                <th class="px-3 py-3">Survey No.</th>
                                <th class="px-3 py-3">Status</th>
                                <th class="px-3 py-3">Date</th>
                                <th class="px-3 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentApplications as $app)
                                @php
                                    $latestStatus = $app->statusHistories()->latest()->first();
                                    $statusText = $latestStatus ? $latestStatus->status : 'Pending';
                                    $badgeColor = match($statusText) {
                                        'Approved' => 'bg-success',
                                        'Rejected' => 'bg-danger',
                                        'In Process' => 'bg-info text-dark',
                                        default => 'bg-warning text-dark',
                                    };
                                @endphp
                                <tr>
                                    <td class="px-3 py-3"><code class="text-primary fw-bold">{{ $app->tracking_no }}</code></td>
                                    <td class="px-3 py-3">{{ $app->applicant->full_name }}</td>
                                    <td class="px-3 py-3">{{ $app->landRecord->survey_no }}</td>
                                    <td class="px-3 py-3">
                                        <span class="badge {{ $badgeColor }} px-2 py-1">{{ $statusText }}</span>
                                    </td>
                                    <td class="px-3 py-3">{{ $app->date_received->format('M d, Y') }}</td>
                                    <td class="px-3 py-3 text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('applications.show', $app->id) }}" class="btn btn-sm btn-link text-primary p-0" title="View Details" style="font-size: 1rem;">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('applications.show', $app->id) }}" class="btn btn-sm btn-link text-warning p-0" title="Edit" style="font-size: 1rem;">
                                                <i class="fas fa-pen-to-square"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2 text-light"></i>
                                        <p>No recent applications</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Velocity Bar Chart (Compact Version)
    var chartMonthlyVelocity = new ApexCharts(document.querySelector("#chart-monthly-velocity"), {
        series: [
            {
                name: 'Submitted',
                data: @json($submittedData)
            },
            {
                name: 'Approved',
                data: @json($approvedData)
            }
        ],
        chart: {
            type: 'bar',
            height: 250,
            toolbar: { show: false }
        },
        colors: ['#0d6efd', '#198754'],
        plotOptions: {
            bar: {
                borderRadius: 4,
                dataLabels: { position: 'top' }
            }
        },
        dataLabels: { enabled: false },
        xaxis: {
            categories: @json($months),
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            title: {
                text: 'Applications',
                style: { fontSize: '12px', fontWeight: 500 }
            }
        },
        legend: { position: 'top', horizontalAlign: 'right' },
        grid: { borderColor: '#e9ecef', strokeDashArray: 4 }
    });
    chartMonthlyVelocity.render();
});
</script>

<!-- ========== ALL MODALS SECTION ========== -->
<!-- MODAL 1: New Application (Master Intake) -->
<div class="modal fade" id="newApplicationModal" tabindex="-1" aria-labelledby="newApplicationLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="newApplicationLabel"><i class="fas fa-file-signature me-2"></i>Master Intake Form</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('applications.masterStore') }}" method="POST" id="masterIntakeForm">
                @csrf
                <div class="modal-body p-4 bg-light">
                    <!-- Error Alert Container (Initially Hidden) -->
                    <div id="formErrorAlert" class="alert alert-danger alert-dismissible fade show d-none" role="alert">
                        <strong><i class="fas fa-exclamation-circle me-2"></i>Validation Errors:</strong>
                        <ul class="mb-0 mt-2" id="errorList">
                            <li>Placeholder error</li>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>

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

                    <h6 class="text-primary fw-bold border-bottom pb-2 mb-3"><i class="fas fa-user me-2"></i>Applicant Information</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-secondary small">Full Name</label>
                            <input type="text" name="full_name" class="form-control form-control-sm" required placeholder="Juan Dela Cruz" value="{{ old('full_name') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small">Address</label>
                            <input type="text" name="address" class="form-control form-control-sm" placeholder="Bocaue, Bulacan" value="{{ old('address') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small">Contact No.</label>
                            <input type="text" name="contact_no" class="form-control form-control-sm" placeholder="09123456789" value="{{ old('contact_no') }}">
                        </div>
                    </div>

                    <h6 class="text-success fw-bold border-bottom pb-2 mb-3"><i class="fas fa-map me-2"></i>Mother Lot / Land Details</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-secondary small">Survey Number</label>
                            <input type="text" name="survey_no" class="form-control form-control-sm" required placeholder="CSD-03-012345" value="{{ old('survey_no') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small">Total Area (sqm)</label>
                            <input type="number" step="0.01" name="total_area" class="form-control form-control-sm" required placeholder="1000" value="{{ old('total_area') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small">Location</label>
                            <input type="text" name="location" class="form-control form-control-sm" required placeholder="Brgy. Igulot" value="{{ old('location') }}">
                        </div>
                    </div>

                    <h6 class="text-warning fw-bold border-bottom pb-2 mb-3"><i class="fas fa-barcode me-2"></i>Application Tracking</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Tracking Number</label>
                            <input type="text" name="tracking_no" class="form-control form-control-sm" required placeholder="CENRO-2026-003" value="{{ old('tracking_no') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Date Received</label>
                            <input type="date" name="date_received" class="form-control form-control-sm" required value="{{ old('date_received') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark btn-sm fw-bold" id="submitIntakeBtn">
                        <i class="fas fa-check-double me-2"></i> Process Intake
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: New Land Record -->
<div class="modal fade" id="newLandRecordModal" tabindex="-1" aria-labelledby="newLandRecordLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="newLandRecordLabel"><i class="fas fa-map me-2"></i>Add Land Record</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('land-records.store') }}" method="POST" id="newLandRecordForm">
                @csrf
                <div class="modal-body p-4">
                    <!-- Error Alert Container (Initially Hidden) -->
                    <div id="landRecordErrorAlert" class="alert alert-danger alert-dismissible fade show d-none" role="alert">
                        <strong><i class="fas fa-exclamation-circle me-2"></i>Validation Errors:</strong>
                        <ul class="mb-0 mt-2" id="landRecordErrorList">
                            <li>Placeholder error</li>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>

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

                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fas fa-barcode me-2 text-info"></i>Survey Number</label>
                        <input type="text" name="survey_no" class="form-control" value="{{ old('survey_no') }}" placeholder="e.g., CSD-03-012345" required>
                        <small class="text-muted d-block mt-1">Format: XXX-XX-XXXXXX</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fas fa-ruler me-2 text-warning"></i>Total Area (sq. meters)</label>
                        <input type="number" step="0.01" name="total_area" class="form-control" value="{{ old('total_area') }}" placeholder="e.g., 1000.50" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fas fa-map-marker-alt me-2 text-danger"></i>Location</label>
                        <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="e.g., Brgy. Igulot, Bocaue" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold" id="submitLandRecordBtn">
                        <i class="fas fa-save me-2"></i> Save Land Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 3: New Applicant -->
<div class="modal fade" id="newApplicantModal" tabindex="-1" aria-labelledby="newApplicantLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold" id="newApplicantLabel"><i class="fas fa-user-plus me-2"></i>New Applicant</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('applicants.store') }}" method="POST" id="newApplicantForm">
                @csrf
                <div class="modal-body p-4">
                    <!-- Error Alert Container (Initially Hidden) -->
                    <div id="applicantErrorAlert" class="alert alert-danger alert-dismissible fade show d-none" role="alert">
                        <strong><i class="fas fa-exclamation-circle me-2"></i>Validation Errors:</strong>
                        <ul class="mb-0 mt-2" id="applicantErrorList">
                            <li>Placeholder error</li>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>

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

                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fas fa-user me-2 text-info"></i>Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="{{ old('full_name') }}" placeholder="e.g., Juan Dela Cruz" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fas fa-map-marker-alt me-2 text-danger"></i>Address</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address') }}" placeholder="e.g., Bocaue, Bulacan">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fas fa-phone me-2 text-success"></i>Contact Number</label>
                        <input type="text" name="contact_no" class="form-control" value="{{ old('contact_no') }}" placeholder="e.g., 09123456789">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info btn-sm fw-bold text-white" id="submitApplicantBtn">
                        <i class="fas fa-save me-2"></i> Save Applicant
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========== MODALS SCRIPTS ========== -->
<script>
    // Helper function to show success toast (shared by all modals)
    function showSuccessToast(title, message) {
        const toastHTML = `
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
                <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-success text-white">
                        <strong class="me-auto">${title}</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', toastHTML);
    }

    // AJAX Form Submission for Master Intake
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('masterIntakeForm');
        const submitBtn = document.getElementById('submitIntakeBtn');
        const errorAlert = document.getElementById('formErrorAlert');
        const errorList = document.getElementById('errorList');

        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            errorAlert.classList.add('d-none');
            errorList.innerHTML = '';

            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    if (response.status === 422) {
                        return response.json().then(data => {
                            throw { status: 422, errors: data.errors };
                        });
                    }
                    throw new Error(`HTTP Error: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('newApplicationModal'));
                if (modal) {
                    modal.hide();
                }

                form.reset();
                showSuccessToast('✅ Success!', data.message || 'Application created successfully!');
                
                // Refresh notification bell
                if (typeof refreshNotificationBell === 'function') {
                    refreshNotificationBell();
                }

                setTimeout(() => {
                    window.location.href = '{{ route("applications.index") }}';
                }, 2000);
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;

                if (error.status === 422 && error.errors) {
                    errorList.innerHTML = '';
                    Object.keys(error.errors).forEach(field => {
                        const messages = error.errors[field];
                        messages.forEach(message => {
                            const li = document.createElement('li');
                            li.textContent = message;
                            errorList.appendChild(li);
                        });
                    });
                    errorAlert.classList.remove('d-none');
                    errorAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    console.error('Error:', error);
                    errorList.innerHTML = '<li>An unexpected error occurred. Please try again.</li>';
                    errorAlert.classList.remove('d-none');
                }
            });
        });
    });

    // AJAX Form Submission for Land Record
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('newLandRecordForm');
        const submitBtn = document.getElementById('submitLandRecordBtn');
        const errorAlert = document.getElementById('landRecordErrorAlert');
        const errorList = document.getElementById('landRecordErrorList');

        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            errorAlert.classList.add('d-none');
            errorList.innerHTML = '';

            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    if (response.status === 422) {
                        return response.json().then(data => {
                            throw { status: 422, errors: data.errors };
                        });
                    }
                    throw new Error(`HTTP Error: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('newLandRecordModal'));
                if (modal) modal.hide();
                form.reset();
                showSuccessToast('✅ Success!', 'Land record created successfully!');
                
                // Refresh notification bell
                if (typeof refreshNotificationBell === 'function') {
                    refreshNotificationBell();
                }
                
                setTimeout(() => {
                    window.location.href = '{{ route("land-records.index") }}';
                }, 2000);
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;

                if (error.status === 422 && error.errors) {
                    errorList.innerHTML = '';
                    Object.keys(error.errors).forEach(field => {
                        error.errors[field].forEach(message => {
                            const li = document.createElement('li');
                            li.textContent = message;
                            errorList.appendChild(li);
                        });
                    });
                    errorAlert.classList.remove('d-none');
                    errorAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    errorList.innerHTML = '<li>An unexpected error occurred. Please try again.</li>';
                    errorAlert.classList.remove('d-none');
                }
            });
        });
    });

    // AJAX Form Submission for Applicant
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('newApplicantForm');
        const submitBtn = document.getElementById('submitApplicantBtn');
        const errorAlert = document.getElementById('applicantErrorAlert');
        const errorList = document.getElementById('applicantErrorList');

        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            errorAlert.classList.add('d-none');
            errorList.innerHTML = '';

            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    if (response.status === 422) {
                        return response.json().then(data => {
                            throw { status: 422, errors: data.errors };
                        });
                    }
                    throw new Error(`HTTP Error: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('newApplicantModal'));
                if (modal) modal.hide();
                form.reset();
                showSuccessToast('✅ Success!', 'Applicant created successfully!');
                
                // Refresh notification bell
                if (typeof refreshNotificationBell === 'function') {
                    refreshNotificationBell();
                }
                
                setTimeout(() => {
                    window.location.href = '{{ route("applicants.index") }}';
                }, 2000);
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;

                if (error.status === 422 && error.errors) {
                    errorList.innerHTML = '';
                    Object.keys(error.errors).forEach(field => {
                        error.errors[field].forEach(message => {
                            const li = document.createElement('li');
                            li.textContent = message;
                            errorList.appendChild(li);
                        });
                    });
                    errorAlert.classList.remove('d-none');
                    errorAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    errorList.innerHTML = '<li>An unexpected error occurred. Please try again.</li>';
                    errorAlert.classList.remove('d-none');
                }
            });
        });
    });
</script>

@endsection


