@extends('layouts.app')

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fas fa-map me-2 text-success"></i> Land Records</h3>
        <a href="{{ route('land-records.create') }}" class="btn btn-success fw-bold shadow-sm">
            <i class="fas fa-plus me-2"></i> New Land Record
        </a>
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
                </div>

                <!-- Export Button (Right) -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm fw-bold dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Export Data">
                        <i class="fas fa-download me-2"></i> Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
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
                            <tr class="land-row">
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
}

document.querySelectorAll('.row-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', () => {
        const allChecked = document.querySelectorAll('.row-checkbox:checked').length === document.querySelectorAll('.row-checkbox').length;
        document.getElementById('selectAll').checked = allChecked;
    });
});
</script>
@endsection
