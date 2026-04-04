<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\LandRecordController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\SubdivisionController;

// Login Routes (public - no authentication required)
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');

// Root route - redirect to login or dashboard
Route::get('/', function () {
    if (session('authenticated')) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

// Logout Route
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth.session');

// Protected Routes - Require authentication
Route::middleware(['auth.session'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Export Routes - Must be defined BEFORE resource routes to avoid conflict with {id} parameter
    Route::get('/applications/export', [ApplicationController::class, 'export'])->name('applications.export');
    Route::get('/land-records/export', [LandRecordController::class, 'export'])->name('land-records.export');
    Route::get('/applicants/export', [ApplicantController::class, 'export'])->name('applicants.export');
    
    // Master Intake Form Route
    Route::get('/master-intake', function () {
        return view('applications.master-intake');
    })->name('applications.masterCreate');
    Route::post('/master-intake', [ApplicationController::class, 'masterStore'])->name('applications.masterStore');
    Route::post('/applications/{id}/status', [ApplicationController::class, 'updateStatus'])->name('applications.updateStatus');
    
    // Resource Routes - Creates all standard CRUD routes automatically
    Route::resource('applicants', ApplicantController::class);
    Route::resource('land-records', LandRecordController::class);
    Route::resource('applications', ApplicationController::class);
    Route::resource('subdivisions', SubdivisionController::class);
    
    // Reports
    Route::get('/reports', function () {
        return view('reports.index');
    })->name('reports.index');
    
    // Search
    Route::get('/search', function () {
        return view('search.results');
    })->name('search');
});
