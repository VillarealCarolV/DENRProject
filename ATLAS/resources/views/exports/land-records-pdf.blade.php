<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Land Records Report</title>
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
            background-color: #28a745;
            color: #fff;
        }
        table th {
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #28a745;
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
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }
        .badge-yes {
            background-color: #ffc107;
            color: #000;
        }
        .badge-no {
            background-color: #6c757d;
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
        <h1>Land Allocation System - Land Records Report</h1>
        <div class="date">Generated on {{ now()->format('F d, Y \a\t g:i A') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Survey No.</th>
                <th>Total Area (sqm)</th>
                <th>Location</th>
                <th>Is Subdivided</th>
            </tr>
        </thead>
        <tbody>
            @forelse($landRecords as $record)
                <tr>
                    <td><strong>{{ $record->survey_no }}</strong></td>
                    <td>{{ number_format($record->total_area, 2) }}</td>
                    <td>{{ $record->location }}</td>
                    <td>
                        <span class="badge @if($record->is_subdivided) badge-yes @else badge-no @endif">
                            {{ $record->is_subdivided ? 'Yes' : 'No' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #999;">No land records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Total Records: {{ count($landRecords) }}</p>
        <p>This report is confidential and intended only for authorized personnel.</p>
    </div>
</body>
</html>
