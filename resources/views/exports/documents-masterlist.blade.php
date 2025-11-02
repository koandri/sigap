<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Documents Masterlist</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .status.approved {
            background-color: #d4edda;
            color: #155724;
        }
        .status.draft {
            background-color: #fff3cd;
            color: #856404;
        }
        .status.pending {
            background-color: #cce5ff;
            color: #004085;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Documents Masterlist</h1>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
        <p>Total Documents: {{ $documents->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Document Number</th>
                <th>Title</th>
                <th>Type</th>
                <th>Department</th>
                <th>Current Version</th>
                <th>Status</th>
                <th>Created By</th>
                <th>Created At</th>
                <th>Physical Location</th>
            </tr>
        </thead>
        <tbody>
            @foreach($documents as $document)
                <tr>
                    <td>{{ $document->document_number }}</td>
                    <td>{{ $document->title }}</td>
                    <td>{{ $document->document_type }}</td>
                    <td>{{ $document->department?->name ?? 'N/A' }}</td>
                    <td>{{ $document->activeVersion?->version_number ?? 'N/A' }}</td>
                    <td>
                        <span class="status {{ $document->activeVersion?->status ?? 'draft' }}">
                            {{ $document->activeVersion?->status ?? 'No Version' }}
                        </span>
                    </td>
                    <td>{{ $document->createdBy->name }}</td>
                    <td>{{ $document->created_at->format('Y-m-d') }}</td>
                    <td>
                        @if($document->physical_location)
                            Room: {{ $document->physical_location['room_no'] ?? 'N/A' }}, 
                            Shelf: {{ $document->physical_location['shelf_no'] ?? 'N/A' }}, 
                            Folder: {{ $document->physical_location['folder_no'] ?? 'N/A' }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
