<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom" style="overflow: visible;">
    <div class="container-fluid px-4" style="overflow: visible;">
        <!-- Sidebar Toggle Button -->
        <button class="btn btn-sm btn-outline-secondary me-3 d-lg-none" id="sidebar-toggle" type="button">
            <i class="bi bi-list"></i>
        </button>

        <!-- Search Bar (Tracking Number) -->
        <div class="flex-grow-1 me-3">
            <form class="d-flex p-2" action="{{ route('search') }}" method="GET"
            style="width: 20rem;">
                <input 
                    class="form-control form-control-sm" 
                    type="search" 
                    name="tracking_no" 
                    placeholder="Search by Tracking No..."
                    aria-label="Search">
                <button class="btn btn-sm btn-outline-secondary ms-2" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>

        <!-- User Info & Dropdown -->
        <div class="ms-auto d-flex gap-2 align-items-center">
            <!-- Processing Queue Bell (Land Officers Only) -->
            @if(auth()->user() && auth()->user()->role === 'land_officer')
                <a href="{{ route('processing-queue') }}" class="btn btn-light position-relative" title="Processing Queue">
                    <i class="fas fa-tasks"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning" style="font-size: 0.65rem; display: none;" id="pendingBadge">
                        0
                    </span>
                </a>
            @endif

            <!-- User Dropdown -->
            <div class="dropdown">
                <button class="btn btn-sm btn-light" type="button" id="userDropdown">
                    <i class="bi bi-person-circle"></i>
                    <span class="ms-2">
                        @if($user)
                            {{ $user->role }} - {{ $user->name }}
                        @else
                            Guest
                        @endif
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" id="userMenu" style="position: absolute; right: 0;">
                    <li><a class="dropdown-item" href="#">Profile</a></li>
                    <li><a class="dropdown-item" href="#">Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="#" onclick="document.getElementById('logout-form2').submit(); return false;">
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
            <form id="logout-form2" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
</nav>

<style>
    /* Ensure dropdown menus display properly */
    .navbar .dropdown {
        position: relative;
    }

    .navbar .dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        min-width: 10rem;
        margin-top: 0.5rem;
        background-color: white;
        border: 1px solid rgba(0, 0, 0, 0.15);
        border-radius: 0.25rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        z-index: 1060;
    }

    .navbar .dropdown-menu-end {
        right: 0;
        left: auto;
    }

    /* Show dropdown when Bootstrap adds the 'show' class */
    .navbar .dropdown-menu.show {
        display: block;
    }
</style>

<script>
    // Function to get status badge color
    function getStatusBadgeClass(status) {
        const statusColors = {
            'pending': 'bg-warning text-dark',
            'processing': 'bg-info text-white',
            'approved': 'bg-success text-white',
            'rejected': 'bg-danger text-white',
            'completed': 'bg-success text-white',
            'on_hold': 'bg-secondary text-white'
        };
        return statusColors[status] || 'bg-secondary text-white';
    }

    // Function to get status label
    function getStatusLabel(status) {
        const statusLabels = {
            'pending': 'Pending',
            'processing': 'Processing',
            'approved': 'Approved',
            'rejected': 'Rejected',
            'completed': 'Completed',
            'on_hold': 'On Hold'
        };
        return statusLabels[status] || status;
    }

    // Initialize dropdown functionality - SINGLE UNIFIED HANDLER
    document.addEventListener('DOMContentLoaded', function() {
        const userDropdownBtn = document.getElementById('userDropdown');
        const notificationBellBtn = document.getElementById('notificationBell');
        const userDropdownMenu = document.getElementById('userMenu');
        const notificationMenu = document.getElementById('notificationMenu');

        // Prevent dropdown menus from closing when clicking inside them
        if (userDropdownMenu) {
            userDropdownMenu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        
        if (notificationMenu) {
            notificationMenu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }

        // User dropdown toggle
        if (userDropdownBtn) {
            userDropdownBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Close notification menu if open
                if (notificationMenu) {
                    notificationMenu.classList.remove('show');
                }
                
                // Toggle user menu
                userDropdownMenu?.classList.toggle('show');
            });
        }
        
        // Notification bell toggle
        if (notificationBellBtn) {
            notificationBellBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Close user menu if open
                if (userDropdownMenu) {
                    userDropdownMenu.classList.remove('show');
                }
                
                // Toggle notification menu
                notificationMenu?.classList.toggle('show');
            });
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            // Close user dropdown if clicking outside
            if (userDropdownMenu && userDropdownBtn && !userDropdownBtn.contains(e.target) && !userDropdownMenu.contains(e.target)) {
                userDropdownMenu.classList.remove('show');
            }
            
            // Close notification menu if clicking outside
            if (notificationMenu && notificationBellBtn && !notificationBellBtn.contains(e.target) && !notificationMenu.contains(e.target)) {
                notificationMenu.classList.remove('show');
            }
        });
    });

    // Function to show notification modal
    function showNotificationModal(event, notificationData) {
        event.preventDefault();
        event.stopPropagation();
        
        // Get modal elements
        const modal = document.getElementById('notificationModal');
        const contentDiv = document.getElementById('notificationContent');
        const actionBtn = document.getElementById('notificationActionBtn');
        
        if (!modal || !contentDiv) {
            return;
        }

        // Check if we need to fetch complete data from backend
        const needsComplete = !notificationData.applicant_name || !notificationData.survey_no || !notificationData.status;
        
        let applicationId = notificationData.application_id;
        
        // If no application_id in data, try to extract from URL
        if (!applicationId && notificationData.url) {
            const match = notificationData.url.match(/\/applications\/(\d+)/);
            if (match) {
                applicationId = match[1];
            }
        }
        
        if (needsComplete) {
            if (applicationId) {
                fetchAndDisplayModal(applicationId, modal, contentDiv, actionBtn, notificationData);
            } else if (notificationData.tracking_no) {
                // Try fetching by tracking number directly
                fetch(`/applications/tracking/${notificationData.tracking_no}/details`)
                    .then(response => response.json())
                    .then(completeData => {
                        displayNotificationModal(completeData, modal, contentDiv, actionBtn);
                    })
                    .catch(error => {
                        displayNotificationModal(notificationData, modal, contentDiv, actionBtn);
                    });
            } else {
                displayNotificationModal(notificationData, modal, contentDiv, actionBtn);
            }
        } else {
            // We have all the data, display it directly
            displayNotificationModal(notificationData, modal, contentDiv, actionBtn);
        }

        // Close notification dropdown
        const notificationMenu = document.getElementById('notificationMenu');
        if (notificationMenu) {
            notificationMenu.classList.remove('show');
        }
    }

    // Function to fetch complete data and display modal
    function fetchAndDisplayModal(applicationId, modal, contentDiv, actionBtn, fallbackData) {
        fetch(`/applications/${applicationId}/details`)
            .then(response => {
                if (!response.ok) throw new Error('Not found by ID');
                return response.json();
            })
            .then(completeData => {
                displayNotificationModal(completeData, modal, contentDiv, actionBtn);
            })
            .catch(error => {
                // Try fetching by tracking number
                fetch(`/applications/tracking/${fallbackData.tracking_no}/details`)
                    .then(response => response.json())
                    .then(completeData => {
                        displayNotificationModal(completeData, modal, contentDiv, actionBtn);
                    })
                    .catch(error2 => {
                        // Fall back to displaying what we have
                        displayNotificationModal(fallbackData, modal, contentDiv, actionBtn);
                    });
            });
    }

    // Function to display the modal with notification data
    function displayNotificationModal(notificationData, modal, contentDiv, actionBtn) {
        console.log('📬 DISPLAY NOTIFICATION MODAL CALLED');
        console.log('   Notification Data:', notificationData);
        
        // Build notification content HTML with standardized format
        const statusBadgeClass = getStatusBadgeClass(notificationData.status || 'pending');
        const statusLabel = getStatusLabel(notificationData.status || 'pending');
        
        console.log('   Status:', notificationData.status);
        console.log('   Lot Type:', notificationData.lot_type);
        console.log('   Application ID:', notificationData.application_id);
        
        let html = `
            <div class="notification-details">
                <div class="mb-4">
                    <h6 class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tracking Number</h6>
                    <p class="fw-bold text-primary" style="font-size: 1.1rem;">${notificationData.tracking_no || 'N/A'}</p>
                </div>

                <div class="mb-4">
                    <h6 class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Applicant Name</h6>
                    <p class="mb-0">${notificationData.applicant_name || 'N/A'}</p>
                </div>

                <div class="mb-4">
                    <h6 class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Location / Survey Number</h6>
                    <p class="mb-0">${notificationData.location || 'N/A'}</p>
                    <p class="text-muted small mb-0"><code>${notificationData.survey_no || 'N/A'}</code></p>
                </div>

                <div class="mb-4">
                    <h6 class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Current Status</h6>
                    <span class="badge ${statusBadgeClass} p-2" style="font-size: 0.9rem;">
                        <i class="fas fa-info-circle me-1"></i>${statusLabel}
                    </span>
                </div>
        `;

        // Add remarks only if they exist
        if (notificationData.remarks && notificationData.remarks.trim()) {
            html += `
                <div class="mb-4">
                    <h6 class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Remarks</h6>
                    <p class="border-start border-3 border-primary ps-3 mb-0">${notificationData.remarks}</p>
                </div>
            `;
        }

        html += `</div>`;
        
        // Insert content
        contentDiv.innerHTML = html;
        console.log('✓ Modal content inserted');
        
        // Get footer for button placement
        const modalFooter = document.getElementById('notificationModalFooter');
        console.log('   Modal footer found:', modalFooter ? 'YES' : 'NO');
        
        // Clear all extra buttons (but keep the close button)
        const extraButtons = modalFooter.querySelectorAll('a.btn-outline-primary, a.btn-warning, a.btn-success, a.btn-primary:not(#notificationActionBtn)');
        extraButtons.forEach(btn => {
            console.log('   Removing extra button:', btn.className);
            btn.remove();
        });
        
        // Make sure actionBtn exists - if not, find/create it
        let actionButton = document.getElementById('notificationActionBtn');
        if (!actionButton) {
            console.log('   Action button not found in DOM, recreating...');
            actionButton = document.createElement('a');
            actionButton.id = 'notificationActionBtn';
            actionButton.style.display = 'none';
            modalFooter.appendChild(actionButton);
        }
        console.log('   Action button ready:', actionButton ? 'YES' : 'NO');
        
        // Smart button logic based on lot type and status
        const applicationId = notificationData.application_id;
        const lotType = notificationData.lot_type;
        
        console.log('🔍 SMART BUTTON LOGIC:');
        console.log('   Lot Type:', lotType);
        console.log('   Status:', notificationData.status);
        console.log('   Application ID:', applicationId);
        
        // Check if application is already assessed (lot_type exists)
        if (lotType === 'existing_lot' && (notificationData.status === 'Pending' || notificationData.status === 'In Process')) {
            console.log('✅ ACTION: Creating QUICK APPROVE button for existing lot');
            // For existing lots already assessed, show quick approve button
            actionButton.innerHTML = `<i class="fas fa-check me-2"></i>Quick Approve`;
            actionButton.className = 'btn btn-success';
            actionButton.href = '#';
            actionButton.onclick = function(e) {
                e.preventDefault();
                console.log('🖱️ QUICK APPROVE button clicked');
                quickApproveExistingLot(applicationId);
            };
            actionButton.style.display = 'inline-block';
            console.log('   ✓ Quick Approve button created and displayed');
            
            // Also add a "View Details" button
            const viewBtn = document.createElement('a');
            viewBtn.href = `/applications/${applicationId}`;
            viewBtn.className = 'btn btn-outline-primary';
            viewBtn.innerHTML = '<i class="fas fa-eye me-2"></i>View Full Details';
            viewBtn.style.marginRight = '0.5rem';
            modalFooter.insertBefore(viewBtn, modalFooter.lastChild);
            console.log('   ✓ View Details button created');
        } else if (lotType === 'subdivision') {
            console.log('✅ ACTION: Creating EDIT & ASSESS button for subdivision');
            // For subdivisions, show edit button to go to edit page for full assessment
            actionButton.href = `/applications/${applicationId}/edit`;
            actionButton.className = 'btn btn-warning';
            actionButton.innerHTML = '<i class="fas fa-pen-to-square me-2"></i>Edit & Assess Subdivision';
            actionButton.onclick = null;
            actionButton.style.display = 'inline-block';
            console.log('   ✓ Edit & Assess button created');
        } else {
            console.log('✅ ACTION: Creating START ASSESSMENT button (not yet assessed)');
            // Not yet assessed, show edit button
            actionButton.href = `/applications/${applicationId}/edit`;
            actionButton.className = 'btn btn-primary';
            actionButton.innerHTML = '<i class="fas fa-pen-to-square me-2"></i>Start Assessment';
            actionButton.onclick = null;
            actionButton.style.display = 'inline-block';
            console.log('   ✓ Start Assessment button created');
        }
        
        // Show modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        console.log('✓ Modal displayed to user');
    }

    // Quick approve function for existing lots
    function quickApproveExistingLot(applicationId) {
        console.log('🚀 QUICK APPROVE FUNCTION STARTED');
        console.log('   Application ID:', applicationId);
        
        if (!confirm('Approve this application as an Existing Lot? This will mark it as APPROVED.')) {
            console.log('❌ User cancelled quick approval');
            return;
        }

        console.log('✓ User confirmed approval');

        // Get the button
        const modalFooter = document.getElementById('notificationModalFooter');
        const btn = modalFooter.querySelector('.btn-success');
        console.log('   Button found:', btn ? 'YES' : 'NO');
        
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Approving...';
            console.log('   ✓ Loading state activated');
        }

        // Perform quick approval via API
        console.log('→ Sending POST request to /applications/' + applicationId + '/quick-approve');
        
        fetch(`/applications/${applicationId}/quick-approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                lot_type: 'existing_lot',
                status: 'Approved',
                land_officer_remarks: 'Approved as existing lot - Quick decision from notification modal.'
            })
        })
        .then(response => {
            console.log('📩 Response received:', response.status, response.statusText);
            if (!response.ok) {
                console.error('   ❌ Response not OK');
                throw new Error('Approval failed: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ SUCCESS! Response data:', data);
            
            // Success! Close modal and refresh
            const modalElement = document.getElementById('notificationModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                console.log('   ✓ Closing modal');
                modal.hide();
            }
            
            // Show success alert
            alert('✅ Application approved successfully!');
            console.log('   ✓ Alert shown, reloading page');
            location.reload(); // Refresh to see updated status
        })
        .catch(error => {
            console.error('❌ ERROR during quick approval:', error);
            alert('❌ Error approving application. Please try again from the edit page.\n\nError: ' + error.message);
            
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check me-2"></i>Quick Approve';
            }
        });
    }
</script>
