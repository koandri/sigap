<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form Request Labels - Request #{{ $request->id }}</title>
    <style>
        @page {
            margin: 10mm;
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .label-container {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .label {
            width: 100mm;
            height: 60mm;
            border: 2px solid #000;
            padding: 10mm;
            margin-bottom: 10mm;
            page-break-inside: avoid;
            display: inline-block;
            vertical-align: top;
            box-sizing: border-box;
        }
        
        .label-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8mm;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5mm;
        }
        
        .form-info {
            flex: 1;
        }
        
        .form-number {
            font-weight: bold;
            font-size: 16pt;
            color: #0066cc;
            margin-bottom: 2mm;
        }
        
        .form-name {
            font-size: 12pt;
            font-weight: bold;
            color: #333;
            line-height: 1.3;
        }
        
        .qr-code {
            width: 50mm;
            height: 50mm;
            margin-left: 5mm;
        }
        
        .label-footer {
            margin-top: 5mm;
        }
        
        .issue-date {
            font-size: 10pt;
            color: #666;
        }
        
        .request-info {
            font-size: 9pt;
            color: #999;
            margin-top: 2mm;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    @foreach($labels as $index => $label)
        <div class="label-container {{ ($index + 1) % 4 == 0 ? 'page-break' : '' }}">
            <div class="label">
                <div class="label-header">
                    <div class="form-info">
                        <div class="form-number">{{ $label['form_number'] }}</div>
                        <div class="form-name">{{ $label['form_name'] }}</div>
                    </div>
                    <div>
                        <img src="{{ $label['qr_code'] }}" class="qr-code" alt="QR Code">
                    </div>
                </div>
                
                <div class="label-footer">
                    <div class="issue-date">
                        <strong>Issue Date:</strong> {{ \Carbon\Carbon::parse($label['issue_date'])->format('d M Y') }}
                    </div>
                    <div class="request-info">
                        Request #{{ $request->id }} | {{ $request->requester->name }}
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</body>
</html>

