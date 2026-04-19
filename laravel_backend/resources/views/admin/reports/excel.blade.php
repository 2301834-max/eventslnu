<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Reports Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #1e293b;
            margin: 24px;
        }

        .hero {
            background: #0f172a;
            color: #ffffff;
            padding: 24px;
            border-radius: 18px;
            margin-bottom: 20px;
        }

        .hero h1 {
            margin: 0 0 8px;
            font-size: 24px;
        }

        .hero p {
            margin: 4px 0;
            font-size: 12px;
            color: #cbd5e1;
        }

        .section-title {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 10px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 24px;
        }

        .metrics td {
            width: 25%;
            padding: 16px;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            vertical-align: top;
        }

        .metrics .label {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #475569;
        }

        .metrics .value {
            display: block;
            margin-top: 8px;
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
        }

        .panel {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
        }

        .panel th,
        .panel td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 12px;
            text-align: left;
        }

        .panel thead th {
            background: #0f172a;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 10px;
        }

        .panel tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .chip {
            display: inline-block;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .chip-draft { background: #e2e8f0; color: #334155; }
        .chip-published { background: #dbeafe; color: #1d4ed8; }
        .chip-ongoing { background: #dcfce7; color: #15803d; }
        .chip-completed { background: #ede9fe; color: #6d28d9; }
        .chip-cancelled { background: #ffe4e6; color: #be123c; }
    </style>
</head>
<body>
    <div class="hero">
        <h1>LNU Smart Events System</h1>
        <p>Admin Reports Export</p>
        <p>Generated: {{ $generatedAt->format('M d, Y h:i A') }}</p>
        <p>
            Filters:
            Status {{ $filters['status'] !== '' ? ucfirst($filters['status']) : 'All' }},
            Date {{ $filters['date_from'] !== '' ? $filters['date_from'] : 'Any' }} to {{ $filters['date_to'] !== '' ? $filters['date_to'] : 'Any' }},
            Search {{ $filters['search'] !== '' ? $filters['search'] : 'None' }}
        </p>
    </div>

    <p class="section-title">Performance Snapshot</p>
    <table class="metrics">
        <tr>
            <td>
                <span class="label">Total Events</span>
                <span class="value">{{ $summary['total_events'] }}</span>
            </td>
            <td>
                <span class="label">Registrations</span>
                <span class="value">{{ $summary['total_registrations'] }}</span>
            </td>
            <td>
                <span class="label">Attendance</span>
                <span class="value">{{ $summary['attendance_records'] }}</span>
            </td>
            <td>
                <span class="label">Average Rate</span>
                <span class="value">{{ number_format($summary['average_attendance_rate'], 2) }}%</span>
            </td>
        </tr>
    </table>

    <p class="section-title">Status Breakdown</p>
    <table class="panel">
        <thead>
            <tr>
                <th>Status</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($statusCounts as $status => $count)
                <tr>
                    <td>{{ ucfirst($status) }}</td>
                    <td>{{ $count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="section-title">Event Performance</p>
    <table class="panel">
        <thead>
            <tr>
                <th>Event</th>
                <th>Location</th>
                <th>Status</th>
                <th>Schedule</th>
                <th>Created By</th>
                <th>Registrations</th>
                <th>Approved</th>
                <th>Attendance</th>
                <th>Attendance Rate</th>
            </tr>
        </thead>
        <tbody>
            @forelse($events as $event)
                @php
                    $attendanceRate = $event->approved_registrations_count > 0
                        ? round(($event->attendance_records_count / $event->approved_registrations_count) * 100, 2)
                        : 0;
                    $chipClass = match ($event->status) {
                        'draft' => 'chip-draft',
                        'published' => 'chip-published',
                        'ongoing' => 'chip-ongoing',
                        'completed' => 'chip-completed',
                        'cancelled' => 'chip-cancelled',
                        default => 'chip-draft',
                    };
                @endphp
                <tr>
                    <td>{{ $event->title }}</td>
                    <td>{{ $event->location }}</td>
                    <td><span class="chip {{ $chipClass }}">{{ ucfirst($event->status) }}</span></td>
                    <td>{{ $event->start_date->format('M d, Y h:i A') }} to {{ $event->end_date->format('M d, Y h:i A') }}</td>
                    <td>{{ $event->creator?->name ?? 'Unknown' }}</td>
                    <td>{{ $event->registrations_count }}</td>
                    <td>{{ $event->approved_registrations_count }}</td>
                    <td>{{ $event->attendance_records_count }}</td>
                    <td>{{ number_format($attendanceRate, 2) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">No events matched the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
