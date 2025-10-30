<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form Request Labels - Request #{{ $request->id }}</title>
    <style>
        @page {
            size: A4;
            margin: 8mm;
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .label {
            width: 63mm;
            height: 42mm;
            border: 1px solid #000;
            padding: 3mm;
            box-sizing: border-box;
            vertical-align: top;
        }
        
        .label-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            height: 100%;
        }
        
        .form-number {
            font-weight: bold;
            font-size: 10pt;
            color: #0066cc;
            margin-bottom: 1mm;
            word-wrap: break-word;
            max-width: 100%;
        }
        
        .form-name {
            font-size: 8pt;
            font-weight: bold;
            margin-bottom: 2mm;
            line-height: 1.1;
            color: #333;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            max-width: 100%;
        }
        
        .qr-code {
            width: 28mm;
            height: 28mm;
            margin: 1mm auto;
        }
        
        .label-footer {
            font-size: 7pt;
            color: #666;
            margin-top: auto;
        }
        
        .issue-date {
            margin-bottom: 0.5mm;
        }
        
        .request-info {
            font-size: 6pt;
            color: #999;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    @php
        $labelChunks = $labels->chunk(3);
        $totalRows = $labelChunks->count();
    @endphp
    
    <table>
        @foreach($labelChunks as $rowIndex => $row)
        <tr>
            @foreach($row as $label)
            <td class="label">
                <div class="label-content">
                    <div class="form-number">{{ $label['form_number'] }}</div>
                    <div class="form-name">{{ $label['form_name'] }}</div>
                    <img src="{{ $label['qr_code'] }}" class="qr-code" alt="QR Code">
                    <div class="label-footer">
                        <div class="issue-date">{{ \Carbon\Carbon::parse($label['issue_date'])->format('d/m/Y') }}</div>
                        <div class="request-info">Req #{{ $request->id }}</div>
                    </div>
                </div>
            </td>
            @endforeach
            
            {{-- Fill empty cells if row has less than 3 labels --}}
            @if($row->count() < 3)
                @for($i = 0; $i < 3 - $row->count(); $i++)
                <td class="label" style="border: 1px dashed #ccc;"></td>
                @endfor
            @endif
        </tr>
        
        {{-- Add page break after every 6 rows (18 labels per page) --}}
        @if(($rowIndex + 1) % 6 == 0 && ($rowIndex + 1) < $totalRows)
        <tr class="page-break"><td colspan="3"></td></tr>
        @endif
        @endforeach
    </table>
</body>
</html>

