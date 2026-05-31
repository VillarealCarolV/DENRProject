@extends('layouts.app')

@section('content')
<style>
    .page-wrapper {
        background-color: #f3f4f6;
        padding: 1.5rem 0.5rem;
        min-height: 100vh;
    }

    .document-container {
        max-width: 950px;
        margin: 0 auto;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    /* Document Header */
    .doc-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .tracking-info h2 {
        font-size: 1.35rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 0.35rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .tracking-icon {
        font-size: 1.35rem;
        color: #3b82f6;
    }

    .tracking-meta {
        font-size: 0.8rem;
        color: #6b7280;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .header-btns {
        display: flex;
        gap: 0.5rem;
    }

    .btn-sm-custom {
        padding: 0.35rem 0.75rem;
        font-size: 0.75rem;
        border: 1px solid #d1d5db;
        background: #f9fafb;
        color: #6b7280;
        border-radius: 0.25rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        text-decoration: none;
        transition: all 0.15s;
    }

    .btn-sm-custom:hover {
        background: #f3f4f6;
        color: #374151;
    }

    /* Tabs */
    .tabs {
        display: flex;
        border-bottom: 1px solid #e5e7eb;
        padding: 0 1.25rem;
        background: white;
    }

    .tab-btn {
        padding: 0.75rem 1rem;
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        font-size: 0.85rem;
        font-weight: 600;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.2s;
    }

    .tab-btn:hover {
        color: #3b82f6;
    }

    .tab-btn.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
    }

    /* Tab Panes */
    .tab-pane {
        display: none;
        padding: 0;
    }

    .tab-pane.active {
        display: block;
    }

    /* Section Separators */
    .section-sep {
        background-color: #f3f4f6;
        padding: 0.75rem 1.25rem;
        font-weight: 600;
        font-size: 0.9rem;
        color: #1f2937;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #e5e7eb;
        user-select: none;
        transition: background 0.15s;
    }

    .section-sep:hover {
        background-color: #e5e7eb;
    }

    .section-sep i:first-child {
        margin-right: 0.5rem;
    }

    .section-sep-arrow {
        font-size: 0.75rem;
        transition: transform 0.2s;
    }

    .section-content {
        padding: 0;
        max-height: none;
        overflow: visible;
        transition: all 0.2s;
    }

    .section-content.collapsed {
        display: none;
    }

    /* Data Table */
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table tr {
        border-bottom: 1px solid #f3f4f6;
    }

    .data-table td {
        padding: 0.45rem 1.25rem;
        font-size: 0.85rem;
    }

    .data-table .label {
        width: 150px;
        color: #9ca3af;
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 500;
        vertical-align: top;
    }

    .data-table .value {
        color: #1f2937;
        font-weight: 400;
    }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }

    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-in-process {
        background: #dbeafe;
        color: #0c4a6e;
    }

    @media (max-width: 768px) {
        .document-container {
            max-width: 100%;
        }

        .doc-header {
            flex-direction: column;
            gap: 0.75rem;
        }

        .tracking-meta {
            flex-direction: column;
            gap: 0.25rem;
        }

        .data-table .label {
            width: 120px;
        }

        .data-table td {
            padding: 0.35rem 0.75rem;
            font-size: 0.8rem;
        }
    }
</style>

<div class="page-wrapper">
    <div class="document-container">
        <!-- HEADER -->
        <div class="doc-header">
            <div class="tracking-info">
                <h2>
                    <i class="fas fa-barcode tracking-icon"></i>
                    {{ $application->tracking_no }}
                </h2>
                <div class="tracking-meta">
                    <span><i class="fas fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($application->date_received)->format('M d, Y') }}</span>
                    <span><i class="fas fa-user"></i> {{ $application->applicant->full_name }}</span>
                </div>
            </div>
            <div class="header-btns">
                <a href="{{ route('applications.index') }}" class="btn-sm-custom">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                @if(auth()->user()->role === 'processing')
                    <button class="btn-sm-custom" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                        <i class="fas fa-edit"></i> Update Status
                    </button>
                @endif
            </div>
        </div>

        <!-- TABS -->
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab(this, 'details')">Details</button>
            <button class="tab-btn" onclick="switchTab(this, 'history')">History</button>
            @if($application->lot_type)
                <button class="tab-btn" onclick="switchTab(this, 'assessment')">Assessment</button>
            @endif
        </div>

        <!-- DETAILS TAB -->
        <div id="details" class="tab-pane active">
            <!-- APPLICANT INFORMATION -->
            <div class="section-sep" onclick="toggleSection(this)">
                <div>
                    <i class="fas fa-user"></i> Applicant Information
                </div>
                <i class="fas fa-chevron-down section-sep-arrow"></i>
            </div>
            <div class="section-content">
                <table class="data-table">
                    <tr>
                        <td class="label">Full Name</td>
                        <td class="value">{{ $application->applicant->full_name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Address</td>
                        <td class="value">{{ $application->applicant->address ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Contact Number</td>
                        <td class="value">{{ $application->applicant->contact_no ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>

            <!-- LAND INFORMATION -->
            <div class="section-sep" onclick="toggleSection(this)">
                <div>
                    <i class="fas fa-map"></i> Land Record Information
                </div>
                <i class="fas fa-chevron-down section-sep-arrow"></i>
            </div>
            <div class="section-content">
                <table class="data-table">
                    <tr>
                        <td class="label">Survey Number</td>
                        <td class="value">{{ $application->landRecord->survey_no }}</td>
                    </tr>
                    <tr>
                        <td class="label">Total Area</td>
                        <td class="value">{{ number_format($application->landRecord->total_area, 2) }} sqm</td>
                    </tr>
                    <tr>
                        <td class="label">Location</td>
                        <td class="value">{{ $application->landRecord->location }}</td>
                    </tr>
                </table>
            </div>

            <!-- APPLICATION DETAILS -->
            <div class="section-sep" onclick="toggleSection(this)">
                <div>
                    <i class="fas fa-file-contract"></i> Application Details
                </div>
                <i class="fas fa-chevron-down section-sep-arrow"></i>
            </div>
            <div class="section-content">
                <table class="data-table">
                    <tr>
                        <td class="label">Patent Type</td>
                        <td class="value">{{ $application->patent_type ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Patent Details</td>
                        <td class="value">{{ $application->patent_details ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Current Status</td>
                        <td class="value">
                            @php
                                $latestStatus = $application->statusHistories()->latest()->first();
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
                    </tr>
                </table>
            </div>
        </div>

        <!-- HISTORY TAB -->
        <div id="history" class="tab-pane">
            <div class="section-sep" onclick="toggleSection(this)">
                <div>
                    <i class="fas fa-history"></i> Status History & Audit Trail
                </div>
                <i class="fas fa-chevron-down section-sep-arrow"></i>
            </div>
            <div class="section-content" style="padding: 0;">
                @forelse($application->statusHistories()->latest()->get() as $history)
                    <div style="padding: 0.6rem 1.25rem; border-bottom: 1px solid #f3f4f6; display: flex; gap: 0.75rem;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #dbeafe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.75rem; flex-shrink: 0;">
                            <i class="fas fa-{{ $history->status === 'Approved' ? 'check' : ($history->status === 'Rejected' ? 'times' : 'hourglass-half') }}"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; font-size: 0.8rem; color: #1f2937;">{{ $history->status }}</div>
                            <div style="font-size: 0.75rem; color: #6b7280; margin-top: 0.15rem;">{{ $history->remarks }}</div>
                            <div style="font-size: 0.7rem; color: #9ca3af; margin-top: 0.25rem;">
                                <i class="fas fa-user me-1"></i>{{ $history->updated_by }} • {{ $history->created_at->format('M d, Y h:i A') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="padding: 1.5rem; text-align: center; color: #9ca3af; font-size: 0.85rem;">No history available</div>
                @endforelse
            </div>
        </div>

        <!-- ASSESSMENT TAB -->
        @if($application->lot_type)
        <div id="assessment" class="tab-pane">
            <!-- LOT CLASSIFICATION -->
            <div class="section-sep" onclick="toggleSection(this)">
                <div>
                    <i class="fas fa-clipboard-check"></i> Lot Classification
                </div>
                <i class="fas fa-chevron-down section-sep-arrow"></i>
            </div>
            <div class="section-content">
                <table class="data-table">
                    <tr>
                        <td class="label">Classification</td>
                        <td class="value">
                            @if($application->lot_type === 'existing_lot')
                                <span class="status-badge" style="background: #d1fae5; color: #065f46;">Existing Lot</span>
                            @else
                                <span class="status-badge" style="background: #fef3c7; color: #92400e;">Subdivision</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Assessed By</td>
                        <td class="value">{{ $application->landOfficer->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Assessment Date</td>
                        <td class="value">{{ $application->assessed_at ? \Carbon\Carbon::parse($application->assessed_at)->format('M d, Y h:i A') : 'Pending' }}</td>
                    </tr>
                </table>
            </div>

            @if($application->lot_type === 'subdivision')
            <!-- SUBDIVISION DETAILS -->
            <div class="section-sep" onclick="toggleSection(this)">
                <div>
                    <i class="fas fa-expand"></i> Subdivision Details
                </div>
                <i class="fas fa-chevron-down section-sep-arrow"></i>
            </div>
            <div class="section-content">
                <table class="data-table">
                    <tr>
                        <td class="label">New Lot Number</td>
                        <td class="value">{{ $application->new_lot_number ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Subdivided Area</td>
                        <td class="value">{{ number_format($application->subdivided_area ?? 0, 2) }} sqm</td>
                    </tr>
                    <tr>
                        <td class="label">Mother Lot Total</td>
                        <td class="value">{{ number_format($application->landRecord->total_area, 2) }} sqm</td>
                    </tr>
                    <tr>
                        <td class="label">Remaining Area</td>
                        <td class="value" style="color: #059669; font-weight: 600;">{{ number_format($application->remaining_area ?? 0, 2) }} sqm</td>
                    </tr>
                </table>
            </div>
            @endif

            <!-- REMARKS -->
            <div class="section-sep" onclick="toggleSection(this)">
                <div>
                    <i class="fas fa-pen"></i> Official Remarks
                </div>
                <i class="fas fa-chevron-down section-sep-arrow"></i>
            </div>
            <div class="section-content">
                <div style="padding: 0.75rem 1.25rem; color: #4b5563; font-size: 0.85rem; line-height: 1.5;">
                    {{ $application->land_officer_remarks ?? 'No remarks provided.' }}
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #3b82f6; color: white;">
                <h5 class="modal-title"><i class="fas fa-tasks me-2"></i> Update Application Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('applications.updateStatus', $application->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="mb-3">Updating status for: <strong>{{ $application->tracking_no }}</strong></p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Status</label>
                        <select name="status" class="form-select form-select-sm" required>
                            <option value="In Process">In Process</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Remarks / Notes</label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="3" placeholder="e.g., All documents verified. Patent ready for signature."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Save Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function switchTab(btn, tabId) {
        // Hide all tabs
        document.querySelectorAll('.tab-pane').forEach(tab => tab.classList.remove('active'));
        
        // Remove active from all buttons
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        
        // Show new tab and mark button active
        document.getElementById(tabId).classList.add('active');
        btn.classList.add('active');
    }

    function toggleSection(headerEl) {
        const content = headerEl.nextElementSibling;
        const arrow = headerEl.querySelector('.section-sep-arrow');
        
        if (content.classList.contains('collapsed')) {
            content.classList.remove('collapsed');
            arrow.classList.remove('fa-chevron-right');
            arrow.classList.add('fa-chevron-down');
        } else {
            content.classList.add('collapsed');
            arrow.classList.remove('fa-chevron-down');
            arrow.classList.add('fa-chevron-right');
        }
    }
</script>

@endsection
