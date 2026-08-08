<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Report - {{ ucfirst($type) }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 40px;
            background: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #0f172a;
        }
        .header p {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 14px;
        }
        .report-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #2563eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #475569;
            font-size: 14px;
        }
        td {
            font-size: 14px;
        }
        .total-row {
            font-weight: 700;
            background-color: #f1f5f9;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-completed { color: #16a34a; background: #dcfce7; }
        .status-scheduled { color: #f59e0b; background: #fef3c7; }
        .status-cancelled { color: #dc2626; background: #fee2e2; }
        
        @media print {
            body { padding: 0; }
            @page { margin: 20mm; }
        }
    </style>
</head>
<body onload="window.print();">

    <div class="header">
        <h1>Care Plus Clinic</h1>
        <p>Official System Report</p>
        <p>Generated on {{ now()->format('F j, Y, g:i a') }}</p>
    </div>

    @if($type === 'financial')
        <div class="report-title">Financial & Revenue Report</div>
        <p style="margin-bottom: 20px; color: #64748b; font-size: 14px;">Date Range: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
        
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Completed Appointments</th>
                    <th style="text-align: right;">Revenue (LKR)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['appointments'] }}</td>
                    <td style="text-align: right;">{{ number_format($row['revenue'], 2) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="2" style="text-align: right;">Total Revenue:</td>
                    <td style="text-align: right; color: #2563eb;">{{ number_format($totalRevenue, 2) }}</td>
                </tr>
            </tbody>
        </table>

    @elseif($type === 'appointments')
        <div class="report-title">Appointments Summary Report</div>
        <p style="margin-bottom: 20px; color: #64748b; font-size: 14px;">Date Range: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
        
        <table>
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $apt)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($apt->appointment_date)->format('M d, Y h:i A') }}</td>
                    <td>{{ $apt->patient->user->name ?? 'Unknown' }}</td>
                    <td>{{ $apt->doctor->user->name ?? 'Unknown' }}</td>
                    <td>
                        <span class="badge status-{{ $apt->status == 'pending' || $apt->status == 'confirmed' ? 'scheduled' : $apt->status }}">
                            {{ ucfirst($apt->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #64748b;">No appointments found in this date range.</td>
                </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="4">Total Appointments: {{ count($appointments) }}</td>
                </tr>
            </tbody>
        </table>

    @elseif($type === 'doctors')
        <div class="report-title">Doctor Workload Report</div>
        <p style="margin-bottom: 20px; color: #64748b; font-size: 14px;">Date Range: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
        
        <table>
            <thead>
                <tr>
                    <th>Doctor Name</th>
                    <th>Specialization</th>
                    <th>Total Appointments</th>
                    <th>Completed</th>
                    <th>Cancelled</th>
                </tr>
            </thead>
            <tbody>
                @forelse($doctorWorkload as $doc)
                <tr>
                    <td>{{ $doc->doctor->user->name ?? 'Unknown' }}</td>
                    <td>{{ $doc->doctor->specialization ?? 'Specialist' }}</td>
                    <td style="font-weight: 600;">{{ $doc->total_appointments }}</td>
                    <td style="color: #16a34a;">{{ $doc->completed }}</td>
                    <td style="color: #dc2626;">{{ $doc->cancelled }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #64748b;">No doctor data found for this date range.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

    @elseif($type === 'patients')
        <div class="report-title">Patient Growth Report</div>
        <p style="margin-bottom: 20px; color: #64748b; font-size: 14px;">Date Range: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
        
        <table>
            <thead>
                <tr>
                    <th>Registration Date</th>
                    <th>Patient Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                </tr>
            </thead>
            <tbody>
                @forelse($newPatients as $patient)
                <tr>
                    <td>{{ $patient->created_at->format('M d, Y h:i A') }}</td>
                    <td style="font-weight: 500;">{{ $patient->user->name ?? 'Unknown' }}</td>
                    <td>{{ $patient->user->email ?? 'N/A' }}</td>
                    <td>{{ $patient->phone ?? 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #64748b;">No new patients registered in this date range.</td>
                </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="4">Total New Patients: {{ count($newPatients) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

</body>
</html>
