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

    /* Document Header - Simple Text */
    .doc-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: start;
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

    /* Tab Content */
    .tab-pane {
        display: none;
    }

    .tab-pane.active {
        display: block;
    }

    /* Section Separator */
    .section-sep {
        padding: 0.5rem 1.25rem;
        background: #f3f4f6;
        border-bottom: 1px solid #e5e7eb;
        font-size: 0.8rem;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        user-select: none;
    }

    .section-sep:hover {
        background: #e5e7eb;
    }

    .section-sep-arrow {
        margin-left: auto;
        font-size: 0.75rem;
        transition: transform 0.2s;
    }

    .section-content {
        display: block;
    }

    .section-content.collapsed {
        display: none;
    }

    /* Data Table - Tight Grid */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        padding: 0;
        margin: 0;
    }

    .data-table td {
        padding: 0.45rem 1.25rem;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: top;
    }

    .data-table tr:last-child td {
        border-bottom: none;
    }

    .data-table .label {
        width: 150px;
        color: #9ca3af;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }

    .data-table .value {
        color: #1f2937;
        font-size: 0.9rem;
        font-weight: 500;
    }

    /* Edit Button */
    .edit-btn-section {
        text-align: right;
        padding-right: 0.5rem;
    }

    .btn-edit {
        padding: 0.35rem 0.75rem;
        font-size: 0.75rem;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: background 0.15s;
    }

    .btn-edit:hover {
        background: #2563eb;
    }

    /* Form Sections */
    .form-area {
        padding: 1rem 1.25rem;
        background: #f9fafb;
        border-left: 3px solid #3b82f6;
        margin: 0.75rem 1.25rem;
        border-radius: 0.25rem;
    }

    .form-area h6 {
        font-size: 0.8rem;
        font-weight: 700;
        margin: 0 0 0.5rem 0;
        color: #1f2937;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .form-area p {
        font-size: 0.75rem;
        color: #6b7280;
        margin: 0 0 0.5rem 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-wrapper {
            padding: 0.5rem;
        }

        .document-container {
            border-radius: 0;
        }

        .doc-header {
            flex-direction: column;
            gap: 0.75rem;
        }

        .data-table .label {
            width: 120px;
            font-size: 0.7rem;
        }

        .data-table td {
            padding: 0.35rem 0.75rem;
        }
    }
</style>

<div class="page-wrapper">
    <div class="document-container">
        
        <!-- Document Header -->
        <div class="doc-header">
            <div class="tracking-info">
                <h2>
                    <i class="fas fa-barcode tracking-icon"></i>
                    Application {{ $application->tracking_no }}
                </h2>
                <div class="tracking-meta">
                    <span><i class="fas fa-calendar me-1"></i>{{ $application->date_received->format('M d, Y') }}</span>
                    <span><i class="fas fa-user me-1"></i>{{ $application->applicant->full_name }}</span>
                </div>
            </div>
            <div class="header-btns">
                <button onclick="window.print()" class="btn-sm-custom" title="Print"><i class="fas fa-print"></i> Print</button>
                <a href="{{ route('applications.index') }}" class="btn-sm-custom" title="Back"><i class="fas fa-arrow-left"></i> Back</a>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab(this, 'details')"><i class="fas fa-file-alt me-1"></i> Details</button>
            <button class="tab-btn" onclick="switchTab(this, 'history')"><i class="fas fa-history me-1"></i> History</button>
            <button class="tab-btn" onclick="switchTab(this, 'documents')"><i class="fas fa-file-pdf me-1"></i> Documents</button>
        </div>

        <!-- DETAILS TAB -->
        <div id="details" class="tab-pane active">

            <!-- Applicant Information -->
            <div class="section-sep" onclick="toggleSection(this)">
                <i class="fas fa-user"></i> Applicant Information
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
                        <td class="value">{{ $application->applicant->address ?? 'Not provided' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Contact</td>
                        <td class="value">{{ $application->applicant->contact_no ?? 'Not provided' }}</td>
                    </tr>
                </table>
            </div>

            <!-- Land Details -->
            <div class="section-sep" onclick="toggleSection(this)">
                <i class="fas fa-map"></i> Land Details
                <i class="fas fa-chevron-down section-sep-arrow"></i>
            </div>
            <div class="section-content">
                <table class="data-table">
                    <tr>
                        <td class="label">Survey No.</td>
                        <td class="value">{{ $application->landRecord->survey_no }}</td>
                    </tr>
                    <tr>
                        <td class="label">Total Area</td>
                        <td class="value"><span style="background: #dbeafe; color: #1e40af; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.8rem; display: inline-block;">{{ number_format($application->landRecord->total_area, 2) }} sqm</span></td>
                    </tr>
                    <tr>
                        <td class="label">Location</td>
                        <td class="value">{{ $application->landRecord->location }}</td>
                    </tr>
                </table>
            </div>

            <!-- Assessment Details -->
            <div class="section-sep" onclick="toggleSection(this)">
                <i class="fas fa-tasks"></i> Assessment Details
                <span class="edit-btn-section"><button onclick="toggleEditMode()" class="btn-edit"><i class="fas fa-edit"></i> Edit</button></span>
                <i class="fas fa-chevron-down section-sep-arrow"></i>
            </div>
            <div class="section-content">
                
                <!-- View Mode -->
                <div id="viewMode">
                    <table class="data-table">
                        <tr>
                            <td class="label">Classification</td>
                            <td class="value">
                                @if($application->lot_type)
                                    <span style="background: {{ $application->lot_type === 'existing_lot' ? '#dbeafe' : '#e0f2fe' }}; color: {{ $application->lot_type === 'existing_lot' ? '#1e40af' : '#0369a1' }}; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.8rem; display: inline-block;">{{ $application->lot_type === 'existing_lot' ? 'Existing Lot' : 'Subdivision' }}</span>
                                @else
                                    <span style="color: #9ca3af;">Not classified</span>
                                @endif
                            </td>
                        </tr>
                        @if($application->lot_type === 'subdivision')
                            <tr>
                                <td class="label" colspan="2" style="font-weight: 600; background: #f9fafb; padding: 8px 12px;">
                                    <i class="fas fa-expand"></i> Subdivision Details
                                </td>
                            </tr>
                            <tr>
                                <td class="label">Mother Lot Total</td>
                                <td class="value">{{ number_format($application->landRecord->total_area, 2) }} sqm</td>
                            </tr>
                            <tr>
                                <td class="label">Number of Lots</td>
                                <td class="value">{{ $application->landRecord->children->count() }}</td>
                            </tr>
                            @if($application->landRecord->children->count() > 0)
                                @foreach($application->landRecord->children as $index => $childLot)
                                <tr>
                                    <td class="label" style="color: #6b7280;">
                                        @php
                                            $letters = 'ABCDEFGHIJ';
                                            echo 'Lot ' . ($letters[$index] ?? 'Unknown');
                                        @endphp
                                    </td>
                                    <td class="value">
                                        <div>
                                            <strong>{{ $childLot->survey_no }}</strong>
                                            <span style="background: #dbeafe; color: #1e40af; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.8rem; display: inline-block; margin-left: 8px;">{{ number_format($childLot->total_area, 2) }} sqm</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td class="label">Total Subdivided</td>
                                    <td class="value">{{ number_format($application->landRecord->children->sum('total_area'), 2) }} sqm</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="label">Remaining</td>
                                <td class="value"><span style="background: #dcfce7; color: #15803d; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.8rem; display: inline-block;">{{ number_format($application->landRecord->total_area - $application->landRecord->children->sum('total_area'), 2) }} sqm</span></td>
                            </tr>
                        @endif
                        <tr>
                            <td class="label">Status</td>
                            <td class="value">
                                @php
                                    $latestStatus = $application->statusHistories()->latest()->first();
                                    $statusText = $latestStatus ? $latestStatus->status : 'Pending';
                                    $statusColor = '#d97706';
                                    if ($statusText === 'In Process') {
                                        $statusColor = '#0891b2';
                                    } elseif ($statusText === 'Approved') {
                                        $statusColor = '#059669';
                                    } elseif ($statusText === 'Rejected') {
                                        $statusColor = '#dc2626';
                                    }
                                @endphp
                                <span style="background: {{ $statusColor }}14; color: {{ $statusColor }}; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-weight: 600; font-size: 0.8rem; display: inline-block;">
                                    <span style="width: 4px; height: 4px; border-radius: 50%; background: {{ $statusColor }}; display: inline-block; margin-right: 0.35rem;"></span>
                                    {{ $statusText }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Remarks</td>
                            <td class="value" style="font-style: italic; font-size: 0.85rem;">{{ $application->land_officer_remarks ?? 'No remarks' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Patent Details</td>
                            <td class="value">{{ $application->patent_details ?? 'Not provided' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Patent Type</td>
                            <td class="value">{{ $application->patent_type ?? 'Not provided' }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Edit Mode (hidden) -->
                <div id="editMode" style="display: none;">
                    <form action="{{ route('applications.update', $application->id) }}" method="POST" id="assessmentForm">
                        @csrf
                        @method('PUT')

                        <div class="form-area">
                            <h6>Step 1: Lot Classification</h6>
                            <p>Determine if this is an existing lot or a subdivision.</p>
                            <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                                <label style="font-size: 0.8rem;"><input type="radio" name="lot_type" class="lot-type-radio" value="existing_lot" {{ $application->lot_type === 'existing_lot' ? 'checked' : '' }}> Existing Lot</label>
                                <label style="font-size: 0.8rem;"><input type="radio" name="lot_type" class="lot-type-radio" value="subdivision" {{ $application->lot_type === 'subdivision' ? 'checked' : '' }}> Subdivision</label>
                            </div>
                        </div>

                        <div id="subdivisionFields" style="display: {{ $application->lot_type === 'subdivision' ? 'block' : 'none' }};" class="form-area">
                            <h6>Step 2: Subdivision Details</h6>
                            <div class="row" style="margin: 0; gap: 0.75rem;">
                                <div class="col-md-6">
                                    <label style="font-size: 0.75rem; display: block; margin-bottom: 0.25rem;">New Lot No.</label>
                                    <input type="text" class="form-control form-control-sm @error('new_lot_number') is-invalid @enderror" id="new_lot_number" name="new_lot_number" value="{{ $application->new_lot_number ?? old('new_lot_number') }}" {{ $application->lot_type === 'subdivision' ? 'required' : '' }}>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-size: 0.75rem; display: block; margin-bottom: 0.25rem;">Subdivided Area (sqm)</label>
                                    <input type="number" class="form-control form-control-sm @error('subdivided_area') is-invalid @enderror" id="subdivided_area" name="subdivided_area" step="0.01" min="0.01" value="{{ $application->subdivided_area ?? old('subdivided_area') }}" {{ $application->lot_type === 'subdivision' ? 'required' : '' }} onchange="calculateRemainingArea()">
                                </div>
                            </div>
                            <div style="font-size: 0.75rem; margin-top: 0.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; padding-top: 0.5rem; border-top: 1px solid #e5e7eb;">
                                <div><strong>Mother Lot:</strong> {{ number_format($application->landRecord->total_area, 2) }} sqm</div>
                                <div style="color: #059669;"><strong>Remaining:</strong> <span id="remainingAreaDisplay">{{ number_format($application->remaining_area ?? ($application->landRecord->total_area - ($application->subdivided_area ?? 0)), 2) }}</span> sqm</div>
                            </div>
                        </div>

                        <div class="form-area">
                            <h6>Step 3: Final Decision</h6>
                            <div class="mb-2">
                                <label style="font-size: 0.75rem; display: block; margin-bottom: 0.25rem;">Status</label>
                                <select class="form-select form-select-sm @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">Select...</option>
                                    <option value="In Process" {{ $application->statusHistories()->latest()->first()?->status === 'In Process' ? 'selected' : '' }}>In Process</option>
                                    <option value="Approved" {{ $application->statusHistories()->latest()->first()?->status === 'Approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="Rejected" {{ $application->statusHistories()->latest()->first()?->status === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label style="font-size: 0.75rem; display: block; margin-bottom: 0.25rem;">Remarks</label>
                                <textarea class="form-control form-control-sm @error('land_officer_remarks') is-invalid @enderror" id="land_officer_remarks" name="land_officer_remarks" rows="2" required>{{ $application->land_officer_remarks ?? old('land_officer_remarks') }}</textarea>
                            </div>
                            <div class="row" style="margin: 0; gap: 0.75rem;">
                                <div class="col-md-6">
                                    <label style="font-size: 0.75rem; display: block; margin-bottom: 0.25rem;">Patent Details</label>
                                    <input type="text" class="form-control form-control-sm" id="patent_details" name="patent_details" value="{{ $application->patent_details ?? old('patent_details') }}">
                                </div>
                                <div class="col-md-6">
                                    <label style="font-size: 0.75rem; display: block; margin-bottom: 0.25rem;">Patent Type</label>
                                    <input type="text" class="form-control form-control-sm" id="patent_type" name="patent_type" value="{{ $application->patent_type ?? old('patent_type') }}">
                                </div>
                            </div>
                        </div>

                        <div style="padding: 0.75rem 1.25rem; border-top: 1px solid #e5e7eb; display: flex; gap: 0.5rem; justify-content: flex-end;">
                            <button type="button" onclick="toggleEditMode()" class="btn-sm-custom"><i class="fas fa-times"></i> Cancel</button>
                            <button type="submit" class="btn-sm-custom" style="background: #059669; color: white; border-color: #059669;"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </form>
                </div>

            </div>

        </div>

        <!-- HISTORY TAB -->
        <div id="history" class="tab-pane">
            <div class="section-sep" onclick="toggleSection(this)">
                <i class="fas fa-history"></i> Status History & Audit Trail
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

        <!-- DOCUMENTS TAB -->
        <div id="documents" class="tab-pane">
            <div class="section-sep" onclick="toggleSection(this)">
                <i class="fas fa-file-pdf"></i> Related Documents
                <i class="fas fa-chevron-down section-sep-arrow"></i>
            </div>
            <div class="section-content">
                <div style="text-align: center; padding: 1.5rem; color: #9ca3af;">
                    <i class="fas fa-file-circle-question" style="font-size: 1.5rem; display: block; margin-bottom: 0.5rem;"></i>
                    <div style="font-size: 0.85rem;">No documents attached</div>
                </div>
            </div>
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

    function toggleEditMode() {
        const viewMode = document.getElementById('viewMode');
        const editMode = document.getElementById('editMode');
        
        viewMode.style.display = viewMode.style.display === 'none' ? 'block' : 'none';
        editMode.style.display = editMode.style.display === 'none' ? 'block' : 'none';
    }

    // Lot type radios
    document.querySelectorAll('.lot-type-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            const subDiv = document.getElementById('subdivisionFields');
            if (this.value === 'subdivision') {
                subDiv.style.display = 'block';
                document.getElementById('new_lot_number').required = true;
                document.getElementById('subdivided_area').required = true;
            } else {
                subDiv.style.display = 'none';
                document.getElementById('new_lot_number').required = false;
                document.getElementById('subdivided_area').required = false;
            }
        });
    });

    function calculateRemainingArea() {
        const motherLot = {{ $application->landRecord->total_area }};
        const subdivided = parseFloat(document.getElementById('subdivided_area').value) || 0;
        document.getElementById('remainingAreaDisplay').textContent = (motherLot - subdivided).toFixed(2);
    }
</script>
@endsection
