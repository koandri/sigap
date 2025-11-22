@extends('layouts.app')

@section('title', 'Asset QR Code')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    QR Code - {{ $asset->name }}
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <button onclick="downloadQR()" class="btn btn-primary">
                        <i class="far fa-download"></i>&nbsp;
                        <span class="d-none d-sm-inline">Download QR (PNG)</span>
                        <span class="d-sm-none">Download</span>
                    </button>
                    <a href="{{ route('options.assets.show', $asset) }}" class="btn btn-outline-secondary">
                        Back to Asset
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Asset QR Code</h3>
                    </div>
                    <div class="card-body text-center">
                        <!-- QR Code -->
                        <div class="mb-4 d-inline-block" id="qrCodeContainer">
                            <img src="{{ $asset->qr_code_url }}" alt="QR Code for {{ $asset->code }}" id="qrCodeImage">
                        </div>

                        <!-- Asset Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Asset Name</label>
                                    <div class="form-control-plaintext">{{ $asset->name }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Asset Code</label>
                                    <div class="form-control-plaintext">{{ $asset->code }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <div class="form-control-plaintext">{{ $asset->assetCategory->name }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="form-control-plaintext">
                                        <span class="badge bg-{{ $asset->status === 'operational' ? 'success' : ($asset->status === 'down' ? 'danger' : 'warning') }} text-white">
                                            {{ ucfirst($asset->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($asset->location)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Location</label>
                                    <div class="form-control-plaintext">{{ $asset->location->name }}</div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Instructions -->
                        <div class="alert alert-info">
                            <h4 class="alert-title">QR Code Instructions</h4>
                            <div class="text-muted">
                                <p>This QR code can be scanned with any mobile device to quickly access asset information.</p>
                                <p><strong>For technicians:</strong> Scan this code to view asset details, maintenance history, and create work orders.</p>
                                <p><strong>For managers:</strong> Use this code to track asset status and maintenance schedules.</p>
                                <p><strong>Download:</strong> Click the "Download QR (PNG)" button above to save the QR code for printing or sharing.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
#qrCodeImage {
    max-width: 100%;
    height: auto;
}
</style>
@endpush

@push('scripts')
<script>
function downloadQR() {
    // Get the image element
    const imgElement = document.querySelector('#qrCodeImage');
    if (!imgElement) {
        alert('QR Code not found');
        return;
    }
    
    // Create download link
    const link = document.createElement('a');
    link.href = imgElement.src;
    link.download = 'asset-{{ $asset->code }}-qr.png';
    
    // Trigger download
    document.body.appendChild(link);
    link.click();
    
    // Cleanup
    document.body.removeChild(link);
}
</script>
@endpush
