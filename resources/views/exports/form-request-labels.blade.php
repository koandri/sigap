<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form Request Labels</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .label {
            width: 3.5in;
            height: 2in;
            border: 1px solid #000;
            margin: 10px;
            padding: 10px;
            display: inline-block;
            vertical-align: top;
            page-break-inside: avoid;
        }
        .label-header {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .label-content {
            font-size: 12px;
            line-height: 1.2;
        }
        .qr-code {
            float: right;
            width: 60px;
            height: 60px;
        }
        .form-number {
            font-weight: bold;
            color: #0066cc;
        }
        .document-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .issued-to {
            margin-bottom: 3px;
        }
        .issued-date {
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>Form Request Labels - Request #{{ $formRequest->id }}</h1>
    
    @foreach($labels as $label)
        <div class="label">
            <div class="label-header">
                <span class="form-number">{{ $label->form_number }}</span>
                <img src="{{ $label->qr_code_url }}" class="qr-code" alt="QR Code">
            </div>
            
            <div class="label-content">
                <div class="document-title">{{ $label->documentVersion->document->title }}</div>
                <div class="issued-to">Issued to: {{ $label->issuedTo->name }}</div>
                <div class="issued-date">Issued: {{ $label->issued_at->format('Y-m-d H:i') }}</div>
            </div>
        </div>
    @endforeach
</body>
</html>
