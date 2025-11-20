<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Perintah Kerja Produksi Basah - {{ $plan->plan_date->format('d M Y') }}</title>
    <link rel="stylesheet" href="/assets/tabler/css/tabler.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" />
    <style>
        @media print {
            .d-print-none { display: none !important; }
            body { margin: 0; padding: 20px; }
            .page-header { margin-bottom: 20px; }
        }
        .document-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        .document-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .document-subtitle {
            font-size: 18px;
            color: #666;
        }
        .info-section {
            margin-bottom: 30px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            width: 200px;
        }
        .info-value {
            flex: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 48px;
            font-weight: bold;
            color: rgba(0, 0, 0, 0.08);
            z-index: -1;
            text-align: center;
            pointer-events: none;
            line-height: 1.6;
            white-space: nowrap;
        }
        .watermark-line {
            display: block;
        }
        @media print {
            .watermark {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="watermark" id="watermark">
        <span class="watermark-line">PT. SURYA INTI ANEKA PANGAN</span>
        <span class="watermark-line">{{ auth()->user()->name }}</span>
        <span class="watermark-line" id="printDateTime"></span>
    </div>
    <script>
        function updatePrintDateTime() {
            const now = new Date();
            const options = { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit',
                hour12: false
            };
            const dateTimeStr = now.toLocaleDateString('id-ID', options) + ' ' + 
                               now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
            document.getElementById('printDateTime').textContent = dateTimeStr;
        }
        updatePrintDateTime();
        setInterval(updatePrintDateTime, 1000);
        window.addEventListener('beforeprint', updatePrintDateTime);
    </script>
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">Surat Perintah Kerja Produksi Basah</h2>
                </div>
                <div class="col-auto ms-auto">
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="far fa-print me-2"></i>Print
                    </button>
                    <a href="{{ route('manufacturing.production-plans.show', $plan) }}" class="btn btn-outline-secondary">
                        <i class="far fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="document-header">
                <div class="document-title">SURAT PERINTAH KERJA PRODUKSI BASAH</div>
                <div class="document-subtitle">PT. SURYA INTI ANEKA PANGAN</div>
            </div>

            <div class="info-section">
                <div class="info-row">
                    <div class="info-label">Tanggal Rencana:</div>
                    <div class="info-value">{{ $plan->plan_date->format('d F Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Mulai Produksi:</div>
                    <div class="info-value">{{ $plan->production_start_date->format('d F Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Siap:</div>
                    <div class="info-value">{{ $plan->ready_date->format('d F Y') }}</div>
                </div>
            </div>

            <h3 class="mt-4 mb-3">Adonan yang Diproduksi</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Adonan</th>
                        <th class="text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adonan as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item['item']->name }}</td>
                        <td class="text-right">{{ number_format($item['quantity'], 0) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <h3 class="mt-4 mb-3">Bahan Baku yang Digunakan</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Bahan Baku</th>
                        <th class="text-right">Jumlah</th>
                        <th>Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rawMaterials as $index => $material)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $material['item']->name }}</td>
                        <td class="text-right">{{ number_format($material['quantity'], 3) }}</td>
                        <td>{{ $material['unit'] ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <h3 class="mt-4 mb-3">Gelondongan yang Diproduksi (Dikelompokkan berdasarkan Lokasi)</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Gelondongan</th>
                        <th class="text-right">GL1</th>
                        <th class="text-right">GL2</th>
                        <th class="text-right">TA</th>
                        <th class="text-right">BL</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gelondongan as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item['item']->name }}</td>
                        <td class="text-right">{{ number_format($item['locations']['GL1'], 0) }}</td>
                        <td class="text-right">{{ number_format($item['locations']['GL2'], 0) }}</td>
                        <td class="text-right">{{ number_format($item['locations']['TA'], 0) }}</td>
                        <td class="text-right">{{ number_format($item['locations']['BL'], 0) }}</td>
                        <td class="text-right"><strong>{{ number_format($item['total'], 0) }}</strong></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>


