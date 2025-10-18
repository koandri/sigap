<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Cleaning Report - {{ $location->name }} - {{ $date }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        .stats {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stat-box {
            display: table-cell;
            width: 25%;
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .stat-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 10px;
            border-radius: 3px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .badge-secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .badge-purple {
            background-color: #e7d6f3;
            color: #6f42c1;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daily Cleaning Report</h1>
        <p><strong>Location:</strong> {{ $location->name }}</p>
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</p>
        <p><strong>Generated:</strong> {{ now()->format('F d, Y H:i') }}</p>
    </div>

    <div class="stats">
        <div class="stat-box">
            <div class="stat-label">Total Tasks</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Completed</div>
            <div class="stat-value" style="color: #28a745;">{{ $stats['completed'] }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Pending</div>
            <div class="stat-value" style="color: #ffc107;">{{ $stats['pending'] }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Missed</div>
            <div class="stat-value" style="color: #dc3545;">{{ $stats['missed'] }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Task #</th>
                <th style="width: 25%;">Item</th>
                <th style="width: 15%;">Schedule</th>
                <th style="width: 15%;">Assigned To</th>
                <th style="width: 15%;">Completed By</th>
                <th style="width: 18%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
            <tr>
                <td>{{ $task->task_number }}</td>
                <td>
                    <strong>{{ $task->item_name }}</strong>
                    @if($task->asset)
                        <br><span class="badge badge-info">{{ $task->asset->code }}</span>
                    @endif
                </td>
                <td>
                    @if($task->cleaning_schedule_id > 0)
                        {{ $task->cleaningSchedule->name }}
                    @else
                        <span class="badge badge-purple">Ad-hoc</span>
                    @endif
                </td>
                <td>
                    @if($task->assignedUser)
                        {{ $task->assignedUser->name }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($task->completedByUser)
                        {{ $task->completedByUser->name }}
                        <br><small>{{ $task->completed_at->format('H:i') }}</small>
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($task->status === 'completed' || $task->status === 'approved')
                        <span class="badge badge-success">✓ {{ ucfirst($task->status) }}</span>
                    @elseif($task->status === 'in-progress')
                        <span class="badge badge-info">⟳ In Progress</span>
                    @elseif($task->status === 'pending')
                        <span class="badge badge-warning">⏱ Pending</span>
                    @elseif($task->status === 'missed')
                        <span class="badge badge-danger">⚠ Missed</span>
                    @else
                        <span class="badge badge-secondary">{{ ucfirst($task->status) }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 30px; color: #999;">
                    No tasks scheduled for this location on the selected date.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>SIGAP - Facility Management System</p>
        <p>This report is system-generated and contains {{ $stats['total'] }} task(s) for the specified date.</p>
    </div>
</body>
</html>

