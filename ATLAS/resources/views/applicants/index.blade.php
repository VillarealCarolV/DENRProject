@extends('layouts.app')

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fas fa-users me-2 text-primary"></i> Applicants</h3>
        <a href="{{ route('applicants.create') }}" class="btn btn-primary fw-bold shadow-sm">
            <i class="fas fa-plus me-2"></i> New Applicant
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
                            placeholder="Search name/contact..."
                            id="searchInput"
                            onkeyup="filterTable()">
                    </div>
                </div>

                <!-- Export Button (Right) -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm fw-bold dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Export Data">
                        <i class="fas fa-download me-2"></i> Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="{{ route('applicants.export', ['format' => 'csv']) }}"><i class="fas fa-file-csv me-2 text-success"></i>CSV</a></li>
                        <li><a class="dropdown-item" href="{{ route('applicants.export', ['format' => 'excel']) }}"><i class="fas fa-file-excel me-2 text-success"></i>Excel</a></li>
                        <li><a class="dropdown-item" href="{{ route('applicants.export', ['format' => 'pdf']) }}"><i class="fas fa-file-pdf me-2 text-danger"></i>PDF</a></li>
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
                            <th class="py-3 px-3">Full Name</th>
                            <th class="py-3 px-3">Address</th>
                            <th class="py-3 px-3">Contact No.</th>
                            <th class="py-3 px-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applicants ?? [] as $applicant)
                            <tr class="applicant-row">
                                <td class="ps-3">
                                    <input type="checkbox" class="form-check-input row-checkbox">
                                </td>
                                <td class="px-3 fw-bold text-primary">{{ $applicant->full_name }}</td>
                                <td class="px-3">{{ $applicant->address ?? '—' }}</td>
                                <td class="px-3">{{ $applicant->contact_no ?? '—' }}</td>
                                <td class="px-3 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('applicants.show', $applicant->id) }}" class="btn btn-sm btn-link text-primary p-0" title="View Details" style="font-size: 1rem;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('applicants.edit', $applicant->id) }}" class="btn btn-sm btn-link text-warning p-0" title="Edit" style="font-size: 1rem;">
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
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 text-light"></i>
                                    <h5>No applicants found.</h5>
                                    <p>Create a new applicant to get started.</p>
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
    const rows = document.querySelectorAll('.applicant-row');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const matchesSearch = text.includes(searchInput);
        row.style.display = matchesSearch ? '' : 'none';
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
