<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC/RO Adonan - {{ $plan->plan_date->format('d M Y') }}</title>
    <link rel="stylesheet" href="/assets/tabler/css/tabler.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
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
            margin-bottom: 40px;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 15px;
            text-align: left;
        }
        table th {
            background-color: #f8f9fa;
            font-weight: bold;
            padding: 15px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .barcode-cell {
            text-align: center;
            padding: 20px 15px;
            min-width: 200px;
        }
        .barcode-cell svg {
            max-width: 250px;
            height: auto;
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
            const dateTimeStr = now.toLocaleString('id-ID', options);
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
                    <h2 class="page-title">JC/RO Adonan</h2>
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
                <div class="document-title">JOB COSTING & ROLL OVER ADONAN</div>
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
            </div>

            <!-- Job Costing Section -->
            <div class="mt-4">
                <h3 class="mb-3">JOB COSTING</h3>
                <h4 class="mb-3">Bahan Baku yang Digunakan untuk Memproduksi Adonan</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th width="200">Kode</th>
                                <th>Nama Bahan Baku</th>
                                <th class="text-right">Jumlah</th>
                                <th>Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jcData['rawMaterials'] ?? [] as $index => $material)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="barcode-cell">
                                    @if($material['item']->accurate_id)
                                        <svg class="barcode-{{ $index }}" data-barcode="{{ $material['item']->accurate_id }}"></svg>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $material['item']->name }}</td>
                                <td class="text-right">{{ number_format($material['quantity'], 3) }}</td>
                                <td>{{ $material['unit'] ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
            </div>

            <hr />

            <!-- Roll Over Section -->
            <div class="mt-5">
                <h3 class="mb-3">ROLL OVER</h3>
                <h4 class="mb-3">Adonan yang Diproduksi</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th width="200">Kode</th>
                                <th>Nama Adonan</th>
                                <th class="text-right">Jumlah</th>
                                <th class="text-right">Persentase (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roData['adonan'] ?? [] as $index => $item)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="barcode-cell">
                                    @if($item['item']->accurate_id)
                                        <svg class="barcode-ro-{{ $index }}" data-barcode="{{ $item['item']->accurate_id }}"></svg>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $item['item']->name }}</td>
                                <td class="text-right">{{ number_format($item['quantity'], 0) }}</td>
                                <td class="text-right">{{ number_format($item['percentage'], 2) }}%</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
            </div>
        </div>
    </div>

    <script src="/assets/tabler/js/tabler.min.js"></script>
    <script>
        // Generate Code 128 barcodes
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-barcode]').forEach(function(element) {
                const barcodeValue = element.getAttribute('data-barcode');
                if (barcodeValue) {
                    try {
                        JsBarcode(element, barcodeValue, {
                            format: "CODE128",
                            width: 3,
                            height: 80,
                            displayValue: true,
                            fontSize: 14,
                            margin: 10
                        });
                    } catch (e) {
                        console.error('Error generating barcode:', e);
                    }
                }
            });
        });
    </script>
</body>
</html>

