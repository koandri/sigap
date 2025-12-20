<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Request Labels - Request #{{ $request->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 10px;
        }
        
        .print-info {
            background: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .print-info h2 {
            margin-bottom: 10px;
            color: #333;
        }
        
        .print-info p {
            color: #666;
            margin-bottom: 5px;
        }
        
        .print-button {
            background: #0066cc;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
        }
        
        .print-button:hover {
            background: #0052a3;
        }
        
        .label-row {
            width: 108mm;
            height: 28mm;
            display: flex;
            justify-content: space-around;
            align-items: center;
            background: white;
            margin-bottom: 10px;
            page-break-after: always;
            page-break-inside: avoid;
        }
        
        .label {
            width: 36mm;
            height: 28mm;
            border: none;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .label.empty {
            border: none;
            visibility: hidden;
        }
        
        .label-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-evenly;
            padding: 0;
            margin: 0;
            width: 100%;
        }
        
        .qr-code {
            width: 19mm;
            height: 19mm;
            margin: 0;
            display: block;
        }
        
        .form-number {
            font-weight: bold;
            font-size: 7pt;
            color: #0066cc;
            text-align: center;
            padding: 0;
            margin: 0;
        }
        
        /* Print styles */
        @media print {
            @page {
                size: 108mm 28mm;
                margin: 0;
            }
            
            body {
                background: white;
                padding: 0;
            }
            
            .print-info {
                display: none;
            }
            
            .label-row {
                margin: 0;
                page-break-after: always;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="print-info">
        <h2>Print Labels - Request #{{ $request->id }}</h2>
        <p><strong>Total Labels:</strong> {{ $labels->count() }}</p>
        <p><strong>Rows:</strong> {{ ceil($labels->count() / 3) }}</p>
        <p><strong>Instructions:</strong></p>
        <ul>
            <li>Click the Print button below</li>
            <li>Select your label printer</li>
            <li>Set paper size to: <strong>108mm × 28mm</strong> (or Custom: 10.8cm × 2.8cm)</li>
            <li>Set margins to: <strong>None</strong></li>
            <li>Print!</li>
        </ul>
        <button class="print-button" onclick="window.print()">
            🖨️ Print Labels
        </button>
    </div>
    
    @php
        $labelChunks = $labels->chunk(3);
    @endphp
    
    @foreach($labelChunks as $row)
    <div class="label-row">
        @foreach($row as $label)
        <div class="label">
            <div class="label-content">
                <img src="{{ $label['qr_code'] }}" class="qr-code" alt="QR Code">
                <div class="form-number">{{ $label['form_number'] }}</div>
            </div>
        </div>
        @endforeach
        
        {{-- Fill empty slots if row has less than 3 labels --}}
        @if($row->count() < 3)
            @for($i = 0; $i < 3 - $row->count(); $i++)
            <div class="label empty"></div>
            @endfor
        @endif
    </div>
    @endforeach
</body>
</html>

