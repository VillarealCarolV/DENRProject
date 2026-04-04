@extends('layouts.app')

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fas fa-folder-open me-2 text-primary"></i> Application Records</h3>
        <a href="{{ route('applications.masterCreate') }}" class="btn btn-primary fw-bold shadow-sm">
            <i class="fas fa-plus me-2"></i> New Master Intake
        </a>
    </div>

    <div class="card shadow-sm border-0">
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
                    <button class="btn btn-outline-secondary btn-sm fw-bold dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Export Data">
                        <i class="fas fa-download me-2"></i> Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
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
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="ps-3" style="width: 30px;">
                                <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th class="py-3 px-3">Tracking No.</th>
                            <th class="py-3 px-3">Applicant Name</th>
                            <th class="py-3 px-3">Survey No.</th>
                            <th class="py-3 px-3">Total Area</th>
                            <th class="py-3 px-3">Date Received</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $app)
                            <tr class="application-row">
                                <td class="ps-3">
                                    <input type="checkbox" class="form-check-input row-checkbox">
                                </td>
                                <td class="px-3 fw-bold text-primary">{{ $app->tracking_no }}</td>
                                <td class="px-3">{{ $app->applicant->full_name }}</td>
                                <td class="px-3">{{ $app->landRecord->survey_no }}</td>
                                <td class="px-3">{{ number_format($app->landRecord->total_area, 2) }} sqm</td>
                                <td class="px-3">{{ \Carbon\Carbon::parse($app->date_received)->format('M d, Y') }}</td>
                                <td class="px-3 text-center">
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
                                    <span class="badge {{ $badgeColor }} px-3 py-2 shadow-sm status-badge">{{ $statusText }}</span>
                                </td>
                                <td class="px-3 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('applications.show', $app->id) }}" class="btn btn-sm btn-link text-primary p-0" title="View Details" style="font-size: 1rem;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('applications.show', $app->id) }}" class="btn btn-sm btn-link text-warning p-0" title="Edit" style="font-size: 1rem;">
                                            <i class="fas fa-pen-to-square"></i>
                                        </a>
                                        <button class="btn btn-sm btn-link text-danger p-0" title="Delete" style="font-size: 1rem;" onclick="if(confirm('Are you sure?')) {}">
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
</script>
@endsection