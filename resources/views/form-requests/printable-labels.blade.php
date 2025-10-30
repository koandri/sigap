<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Printable Labels - Request #{{ $request->id }}</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .labels-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 5mm;
        }
        
        .label {
            width: 90mm;
            height: 50mm;
            border: 1px dashed #ccc;
            padding: 5mm;
            box-sizing: border-box;
            page-break-inside: avoid;
        }
        
        .label-row {
            display: flex;
            align-items: flex-start;
            margin-bottom: 3mm;
        }
        
        .label-content {
            flex: 1;
        }
        
        .form-number {
            font-weight: bold;
            font-size: 14pt;
            color: #0066cc;
            margin-bottom: 2mm;
        }
        
        .form-name {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 2mm;
            line-height: 1.2;
        }
        
        .issue-date {
            font-size: 9pt;
            color: #666;
        }
        
        .qr-code {
            width: 40mm;
            height: 40mm;
            margin-left: 5mm;
        }
    </style>
</head>
<body>
    <div class="labels-grid">
        @foreach($labels as $label)
            <div class="label">
                <div class="label-row">
                    <div class="label-content">
                        <div class="form-number">{{ $label['form_number'] }}</div>
                        <div class="form-name">{{ $label['form_name'] }}</div>
                        <div class="issue-date">{{ \Carbon\Carbon::parse($label['issue_date'])->format('d M Y') }}</div>
                    </div>
                    <div>
                        <img src="{{ $label['qr_code'] }}" class="qr-code" alt="QR Code">
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>

