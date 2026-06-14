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
        color: #16a34a;
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

    /* Section Container */
    .section-container {
        border-top: 4px solid #16a34a;
        background: white;
        margin-bottom: 1.5rem;
        margin-left: 1.25rem;
        margin-right: 1.25rem;
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
        grid-template-columns: repeat(2, 1fr);
        gap: 0;
        padding: 1rem 1.25rem;
    }

    .metadata-grid.grid-3col {
        grid-template-columns: repeat(3, 1fr);
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
        .metadata-grid.grid-3col,
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

        .doc-header {
            flex-direction: column;
            gap: 0.75rem;
        }

        .section-container {
            margin-left: 0;
            margin-right: 0;
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

    .status-subdivided-yes {
        background: #d1fae5;
        color: #065f46;
    }

    .status-subdivided-no {
        background: #f3f4f6;
        color: #374151;
    }

    /* Content Wrapper for scrolling */
    .content-wrapper {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem 0;
    }
</style>

<div class="page-wrapper">
    <div class="document-container">
        <!-- HEADER -->
        <div class="doc-header">
            <div class="tracking-info">
                <h2>
                    <i class="fas fa-map tracking-icon"></i>
                    {{ $landRecord->survey_no }}
                </h2>
                <div class="tracking-meta">
                    <span><i class="fas fa-calendar-alt"></i> Created: {{ $landRecord->created_at->format('M d, Y') }}</span>
                    <span><i class="fas fa-sync"></i> Updated: {{ $landRecord->updated_at->format('M d, Y') }}</span>
                </div>
            </div>
            <div class="header-btns">
                <a href="{{ route('land-records.index') }}" class="btn-sm-custom">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <a href="{{ route('land-records.edit', $landRecord->id) }}" class="btn-sm-custom">
                    <i class="fas fa-edit"></i> Edit
                </a>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="content-wrapper">
            @if(session('success'))
                <div style="margin-left: 1.25rem; margin-right: 1.25rem; margin-top: 1rem;">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            <!-- LAND RECORD INFORMATION PANEL -->
            <div class="section-container">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-map-marker-alt"></i> Land Record Details
                    </h3>
                </div>
                <div class="section-subheader">
                    <i class="fas fa-chevron-down"></i> Details
                </div>
                <div class="metadata-grid grid-3col">
                    <div class="metadata-item">
                        <span class="metadata-label">Survey Number</span>
                        <div class="metadata-value">{{ $landRecord->survey_no }}</div>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Location</span>
                        <div class="metadata-value">{{ $landRecord->location }}</div>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Total Area</span>
                        <div class="metadata-value">{{ number_format($landRecord->total_area, 2) }} sqm</div>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Subdivided</span>
                        <div class="metadata-value">
                            @if($landRecord->is_subdivided)
                                <span class="status-badge status-subdivided-yes">Yes</span>
                            @else
                                <span class="status-badge status-subdivided-no">No</span>
                            @endif
                        </div>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Created Date</span>
                        <div class="metadata-value">{{ $landRecord->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Last Updated</span>
                        <div class="metadata-value">{{ $landRecord->updated_at->format('M d, Y h:i A') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
