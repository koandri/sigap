<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form Request Labels - Request #{{ $request->id }}</title>
    <style>
        @page {
            size: 115mm 42mm; /* Paper width × Row height */
            margin: 0;
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            width: 115mm;
        }
        
        .label-row {
            width: 115mm;
            height: 42mm;
            display: flex;
            justify-content: space-evenly;
            align-items: center;
            page-break-after: always;
            box-sizing: border-box;
            padding: 2mm;
        }
        
        .label {
            width: 35mm;
            height: 38mm;
            border: 1px solid #000;
            padding: 1.5mm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
        }
        
        .label-header {
            text-align: center;
            width: 100%;
        }
        
        .form-number {
            font-weight: bold;
            font-size: 8pt;
            color: #0066cc;
            margin-bottom: 0.5mm;
            word-wrap: break-word;
            line-height: 1.1;
        }
        
        .form-name {
            font-size: 6pt;
            font-weight: bold;
            line-height: 1;
            color: #333;
            max-height: 8mm;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .qr-code {
            width: 22mm;
            height: 22mm;
            margin: 0.5mm 0;
        }
        
        .label-footer {
            font-size: 5pt;
            color: #666;
            text-align: center;
            line-height: 1;
        }
        
        .issue-date {
            margin-bottom: 0.3mm;
        }
        
        .request-info {
            font-size: 4.5pt;
            color: #999;
        }
        
        /* Print-specific styles */
        @media print {
            body {
                width: 115mm;
            }
            
            .label-row {
                page-break-after: always;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    @php
        $labelChunks = $labels->chunk(3);
    @endphp
    
    @foreach($labelChunks as $row)
    <div class="label-row">
        @foreach($row as $label)
        <div class="label">
            <div class="label-header">
                <div class="form-number">{{ $label['form_number'] }}</div>
                <div class="form-name">{{ $label['form_name'] }}</div>
            </div>
            <img src="{{ $label['qr_code'] }}" class="qr-code" alt="QR Code">
            <div class="label-footer">
                <div class="issue-date">{{ \Carbon\Carbon::parse($label['issue_date'])->format('d/m/Y') }}</div>
                <div class="request-info">Req #{{ $request->id }}</div>
            </div>
        </div>
        @endforeach
        
        {{-- Fill empty slots if row has less than 3 labels --}}
        @if($row->count() < 3)
            @for($i = 0; $i < 3 - $row->count(); $i++)
            <div class="label" style="border: 1px dashed #ccc; background: transparent;"></div>
            @endfor
        @endif
    </div>
    @endforeach
</body>
</html>

