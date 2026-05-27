<!-- Notification Modal Component -->
<!-- This modal displays notification details when a user clicks on a notification -->

<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <!-- Modal Header -->
            <div class="modal-header bg-gradient text-white border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="modal-title fw-bold" id="notificationModalLabel">
                    <i class="fas fa-bell me-2"></i>Notification Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                <div id="notificationContent">
                    <!-- Content will be dynamically inserted here by JavaScript -->
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading notification details...</p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light border-top" id="notificationModalFooter">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
                <a href="#" class="btn btn-primary" id="notificationActionBtn" style="display: none;">
                    <i class="fas fa-external-link-alt me-2"></i>View Application
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Styles -->
<style>
    .notification-modal-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .notification-modal-icon.success {
        background-color: #d4edda;
        color: #155724;
    }

    .notification-modal-icon.info {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .notification-modal-icon.warning {
        background-color: #fff3cd;
        color: #856404;
    }

    .notification-modal-icon.danger {
        background-color: #f8d7da;
        color: #721c24;
    }

    .notification-detail-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
    }

    .notification-detail-label {
        color: #495057;
        min-width: 130px;
        margin-right: 1rem;
        font-weight: 600;
    }

    .notification-detail-value {
        color: #212529;
        flex: 1;
    }

    .notification-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .notification-details {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 0.5rem;
    }

    .modal-header {
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    }

    #notificationContent h4 {
        color: #212529;
        font-weight: 700;
    }
</style>
