@extends('layouts.app')

@section('title', 'Asset Details')

@push('css')
<style>
@media print {
    .d-print-none,
    .btn,
    .dropdown,
    .nav-tabs,
    .card-header {
        display: none !important;
    }
    
    .card {
        border: none;
        box-shadow: none;
        page-break-inside: avoid;
    }
    
    .page-header {
        border-bottom: 2px solid #000;
        margin-bottom: 1rem;
    }
    
    .tab-content > .tab-pane {
        display: block !important;
    }
    
    .tab-content > .tab-pane:not(.active) {
        display: none !important;
    }
}
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    {{ $asset->name }}
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    @can('maintenance.work-orders.create')
                    <a href="{{ route('maintenance.work-orders.create', ['asset_id' => $asset->id]) }}" class="btn btn-primary">
                        <i class="far fa-plus"></i>&nbsp;
                        Create Work Order
                    </a>
                    @endcan
                    @can('maintenance.assets.manage')
                    <a href="{{ route('options.assets.edit', $asset) }}" class="btn btn-outline-secondary">
                        <i class="far fa-pen"></i>&nbsp;
                        Edit Asset
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <!-- Quick Stats Row -->
        <div class="row row-cards mb-3">
            <div class="col-sm-6 col-lg-2">
                <div class="card cursor-pointer" onclick="document.getElementById('pending-wo-tab').click()" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">
                                <i class="far fa-clock text-warning me-1"></i>
                                Pending Work Orders
                            </div>
                        </div>
                        <div class="h1 mb-0 text-warning">
                            {{ $pendingWorkOrdersCount }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-2">
                <div class="card cursor-pointer" onclick="document.getElementById('completed-wo-tab').click()" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">
                                <i class="far fa-check-circle text-success me-1"></i>
                                Completed Work Orders
                            </div>
                        </div>
                        <div class="h1 mb-0 text-success">
                            {{ $completedWorkOrdersCount }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-2">
                <div class="card cursor-pointer" onclick="document.getElementById('maintenance-schedules-tab').click()" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">
                                <i class="far fa-calendar-alt text-info me-1"></i>
                                Maintenance Schedules
                            </div>
                        </div>
                        <div class="h1 mb-0">{{ $asset->maintenanceSchedules->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-2">
                <div class="card cursor-pointer" onclick="document.getElementById('maintenance-history-tab').click()" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">
                                <i class="far fa-clipboard-list text-primary me-1"></i>
                                Maintenance Logs
                            </div>
                        </div>
                        <div class="h1 mb-0">{{ $asset->maintenanceLogs->count() }}</div>
                    </div>
                </div>
            </div>
            @if($nextMaintenanceDue)
            <div class="col-sm-6 col-lg-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">
                                <i class="far fa-calendar-check text-danger me-1"></i>
                                Next Maintenance Due
                            </div>
                        </div>
                        <div class="h1 mb-0 text-danger">
                            {{ $nextMaintenanceDue->next_due_date?->format('d M') ?? '-' }}
                        </div>
                        <small class="text-muted">{{ $nextMaintenanceDue->next_due_date?->diffForHumans() ?? '' }}</small>
                    </div>
                </div>
            </div>
            @endif
            <div class="col-sm-6 col-lg-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">
                                <i class="far fa-puzzle-piece text-secondary me-1"></i>
                                Components
                            </div>
                        </div>
                        <div class="h1 mb-0">{{ $componentStatusSummary['total'] }}</div>
                        <small class="text-muted">{{ $componentStatusSummary['active'] }} active</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asset Information -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Asset Information</h3>
            </div>
            <div class="card-body p-0">
                <ul class="nav nav-tabs nav-fill border-bottom" role="tablist" style="padding-left: 1.5rem; padding-right: 1.5rem; padding-top: 0.5rem;">
                    <li class="nav-item">
                        <a class="nav-link active" href="#basic-info" data-bs-toggle="tab">
                            Basic Information
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#technical-details" data-bs-toggle="tab">
                            Technical Details
                        </a>
                    </li>
                </ul>
                <div class="tab-content p-3">
                    <!-- Basic Information Tab -->
                    <div class="tab-pane active show" id="basic-info">
                        @if($asset->status === 'disposed')
                        <div class="alert alert-danger mb-3">
                            <h4 class="alert-title"><i class="far fa-ban"></i> Asset Disposed</h4>
                            <div class="text-secondary">
                                <strong>Disposal Date:</strong> {{ $asset->disposed_date?->format('M d, Y') }}<br>
                                @if($asset->disposedBy)
                                <strong>Disposed By:</strong> {{ $asset->disposedBy->name }}<br>
                                @endif
                                @if($asset->disposalWorkOrder)
                                <strong>Related Work Order:</strong> 
                                <a href="{{ route('maintenance.work-orders.show', $asset->disposalWorkOrder) }}" class="alert-link">
                                    {{ $asset->disposalWorkOrder->wo_number }}
                                </a><br>
                                @endif
                                @if($asset->disposal_reason)
                                <strong>Reason:</strong> {{ $asset->disposal_reason }}
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($asset->warranty_expiry && $asset->warranty_expiry->isFuture() && $asset->warranty_expiry->diffInDays(now()) <= 90)
                        <div class="alert alert-warning mb-3">
                            <h4 class="alert-title"><i class="far fa-exclamation-triangle"></i> Warranty Expiring Soon</h4>
                            <div class="text-secondary">
                                Warranty expires in <strong>{{ $asset->warranty_expiry->diffForHumans() }}</strong> ({{ $asset->warranty_expiry->format('d M Y') }})
                            </div>
                        </div>
                        @endif

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Name</label>
                                    <div>{{ $asset->name }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Code</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <span>{{ $asset->code }}</span>
                                        <button class="btn btn-sm btn-icon" onclick="copyToClipboard('{{ $asset->code }}')" title="Copy Asset Code">
                                            <i class="far fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <div>
                                        @php
                                            $statusConfig = [
                                                'operational' => ['color' => 'success', 'icon' => 'check-circle'],
                                                'down' => ['color' => 'danger', 'icon' => 'exclamation-circle'],
                                                'disposed' => ['color' => 'dark', 'icon' => 'ban'],
                                                'maintenance' => ['color' => 'warning', 'icon' => 'wrench'],
                                            ];
                                            $config = $statusConfig[$asset->status] ?? ['color' => 'secondary', 'icon' => 'question-circle'];
                                        @endphp
                                        <span class="badge bg-{{ $config['color'] }} text-white fs-6">
                                            <i class="far fa-{{ $config['icon'] }}"></i>
                                            {{ ucfirst($asset->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Category</label>
                                    <div>
                                        <a href="{{ route('options.asset-categories.show', $asset->assetCategory) }}">
                                            {{ $asset->assetCategory->name }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Location</label>
                                    <div>{{ $asset->location->name ?? '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Department</label>
                                    <div>{{ $asset->department?->name ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Assigned To</label>
                                    <div>{{ $asset->user?->name ?? 'Unassigned' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Purchase Date</label>
                                    <div>
                                        @if($asset->purchase_date)
                                            {{ $asset->purchase_date->format('d M Y') }}
                                            <small class="text-muted">({{ $asset->purchase_date->diffForHumans() }})</small>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Warranty Expiry</label>
                                    <div>
                                        @if($asset->warranty_expiry)
                                            {{ $asset->warranty_expiry->format('d M Y') }}
                                            <small class="text-muted">({{ $asset->warranty_expiry->isFuture() ? 'expires ' . $asset->warranty_expiry->diffForHumans() : 'expired ' . $asset->warranty_expiry->diffForHumans() }})</small>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <div>
                                        <span class="badge bg-{{ $asset->status === 'operational' ? 'success' : ($asset->status === 'down' ? 'danger' : ($asset->status === 'disposed' ? 'dark' : 'warning')) }} text-white">
                                            {{ ucfirst($asset->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Asset Photos Section -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <hr class="my-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="mb-0">Asset Photos</h4>
                                    <div class="d-flex gap-2 align-items-center">
                                        @if($asset->photos->count() > 0)
                                            <span class="badge bg-primary text-white">{{ $asset->photos->count() }} photo(s)</span>
                                        @endif
                                        @can('maintenance.assets.manage')
                                        <a href="{{ route('options.assets.edit', $asset) }}" class="btn btn-sm btn-primary">
                                            <i class="far fa-plus"></i>&nbsp;Upload Photos
                                        </a>
                                        @endcan
                                    </div>
                                </div>
                                
                                @if($asset->photos->count() > 0)
                                    @php
                                        // Sort photos to show primary photo first
                                        $sortedPhotos = $asset->photos->sortByDesc('is_primary')->values();
                                    @endphp
                                    <div class="row g-3" id="photo-gallery">
                                        @foreach($sortedPhotos as $index => $photo)
                                            @if($photo->file_path)
                                            <div class="col-md-3 col-sm-4 col-6 photo-item" data-photo-id="{{ $photo->id }}">
                                                <div class="card h-100 position-relative">
                                                    @php
                                                        $photoUrl = Storage::disk('s3')->url($photo->file_path);
                                                        $photoTitle = $photo->is_primary ? 'Primary Photo' : 'Photo ' . ($index + 1);
                                                        if ($photo->captured_at) {
                                                            $photoTitle .= ' - ' . $photo->captured_at->setTimezone('Asia/Jakarta')->format('d M Y H:i');
                                                        }
                                                    @endphp
                                                    <a href="{{ $photoUrl }}" 
                                                       data-lightbox="asset-photos-{{ $asset->id }}" 
                                                       data-title="{{ $photoTitle }}"
                                                       class="d-block">
                                                        <img src="{{ $photoUrl }}" 
                                                             class="card-img-top" 
                                                             style="height: 200px; object-fit: cover; cursor: zoom-in;" 
                                                             alt="{{ $photoTitle }}"
                                                             loading="lazy">
                                                    </a>
                                                    @if($photo->is_primary)
                                                        <span class="badge bg-primary text-white position-absolute top-0 start-0 m-2">
                                                            <i class="far fa-star"></i> Primary
                                                        </span>
                                                    @endif
                                                    <div class="card-body p-2">
                                                        <small class="text-muted d-block">
                                                            <strong>Captured:</strong> {{ $photo->captured_at ? $photo->captured_at->setTimezone('Asia/Jakarta')->format('d M Y H:i') : '-' }}
                                                        </small>
                                                        <small class="text-muted d-block">
                                                            <strong>Uploaded:</strong> {{ $photo->uploaded_at->setTimezone('Asia/Jakarta')->format('d M Y H:i') }}
                                                        </small>
                                                        @can('maintenance.assets.manage')
                                                        <div class="mt-2 d-flex gap-1">
                                                            @if(!$photo->is_primary)
                                                            <button type="button" class="btn btn-sm btn-outline-primary flex-fill" onclick="setPrimaryPhoto({{ $asset->id }}, {{ $photo->id }})">
                                                                <i class="far fa-star"></i>&nbsp;Set Primary
                                                            </button>
                                                            @endif
                                                            <button type="button" class="btn btn-sm btn-outline-danger {{ $photo->is_primary ? 'w-100' : '' }}" onclick="deletePhoto({{ $asset->id }}, {{ $photo->id }})">
                                                                <i class="far fa-trash"></i>
                                                            </button>
                                                        </div>
                                                        @endcan
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="empty">
                                        <div class="empty-icon">
                                            <i class="far fa-images"></i>
                                        </div>
                                        <p class="empty-title">No photos available</p>
                                        <p class="empty-subtitle text-muted">
                                            This asset doesn't have any photos yet.
                                        </p>
                                        @can('maintenance.assets.manage')
                                        <div class="empty-action">
                                            <a href="{{ route('options.assets.edit', $asset) }}" class="btn btn-primary">
                                                <i class="far fa-plus"></i>
                                                Add Photos
                                            </a>
                                        </div>
                                        @endcan
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- QR Code Section -->
                        @if($asset->qr_code_url)
                        <div class="row mb-3">
                            <div class="col-12">
                                <hr class="my-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="mb-0">QR Code</h4>
                                    <button onclick="downloadQR()" class="btn btn-sm btn-primary">
                                        <i class="far fa-download"></i>&nbsp;Download QR Code
                                    </button>
                                </div>
                                <div class="text-center">
                                    <div class="mb-3 d-inline-block" id="qrCodeContainer">
                                        <img src="{{ $asset->qr_code_url }}" 
                                             alt="QR Code for {{ $asset->code }}" 
                                             id="qrCodeImage"
                                             style="max-width: 300px; height: auto;">
                                    </div>
                                    <p class="text-muted small">
                                        Scan this QR code to quickly access asset information on mobile devices.
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Technical Details Tab -->
                    <div class="tab-pane" id="technical-details">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Serial Number</label>
                                    <div>{{ $asset->serial_number ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Manufacturer</label>
                                    <div>{{ $asset->manufacturer ?? '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Model</label>
                                    <div>{{ $asset->model ?? '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Component & Lifetime Information -->
                        @if($asset->isComponent() || $asset->hasComponents() || $asset->lifetime_unit)
                        <div class="row mb-3">
                            @if($asset->isComponent() && $asset->parentAsset)
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Parent Asset</label>
                                    <div>
                                        <a href="{{ route('options.assets.show', $asset->parentAsset) }}">
                                            {{ $asset->parentAsset->name }} ({{ $asset->parentAsset->code }})
                                        </a>
                                        @if($asset->component_type)
                                            <span class="badge bg-info ms-2">{{ $asset->component_type->label() }}</span>
                                        @endif
                                    </div>
                                    @if($asset->installed_date)
                                        <small class="text-muted">Installed: {{ $asset->installed_date->format('d M Y') }}</small>
                                    @endif
                                </div>
                            </div>
                            @endif



                            @if($asset->lifetime_unit)
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Lifetime Unit</label>
                                    <div>
                                        <span class="badge bg-info">{{ $asset->lifetime_unit->label() }}</span>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($asset->expected_lifetime_value)
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Expected Lifetime</label>
                                    <div>
                                        {{ number_format($asset->expected_lifetime_value, 2) }}
                                        @if($asset->lifetime_unit)
                                            {{ $asset->lifetime_unit->label() }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($asset->actual_lifetime_value)
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Actual Lifetime</label>
                                    <div>
                                        {{ number_format($asset->actual_lifetime_value, 2) }}
                                        @if($asset->lifetime_unit)
                                            {{ $asset->lifetime_unit->label() }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                            @php
                                $lifetimePercentage = $asset->getLifetimePercentage();
                                $remainingLifetime = $asset->getRemainingLifetime();
                            @endphp

                            @if($lifetimePercentage !== null)
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Lifetime Used</label>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar {{ $lifetimePercentage >= 100 ? 'bg-danger' : ($lifetimePercentage >= 80 ? 'bg-warning' : 'bg-success') }}" 
                                             role="progressbar" 
                                             style="width: {{ min(100, $lifetimePercentage) }}%" 
                                             aria-valuenow="{{ $lifetimePercentage }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            {{ number_format($lifetimePercentage, 1) }}%
                                        </div>
                                    </div>
                                    @if($remainingLifetime !== null)
                                        <small class="text-muted">Remaining: {{ number_format($remainingLifetime, 2) }} {{ $asset->lifetime_unit?->label() ?? '' }}</small>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif

                        <!-- Specifications Section -->
                        @if($asset->specifications)
                            @php
                                $specsArray = [];
                                if (is_object($asset->specifications) && method_exists($asset->specifications, 'toArray')) {
                                    $specsArray = $asset->specifications->toArray();
                                } elseif (is_array($asset->specifications)) {
                                    $specsArray = $asset->specifications;
                                }
                                
                                // Handle malformed data - if we only have one key with a concatenated string value
                                // Try to parse it as key-value pairs (e.g., "440Vpower: 38940Wweight: 263kgdimensions: {...}")
                                if (count($specsArray) === 1) {
                                    $firstKey = array_key_first($specsArray);
                                    $firstValue = $specsArray[$firstKey];
                                    if (is_string($firstValue) && (stripos($firstValue, 'power:') !== false || stripos($firstValue, 'weight:') !== false)) {
                                        $parsed = [];
                                        $value = html_entity_decode($firstValue, ENT_QUOTES, 'UTF-8');
                                        
                                        // Extract voltage (everything before "power:")
                                        if (preg_match('/^(.+?)(?=power:|$)/i', $value, $matches)) {
                                            $voltage = trim($matches[1]);
                                            if (!empty($voltage) && preg_match('/\d/', $voltage)) {
                                                $parsed['voltage'] = $voltage;
                                            }
                                        }
                                        
                                        // Extract power (stop at next label like "weight:" or "dimensions:", even without space)
                                        if (preg_match('/power:\s*([0-9]+[^\s]*(?:W|kW|MW)?)(?=(?:weight|dimensions):|$)/i', $value, $matches)) {
                                            $parsed['power'] = trim($matches[1]);
                                        }
                                        
                                        // Extract weight (stop at next label like "dimensions:", even without space)
                                        if (preg_match('/weight:\s*([0-9]+[^\s]*(?:kg|g|lbs|oz)?)(?=dimensions:|$)/i', $value, $matches)) {
                                            $parsed['weight'] = trim($matches[1]);
                                        }
                                        
                                        // Extract dimensions JSON
                                        if (preg_match('/dimensions:\s*(\{[^}]+\})/i', $value, $matches)) {
                                            $dimJson = json_decode($matches[1], true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($dimJson)) {
                                                $parsed['dimensions'] = $dimJson;
                                            }
                                        }
                                        
                                        if (!empty($parsed) && count($parsed) > 1) {
                                            $specsArray = $parsed;
                                        }
                                    }
                                }
                            @endphp
                            @if(!empty($specsArray))
                            <div class="row mb-3">
                                <div class="col-12">
                                    <hr class="my-4">
                                    <h4 class="mb-3">Specifications</h4>
                                    <div class="row g-3">
                                        @foreach($specsArray as $key => $value)
                                            @if($value !== null && $value !== '')
                                            @php
                                                // Clean up string values to remove trailing labels (with or without space before label)
                                                if (is_string($value)) {
                                                    // Remove labels that appear at the end (e.g., "38940Wweight:" -> "38940W")
                                                    $value = preg_replace('/([0-9]+[^\s]*(?:W|kW|MW|kg|g|lbs|oz)?)(?:power|weight|dimensions|voltage):/i', '$1', $value);
                                                    // Remove any remaining trailing labels
                                                    $value = preg_replace('/\s*(?:power|weight|dimensions|voltage):\s*$/i', '', $value);
                                                    $value = trim($value);
                                                }
                                            @endphp
                                            <div class="col-12 col-md-6 col-lg-4">
                                                <div class="mb-3">
                                                    <div class="fw-bold text-capitalize mb-1" style="font-size: 0.875rem; color: #6c757d;">
                                                        {{ str_replace('_', ' ', $key) }}
                                                    </div>
                                                    <div class="text-muted" style="word-wrap: break-word; word-break: break-word;">
                                                        @if(is_array($value))
                                                            @if(isset($value['length']) && isset($value['width']) && isset($value['height']))
                                                                {{ $value['length'] }} × {{ $value['width'] }} × {{ $value['height'] }}{{ isset($value['unit']) ? ' ' . $value['unit'] : '' }}
                                                            @else
                                                                {{ json_encode($value) }}
                                                            @endif
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Components Section -->
        @if($asset->hasComponents() || !$asset->isComponent())
        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Components</h3>
                    @can('maintenance.assets.manage')
                    &nbsp;
                    <a href="{{ route('assets.components', $asset) }}" class="btn btn-sm btn-primary">
                        <i class="far fa-puzzle-piece"></i>&nbsp;Manage Components
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                @if($asset->hasComponents())
                    <div class="mb-3">
                        <span class="badge bg-success">{{ $componentStatusSummary['active'] }} Active</span>
                        @if($componentStatusSummary['inactive'] > 0)
                            <span class="badge bg-secondary">{{ $componentStatusSummary['inactive'] }} Inactive</span>
                        @endif
                        <span class="text-muted ms-2">Total: {{ $componentStatusSummary['total'] }} components</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Installed Date</th>
                                    <th>Status</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($asset->childAssets as $component)
                                <tr>
                                    <td>
                                        <a href="{{ route('options.assets.show', $component) }}">
                                            {{ $component->code }}
                                        </a>
                                    </td>
                                    <td>{{ $component->name }}</td>
                                    <td>
                                        @if($component->component_type)
                                            <span class="badge bg-info">{{ $component->component_type->label() }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $component->installed_date ? $component->installed_date->format('d M Y') : '-' }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $component->status === 'operational' ? 'success' : ($component->status === 'down' ? 'danger' : ($component->status === 'disposed' ? 'dark' : 'warning')) }} text-white">
                                            {{ ucfirst($component->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('options.assets.show', $component) }}" class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty">
                        <div class="empty-icon">
                            <i class="far fa-puzzle-piece"></i>
                        </div>
                        <p class="empty-title">No components attached</p>
                        <p class="empty-subtitle text-muted">
                            This asset has no child components.
                        </p>
                        @can('maintenance.assets.manage')
                        <div class="empty-action">
                            <a href="{{ route('assets.components', $asset) }}" class="btn btn-primary">
                                <i class="far fa-plus"></i>
                                Attach Component
                            </a>
                        </div>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Lifetime Metrics Link -->
        @if($asset->lifetime_unit || $asset->expected_lifetime_value || $asset->actual_lifetime_value)
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Lifetime Metrics</h3>
            </div>
            <div class="card-body">
                <a href="{{ route('assets.lifetime', $asset) }}" class="btn btn-outline-primary">
                    <i class="far fa-chart-line"></i>&nbsp;
                    View Lifetime Report
                </a>
            </div>
        </div>
        @endif

        <!-- Work Orders Section with Tabs -->
        <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Work Orders</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="nav nav-tabs nav-fill border-bottom" role="tablist" style="padding-left: 1.5rem; padding-right: 1.5rem; padding-top: 0.5rem;">
                            <li class="nav-item">
                                <a class="nav-link active" href="#pending-wo" data-bs-toggle="tab">
                                    Pending Work Orders
                                    @php
                                        $pendingCount = $asset->workOrders()->whereNotIn('status', ['completed', 'cancelled', 'closed'])->count();
                                    @endphp
                                    @if($pendingCount > 0)
                                        <span class="badge bg-warning text-dark ms-1">{{ $pendingCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#completed-wo" data-bs-toggle="tab">
                                    Completed Work Orders
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content p-3">
                            <!-- Pending Work Orders Tab -->
                            <div class="tab-pane active show" id="pending-wo">
                                @if($pendingWorkOrders->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-vcenter">
                                            <thead>
                                                <tr>
                                                    <th>WO Number</th>
                                                    <th>Type</th>
                                                    <th>Priority</th>
                                                    <th>Status</th>
                                                    <th>Assigned To</th>
                                                    <th>Created</th>
                                                    <th class="w-1"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($pendingWorkOrders as $workOrder)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('maintenance.work-orders.show', $workOrder) }}" class="text-reset fw-bold">
                                                            {{ $workOrder->wo_number }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $workOrder->maintenanceType->name }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $workOrder->priority === 'urgent' ? 'danger' : ($workOrder->priority === 'high' ? 'warning' : ($workOrder->priority === 'medium' ? 'info' : 'secondary')) }} text-white">
                                                            {{ ucfirst($workOrder->priority) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $workOrder->status === 'open' ? 'warning' : ($workOrder->status === 'assigned' ? 'info' : ($workOrder->status === 'in_progress' ? 'primary' : 'secondary')) }} text-white">
                                                            {{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $workOrder->assignedUser?->name ?? 'Unassigned' }}</td>
                                                    <td>{{ $workOrder->created_at->format('d M Y') }}</td>
                                                    <td>
                                                        <a href="{{ route('maintenance.work-orders.show', $workOrder) }}" class="btn btn-sm btn-outline-primary">
                                                            View
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="empty">
                                        <div class="empty-icon">
                                            <i class="far fa-clipboard icon"></i>&nbsp;
                                        </div>
                                        <p class="empty-title">No pending work orders</p>
                                        <p class="empty-subtitle text-muted">
                                            There are no pending work orders for this asset.
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Completed Work Orders Tab -->
                            <div class="tab-pane" id="completed-wo">
                                @if($completedWorkOrders->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-vcenter">
                                            <thead>
                                                <tr>
                                                    <th>WO Number</th>
                                                    <th>Type</th>
                                                    <th>Completed Date</th>
                                                    <th>Completed By</th>
                                                    <th>Duration</th>
                                                    <th class="w-1"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($completedWorkOrders as $workOrder)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('maintenance.work-orders.show', $workOrder) }}" class="text-reset fw-bold">
                                                            {{ $workOrder->wo_number }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $workOrder->maintenanceType->name }}</td>
                                                    <td>{{ $workOrder->completed_date?->format('d M Y H:i') ?? '-' }}</td>
                                                    <td>{{ $workOrder->verifiedBy?->name ?? $workOrder->assignedUser?->name ?? '-' }}</td>
                                                    <td>
                                                        @if($workOrder->work_started_at && $workOrder->work_finished_at)
                                                            {{ $workOrder->work_started_at->diffForHumans($workOrder->work_finished_at, true) }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('maintenance.work-orders.show', $workOrder) }}" class="btn btn-sm btn-outline-primary">
                                                            View
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @php
                                        $totalCompleted = $asset->workOrders()->whereIn('status', ['completed', 'closed'])->count();
                                    @endphp
                                    @if($totalCompleted > 10)
                                    <div class="mt-3 text-center">
                                        <a href="{{ route('maintenance.work-orders.index', ['asset_id' => $asset->id, 'status' => 'completed']) }}" class="btn btn-outline-primary">
                                            View All Completed Work Orders ({{ $totalCompleted }})
                                        </a>
                                    </div>
                                    @endif
                                @else
                                    <div class="empty">
                                        <div class="empty-icon">
                                            <i class="far fa-clipboard icon"></i>&nbsp;
                                        </div>
                                        <p class="empty-title">No completed work orders</p>
                                        <p class="empty-subtitle text-muted">
                                            No work orders have been completed for this asset yet.
                                        </p>
                                    </div>
                                @endif
                            </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Section with Tabs -->
        <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Maintenance</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="nav nav-tabs nav-fill border-bottom" role="tablist" style="padding-left: 1.5rem; padding-right: 1.5rem; padding-top: 0.5rem;">
                            <li class="nav-item">
                                <a class="nav-link active" href="#maintenance-schedules" data-bs-toggle="tab">
                                    Maintenance Schedules
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#maintenance-history" data-bs-toggle="tab">
                                    Recent Maintenance History
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content p-3">
                            <!-- Maintenance Schedules Tab -->
                            <div class="tab-pane active show" id="maintenance-schedules">
                                @if($asset->maintenanceSchedules->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-vcenter">
                                            <thead>
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Frequency</th>
                                                    <th>Next Due</th>
                                                    <th>Assigned To</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($asset->maintenanceSchedules as $schedule)
                                                <tr>
                                                    <td>{{ $schedule->maintenanceType->name }}</td>
                                                    <td>{{ ucfirst($schedule->frequency_type->value) }}</td>
                                                    <td>{{ $schedule->next_due_date?->format('d M Y') ?? '-' }}</td>
                                                    <td>{{ $schedule->assignedUser?->name ?? 'Unassigned' }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $schedule->is_active ? 'success' : 'secondary' }} text-white">
                                                            {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="empty">
                                        <div class="empty-icon">
                                            <i class="far fa-clock icon"></i>&nbsp;
                                        </div>
                                        <p class="empty-title">No maintenance schedules</p>
                                        <p class="empty-subtitle text-muted">
                                            No maintenance schedules have been set up for this asset.
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Maintenance History Tab -->
                            <div class="tab-pane" id="maintenance-history">
                                @if($asset->maintenanceLogs->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-vcenter">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Type</th>
                                                    <th>Description</th>
                                                    <th>Performed By</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($asset->maintenanceLogs as $log)
                                                <tr>
                                                    <td>
                                                        {{ $log->performed_at?->format('d M Y') ?? '-' }}
                                                        @if($log->performed_at)
                                                            <small class="text-muted d-block">({{ $log->performed_at->diffForHumans() }})</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($log->workOrder && $log->workOrder->maintenanceType)
                                                            {{ $log->workOrder->maintenanceType->name }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>{{ Str::limit($log->action_taken ?? '-', 50) }}</td>
                                                    <td>{{ $log->performedBy?->name ?? '-' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @php
                                        $totalLogs = $asset->maintenanceLogs()->count();
                                    @endphp
                                    @if($totalLogs > 10)
                                    <div class="mt-3 text-center">
                                        <a href="{{ route('maintenance.logs.asset', $asset) }}" class="btn btn-outline-primary">
                                            View All Maintenance History ({{ $totalLogs }})
                                        </a>
                                    </div>
                                    @endif
                                @else
                                    <div class="empty">
                                        <div class="empty-icon">
                                            <i class="far fa-clipboard icon"></i>&nbsp;
                                        </div>
                                        <p class="empty-title">No maintenance history</p>
                                        <p class="empty-subtitle text-muted">
                                            No maintenance has been performed on this asset yet.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// Copy to clipboard function
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show toast notification
        const toast = document.createElement('div');
        toast.className = 'toast show position-fixed top-0 end-0 m-3';
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="toast-header bg-success text-white">
                <i class="far fa-check me-2"></i>
                <strong class="me-auto">Copied!</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                Asset code copied to clipboard
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }).catch(function(err) {
        alert('Failed to copy: ' + err);
    });
}

// Fix tab click handlers for quick stats
document.addEventListener('DOMContentLoaded', function() {
    // Fix tab IDs for click handlers
    const pendingTab = document.querySelector('a[href="#pending-wo"]');
    const completedTab = document.querySelector('a[href="#completed-wo"]');
    const schedulesTab = document.querySelector('a[href="#maintenance-schedules"]');
    const historyTab = document.querySelector('a[href="#maintenance-history"]');
    
    if (pendingTab) pendingTab.id = 'pending-wo-tab';
    if (completedTab) completedTab.id = 'completed-wo-tab';
    if (schedulesTab) schedulesTab.id = 'maintenance-schedules-tab';
    if (historyTab) historyTab.id = 'maintenance-history-tab';
});

function setPrimaryPhoto(assetId, photoId) {
    if (!confirm('Set this photo as primary?')) return;
    
    fetch(`/options/assets/${assetId}/photos/${photoId}/primary`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to set primary photo: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while setting primary photo');
    });
}

function deletePhoto(assetId, photoId) {
    if (!confirm('Are you sure you want to delete this photo?')) return;
    
    fetch(`/options/assets/${assetId}/photos/${photoId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to delete photo: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting photo');
    });
}

// Download QR code function
function downloadQR() {
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
