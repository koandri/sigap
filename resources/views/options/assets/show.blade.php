@extends('layouts.app')

@section('title', 'Asset Details')

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
                        Edit
                    </a>
                    <a href="{{ route('options.assets.qr-code', $asset) }}" class="btn btn-outline-secondary">
                        <i class="far fa-qrcode"></i>&nbsp;
                        QR Code
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
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Pending Work Orders</div>
                        </div>
                        <div class="h1 mb-0 text-warning">
                            {{ $asset->workOrders()->whereNotIn('status', ['completed', 'cancelled', 'closed'])->count() }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Completed Work Orders</div>
                        </div>
                        <div class="h1 mb-0 text-success">
                            {{ $asset->workOrders()->whereIn('status', ['completed', 'closed'])->count() }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Maintenance Schedules</div>
                        </div>
                        <div class="h1 mb-0">{{ $asset->maintenanceSchedules->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Maintenance Logs</div>
                        </div>
                        <div class="h1 mb-0">{{ $asset->maintenanceLogs->count() }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asset Photos -->
        @if($asset->photos->count() > 0)
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Asset Photos</h3>
            </div>
            <div class="card-body">
                <div class="row g-2" id="photo-gallery">
                    @foreach($asset->photos as $photo)
                    <div class="col-md-3 col-sm-4 col-6 photo-item" data-photo-id="{{ $photo->id }}">
                        <div class="card {{ $photo->is_primary ? 'border-primary' : '' }}">
                            <img src="{{ Storage::disk('s3')->url($photo->photo_path) }}" 
                                 class="card-img-top" 
                                 style="height: 200px; object-fit: cover; cursor: pointer;" 
                                 alt="Photo"
                                 onclick="openPhotoModal('{{ Storage::disk('s3')->url($photo->photo_path) }}')">
                            <div class="card-body p-2">
                                <small class="text-muted d-block">
                                    Captured: {{ $photo->captured_at ? $photo->captured_at->setTimezone('Asia/Jakarta')->format('d M Y H:i') : '-' }}
                                </small>
                                <small class="text-muted d-block">
                                    Uploaded: {{ $photo->uploaded_at->setTimezone('Asia/Jakarta')->format('d M Y H:i') }}
                                </small>
                                @if($photo->is_primary)
                                    <span class="badge bg-primary mt-1">Primary</span>
                                @endif
                                @can('maintenance.assets.manage')
                                <div class="mt-2">
                                    @if(!$photo->is_primary)
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="setPrimaryPhoto({{ $asset->id }}, {{ $photo->id }})">
                                        Set Primary
                                    </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deletePhoto({{ $asset->id }}, {{ $photo->id }})">
                                        Delete
                                    </button>
                                </div>
                                @endcan
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Asset Information -->
        <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Asset Information</h3>
                    </div>
                    <div class="card-body">
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
                                    <div>{{ $asset->code }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
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

                        @if($asset->status === 'disposed')
                        <div class="alert alert-danger mb-3">
                            <h4 class="alert-title">🚫 Asset Disposed</h4>
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
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Department</label>
                                    <div>{{ $asset->department?->name ?? '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Purchase Date</label>
                                    <div>{{ $asset->purchase_date ? $asset->purchase_date->format('d M Y') : '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Warranty Expiry</label>
                                    <div>{{ $asset->warranty_expiry ? $asset->warranty_expiry->format('d M Y') : '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Assigned To</label>
                                    <div>{{ $asset->user?->name ?? 'Unassigned' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Active Status</label>
                                    <div>
                                        <span class="badge bg-{{ $asset->is_active ? 'success' : 'secondary' }} text-white">
                                            {{ $asset->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
            </div>
        </div>

        <!-- Specifications -->
        @if($asset->specifications && count($asset->specifications) > 0)
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Specifications</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($asset->specifications as $key => $value)
                        @if(!empty($value))
                        <div class="col-md-6 col-lg-4">
                            <div class="mb-2">
                                <label class="form-label fw-bold text-capitalize">{{ str_replace('_', ' ', $key) }}</label>
                                <div>{{ $value }}</div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Work Orders Section with Tabs -->
        <div class="card mt-3">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
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
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Pending Work Orders Tab -->
                            <div class="tab-pane active show" id="pending-wo">
                                @php
                                    $pendingWorkOrders = $asset->workOrders()
                                        ->whereNotIn('status', ['completed', 'cancelled', 'closed'])
                                        ->with(['maintenanceType', 'assignedUser', 'requestedBy'])
                                        ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
                                        ->orderBy('created_at', 'desc')
                                        ->get();
                                @endphp
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
                                @php
                                    $completedWorkOrders = $asset->workOrders()
                                        ->whereIn('status', ['completed', 'closed'])
                                        ->with(['maintenanceType', 'assignedUser', 'verifiedBy'])
                                        ->orderBy('completed_date', 'desc')
                                        ->take(10)
                                        ->get();
                                @endphp
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

        <!-- Maintenance Schedules -->
        <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Maintenance Schedules</h3>
                    </div>
                    <div class="card-body">
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
        </div>

        <!-- Maintenance History -->
        <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Recent Maintenance History</h3>
                    </div>
                    <div class="card-body">
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
                                        @foreach($asset->maintenanceLogs->take(10) as $log)
                                        <tr>
                                            <td>{{ $log->maintenance_date?->format('d M Y') ?? '-' }}</td>
                                            <td>{{ $log->maintenanceType?->name ?? '-' }}</td>
                                            <td>{{ Str::limit($log->description ?? '-', 50) }}</td>
                                            <td>{{ $log->performedByUser?->name ?? '-' }}</td>
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

<!-- Photo Modal -->
<div class="modal modal-blur fade" id="photo-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modal-photo-img" src="" alt="Photo" class="img-fluid">
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openPhotoModal(imageUrl) {
    document.getElementById('modal-photo-img').src = imageUrl;
    const modal = new bootstrap.Modal(document.getElementById('photo-modal'));
    modal.show();
}

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
</script>
@endsection
