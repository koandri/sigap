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
                    <button onclick="window.print()" class="btn btn-primary d-none d-sm-inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-2v6z"/>
                            <path d="M7 7h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-2v6z"/>
                            <path d="M7 17v-6a2 2 0 0 1 2 -2h2"/>
                            <path d="M17 7v-6a2 2 0 0 0 -2 -2h-2"/>
                        </svg>
                        Print QR Code
                    </button>
                    <a href="{{ route('maintenance.assets.show', $asset) }}" class="btn btn-outline-secondary">
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
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Asset QR Code</h3>
                    </div>
                    <div class="card-body text-center">
                        <!-- QR Code -->
                        <div class="mb-4 position-relative d-inline-block">
                            {!! $qrCode !!}
                            @if($hasIcon ?? false)
                            <div class="qr-icon-overlay">
                                <img src="{{ asset('imgs/qr_icon.png') }}" alt="Icon" class="qr-center-icon">
                            </div>
                            @endif
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
                                        <span class="badge bg-{{ $asset->status === 'operational' ? 'success' : ($asset->status === 'down' ? 'danger' : 'warning') }}">
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
                                    <div class="form-control-plaintext">{{ $asset->location }}</div>
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
                            </div>
                        </div>

                        <!-- Print Instructions -->
                        <div class="alert alert-secondary d-print-block">
                            <h4 class="alert-title">Print Instructions</h4>
                            <div class="text-muted">
                                <p>Print this QR code and attach it to the physical asset for easy identification and maintenance tracking.</p>
                                <p>Recommended print size: 2" x 2" (5cm x 5cm) or larger for easy scanning.</p>
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
.qr-icon-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 8px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.qr-center-icon {
    width: 60px;
    height: 60px;
    display: block;
    object-fit: contain;
}

@media print {
    .page-header,
    .btn-list,
    .alert:not(.d-print-block) {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .container-xl {
        max-width: none !important;
    }
    
    .qr-icon-overlay {
        padding: 4px;
    }
    
    .qr-center-icon {
        width: 50px;
        height: 50px;
    }
}
</style>
@endpush
