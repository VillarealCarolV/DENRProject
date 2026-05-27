@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-2"><i class="bi bi-search"></i> Search Results</h2>
        @if($tracking_no)
            <p class="text-muted">Results for: <strong>{{ $tracking_no }}</strong></p>
        @else
            <p class="text-muted">Enter a tracking number to search</p>
        @endif
    </div>
</div>

@if($tracking_no)
    @if($results->count() > 0)
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-3 py-3">Tracking No.</th>
                                        <th class="px-3 py-3">Applicant</th>
                                        <th class="px-3 py-3">Survey No.</th>
                                        <th class="px-3 py-3">Status</th>
                                        <th class="px-3 py-3">Date</th>
                                        <th class="px-3 py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($results as $app)
                                        @php
                                            $latestStatus = $app->statusHistories()->latest()->first();
                                            $statusText = $latestStatus ? $latestStatus->status : 'Pending';
                                            $badgeColor = match($statusText) {
                                                'Approved' => 'bg-success',
                                                'Rejected' => 'bg-danger',
                                                'In Process' => 'bg-info text-dark',
                                                default => 'bg-warning text-dark',
                                            };
                                        @endphp
                                        <tr>
                                            <td class="px-3 py-3"><code class="text-primary fw-bold">{{ $app->tracking_no }}</code></td>
                                            <td class="px-3 py-3">{{ $app->applicant->full_name }}</td>
                                            <td class="px-3 py-3">{{ $app->landRecord->survey_no }}</td>
                                            <td class="px-3 py-3">
                                                <span class="badge {{ $badgeColor }} px-2 py-1">{{ $statusText }}</span>
                                            </td>
                                            <td class="px-3 py-3">{{ $app->date_received->format('M d, Y') }}</td>
                                            <td class="px-3 py-3">
                                                <a href="{{ route('applications.show', $app->id) }}" class="btn btn-sm btn-link text-primary p-0">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info" role="alert">
                    <i class="bi bi-info-circle"></i>
                    No applications found matching tracking number: <strong>{{ $tracking_no }}</strong>
                </div>
            </div>
        </div>
    @endif
@else
    <div class="row">
        <div class="col-12">
            <div class="alert alert-secondary" role="alert">
                <i class="bi bi-exclamation-circle"></i>
                Please enter a tracking number to search
            </div>
        </div>
    </div>
@endif
@endsection
