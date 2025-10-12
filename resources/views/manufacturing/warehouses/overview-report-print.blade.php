<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Overview Report</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header .subtitle {
            margin: 5px 0 0 0;
            font-size: 11px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 11px;
        }
        td {
            font-size: 11px;
        }
        .summary {
            margin-bottom: 15px;
            font-size: 11px;
        }
        .summary strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Warehouse Overview Report</h1>
        <div class="subtitle">Generated on {{ now()->setTimezone('Asia/Jakarta')->format('F j, Y \a\t g:i A') }} by {{ Auth::user()->name }}</div>
    </div>

    <div class="summary">
        <strong>Summary:</strong> 
        Total Items: {{ number_format($summary['total_items']) }} | 
        Total Quantity: {{ number_format($summary['total_quantity']) }} | 
        Expiring Soon (7 days): {{ number_format($summary['expiring_soon_count']) }} | 
        Expired Items: {{ number_format($summary['expired_count']) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Location</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Expiry Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr>
                <td>{{ $item->item->name }}</td>
                <td>{{ $item->shelfPosition->full_location_code }}</td>
                <td>{{ number_format($item->quantity, 2) }}</td>
                <td>{{ $item->item->unit }}</td>
                <td>{{ $item->expiry_date ? $item->expiry_date->format('Y-m-d') : 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px;">No items found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
