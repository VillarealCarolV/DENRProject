@extends('layouts.app')

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fas fa-map me-2 text-success"></i> Land Records</h3>
        {{-- <a href="{{ route('land-records.create') }}" class="btn btn-success fw-bold shadow-sm">
            <i class="fas fa-plus me-2"></i> New Land Record
        </a> --}}
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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
                            placeholder="Search survey/location..."
                            id="searchInput"
                            onkeyup="filterTable()">
                    </div>

                    <select class="form-select form-select-sm" style="max-width: 150px;" id="subdivisionFilter" onchange="filterTable()">
                        <option value="">All Records</option>
                        <option value="Yes">Subdivided</option>
                        <option value="No">Not Subdivided</option>
                    </select>
                    
                    <!-- Bulk Delete Button (Hidden by default) -->
                    <button id="bulkDeleteBtn" class="btn btn-danger btn-sm fw-bold d-none" onclick="confirmBulkDelete()" title="Delete selected land records">
                        <i class="fas fa-trash-can me-2"></i> Delete Selected (<span id="selectedCount">0</span>)
                    </button>
                </div>

                <!-- Export Button (Right) -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm fw-bold" type="button" onclick="document.getElementById('landRecordsExportMenu').classList.toggle('show')">
                        <i class="fas fa-download me-2"></i> Export <i class="fas fa-caret-down ms-1"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" id="landRecordsExportMenu" style="position: absolute; right: 0;">
                        <li><a class="dropdown-item" href="{{ route('land-records.export', ['format' => 'csv']) }}"><i class="fas fa-file-csv me-2 text-success"></i>CSV</a></li>
                        <li><a class="dropdown-item" href="{{ route('land-records.export', ['format' => 'excel']) }}"><i class="fas fa-file-excel me-2 text-success"></i>Excel</a></li>
                        <li><a class="dropdown-item" href="{{ route('land-records.export', ['format' => 'pdf']) }}"><i class="fas fa-file-pdf me-2 text-danger"></i>PDF</a></li>
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
                            <th class="py-3 px-3">Survey No.</th>
                            <th class="py-3 px-3">Location</th>
                            <th class="py-3 px-3">Total Area</th>
                            <th class="py-3 px-3 text-center">Subdivided</th>
                            <th class="py-3 px-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($landRecords ?? [] as $record)
                            <tr class="land-row" data-land-record-id="{{ $record->id }}">
                                <td class="ps-3">
                                    <input type="checkbox" class="form-check-input row-checkbox">
                                </td>
                                <td class="px-3 fw-bold text-primary">{{ $record->survey_no }}</td>
                                <td class="px-3">{{ $record->location }}</td>
                                <td class="px-3">{{ number_format($record->total_area, 2) }} sqm</td>
                                <td class="px-3 text-center">
                                    @if($record->is_subdivided)
                                        <span class="badge bg-success px-3 py-2 subdivision-status">Yes</span>
                                    @else
                                        <span class="badge bg-secondary px-3 py-2 subdivision-status">No</span>
                                    @endif
                                </td>
                                <td class="px-3 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('land-records.show', $record->id) }}" class="btn btn-sm btn-link text-primary p-0" title="View Details" style="font-size: 1rem;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('land-records.edit', $record->id) }}" class="btn btn-sm btn-link text-warning p-0" title="Edit" style="font-size: 1rem;">
                                            <i class="fas fa-pen-to-square"></i>
                                        </a>
                                        <button class="btn btn-sm btn-link text-danger p-0" title="Delete" style="font-size: 1rem;" onclick="if(confirm('Are you sure?')) {}">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 text-light"></i>
                                    <h5>No land records found.</h5>
                                    <p>Create a new land record to get started.</p>
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
    const subdivisionFilter = document.getElementById('subdivisionFilter').value;
    const rows = document.querySelectorAll('.land-row');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const statusBadge = row.querySelector('.subdivision-status');
        const statusText = statusBadge ? statusBadge.textContent.trim() : '';
        
        const matchesSearch = text.includes(searchInput);
        const matchesStatus = subdivisionFilter === '' || statusText === subdivisionFilter;

        row.style.display = matchesSearch && matchesStatus ? '' : 'none';
    });
}

function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    const selectAll = document.getElementById('selectAll');
    checkboxes.forEach(checkbox => checkbox.checked = selectAll.checked);
    updateBulkDeleteButton();
}

function updateBulkDeleteButton() {
    const selectedCount = document.querySelectorAll('.row-checkbox:checked').length;
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectedCountSpan = document.getElementById('selectedCount');
    
    selectedCountSpan.textContent = selectedCount;
    
    if (selectedCount > 0) {
        bulkDeleteBtn.classList.remove('d-none');
    } else {
        bulkDeleteBtn.classList.add('d-none');
    }
}

function confirmBulkDelete() {
    const selectedCount = document.querySelectorAll('.row-checkbox:checked').length;
    
    if (selectedCount === 0) {
        alert('Please select at least one land record to delete.');
        return;
    }
    
    const confirmMessage = `Are you sure you want to delete ${selectedCount} land record(s)?\n\nThis action cannot be undone.`;
    
    if (confirm(confirmMessage)) {
        executeBulkDelete();
    }
}

function executeBulkDelete() {
    const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked'))
        .map(checkbox => checkbox.closest('.land-row').dataset.landRecordId);
    
    if (selectedIds.length === 0) {
        alert('Please select at least one land record to delete.');
        return;
    }
    
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const originalText = bulkDeleteBtn.innerHTML;
    bulkDeleteBtn.disabled = true;
    bulkDeleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Deleting...';
    
    fetch('{{ route("land-records.bulkDelete") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ ids: selectedIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.role = 'alert';
            alertDiv.innerHTML = `
                <i class="fas fa-check-circle me-2"></i> ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.container-fluid');
            container.insertBefore(alertDiv, container.querySelector('.card'));
            
            // Remove deleted rows from table
            selectedIds.forEach(id => {
                const row = document.querySelector(`tr.land-row[data-land-record-id="${id}"]`);
                if (row) {
                    row.style.animation = 'fadeOut 0.3s';
                    setTimeout(() => row.remove(), 300);
                }
            });
            
            // Reset checkboxes and button
            document.getElementById('selectAll').checked = false;
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
            updateBulkDeleteButton();
            
            // Dismiss alert after 4 seconds
            setTimeout(() => {
                const closeBtn = alertDiv.querySelector('.btn-close');
                if (closeBtn) closeBtn.click();
            }, 4000);
        } else {
            alert(`Error: ${data.message}`);
        }
        
        bulkDeleteBtn.disabled = false;
        bulkDeleteBtn.innerHTML = originalText;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting land records.');
        bulkDeleteBtn.disabled = false;
        bulkDeleteBtn.innerHTML = originalText;
    });
}

// Add fade-out animation
const style = document.createElement('style');
style.innerHTML = `
    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

document.querySelectorAll('.row-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', () => {
        const allChecked = document.querySelectorAll('.row-checkbox:checked').length === document.querySelectorAll('.row-checkbox').length;
        document.getElementById('selectAll').checked = allChecked;
        updateBulkDeleteButton();
    });
});

// Close export dropdown only when clicking outside of it
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('landRecordsExportMenu');
        if (!menu) return;
        
        // Check if the click is on the export dropdown or the button that toggles it
        const exportButton = document.querySelector('button[onclick*="landRecordsExportMenu"]');
        const clickedMenu = event.target.closest('#landRecordsExportMenu');
        const clickedButton = event.target === exportButton || event.target.closest('button[onclick*="landRecordsExportMenu"]');
        
        // Only close if clicking outside both the menu and button
        if (!clickedMenu && !clickedButton) {
            menu.classList.remove('show');
        }
    });
});
</script>
@endsection
