<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\LandRecordController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SubdivisionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Search route
    Route::get('/search', SearchController::class)->name('search');

    // Export routes MUST be defined BEFORE resource routes to avoid route conflicts
    Route::get('applications/export', [ApplicationController::class, 'export'])->name('applications.export');
    Route::get('applicants/export', [ApplicantController::class, 'export'])->name('applicants.export');
    Route::get('land-records/export', [LandRecordController::class, 'export'])->name('land-records.export');

    // API endpoint for notification modal - get application details
    Route::get('applications/{application}/details', [ApplicationController::class, 'getDetails'])->name('applications.details');
    Route::get('applications/tracking/{trackingNo}/details', [ApplicationController::class, 'getDetailsByTracking'])->name('applications.detailsByTracking');

    // Custom routes for master intake form (before resource routes)
    Route::get('applications/master/create', [ApplicationController::class, 'masterCreate'])->name('applications.masterCreate');
    Route::post('applications/master/store', [ApplicationController::class, 'masterStore'])->name('applications.masterStore');

    // Intake Workstation for Records Officers
    Route::get('applications/workstation/view', [ApplicationController::class, 'workstation'])->name('applications.workstation');
    Route::get('applications/table-data', [ApplicationController::class, 'getTableData'])->name('applications.getTableData');
    Route::get('applications/next-tracking-number', [ApplicationController::class, 'getNextTrackingNumber'])->name('applications.nextTrackingNumber');

    // Processing Queue - Task/Application review workstation
    Route::get('processing-queue', [ApplicationController::class, 'processingQueue'])->name('processing-queue');
    Route::get('api/applications/pending-count', [ApplicationController::class, 'getPendingCount'])->name('applications.pendingCount');
    Route::post('api/applications/update-status', [ApplicationController::class, 'updateStatusFromModal'])->name('applications.updateStatus');

    // Resource routes for applications, applicants, and land records
    Route::resource('applications', ApplicationController::class);
    Route::resource('applicants', ApplicantController::class);
    Route::resource('land-records', LandRecordController::class);
    // Reports
    Route::resource('reports', ReportsController::class, ['only' => ['index']]);
    Route::get('reports/my-pending-backlog', [ReportsController::class, 'myPendingBacklog'])->name('reports.myPendingBacklog');
    Route::get('reports/land-subdivision-report', [ReportsController::class, 'landSubdivisionReport'])->name('reports.landSubdivisionReport');

    // Subdivision routes
    Route::get('subdivisions/create', [SubdivisionController::class, 'create'])->name('subdivisions.create');
    Route::post('subdivisions/store', [SubdivisionController::class, 'store'])->name('subdivisions.store');

    // Application status update route
    Route::post('applications/{id}/updateStatus', [ApplicationController::class, 'updateStatus'])->name('applications.updateStatus');

    // Quick approve route for existing lots from notification modal
    Route::post('applications/{application}/quick-approve', [ApplicationController::class, 'quickApprove'])->name('applications.quickApprove');

    // Notification routes
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    
    // AJAX Notification endpoints (for real-time updates)
    Route::get('api/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unreadCount');
    Route::post('api/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsReadAjax'])->name('notifications.markAsReadAjax');

    // User Management routes
    Route::resource('users', UserController::class);
});

require __DIR__.'/auth.php';
