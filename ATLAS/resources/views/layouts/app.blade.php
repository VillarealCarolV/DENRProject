<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ATLAS') }}</title>

    <!-- Bootstrap CSS (Local) -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- SweetAlert2 CSS (Local) -->
    <link href="{{ asset('css/sweetalert2.min.css') }}" rel="stylesheet">
  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Custom Sidebar Styles -->
    <style>
        :root {
            --sidebar-width: 100px;
            --sidebar-collapsed-width: 0px;
        }

        body {
            margin-left: 0;
            padding-left: 0;
        }

        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            overflow-x: hidden;
            background-color: #1a1a1a;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
            transition: width 0.3s ease, transform 0.3s ease, left 0.3s ease;
            z-index: 1050;
            display: flex;
            flex-direction: column;
        }

        .sidebar.collapsed {
            width: 0;
            transform: translateX(-100%);
            left: -100px;
            overflow: hidden;
        }

        .d-flex.flex-column.flex-grow-1 {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
        }

        body.sidebar-collapsed .d-flex.flex-column.flex-grow-1 {
            margin-left: 0;
        }

        .sidebar-link {
            color: #ffffff !important;
            padding: 15px 0 !important;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 4px;
            border: none;
            transition: all 0.3s ease;
            font-weight: 500;
            min-height: auto;
            position: relative;
            width: 100%;
        }

        .sidebar-link i {
            font-size: 1.5rem;
            color: #ffffff;
            display: block;
        }

        .sidebar-link .nav-text {
            color: #ffffff;
            font-size: 0.75rem;
            word-wrap: break-word;
            max-width: 90px;
            display: block;
            line-height: 1.1;
        }

        .sidebar-link:hover {
            background-color: rgba(13, 110, 253, 0.15);
            color: #ffffff !important;
        }

        .sidebar-link.active {
            background-color: #0d6efd;
            color: #ffffff !important;
            width: 100%;
            margin-left: 0;
            padding-left: 0;
            padding-right: 0;
        }

        .sidebar-link.active i,
        .sidebar-link:hover i {
            color: #ffffff;
        }

        .sidebar-header {
            padding: 15px !important;
            text-align: center;
        }

        .sidebar-footer {
            padding: 10px !important;
            margin-top: auto;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar-footer .btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            padding: 12px 8px !important;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 6px;
            background-color: rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .sidebar-footer .btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .sidebar-footer .btn i {
            font-size: 1.2rem;
        }

        .sidebar-footer .btn .nav-text {
            font-size: 0.65rem;
        }

        /* Mobile responsive - sidebar hidden by default on small screens */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                left: -100px;
            }

            body.sidebar-active .sidebar {
                transform: translateX(0);
                left: 0;
            }

            .d-flex.flex-column.flex-grow-1 {
                margin-left: 0;
            }

            body.sidebar-active .d-flex.flex-column.flex-grow-1 {
                margin-left: 100px;
            }
        }

       

        .notification-item {
            transition: background-color 0.2s ease, border-color 0.2s ease;
            border-bottom: 1px solid #e9ecef;
        }

        .notification-item:hover {
            background-color: rgba(13, 110, 253, 0.05);
            border-bottom-color: rgba(13, 110, 253, 0.2) !important;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item strong {
            color: #212529;
            font-weight: 600;
        }

        .notification-item .badge {
            font-weight: 600;
            letter-spacing: 0.3px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .notification-item small.text-muted {
            color: #6c757d;
        }

        .notification-item small.text-secondary {
            color: #6c757d;
            font-size: 0.75rem !important;
        }

        /* Scrollbar styling for notification list */
        .card-body::-webkit-scrollbar {
            width: 6px;
        }

        .card-body::-webkit-scrollbar-track {
            background: #f1f3f5;
            border-radius: 3px;
        }

        .card-body::-webkit-scrollbar-thumb {
            background: #adb5bd;
            border-radius: 3px;
        }

        .card-body::-webkit-scrollbar-thumb:hover {
            background: #868e96;
        }

        /* Navbar z-index to ensure dropdowns appear above sidebar */
        .navbar {
            position: relative;
            z-index: 1040;
            overflow: visible !important;
        }

        .navbar .dropdown-menu {
            z-index: 1060;
            position: absolute;
        }

        /* Fix dropdown visibility in cards */
        .card {
            overflow: visible !important;
        }

        .card-body {
            overflow: visible !important;
        }

        .table-responsive {
            overflow: visible !important;
        }

        .container-fluid {
            overflow: visible !important;
        }

        main {
            overflow: visible !important;
        }

        /* Ensure dropdown menus are always visible - HIGHEST z-index */
        .dropdown-menu {
            position: absolute !important;
            z-index: 9999 !important;
            display: none;
        }

        .dropdown-menu.show {
            display: block !important;
            z-index: 9999 !important;
        }

        /* Bootstrap dropdown toggle fix */
        [data-bs-toggle="dropdown"] {
            position: relative;
        }

        /* Ensure dropdown container doesn't clip content */
        .dropdown {
            overflow: visible !important;
            position: relative;
        }

        /* ============================================= */
        /* MINIMALIST DESIGN: Clean & Simple Styling    */
        /* ============================================= */

        /* TABLE STYLING: Thin borders, no dark headers */
        .table {
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        .table thead {
            background-color: transparent !important;
            border-bottom: 2px solid #d1d5db;
        }

        .table thead th {
            background-color: transparent !important;
            color: #374151 !important;
            font-weight: 600;
            border: none !important;
            padding: 0.875rem 0.75rem !important;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .table tbody tr {
            border-bottom: 1px solid #e5e7eb !important;
            border-top: none !important;
            transition: background-color 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: #f9fafb !important;
        }

        .table tbody td {
            border: none !important;
            padding: 1rem 0.75rem !important;
            vertical-align: middle;
            color: #374151;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: transparent !important;
        }

        /* GHOST BUTTONS: Outline only, no fill */
        .btn-ghost {
            background-color: transparent;
            border: 1px solid #d1d5db;
            color: #374151;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-ghost:hover {
            background-color: #f3f4f6;
            border-color: #9ca3af;
            color: #111827;
        }

        .btn-ghost:active {
            background-color: #e5e7eb;
        }

        /* STATUS BADGES: Colored text with dot indicator */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0 !important;
            background-color: transparent !important;
            border: none !important;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-badge::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }

        .status-badge.status-pending {
            color: #d97706;
        }

        .status-badge.status-pending::before {
            background-color: #d97706;
        }

        .status-badge.status-in-process {
            color: #0891b2;
        }

        .status-badge.status-in-process::before {
            background-color: #0891b2;
        }

        .status-badge.status-approved {
            color: #059669;
        }

        .status-badge.status-approved::before {
            background-color: #059669;
        }

        .status-badge.status-rejected {
            color: #dc2626;
        }

        .status-badge.status-rejected::before {
            background-color: #dc2626;
        }

        /* ACTION LINKS: Simple text with subtle hover */
        .action-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            margin: -0.25rem -0.5rem;
        }

        .action-link:hover {
            color: #1d4ed8;
            background-color: #eff6ff;
        }

        /* CARD STYLING: Remove heavy shadows */
        .card {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08) !important;
            border: 1px solid #e5e7eb !important;
        }

        .card-header {
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb !important;
        }

        /* FORM STYLING: Cleaner inputs */
        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            box-shadow: none;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* PRIMARY ACTION BUTTONS */
        .btn-primary {
            background-color: #3b82f6;
            border-color: #3b82f6;
            font-weight: 500;
        }

        .btn-primary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }

        /* REMOVE SHADOWS */
        .shadow-sm {
            box-shadow: none !important;
        }

        .shadow {
            box-shadow: none !important;
        }

        /* ALERTS: Cleaner styling */
        .alert {
            border: 1px solid #e5e7eb;
            box-shadow: none;
        }

        .alert-success {
            background-color: #f0fdf4;
            border-color: #86efac;
            color: #166534;
        }

        .alert-danger {
            background-color: #fef2f2;
            border-color: #fca5a5;
            color: #991b1b;
        }

        .alert-warning {
            background-color: #fffbeb;
            border-color: #fde047;
            color: #92400e;
        }

        .alert-info {
            background-color: #f0f9ff;
            border-color: #bae6fd;
            color: #0c4a6e;
        }

        /* READ-ONLY INPUT FIELDS */
        input[readonly] {
            background-color: #f3f4f6 !important;
            cursor: not-allowed;
            color: #6b7280;
        }

        input[readonly]:focus {
            border-color: #d1d5db !important;
            box-shadow: none !important;
        }

        /* Notification Badge Animation */
        @keyframes notificationPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .notification-badge-update {
            animation: notificationPulse 0.4s ease-in-out;
        }

        /* MINIMALIST NOTIFICATION STYLE */
        .notification-item {
            border-bottom: 1px solid #f0f0f0 !important;
            padding: 12px 16px !important;
            background-color: #ffffff !important;
            transition: background-color 0.15s ease !important;
        }

        .notification-item:hover {
            background-color: #f9fafb !important;
        }

        .notification-item:last-child {
            border-bottom: none !important;
        }
    </style>

    <script>
        /**
         * Refresh notification count (AJAX)
         * Call this after any form submission or status change
         */
        /**
         * Refresh pending tasks count for Processing Queue
         */
        function refreshPendingCount() {
            fetch('{{ route("applications.pendingCount") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                const pendingBadge = document.getElementById('pendingBadge');
                if (!pendingBadge) return;
                
                // Update badge text
                pendingBadge.textContent = data.count;
                
                // Show/hide badge based on count
                if (data.count > 0) {
                    pendingBadge.style.display = 'block';
                } else {
                    pendingBadge.style.display = 'none';
                }
                
                console.log('✓ Pending count refreshed:', data.count);
            })
            .catch(error => console.error('Error refreshing pending count:', error));
        }

        function refreshNotificationBell() {
            fetch('{{ route("notifications.unreadCount") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                const bellButton = document.getElementById('notificationBell');
                if (!bellButton) return;
                
                // Remove existing badge
                const existingBadge = bellButton.querySelector('.badge');
                if (existingBadge) {
                    existingBadge.remove();
                }
                
                // Add animation
                bellButton.classList.add('notification-badge-update');
                setTimeout(() => bellButton.classList.remove('notification-badge-update'), 400);
                
                // Add new badge if count > 0
                if (data.count > 0) {
                    const badge = document.createElement('span');
                    badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                    badge.style.fontSize = '0.65rem';
                    badge.textContent = data.count;
                    bellButton.appendChild(badge);
                }
                
                // Update dropdown menu
                updateNotificationDropdown(data.notifications);
                
                console.log('✓ Notification bell refreshed. Unread count:', data.count);
            })
            .catch(error => console.error('Error refreshing notifications:', error));
        }
        
        /**
         * Update the notification dropdown menu with latest unread notifications
         * Minimalist List Design
         */
        function updateNotificationDropdown(notifications) {
            const notificationMenu = document.getElementById('notificationMenu');
            if (!notificationMenu) return;
            
            // Remove existing notification items (keep header)
            const items = notificationMenu.querySelectorAll('.notification-item');
            items.forEach(item => item.remove());
            
            // Remove empty state
            const emptyState = notificationMenu.querySelector('span.text-muted');
            if (emptyState) emptyState.remove();
            
            if (notifications.length === 0) {
                const emptyLi = document.createElement('li');
                emptyLi.innerHTML = '<span class="dropdown-item text-muted text-center py-3">No new notifications</span>';
                notificationMenu.appendChild(emptyLi);
                return;
            }
            
            // Add notification items with minimalist list design
            notifications.forEach(notif => {
                const li = document.createElement('li');
                const a = document.createElement('a');
                a.className = 'dropdown-item notification-item';
                a.href = '#';
                a.style.cursor = 'pointer';
                a.style.padding = '12px 16px';
                a.style.borderBottom = '1px solid #f0f0f0';
                a.style.backgroundColor = '#ffffff';
                a.style.transition = 'background-color 0.15s ease';
                
                // Store notification data in data attribute
                a.dataset.notificationData = JSON.stringify(notif);
                
                // Determine status color and label
                let statusDotColor = '#d97706'; // Default orange for pending
                let statusLabel = 'Pending';
                
                if (notif.status === 'approved') {
                    statusDotColor = '#059669';
                    statusLabel = 'Approved';
                } else if (notif.status === 'rejected') {
                    statusDotColor = '#dc2626';
                    statusLabel = 'Rejected';
                } else if (notif.status === 'in-process' || notif.status === 'in_process') {
                    statusDotColor = '#0891b2';
                    statusLabel = 'In Process';
                }
                
                a.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                        <!-- Left Content -->
                        <div style="flex: 1; min-width: 0;">
                            <!-- Top Line: Tracking + Status -->
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                <span style="width: 8px; height: 8px; border-radius: 50%; background-color: ${statusDotColor}; flex-shrink: 0;"></span>
                                <strong style="color: #1f2937; font-size: 0.9rem; word-break: break-word;">${notif.tracking_no}: ${statusLabel}</strong>
                            </div>
                            
                            <!-- Bottom Line: Applicant & Location (muted) -->
                            <small style="color: #9ca3af; font-size: 0.8rem; display: block; word-break: break-word;">
                                ${notif.applicant_name || 'N/A'} • ${notif.location || 'N/A'}
                            </small>
                        </div>
                        
                        <!-- Right: Timestamp & View Link -->
                        <div style="flex-shrink: 0; text-align: right; display: flex; flex-direction: column; gap: 4px; align-items: flex-end;">
                            <small style="color: #9ca3af; font-size: 0.75rem; white-space: nowrap;">${notif.created_at}</small>
                            <a href="#" style="color: #3b82f6; text-decoration: none; font-size: 0.75rem; font-weight: 500; padding: 2px 0; cursor: pointer;">View</a>
                        </div>
                    </div>
                `;
                
                // Add hover effect
                a.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f9fafb';
                });
                a.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '#ffffff';
                });
                
                // Add click handler
                a.addEventListener('click', function(e) {
                    e.preventDefault();
                    const data = JSON.parse(this.dataset.notificationData);
                    if (typeof showNotificationModal === 'function') {
                        showNotificationModal(e, data);
                    }
                });
                
                li.appendChild(a);
                notificationMenu.appendChild(li);
            });
        }
        
        // Event listener for notification links (both static and dynamic)
        document.addEventListener('click', function(e) {
            if (e.target.closest('.notification-link')) {
                e.preventDefault();
                const link = e.target.closest('.notification-link');
                const data = JSON.parse(link.dataset.notification);
                if (typeof showNotificationModal === 'function') {
                    showNotificationModal(e, data);
                }
            }
        });
        
        // Auto-refresh pending count and notifications every 30 seconds
        // Only Land Officers see the pending count badge
        @if(auth()->user() && auth()->user()->role === 'land_officer')
            refreshPendingCount(); // Initial call on page load
            setInterval(refreshPendingCount, 30000);
        @endif
        setInterval(refreshNotificationBell, 30000);
    </script>
</head>
<body class="d-flex">
    <!-- Sidebar -->
    <x-sidebar :active="request()->route()->getName()" />

    <!-- Main Content Area -->
    <div class="d-flex flex-column flex-grow-1" style="min-height: 100vh; overflow: visible;">
        <!-- Top Navbar -->
        <x-navbar :user="auth()->user()" />

        <!-- Page Content -->
        <main class="flex-grow-1 bg-light">
            <!-- Breadcrumbs -->
            <x-breadcrumbs :breadcrumbs="$breadcrumbs ?? []" />

            <!-- Sub Navigation (if needed) -->
            @if(isset($tabs))
                <x-sub-nav :tabs="$tabs" :active="$activeTab ?? ''" />
            @endif

            <!-- Page Content -->
            <div class="container-fluid py-4">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Notification Modal -->
    <x-notification-modal />

    <!-- jQuery (Local) -->
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>

    <!-- Bootstrap JS Bundle (Local) -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>

    <!-- SweetAlert2 (Local) -->
    <script src="{{ asset('js/sweetalert2.min.js') }}"></script>

    <!-- Initialize Dropdowns & Sidebar -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all dropdowns
            var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'))
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });

            // Sidebar toggle functionality
            const sidebarToggleBtn = document.getElementById('sidebar-toggle');
            const sidebar = document.querySelector('.sidebar');
            const body = document.body;
            const mainContent = document.querySelector('.d-flex.flex-column.flex-grow-1');

            // Initialize sidebar state from localStorage
            const sidebarState = localStorage.getItem('sidebarState') || 'visible';
            if (sidebarState === 'collapsed') {
                body.classList.add('sidebar-collapsed');
                if (sidebar) sidebar.classList.add('collapsed');
            } else {
                body.classList.remove('sidebar-collapsed');
                if (sidebar) sidebar.classList.remove('collapsed');
            }

            // Toggle sidebar when button is clicked
            if (sidebarToggleBtn) {
                sidebarToggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    body.classList.toggle('sidebar-active');
                    if (window.innerWidth >= 992) {
                        body.classList.toggle('sidebar-collapsed');
                        if (sidebar) sidebar.classList.toggle('collapsed');
                        const newState = body.classList.contains('sidebar-collapsed') ? 'collapsed' : 'visible';
                        localStorage.setItem('sidebarState', newState);
                    }
                });
            }

            // Close sidebar when a link is clicked on mobile
            const sidebarLinks = document.querySelectorAll('.sidebar-link');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        body.classList.remove('sidebar-active');
                    }
                });
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992) {
                    body.classList.remove('sidebar-active');
                }
            });

            // Close all dropdowns when clicking outside
            document.addEventListener('click', function(event) {
                const dropdowns = document.querySelectorAll('.dropdown-menu.show');
                dropdowns.forEach(menu => {
                    const button = menu.previousElementSibling;
                    if (!menu.contains(event.target) && !button.contains(event.target)) {
                        menu.classList.remove('show');
                    }
                });
            });

            /**
             * GLOBAL DELETE HANDLER
             * Handles all delete operations across the application with SweetAlert2 confirmation
             * 
             * Usage: Add data attributes to any delete button:
             * <button class="delete-btn-handler" 
             *         data-url="/route/to/delete/RESOURCE_ID"
             *         data-name="Item Name"
             *         data-row-selector="tr.item-row-RESOURCE_ID"
             *         data-refresh="true">
             *     <i class="fas fa-trash"></i> Delete
             * </button>
             */
            $(document).on('click', '.delete-btn-handler', function(e) {
                e.preventDefault();

                const $btn = $(this);
                const url = $btn.data('url');
                const name = $btn.data('name') || 'this item';
                const rowSelector = $btn.data('row-selector');
                const shouldRefresh = $btn.data('refresh') === true || $btn.data('refresh') === 'true';
                const csrfToken = $('meta[name="csrf-token"]').attr('content');

                if (!url) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Delete URL not configured properly',
                        icon: 'error',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }

                // Show SweetAlert2 confirmation
                Swal.fire({
                    title: 'Delete ' + name + '?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Deleting...',
                            html: 'Please wait while we delete ' + name,
                            icon: 'info',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: (modal) => {
                                Swal.showLoading();
                            }
                        });

                        // Send AJAX DELETE request
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            success: function(response) {
                                // Remove row from DOM if selector provided
                                if (rowSelector) {
                                    $(rowSelector).fadeOut(300, function() {
                                        $(this).remove();
                                    });
                                }

                                // Show success message
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: name + ' has been successfully deleted.',
                                    icon: 'success',
                                    confirmButtonColor: '#3085d6',
                                    timer: 2000,
                                    timerProgressBar: true
                                }).then(() => {
                                    // Refresh page if configured
                                    if (shouldRefresh) {
                                        setTimeout(() => {
                                            location.reload();
                                        }, 500);
                                    }
                                });
                            },
                            error: function(xhr, status, error) {
                                let errorMsg = 'An error occurred while deleting ' + name;

                                try {
                                    const response = xhr.responseJSON || JSON.parse(xhr.responseText);
                                    if (response.message) {
                                        errorMsg = response.message;
                                    } else if (response.error) {
                                        errorMsg = response.error;
                                    }
                                } catch (e) {
                                    if (xhr.status === 404) {
                                        errorMsg = name + ' not found or already deleted.';
                                    } else if (xhr.status === 403) {
                                        errorMsg = 'You do not have permission to delete ' + name;
                                    } else {
                                        errorMsg = 'Server error (' + xhr.status + '): ' + error;
                                    }
                                }

                                Swal.fire({
                                    title: 'Deletion Failed',
                                    text: errorMsg,
                                    icon: 'error',
                                    confirmButtonColor: '#3085d6'
                                });

                                console.error('Delete error:', {
                                    url: url,
                                    status: xhr.status,
                                    error: error,
                                    response: xhr.responseText
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
