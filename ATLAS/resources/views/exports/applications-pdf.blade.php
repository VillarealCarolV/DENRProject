<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Applications Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #000;
        }
        .header .date {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table thead {
            background-color: #000;
            color: #fff;
        }
        table th {
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #000;
        }
        table td {
            padding: 8px 10px;
            border: 1px solid #ddd;
        }
        table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        table tbody tr:hover {
            background-color: #f0f0f0;
        }
        .status {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }
        .status-pending {
            background-color: #ffc107;
            color: #000;
        }
        .status-approved {
            background-color: #28a745;
            color: #fff;
        }
        .status-rejected {
            background-color: #dc3545;
            color: #fff;
        }
        .status-in-process {
            background-color: #17a2b8;
            color: #fff;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Land Allocation System - Applications Report</h1>
        <div class="date">Generated on {{ now()->format('F d, Y \a\t g:i A') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tracking No.</th>
                <th>Applicant Name</th>
                <th>Survey No.</th>
                <th>Total Area (sqm)</th>
                <th>Date Received</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applications as $app)
                @php
                    $latestStatus = $app->statusHistories()->latest()->first();
                    $statusText = $latestStatus ? $latestStatus->status : 'Pending';
                    $statusClass = match($statusText) {
                        'Approved' => 'status-approved',
                        'Rejected' => 'status-rejected',
                        'In Process' => 'status-in-process',
                        default => 'status-pending',
                    };
                @endphp
                <tr>
                    <td><strong>{{ $app->tracking_no }}</strong></td>
                    <td>{{ $app->applicant->full_name }}</td>
                    <td>{{ $app->landRecord->survey_no }}</td>
                    <td>{{ number_format($app->landRecord->total_area, 2) }}</td>
                    <td>{{ $app->date_received->format('M d, Y') }}</td>
                    <td>
                        <span class="status {{ $statusClass }}">{{ $statusText }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #999;">No applications found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Total Records: {{ count($applications) }}</p>
        <p>This report is confidential and intended only for authorized personnel.</p>
    </div>
</body>
</html>
