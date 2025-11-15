<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents Masterlist - Print</title>
    <link href="{{ asset('assets/tabler/dist/css/tabler.min.css') }}" rel="stylesheet"/>
    <style>
        @page {
            size: landscape;
            margin: 1cm;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18pt;
            font-weight: bold;
        }
        
        .header .print-info {
            margin-top: 10px;
            font-size: 9pt;
            color: #666;
        }
        
        .filters-applied {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 20px;
            border-left: 3px solid #0054a6;
            font-size: 9pt;
        }
        
        .department-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .department-title {
            font-size: 14pt;
            font-weight: bold;
            color: #0054a6;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #0054a6;
        }
        
        .type-section {
            margin-bottom: 20px;
        }
        
        .type-title {
            font-size: 12pt;
            font-weight: bold;
            color: #666;
            margin-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9pt;
            table-layout: fixed;
        }
        
        table thead tr {
            background-color: #f8f9fa;
            border-bottom: 2px solid #000;
        }
        
        table th {
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
        }
        
        table td {
            padding: 6px;
            border-bottom: 1px solid #ddd;
        }
        
        table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            padding: 2px 8px;
            font-size: 8pt;
            border-radius: 3px;
        }
        
        .badge.bg-success {
            background-color: #28a745;
            color: white;
        }
        
        .badge.bg-warning {
            background-color: #ffc107;
            color: #000;
        }
        
        .no-documents {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            text-align: center;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #ddd;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body onload="window.print();">
    <div class="header">
        <h1>DOCUMENTS MASTERLIST</h1>
    </div>

    @if(!empty($filters['department']) || !empty($filters['type']) || !empty($filters['search']))
        <div class="filters-applied">
            <strong>Filters Applied:</strong>
            @if(!empty($filters['department']))
                Department: <strong>{{ \App\Models\Department::find($filters['department'])?->name ?? 'N/A' }}</strong>
            @endif
            @if(!empty($filters['type']))
                | Type: <strong>
                    @php
                        try {
                            $filterTypeLabel = \App\Enums\DocumentType::from($filters['type'])->label();
                        } catch (\ValueError $e) {
                            $filterTypeLabel = 'Unknown Type';
                        }
                    @endphp
                    {{ $filterTypeLabel }}
                </strong>
            @endif
            @if(!empty($filters['search']))
                | Search: <strong>{{ $filters['search'] }}</strong>
            @endif
        </div>
    @endif

    @if($masterlist->count() > 0)
        @foreach($masterlist as $departmentName => $departmentDocuments)
            <div class="department-section">
                <div class="department-title">{{ $departmentName }}</div>
                
                @foreach($departmentDocuments as $documentType => $documents)
                    <div class="type-section">
                        <div class="type-title">
                            @php
                                try {
                                    $typeLabel = \App\Enums\DocumentType::from($documentType)->label();
                                } catch (\ValueError $e) {
                                    $typeLabel = 'Unknown Type (' . $documentType . ')';
                                }
                            @endphp
                            {{ $typeLabel }}
                        </div>
                        
                        <table>
                            <colgroup>
                                <col style="width: 12%;">
                                <col style="width: 30%;">
                                <col style="width: 10%;">
                                <col style="width: 18%;">
                                <col style="width: 12%;">
                                <col style="width: 18%;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Document Number</th>
                                    <th>Title</th>
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
                                        <td>
                                            <div style="font-weight: bold;">{{ $document->title }}</div>
                                            @if($document->description)
                                                <div style="font-size: 8pt; color: #666;">{{ Str::limit($document->description, 60) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($document->activeVersion)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-warning">No Active</span>
                                            @endif
                                        </td>
                                        <td>{{ $document->creator->name }}</td>
                                        <td>{{ $document->created_at->format('Y-m-d') }}</td>
                                        <td>{{ $document->physical_location_string }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        @endforeach
    @else
        <div class="no-documents">
            <p>No documents found matching the selected filters.</p>
        </div>
    @endif

    <div class="footer">
        Generated on: {{ now()->format('d F Y H:i:s') }} | Printed by: {{ auth()->user()->name }}
    </div>
</body>
</html>

