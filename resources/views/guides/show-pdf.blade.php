<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }

        .guide-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #206bc4;
        }

        .guide-header h1 {
            font-size: 24pt;
            font-weight: bold;
            color: #206bc4;
        }

        .guide-body {
            margin-top: 20px;
        }

        .guide-body h1 {
            font-size: 20pt;
            font-weight: bold;
            margin-top: 30px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
            color: #206bc4;
        }

        .guide-body h2 {
            font-size: 16pt;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e9ecef;
        }

        .guide-body h3 {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .guide-body h4 {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 8px;
        }

        .guide-body p {
            margin-bottom: 12px;
            text-align: justify;
        }

        .guide-body ul,
        .guide-body ol {
            margin-bottom: 12px;
            margin-left: 30px;
        }

        .guide-body li {
            margin-bottom: 6px;
        }

        .guide-body code {
            background-color: #f1f3f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10pt;
            font-family: 'Courier New', monospace;
            color: #000;
        }

        .guide-body pre {
            background-color: #f1f3f5;
            padding: 12px;
            border-radius: 5px;
            overflow-x: auto;
            margin-bottom: 12px;
            font-family: 'Courier New', monospace;
            font-size: 10pt;
            color: #000;
        }

        .guide-body pre code {
            background-color: transparent;
            padding: 0;
            color: #000;
        }

        .guide-body table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
            font-size: 10pt;
        }

        .guide-body table th,
        .guide-body table td {
            padding: 8px;
            border: 1px solid #dee2e6;
        }

        .guide-body table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .guide-body blockquote {
            border-left: 4px solid #206bc4;
            padding-left: 15px;
            margin-left: 0;
            margin-bottom: 12px;
            color: #6c757d;
        }

        .guide-body a {
            color: #206bc4;
            text-decoration: underline;
        }

        .page-break {
            page-break-after: always;
        }

        @media print {
            .guide-body {
                page-break-inside: avoid;
            }

            .guide-body h1,
            .guide-body h2,
            .guide-body h3 {
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="guide-header">
        <h1>{{ $title }}</h1>
    </div>

    <div class="guide-body">
        {!! $content !!}
    </div>
</body>
</html>


