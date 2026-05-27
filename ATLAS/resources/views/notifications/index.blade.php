@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <!-- Card Header -->
            <div class="card-header bg-white border-bottom py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">All Notifications</h5>
                        <small class="text-muted">{{ $notifications->total() }} total notifications</small>
                    </div>
                    <div class="col-auto">
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" style="background: transparent; border: 1px solid #d1d5db; color: #6b7280; padding: 6px 12px; font-size: 0.85rem; border-radius: 4px; cursor: pointer; transition: all 0.15s ease; font-weight: 500;">
                                    <i class="fas fa-check-double me-1"></i> Mark All as Read
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Minimalist Toolbar -->
            <div style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-bottom: 1px solid #f0f0f0; background-color: #ffffff; flex-wrap: wrap;">
                <form method="GET" action="{{ route('notifications.index') }}" class="d-flex align-items-center gap-2" style="flex: 1; min-width: 300px; flex-wrap: wrap;">
                    
                    <!-- Search Icon + Tracking Number Input -->
                    <div style="display: flex; align-items: center; gap: 6px; position: relative;">
                        <i class="fas fa-search" style="color: #d1d5db; font-size: 0.875rem;"></i>
                        <input type="text" 
                               name="filter_tracking" 
                               class="form-control form-control-sm" 
                               placeholder="Tracking No."
                               value="{{ request('filter_tracking') }}"
                               style="width: 130px; border: 1px solid #e5e7eb; padding: 4px 8px; font-size: 0.85rem; background-color: #f9fafb;">
                    </div>

                    <!-- Filter Icon + Status Dropdown -->
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-filter" style="color: #d1d5db; font-size: 0.875rem;"></i>
                        <select name="filter_status" class="form-select form-select-sm" style="width: 120px; border: 1px solid #e5e7eb; padding: 4px 8px; font-size: 0.85rem; background-color: #f9fafb;">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('filter_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in-process" {{ request('filter_status') === 'in-process' ? 'selected' : '' }}>In Process</option>
                            <option value="approved" {{ request('filter_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('filter_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <!-- Calendar Icon + Date Input -->
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-calendar-alt" style="color: #d1d5db; font-size: 0.875rem;"></i>
                        <input type="date" 
                               name="filter_date" 
                               class="form-control form-control-sm" 
                               value="{{ request('filter_date') }}"
                               style="width: 130px; border: 1px solid #e5e7eb; padding: 4px 8px; font-size: 0.85rem; background-color: #f9fafb;">
                    </div>

                    <!-- Filter Button (Ghost Button) -->
                    <button type="submit" style="background: transparent; border: 1px solid #d1d5db; color: #6b7280; padding: 4px 10px; font-size: 0.85rem; border-radius: 4px; cursor: pointer; transition: all 0.15s ease; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-arrow-right" style="font-size: 0.75rem;"></i> Filter
                    </button>

                    <!-- Reset Link (Ghost Button) -->
                    <a href="{{ route('notifications.index') }}" style="background: transparent; border: 1px solid #d1d5db; color: #6b7280; padding: 4px 10px; font-size: 0.85rem; border-radius: 4px; text-decoration: none; cursor: pointer; transition: all 0.15s ease; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-redo" style="font-size: 0.75rem;"></i> Reset
                    </a>
                </form>
            </div>

            <!-- Notifications List -->
            <div class="card-body p-0">
                @forelse($notifications as $notification)
                    @php
                        $status = $notification->data['status'] ?? 'pending';
                        $statusDotColor = '#d97706'; // Default orange
                        $statusLabel = 'Pending';
                        
                        if ($status === 'approved') {
                            $statusDotColor = '#059669';
                            $statusLabel = 'Approved';
                        } elseif ($status === 'rejected') {
                            $statusDotColor = '#dc2626';
                            $statusLabel = 'Rejected';
                        } elseif ($status === 'in-process' || $status === 'in_process') {
                            $statusDotColor = '#0891b2';
                            $statusLabel = 'In Process';
                        }
                        
                        $isUnread = $notification->read_at === null;
                        $applicantName = $notification->data['applicant_name'] ?? 'N/A';
                        $location = $notification->data['location'] ?? 'N/A';
                    @endphp
                    <div class="notification-activity" 
                         style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 14px 16px; background-color: #ffffff; border-bottom: 1px solid #f0f0f0; cursor: pointer; transition: background-color 0.15s ease;"
                         data-notification='{{ json_encode($notification->data) }}'
                         onclick="if (typeof showNotificationModal === 'function') showNotificationModal(event, this)">
                        
                        <!-- Left Content -->
                        <div style="flex: 1; min-width: 0;">
                            <!-- Top Line: Tracking + Status Dot -->
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                <span style="width: 8px; height: 8px; border-radius: 50%; background-color: {{ $statusDotColor }}; flex-shrink: 0;"></span>
                                <strong style="color: #1f2937; font-size: 0.95rem; word-break: break-word;">{{ $notification->data['tracking_no'] ?? 'N/A' }}: {{ $statusLabel }}</strong>
                                @if($isUnread)
                                    <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background-color: #3b82f6; margin-left: 4px; flex-shrink: 0;"></span>
                                @endif
                            </div>
                            
                            <!-- Bottom Line: Applicant & Location -->
                            <small style="color: #9ca3af; font-size: 0.8rem; display: block; word-break: break-word;">
                                {{ $applicantName }} • {{ $location }}
                            </small>
                        </div>
                        
                        <!-- Right: Timestamp & Actions -->
                        <div style="flex-shrink: 0; text-align: right; display: flex; flex-direction: column; gap: 8px; align-items: flex-end;">
                            <small style="color: #9ca3af; font-size: 0.75rem; white-space: nowrap;">{{ $notification->created_at->diffForHumans() }}</small>
                            
                            @if($isUnread)
                                <a href="{{ route('notifications.markAsRead', $notification->id) }}" 
                                   class="btn btn-link p-0" 
                                   style="color: #3b82f6; font-size: 0.75rem; font-weight: 500; text-decoration: none; padding: 0 !important;"
                                   onclick="event.stopPropagation();"
                                   title="Mark as read">Mark Read</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-bell-slash fa-3x mb-3 text-light"></i>
                        <p class="h6">No notifications found</p>
                        <small>Adjust your filters or check back later for new notifications.</small>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($notifications->hasPages())
                <div class="card-footer bg-white border-top pt-3">
                    <nav aria-label="Page navigation">
                        {{ $notifications->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .notification-activity {
        transition: background-color 0.15s ease !important;
    }

    .notification-activity:hover {
        background-color: #f9fafb !important;
    }

    .notification-activity:last-child {
        border-bottom: none !important;
    }

    .notification-item:hover {
        background-color: rgba(13, 110, 253, 0.08) !important;
    }

    .notification-item strong {
        font-weight: 600;
        color: #212529;
    }

    .notification-item .badge {
        font-weight: 600;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }
</style>

<!-- Notification Modal -->
@include('components.notification-modal')

<!-- JavaScript Handler -->
<script>
    // Show notification details in modal
    function showNotificationModal(event, element) {
        // Prevent default browser behavior and stop event propagation
        event.preventDefault();
        event.stopPropagation();
        
        // Get notification data from data attribute
        const notificationData = JSON.parse(element.getAttribute('data-notification'));
        
        // Format timestamp
        const createdAt = new Date(notificationData.created_at || Date.now());
        const formattedTime = createdAt.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });

        // Determine type for styling
        let type = 'info';
        if (notificationData.message && notificationData.message.includes('Approved')) {
            type = 'approval';
        } else if (notificationData.message && notificationData.message.includes('Rejected')) {
            type = 'rejection';
        } else if (notificationData.message && notificationData.message.includes('Subdivision')) {
            type = 'subdivision';
        }

        // Prepare data
        const modalData = {
            message: notificationData.message || 'New Notification',
            type: type,
            tracking_no: notificationData.tracking_no || 'N/A',
            applicant_name: notificationData.applicant_name || 'N/A',
            survey_no: notificationData.survey_no || 'N/A',
            status: notificationData.status || 'Pending',
            lot_type: notificationData.lot_type || null,
            remarks: notificationData.remarks || null,
            timestamp: formattedTime,
            url: notificationData.url || null
        };

        // Display modal
        displayNotificationModal(modalData);
    }

    // Display the modal with notification details
    function displayNotificationModal(data) {
        try {
            // Get modal element
            const modalElement = document.getElementById('notificationModal');
            if (!modalElement) {
                console.error('Modal element not found');
                return;
            }

            // Get modal instance or create new one
            let modal = bootstrap.Modal.getInstance(modalElement);
            if (!modal) {
                modal = new bootstrap.Modal(modalElement);
            }

            // Determine icon based on type
            let iconClass = 'fa-file-alt';
            let iconBgClass = 'info';
            let badgeColor = 'bg-info';

            if (data.type === 'approval') {
                iconClass = 'fa-check-circle';
                iconBgClass = 'success';
                badgeColor = 'bg-success';
            } else if (data.type === 'rejection') {
                iconClass = 'fa-times-circle';
                iconBgClass = 'danger';
                badgeColor = 'bg-danger';
            } else if (data.type === 'subdivision') {
                iconClass = 'fa-map';
                iconBgClass = 'info';
                badgeColor = 'bg-info';
            }

            // Build modal content
            let content = `
                <div class="text-center mb-4">
                    <div class="notification-modal-icon ${iconBgClass}">
                        <i class="fas ${iconClass}"></i>
                    </div>
                    <h4 class="mb-1">${htmlEscape(data.message)}</h4>
                    <small class="text-muted">${htmlEscape(data.timestamp)}</small>
                </div>

                <div class="notification-details">
                    <div class="notification-detail-item">
                        <div class="notification-detail-label"><strong>Tracking No:</strong></div>
                        <div class="notification-detail-value">${htmlEscape(data.tracking_no)}</div>
                    </div>

                    <div class="notification-detail-item">
                        <div class="notification-detail-label"><strong>Applicant:</strong></div>
                        <div class="notification-detail-value">${htmlEscape(data.applicant_name)}</div>
                    </div>

                    <div class="notification-detail-item">
                        <div class="notification-detail-label"><strong>Survey No:</strong></div>
                        <div class="notification-detail-value">${htmlEscape(data.survey_no)}</div>
                    </div>

                    <div class="notification-detail-item">
                        <div class="notification-detail-label"><strong>Status:</strong></div>
                        <div class="notification-detail-value">
                            <span class="notification-badge ${badgeColor}">${htmlEscape(data.status)}</span>
                        </div>
                    </div>

                    ${data.lot_type ? `
                    <div class="notification-detail-item">
                        <div class="notification-detail-label"><strong>Lot Type:</strong></div>
                        <div class="notification-detail-value">
                            ${data.lot_type === 'subdivision' ? 'Subdivision' : 'Existing Lot'}
                        </div>
                    </div>
                    ` : ''}

                    ${data.remarks ? `
                    <div class="notification-detail-item">
                        <div class="notification-detail-label"><strong>Remarks:</strong></div>
                        <div class="notification-detail-value">
                            <p class="text-muted mb-0" style="white-space: pre-wrap;">${htmlEscape(data.remarks)}</p>
                        </div>
                    </div>
                    ` : ''}

                    <hr class="my-3">

                    <div class="notification-detail-item">
                        <div class="notification-detail-label"><strong>Received:</strong></div>
                        <div class="notification-detail-value text-muted small">${htmlEscape(data.timestamp)}</div>
                    </div>
                </div>
            `;

            // Set modal content
            const contentDiv = document.getElementById('notificationContent');
            if (contentDiv) {
                contentDiv.innerHTML = content;
            }

            // Set view button
            const viewBtn = document.getElementById('notificationActionBtn');
            if (viewBtn) {
                if (data.url) {
                    viewBtn.href = data.url;
                    viewBtn.style.display = 'block';
                } else {
                    viewBtn.style.display = 'none';
                }
            }

            // Show the modal
            modal.show();
        } catch (error) {
            console.error('Error displaying notification modal:', error);
            alert('Unable to display notification details');
        }
    }

    // Helper function to escape HTML
    function htmlEscape(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>

@endsection
