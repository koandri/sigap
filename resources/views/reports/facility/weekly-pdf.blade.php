<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Cleaning Report</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #206bc4;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .print-button:hover {
            background-color: #1a5aa8;
        }
        @media print {
            .print-button {
                display: none;
            }
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        .header p {
            margin: 3px 0;
            font-size: 11px;
        }
        .legend {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 4px;
        }
        .legend-item {
            display: inline-block;
            margin-right: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #e9ecef;
            font-weight: bold;
            font-size: 10px;
        }
        th.location-col {
            text-align: left;
            width: 20%;
        }
        td.location-cell {
            text-align: left;
            font-weight: bold;
        }
        .cell-completed {
            background-color: #d4edda;
            font-size: 18px;
            font-weight: bold;
            color: #155724;
        }
        .cell-partial {
            background-color: #fff3cd;
            font-size: 18px;
            font-weight: bold;
            color: #856404;
        }
        .cell-none {
            background-color: #f8d7da;
            font-size: 18px;
            font-weight: bold;
            color: #721c24;
        }
        .cell-no-tasks {
            background-color: #f8f9fa;
            font-size: 18px;
            color: #999;
        }
        .task-count {
            display: block;
            font-size: 8px;
            color: #666;
            margin-top: 2px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button">
        🖨️ Print / Save as PDF
    </button>

    <div class="header">
        <h1>Weekly Cleaning Report</h1>
        <p><strong>Period:</strong> {{ $weekStart->format('F d, Y') }} - {{ $weekEnd->format('F d, Y') }}</p>
        <p>This report provides a weekly overview of cleaning task completion across locations.</p>
    </div>

    <div class="legend">
        <strong>Legend:</strong>
        <span class="legend-item"><strong style="color: #155724; font-size: 14px;">✓</strong> All tasks completed</span>
        <span class="legend-item"><strong style="color: #856404; font-size: 14px;">⚠</strong> Partially completed</span>
        <span class="legend-item"><strong style="color: #721c24; font-size: 14px;">✗</strong> No tasks completed</span>
        <span class="legend-item"><strong style="color: #999; font-size: 14px;">-</strong> No tasks scheduled</span>
    </div>

    <table>
        <thead>
            <tr>
                <th class="location-col">Location</th>
                @for($i = 0; $i < 7; $i++)
                    @php
                        $dayDate = $weekStart->copy()->addDays($i);
                    @endphp
                    <th>
                        {{ $dayDate->format('D') }}<br>
                        <small>{{ $dayDate->format('M d') }}</small>
                    </th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @forelse($gridData as $row)
            <tr>
                <td class="location-cell">{{ $row['location']->name }}</td>
                @foreach($row['days'] as $day)
                <td class="{{ $day['indicator'] === '✓' ? 'cell-completed' : ($day['indicator'] === '⚠' ? 'cell-partial' : ($day['indicator'] === '-' ? 'cell-no-tasks' : 'cell-none')) }}">
                    @if($day['indicator'] === '✓')
                        ✓
                    @elseif($day['indicator'] === '⚠')
                        ⚠
                    @elseif($day['indicator'] === '-')
                        -
                    @else
                        ✗
                    @endif
                    @if($day['indicator'] !== '-')
                    <span class="task-count">{{ $day['completed'] }}/{{ $day['total'] }}</span>
                    @endif
                </td>
                @endforeach
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 30px; color: #999;">
                    No locations found for the selected filters.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>SIGAP - Facility Management System</p>
        <p><strong>Printed By:</strong> {{ auth()->user()->name }}</p>
        <p><strong>Printed Date/Time:</strong> {{ now()->format('F d, Y H:i') }}</p>
    </div>
</body>
</html>

