<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Applicants Report</title>
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
            background-color: #0d6efd;
            color: #fff;
        }
        table th {
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #0d6efd;
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
        <h1>Land Allocation System - Applicants Report</h1>
        <div class="date">Generated on {{ now()->format('F d, Y \a\t g:i A') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Address</th>
                <th>Contact No.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applicants as $applicant)
                <tr>
                    <td><strong>{{ $applicant->full_name }}</strong></td>
                    <td>{{ $applicant->address }}</td>
                    <td>{{ $applicant->contact_no }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #999;">No applicants found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Total Records: {{ count($applicants) }}</p>
        <p>This report is confidential and intended only for authorized personnel.</p>
    </div>
</body>
</html>
