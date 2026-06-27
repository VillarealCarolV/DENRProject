@extends('layouts.app')

@section('content')
<style>
    .page-wrapper {
        background-color: white;
        padding: 0;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .document-container {
        width: 100%;
        height: 100%;
        background: white;
        border: none;
        border-radius: 0;
        box-shadow: none;
        overflow: hidden;
        display: flex;
        flex-direction: column;
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

    /* Section Container */
    .section-container {
        
        background: white;
        margin-bottom: 1.5rem;
        border-radius: 0.375rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    /* Section Header with Actions */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .section-title {
        font-weight: 600;
        font-size: 0.95rem;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
    }

    .section-title i {
        color: #16a34a;
        font-size: 1rem;
    }

    .section-actions {
        display: flex;
        gap: 0.5rem;
    }

    /* Sage Green Sub-header */
    .section-subheader {
        background-color: #7cb382;
        padding: 0.5rem 1.25rem;
        color: white;
        font-size: 0.8rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        border-bottom: 1px solid #6b9b6d;
    }

    .section-subheader i {
        font-size: 0.7rem;
    }

    /* High-Density Grid for Metadata */
    .metadata-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 0;
        padding: 1rem 1.25rem;
    }

    .metadata-grid.grid-2col {
        grid-template-columns: repeat(2, 1fr);
    }

    .metadata-grid.grid-4col {
        grid-template-columns: repeat(4, 1fr);
    }

    .metadata-item {
        padding-bottom: 0.75rem;
        padding-right: 1.5rem;
        border-right: 1px solid #e5e7eb;
    }

    .metadata-item:nth-child(2n) {
        border-right: none;
    }

    @media (max-width: 1024px) {
        .metadata-grid.grid-4col {
            grid-template-columns: repeat(2, 1fr);
        }

        .metadata-item:nth-child(2n) {
            border-right: 1px solid #e5e7eb;
        }

        .metadata-item:nth-child(4n) {
            border-right: none;
        }
    }

    @media (max-width: 768px) {
        .metadata-grid {
            grid-template-columns: 1fr;
        }

        .metadata-item {
            border-right: none;
            padding-right: 0;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .metadata-item:last-child {
            border-bottom: none;
        }
    }

    .metadata-label {
        color: #9ca3af;
        font-size: 0.7rem;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.3px;
        margin-bottom: 0.25rem;
        display: block;
    }

    .metadata-value {
        color: #1f2937;
        font-weight: 500;
        font-size: 0.9rem;
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

        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .metadata-grid {
            grid-template-columns: 1fr;
        }

        .metadata-item {
            border-right: none;
            padding-right: 0;
            padding-bottom: 1rem;
        }

        .section-actions {
            width: 100%;
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
            
            <!-- APPLICANT INFORMATION PANEL -->
            <div class="section-container">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-user"></i> Applicant Information
                    </h3>
                    <div class="section-actions">
                        <!-- Add action buttons here if needed -->
                    </div>
                </div>
                <div class="section-subheader">
                    <i class="fas fa-chevron-down"></i> Details
                </div>
                <div class="metadata-grid grid-2col">
                    <div class="metadata-item">
                        <span class="metadata-label">Full Name</span>
                        <div class="metadata-value">{{ $application->applicant->full_name }}</div>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Contact Number</span>
                        <div class="metadata-value">{{ $application->applicant->contact_no ?? 'N/A' }}</div>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Address</span>
                        <div class="metadata-value">{{ $application->applicant->address ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <!-- LAND INFORMATION PANEL -->
            <div class="section-container">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-map"></i> Land Record Information
                    </h3>
                    <div class="section-actions">
                        <!-- Add action buttons here if needed -->
                    </div>
                </div>
                <div class="section-subheader">
                    <i class="fas fa-chevron-down"></i> Details
                </div>
                <div class="metadata-grid grid-4col">
                    <div class="metadata-item">
                        <span class="metadata-label">Survey Number</span>
                        <div class="metadata-value">{{ $application->landRecord->survey_no }}</div>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Total Area</span>
                        <div class="metadata-value">{{ number_format($application->landRecord->total_area, 2) }} sqm</div>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Location</span>
                        <div class="metadata-value">{{ $application->landRecord->location }}</div>
                    </div>
                </div>
            </div>

            <!-- APPLICATION DETAILS PANEL -->
            <div class="section-container">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-file-contract"></i> Application Details
                    </h3>
                    <div class="section-actions">
                        <!-- Add action buttons here if needed -->
                    </div>
                </div>
                <div class="section-subheader">
                    <i class="fas fa-chevron-down"></i> Details
                </div>
                <div class="metadata-grid grid-2col">
                    <div class="metadata-item">
                        <span class="metadata-label">Patent Type</span>
                        <div class="metadata-value">{{ $application->patent_type ?? 'N/A' }}</div>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Current Status</span>
                        <div class="metadata-value">
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
                        </div>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Patent Details</span>
                        <div class="metadata-value">{{ $application->patent_details ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

        </div>

        <!-- HISTORY TAB -->
        <div id="history" class="tab-pane">
            <div class="section-container">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-history"></i> Status History & Audit Trail
                    </h3>
                    <div class="section-actions">
                        <!-- Add action buttons here if needed -->
                    </div>
                </div>
                <div class="section-subheader">
                    <i class="fas fa-chevron-down"></i> Details
                </div>
                <div style="padding: 0;">
                    @forelse($application->statusHistories()->latest()->get() as $history)
                        <div style="padding: 0.75rem 1.25rem; border-bottom: 1px solid #f3f4f6; display: flex; gap: 0.75rem;">
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
        </div>

        <!-- ASSESSMENT TAB -->
        @if($application->lot_type)
        <div id="assessment" class="tab-pane">
            
            <!-- LOT CLASSIFICATION PANEL -->
            <div class="section-container">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-clipboard-check"></i> Lot Classification
                    </h3>
                    <div class="section-actions">
                        <!-- Add action buttons here if needed -->
                    </div>
                </div>
                <div class="section-subheader">
                    <i class="fas fa-chevron-down"></i> Details
                </div>
                <div class="metadata-grid grid-2col">
                    <div class="metadata-item">
                        <span class="metadata-label">Classification</span>
                        <div class="metadata-value">
                            @if($application->lot_type === 'existing_lot')
                                <span class="status-badge" style="background: #d1fae5; color: #065f46;">Existing Lot</span>
                            @else
                                <span class="status-badge" style="background: #fef3c7; color: #92400e;">Subdivision</span>
                            @endif
                        </div>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Assessed By</span>
                        <div class="metadata-value">{{ $application->landOfficer->name ?? 'N/A' }}</div>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Assessment Date</span>
                        <div class="metadata-value">{{ $application->assessed_at ? \Carbon\Carbon::parse($application->assessed_at)->format('M d, Y h:i A') : 'Pending' }}</div>
                    </div>
                </div>
            </div>

            @if($application->lot_type === 'subdivision')
            <!-- SUBDIVISION DETAILS PANEL -->
            <div class="section-container">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-expand"></i> Subdivision Details
                    </h3>
                    <div class="section-actions">
                        <!-- Add action buttons here if needed -->
                    </div>
                </div>
                <div class="section-subheader">
                    <i class="fas fa-chevron-down"></i> Mother Lot Information
                </div>
                <div class="metadata-grid grid-4col">
                    <div class="metadata-item">
                        <span class="metadata-label">Mother Lot Survey No.</span>
                        <div class="metadata-value">{{ $application->landRecord->survey_no }}</div>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Mother Lot Total Area</span>
                        <div class="metadata-value">{{ number_format($application->landRecord->total_area, 2) }} sqm</div>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Number of Subdivided Lots</span>
                        <div class="metadata-value">{{ $application->landRecord->children->count() }}</div>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Remaining Area</span>
                        <div class="metadata-value" style="color: #059669; font-weight: 600;">
                            {{ number_format($application->landRecord->total_area - $application->landRecord->children->sum('total_area'), 2) }} sqm
                        </div>
                    </div>
                </div>

                @if($application->landRecord->children->count() > 0)
                <div class="section-subheader" style="margin-top: 20px;">
                    <i class="fas fa-chevron-down"></i> Subdivided Lots
                </div>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($application->landRecord->children as $index => $childLot)
                    <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 14px;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px;">
                            <div>
                                <span style="font-size: 0.7rem; color: #6b7280; font-weight: 600; text-transform: uppercase;">New Lot Number</span>
                                <div style="font-size: 0.95rem; color: #1f2937; font-weight: 600; margin-top: 4px;">{{ $childLot->survey_no }}</div>
                            </div>
                            <div>
                                <span style="font-size: 0.7rem; color: #6b7280; font-weight: 600; text-transform: uppercase;">Subdivided Area</span>
                                <div style="font-size: 0.95rem; color: #1f2937; font-weight: 600; margin-top: 4px;">{{ number_format($childLot->total_area, 2) }} sqm</div>
                            </div>
                            <div>
                                <span style="font-size: 0.7rem; color: #6b7280; font-weight: 600; text-transform: uppercase;">Lot Designation</span>
                                <div style="font-size: 0.95rem; color: #3b82f6; font-weight: 600; margin-top: 4px;">
                                    @php
                                        $letters = 'ABCDEFGHIJ';
                                        echo $letters[$index] ?? 'Unknown';
                                    @endphp
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            <!-- REMARKS PANEL -->
            <div class="section-container">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-pen"></i> Official Remarks
                    </h3>
                    <div class="section-actions">
                        <!-- Add action buttons here if needed -->
                    </div>
                </div>
                <div class="section-subheader">
                    <i class="fas fa-chevron-down"></i> Details
                </div>
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
    <div class="modal-dialog modal-fullscreen">
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
</script>

@endsection
