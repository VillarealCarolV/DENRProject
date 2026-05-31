@extends('layouts.app')

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fas fa-folder-open me-2 text-primary"></i> Application Records</h3>
        <a href="{{ route('applications.masterCreate') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> New Master Intake
        </a>
    </div>

    <div class="card border-0">
        <!-- Action Bar -->
        <div class="card-body p-3 border-bottom bg-light">
            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                <!-- Search & Filters (Left) -->
                <div class="d-flex gap-2 align-items-center flex-wrap" style="flex: 1; min-width: 300px;">
                    <div class="input-group input-group-sm" style="max-width: 250px;">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input 
                            type="text" 
                            class="form-control border-start-0" 
                            placeholder="Search tracking/applicant..."
                            id="searchInput"
                            onkeyup="filterTable()">
                    </div>

                    <select class="form-select form-select-sm" style="max-width: 150px;" id="statusFilter" onchange="filterTable()">
                        <option value="">All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="In Process">In Process</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>

                <!-- Export Button (Right) -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm fw-bold" type="button" onclick="document.getElementById('applicationsExportMenu').classList.toggle('show')">
                        <i class="fas fa-download me-2"></i> Export <i class="fas fa-caret-down ms-1"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" id="applicationsExportMenu" style="position: absolute; right: 0;">
                        <li><a class="dropdown-item" href="{{ route('applications.export', ['format' => 'csv']) }}"><i class="fas fa-file-csv me-2 text-success"></i>CSV</a></li>
                        <li><a class="dropdown-item" href="{{ route('applications.export', ['format' => 'excel']) }}"><i class="fas fa-file-excel me-2 text-success"></i>Excel</a></li>
                        <li><a class="dropdown-item" href="{{ route('applications.export', ['format' => 'pdf']) }}"><i class="fas fa-file-pdf me-2 text-danger"></i>PDF</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 30px;">
                                <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th>Tracking No.</th>
                            <th>Applicant Name</th>
                            <th>Survey No.</th>
                            <th>Total Area</th>
                            <th>Date Received</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $app)
                            <tr class="application-row" data-app-id="{{ $app->id }}">
                                <td>
                                    <input type="checkbox" class="form-check-input row-checkbox">
                                </td>
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
                                        @if(auth()->user()->role === 'land_officer')
                                            <a href="{{ route('applications.edit', $app->id) }}" class="action-link" title="Edit/Approve">
                                                <i class="fas fa-pen-to-square"></i>
                                            </a>
                                        @endif
                                        <button class="action-link delete-btn-handler" 
                                                style="border: none; background: none; padding: 0.25rem 0.5rem; cursor: pointer;" 
                                                data-url="{{ route('applications.destroy', $app->id) }}"
                                                data-name="Application {{ $app->tracking_no }}"
                                                data-row-selector="tr.application-row[data-app-id='{{ $app->id }}']"
                                                title="Delete">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>

                                <div class="modal fade" id="statusModal{{ $app->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title"><i class="fas fa-tasks me-2"></i>Update Application Status</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('applications.updateStatus', $app->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body text-start">
                                                    <p class="mb-3">Updating status for Tracking No: <strong>{{ $app->tracking_no }}</strong></p>
                                                    
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 text-light"></i>
                                    <h5>No applications found in the system.</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
console.log('🗂️ APPLICATION LIST PAGE LOADED');
console.log('ℹ️ User role:', '{{ auth()->user()->role }}');

function filterTable() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('.application-row');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const statusBadge = row.querySelector('.status-badge');
        const statusText = statusBadge ? statusBadge.textContent.trim() : '';
        
        const matchesSearch = text.includes(searchInput);
        const matchesStatus = statusFilter === '' || statusText === statusFilter;

        row.style.display = matchesSearch && matchesStatus ? '' : 'none';
    });
}

function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    const selectAll = document.getElementById('selectAll');
    checkboxes.forEach(checkbox => checkbox.checked = selectAll.checked);
}

document.querySelectorAll('.row-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', () => {
        const allChecked = document.querySelectorAll('.row-checkbox:checked').length === document.querySelectorAll('.row-checkbox').length;
        document.getElementById('selectAll').checked = allChecked;
    });
});

// Add tracking to Edit/Approve buttons
document.addEventListener('DOMContentLoaded', function() {
    console.log('✓ Application List Page Ready');
    
    // Find all Edit/Approve buttons (pen-to-square icons for Land Officers)
    document.querySelectorAll('a[title="Edit/Approve"]').forEach((button, index) => {
        button.addEventListener('click', function(e) {
            const row = this.closest('.application-row');
            const trackingNo = row.querySelector('.text-primary').textContent.trim();
            
            console.log('✏️ LAND OFFICER CLICKING EDIT/APPROVE');
            console.log('📋 Application Details:', {
                trackingNo: trackingNo,
                applicant: row.cells[2].textContent.trim(),
                surveyNo: row.cells[3].textContent.trim(),
                totalArea: row.cells[4].textContent.trim(),
                status: row.querySelector('.status-badge').textContent.trim(),
                timestamp: new Date().toLocaleTimeString()
            });
            
            // Log that we're redirecting to edit form
            console.log('→ Redirecting to assessment form...');
        });
    });
});

// Close export dropdown only when clicking outside of it
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('applicationsExportMenu');
        if (!menu) return;
        
        // Check if the click is on the export dropdown or the button that toggles it
        const exportButton = document.querySelector('button[onclick*="applicationsExportMenu"]');
        const clickedMenu = event.target.closest('#applicationsExportMenu');
        const clickedButton = event.target === exportButton || event.target.closest('button[onclick*="applicationsExportMenu"]');
        
        // Only close if clicking outside both the menu and button
        if (!clickedMenu && !clickedButton) {
            menu.classList.remove('show');
        }
    });
});
</script>
@endsection