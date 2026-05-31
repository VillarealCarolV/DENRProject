@extends('layouts.app')

@section('content')
<script>console.error('=== PROCESSING QUEUE PAGE LOADED ===')</script>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="background-color: #ffffff;">
            
            <!-- Top Utility Toolbar -->
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 16px; border-bottom: 1px solid #f0f0f0; flex-wrap: wrap;">
                
                <!-- Left Side: Primary & Secondary Actions -->
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    
                    <!-- Primary Action: New Application -->
                    <a href="{{ route('applications.masterCreate') }}" 
                       style="background: #3b82f6; color: white; border: none; padding: 6px 12px; font-size: 0.85rem; border-radius: 4px; cursor: pointer; text-decoration: none; display: flex; align-items: center; gap: 6px; font-weight: 500; transition: background-color 0.15s ease;">
                        <i class="fas fa-plus" style="font-size: 0.75rem;"></i> New Application
                    </a>

                    <!-- Secondary Actions -->
                    <button onclick="location.reload()" 
                            style="background: transparent; border: 1px solid #d1d5db; color: #6b7280; padding: 6px 12px; font-size: 0.85rem; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.15s ease;">
                        <i class="fas fa-sync-alt" style="font-size: 0.75rem;"></i> Refresh
                    </button>

                    <button id="toggleFiltersBtn" 
                            style="background: transparent; border: 1px solid #d1d5db; color: #6b7280; padding: 6px 12px; font-size: 0.85rem; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.15s ease;">
                        <i class="fas fa-sliders-h" style="font-size: 0.75rem;"></i> Filters
                    </button>

                    <button style="background: transparent; border: 1px solid #d1d5db; color: #6b7280; padding: 6px 12px; font-size: 0.85rem; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.15s ease;">
                        <i class="fas fa-columns" style="font-size: 0.75rem;"></i> Columns
                    </button>
                </div>

                <!-- Right Side: Search Bar -->
                <div style="display: flex; align-items: center; gap: 6px; position: relative;">
                    <i class="fas fa-search" style="color: #d1d5db; font-size: 0.875rem; position: absolute; left: 10px; pointer-events: none;"></i>
                    <input type="text" 
                           placeholder="Search tracking, applicant..."
                           style="width: 200px; border: 1px solid #e5e7eb; padding: 6px 10px 6px 30px; font-size: 0.85rem; background-color: #f9fafb; border-radius: 4px;">
                </div>
            </div>

            <!-- Collapsible Filters Section -->
            <div id="filtersSection" style="display: none; padding: 12px 16px; border-bottom: 1px solid #f0f0f0; background-color: #f9fafb;">
                <form method="GET" action="{{ route('processing-queue') }}" class="d-flex align-items-center gap-2" style="flex-wrap: wrap;">
                    
                    <!-- Status Filter -->
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <label style="font-size: 0.85rem; color: #6b7280; font-weight: 500; margin-bottom: 0;">Status:</label>
                        <select name="status" class="form-select form-select-sm" style="width: 150px; border: 1px solid #e5e7eb; padding: 4px 8px; font-size: 0.85rem; background-color: #ffffff;">
                            <option value="">All Statuses</option>
                            <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="In Process" {{ request('status') === 'In Process' ? 'selected' : '' }}>In Process</option>
                        </select>
                    </div>

                    <!-- Sort Filter -->
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <label style="font-size: 0.85rem; color: #6b7280; font-weight: 500; margin-bottom: 0;">Sort:</label>
                        <select name="sort" class="form-select form-select-sm" style="width: 150px; border: 1px solid #e5e7eb; padding: 4px 8px; font-size: 0.85rem; background-color: #ffffff;">
                            <option value="oldest" {{ request('sort') !== 'newest' ? 'selected' : '' }}>Oldest First</option>
                            <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest First</option>
                        </select>
                    </div>

                    <!-- Filter Button -->
                    <button type="submit" style="background: transparent; border: 1px solid #d1d5db; color: #6b7280; padding: 4px 10px; font-size: 0.85rem; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.15s ease;">
                        <i class="fas fa-arrow-right" style="font-size: 0.75rem;"></i> Apply
                    </button>

                    <!-- Reset Link -->
                    <a href="{{ route('processing-queue') }}" style="background: transparent; border: 1px solid #d1d5db; color: #6b7280; padding: 4px 10px; font-size: 0.85rem; border-radius: 4px; text-decoration: none; display: flex; align-items: center; gap: 6px; transition: all 0.15s ease;">
                        <i class="fas fa-redo" style="font-size: 0.75rem;"></i> Reset
                    </a>
                </form>
            </div>

            <!-- Table Container -->
            <div style="overflow-x: auto;">
                <table class="table table-sm table-hover" style="margin-bottom: 0; border-collapse: collapse;">
                    <thead style="background-color: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                        <tr>
                            <th style="padding: 10px 14px; font-size: 0.8rem; font-weight: 600; color: #6b7280; letter-spacing: 0.3px; text-transform: uppercase; border: none;">Tracking No.</th>
                            <th style="padding: 10px 14px; font-size: 0.8rem; font-weight: 600; color: #6b7280; letter-spacing: 0.3px; text-transform: uppercase; border: none;">Applicant / Record Name</th>
                            <th style="padding: 10px 14px; font-size: 0.8rem; font-weight: 600; color: #6b7280; letter-spacing: 0.3px; text-transform: uppercase; border: none;">Date Submitted</th>
                            <th style="padding: 10px 14px; font-size: 0.8rem; font-weight: 600; color: #6b7280; letter-spacing: 0.3px; text-transform: uppercase; border: none;">Status</th>
                            <th style="padding: 10px 14px; font-size: 0.8rem; font-weight: 600; color: #6b7280; letter-spacing: 0.3px; text-transform: uppercase; border: none; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $application)
                            @php
                                $latestStatus = $application->statusHistories()->latest()->first();
                                $statusText = $latestStatus ? $latestStatus->status : 'Pending';
                                
                                // Determine status color
                                $statusColor = '#d97706'; // Default orange (Pending)
                                if ($statusText === 'In Process') {
                                    $statusColor = '#0891b2'; // Cyan
                                } elseif ($statusText === 'Approved') {
                                    $statusColor = '#059669'; // Green
                                } elseif ($statusText === 'Rejected') {
                                    $statusColor = '#dc2626'; // Red
                                }
                            @endphp
                            <tr class="clickable-row" 
                                data-id="{{ $application->id }}"
                                data-application-id="{{ $application->id }}"
                                data-tracking-no="{{ $application->tracking_no }}"
                                data-applicant-name="{{ $application->applicant->full_name }}"
                                data-survey-no="{{ $application->landRecord->survey_no }}"
                                data-status="{{ $statusText }}"
                                style="border-bottom: 1px solid #f0f0f0; transition: background-color 0.15s ease; cursor: pointer;">
                                <td style="padding: 12px 14px; font-size: 0.9rem; font-weight: 600; color: #1f2937; border: none;">
                                    {{ $application->tracking_no }}
                                </td>
                                <td style="padding: 12px 14px; font-size: 0.9rem; color: #374151; border: none;">
                                    <div style="font-weight: 500;">{{ $application->applicant->full_name }}</div>
                                    <small style="color: #9ca3af;">{{ $application->landRecord->survey_no }}</small>
                                </td>
                                <td style="padding: 12px 14px; font-size: 0.9rem; color: #6b7280; border: none; white-space: nowrap;">
                                    {{ $application->date_received->format('M d, Y') }}
                                </td>
                                <td style="padding: 12px 14px; border: none;">
                                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 8px; background-color: {{ $statusColor }}14; color: {{ $statusColor }}; border-radius: 4px; font-size: 0.85rem; font-weight: 500;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background-color: {{ $statusColor }};"></span>
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td style="padding: 12px 14px; border: none; text-align: center;">
                                    <a href="{{ route('applications.edit', $application->id) }}" 
                                       class="btn-review"
                                       style="background: #3b82f6; color: white; padding: 4px 10px; font-size: 0.8rem; border-radius: 3px; text-decoration: none; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: background-color 0.15s ease;">
                                        <i class="fas fa-arrow-right" style="font-size: 0.7rem;"></i> Review
                                    </a>
                                    <button class="delete-btn-handler" 
                                            data-url="{{ route('applications.destroy', $application->id) }}"
                                            data-name="Application {{ $application->tracking_no }}"
                                            data-row-selector="tr.clickable-row[data-application-id='{{ $application->id }}']"
                                            style="background: #dc2626; color: white; padding: 4px 10px; font-size: 0.8rem; border-radius: 3px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: background-color 0.15s ease; margin-left: 4px;"
                                            title="Delete Application">
                                        <i class="fas fa-trash" style="font-size: 0.7rem;"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 text-light"></i>
                                    <p class="h6 mb-2">No pending records</p>
                                    <small>All records are reviewed and processed!</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination & Info Footer -->
            @if($applications->hasPages() || $applications->count() > 0)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-top: 1px solid #f0f0f0; background-color: #f9fafb; flex-wrap: wrap; gap: 10px;">
                    <div style="font-size: 0.85rem; color: #6b7280;">
                        Showing <strong>{{ $applications->count() }}</strong> of <strong>{{ $applications->total() }}</strong> records
                    </div>
                    
                    @if($applications->hasPages())
                        <nav style="display: flex; gap: 4px; align-items: center;">
                            {{ $applications->links('pagination::bootstrap-5') }}
                        </nav>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .table tbody tr:hover {
        background-color: #eff6ff !important;
    }

    .table-sm th, .table-sm td {
        vertical-align: middle;
    }

    /* Ghost button hover effect */
    button[style*="border: 1px solid #d1d5db"]:hover,
    a[style*="border: 1px solid #d1d5db"]:hover {
        border-color: #9ca3af !important;
        background-color: #f3f4f6 !important;
    }

    /* Primary button hover effect */
    a[style*="background: #3b82f6"]:hover {
        background-color: #2563eb !important;
    }

    /* Review button hover effect */
    a[style*="background: #3b82f6"][style*="display: inline-flex"]:hover {
        background-color: #2563eb !important;
    }

    /* Pagination styling */
    .pagination {
        margin-bottom: 0 !important;
        gap: 4px;
    }

    .pagination .page-link {
        border: 1px solid #d1d5db;
        color: #3b82f6;
        font-size: 0.85rem;
        padding: 4px 8px;
        border-radius: 3px;
    }

    .pagination .page-link:hover {
        background-color: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }

    .pagination .page-item.active .page-link {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }

    /* Clickable Row - Processing Queue */
    .clickable-row {
        cursor: pointer;
        transition: background-color 0.15s ease;
    }

    .clickable-row:hover {
        background-color: #eff6ff !important;
        box-shadow: inset 0 0 0 1px #bfdbfe;
    }

    /* Minimalist Modal Styles */
    .modal-header-minimalist {
        border: none;
        background-color: #ffffff;
        padding: 16px 20px;
    }

    .modal-body-minimalist {
        background-color: #ffffff;
        padding: 20px;
    }

    .form-group-minimalist {
        margin-bottom: 16px;
    }

    .label-minimalist {
        font-size: 0.85rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .input-minimalist {
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        padding: 8px 12px;
        font-size: 0.9rem;
    }

    .input-minimalist:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }
</style>

<!-- Minimalist Processing Modal -->
<div class="modal fade" id="processingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-radius: 6px;">
            
            <div class="modal-header-minimalist d-flex justify-content-between align-items-start" style="border-bottom: 1px solid #f0f0f0; padding: 10px 15px;">
                <div>
                    <h5 class="mb-0" style="font-size: 0.95rem; font-weight: 700; color: #1f2937;">
                        <i class="fas fa-tasks me-2" style="color: #3b82f6;"></i> Process Application
                    </h5>
                    <small id="modalTrackingInfo" style="color: #9ca3af; font-size: 0.75rem;">Tracking: <strong>CENRO-2026-006</strong></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body-minimalist" style="padding: 10px 15px; max-height: 70vh; overflow-y: auto;">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 10px; padding: 8px 12px; background-color: #f9fafb; border-radius: 4px;">
                    <div>
                        <div class="label-minimalist" style="font-size: 0.7rem; color: #6b7280; font-weight: 600; text-transform: uppercase;">Applicant</div>
                        <div id="modalApplicant" style="font-size: 0.85rem; color: #1f2937; font-weight: 600;">Erica Cruz</div>
                    </div>
                    <div>
                        <div class="label-minimalist" style="font-size: 0.7rem; color: #6b7280; font-weight: 600; text-transform: uppercase;">Survey No.</div>
                        <div id="modalSurveyNo" style="font-size: 0.85rem; color: #1f2937; font-weight: 600;">PSU-123-0034</div>
                    </div>
                </div>

                <form id="processForm">
                    @csrf
                    <input type="hidden" id="applicationId" name="application_id">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; border-top: 1px solid #e5e7eb; padding-top: 10px; margin-bottom: 8px;">
                        <div class="form-group-minimalist">
                            <label class="label-minimalist" style="font-size: 0.75rem;">Lot Classification <span style="color: #dc2626;">*</span></label>
                            <div style="display: flex; gap: 12px; margin-top: 2px;">
                                <label style="font-size: 0.8rem; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="radio" name="lot_classification" value="existing"> Existing
                                </label>
                                <label style="font-size: 0.8rem; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="radio" name="lot_classification" value="subdivision"> Subdiv
                                </label>
                            </div>
                        </div>
                        <div id="subdivisionLotSection" class="form-group-minimalist" style="display: none;">
                            <label class="label-minimalist" style="font-size: 0.75rem;">New Lot Number</label>
                            <input type="text" id="subdivisionLotNumber" name="subdivision_lot_number" class="form-control" style="font-size: 0.8rem; padding: 2px 8px; height: 30px;" placeholder="Lot 1A">
                        </div>
                    </div>

                    <div id="decisionRemarksSection" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 8px;">
                        <div class="form-group-minimalist">
                            <label class="label-minimalist" style="font-size: 0.75rem;">Application Status <span style="color: #dc2626;">*</span></label>
                            <select id="processStatus" name="status" class="form-control" style="font-size: 0.8rem; padding: 2px 8px; height: 30px;" required>
                                <option value="">-- Select Status --</option>
                                <option value="In Process">In Process</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="form-group-minimalist">
                            <label class="label-minimalist" style="font-size: 0.75rem;">Patent Type</label>
                            <select id="patentType" name="patent_type" class="form-control" style="font-size: 0.8rem; padding: 2px 8px; height: 30px;">
                                <option value="">-- Select Type --</option>
                                <option value="Residential">Residential</option>
                                <option value="Agricultural">Agricultural</option>
                                <option value="Commercial">Commercial</option>
                                <option value="Industrial">Industrial</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 5px;">
                        <div class="form-group-minimalist">
                            <label class="label-minimalist" style="font-size: 0.75rem;">Patent Details</label>
                            <input type="text" id="patentDetails" name="patent_details" class="form-control" style="font-size: 0.8rem; padding: 2px 8px; height: 30px;" placeholder="Grant Details">
                        </div>
                        <div class="form-group-minimalist">
                            <label class="label-minimalist" style="font-size: 0.75rem;">Official Remarks <span style="color: #dc2626;">*</span></label>
                            <textarea id="processRemarks" name="remarks" class="form-control" rows="1" style="font-size: 0.8rem; padding: 4px 8px; resize: none;" placeholder="Remarks..."></textarea>
                        </div>
                    </div>

                    <div id="processErrorMsg" class="alert alert-danger d-none" style="font-size: 0.7rem; padding: 4px 10px; margin-top: 5px; margin-bottom: 0;"></div>
                    <div id="processSuccessMsg" class="alert alert-success d-none" style="font-size: 0.7rem; padding: 4px 10px; margin-top: 5px; margin-bottom: 0;"></div>
                </form>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #f0f0f0; background-color: #f9fafb; padding: 8px 15px;">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal" style="font-size: 0.8rem; padding: 4px 12px;">Cancel</button>
                <button type="button" id="saveProcessBtn" class="btn btn-sm btn-primary" style="font-size: 0.8rem; padding: 4px 12px; background-color: #3b82f6; border: none;">
                    <i class="fas fa-save me-1"></i> Save Assessment
                </button>
            </div>
        </div>
    </div>
</div>


<script>
    // Wait for jQuery and Bootstrap to be fully loaded before initializing
    function waitForDependencies(callback, attempts = 0) {
        if (typeof jQuery !== 'undefined' && typeof bootstrap !== 'undefined') {
            callback();
        } else if (attempts < 50) {  // Wait up to 5 seconds
            setTimeout(() => waitForDependencies(callback, attempts + 1), 100);
        } else {
            console.error('jQuery or Bootstrap failed to load. Using fallback initialization.');
            // Fallback: at least try to initialize
            callback();
        }
    }

    waitForDependencies(function() {
        console.log('jQuery and Bootstrap loaded. Initializing processing queue...');
        setupRowClickHandlers();
    });

    // Row click handler - Process Application modal
    function setupRowClickHandlers() {
        if (typeof jQuery === 'undefined' || typeof bootstrap === 'undefined') {
            console.error('jQuery or Bootstrap not available. Retrying...');
            setTimeout(setupRowClickHandlers, 100);
            return;
        }
        
        $(document).on('click', '.clickable-row', function(e) {
            // Don't trigger if they clicked the 'Review' button specifically
            if ($(e.target).closest('.btn-review').length) {
                return;
            }

            try {
                // Get the ID and all data
                let appId = $(this).data('id');
                let tracking = $(this).data('tracking-no');
                let applicant = $(this).data('applicant-name');
                let survey = $(this).data('survey-no');
                let status = $(this).data('status');
                
                // Populate modal fields
                $('#applicationId').val(appId);
                $('#modalTrackingInfo strong').text(tracking);
                $('#modalApplicant').text(applicant);
                $('#modalSurveyNo').text(survey);
                $('#processStatus').val(status || '');
                $('#processRemarks').val('');
                $('#processErrorMsg').addClass('d-none');
                $('#processSuccessMsg').addClass('d-none');
                
                // Reset lot classification and patent fields
                $('input[name="lot_classification"]').prop('checked', false);
                $('#subdivisionLotSection').hide();
                $('#decisionRemarksSection').hide();
                $('#subdivisionLotNumber').val('');
                $('#patentType').val('');
                $('#patentDetails').val('');
                
                // Show modal using Bootstrap 5 API
                let modalEl = document.getElementById('processingModal');
                if (modalEl) {
                    let modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            } catch (err) {
                console.error('Error opening processing modal:', err.message);
            }
        });
        
        // Handle lot classification radio buttons
        $(document).on('change', 'input[name="lot_classification"]', function() {
            // Show decision section when any lot classification is selected
            $('#decisionRemarksSection').show();
            
            if ($(this).val() === 'subdivision') {
                $('#subdivisionLotSection').show();
            } else {
                $('#subdivisionLotSection').hide();
                $('#subdivisionLotNumber').val('');
            }
        });
        
        // Auto-fill remarks when status is changed to "Approved" (only if empty)
        $(document).on('change', '#processStatus', function() {
            let selectedStatus = $(this).val();
            let remarksField = $('#processRemarks');
            
            if (selectedStatus === 'Approved') {
                // Only fill if the field is currently empty
                if (!remarksField.val().trim()) {
                    remarksField.val(' Patent ');
                }
            }
        });
        
        // Handle Save Assessment button click
        $(document).on('click', '#saveProcessBtn', function() {
            let appId = $('#applicationId').val();
            
            // Validate required fields
            if (!appId) {
                showProcessError('Application ID is missing');
                return;
            }
            
            if (!$('#processStatus').val()) {
                showProcessError('Please select an Application Status');
                return;
            }
            
            if (!$('#processRemarks').val()) {
                showProcessError('Please enter Official Remarks');
                return;
            }
            
            // Prepare JSON data
            let submitData = {
                application_id: $('#applicationId').val(),
                lot_classification: $('input[name="lot_classification"]:checked').val() || '',
                subdivision_lot_number: $('#subdivisionLotNumber').val() || '',
                status: $('#processStatus').val(),
                patent_type: $('#patentType').val() || '',
                patent_details: $('#patentDetails').val() || '',
                remarks: $('#processRemarks').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            };
            
            console.log('Submitting data:', submitData);
            
            // Show loading state
            $('#saveProcessBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
            $('#processErrorMsg').addClass('d-none');
            $('#processSuccessMsg').addClass('d-none');
            
            // Send AJAX request
            $.ajax({
                url: `/applications/${appId}/process`,
                type: 'POST',
                data: JSON.stringify(submitData),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    console.log('Success response:', response);
                    showProcessSuccess(response.message || 'Assessment saved successfully!');
                    
                    // Reload applications list after 1.5 seconds
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                },
                error: function(xhr, status, error) {
                    console.error('Ajax error:', status, error);
                    console.error('Response:', xhr.responseText);
                    
                    let errorMsg = 'An error occurred while saving the assessment';
                    
                    try {
                        let response = xhr.responseJSON || JSON.parse(xhr.responseText);
                        
                        if (response.message) {
                            errorMsg = response.message;
                        } else if (response.errors) {
                            // Handle validation errors
                            let errorList = [];
                            for (let field in response.errors) {
                                errorList.push(response.errors[field].join(', '));
                            }
                            errorMsg = errorList.join(' | ');
                        }
                    } catch (e) {
                        errorMsg = 'Server error: ' + (xhr.status || 'Unknown') + ' - ' + error;
                    }
                    
                    showProcessError(errorMsg);
                },
                complete: function() {
                    $('#saveProcessBtn').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Assessment');
                }
            });
        });
    }
    
    // Helper functions for showing messages
    function showProcessError(message) {
        $('#processErrorMsg').removeClass('d-none').text(message);
        $('#processSuccessMsg').addClass('d-none');
        console.error('Process Error:', message);
    }
    
    function showProcessSuccess(message) {
        $('#processSuccessMsg').removeClass('d-none').text(message);
        $('#processErrorMsg').addClass('d-none');
        console.log('Process Success:', message);
    }
    
    // Toggle filters section
    $(document).on('click', '#toggleFiltersBtn', function() {
        $('#filtersSection').slideToggle(300);
    });
</script>
@endsection
