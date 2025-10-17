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
                        <i class="fa-regular fa-plus"></i>
                        Create Work Order
                    </a>
                    @endcan
                    @can('maintenance.assets.manage')
                    <a href="{{ route('maintenance.assets.edit', $asset) }}" class="btn btn-outline-secondary">
                        <i class="fa-regular fa-pen"></i>
                        Edit
                    </a>
                    <a href="{{ route('maintenance.assets.qr-code', $asset) }}" class="btn btn-outline-secondary">
                        <i class="fa-regular fa-qrcode"></i>
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
                                        <a href="{{ route('maintenance.asset-categories.show', $asset->assetCategory) }}">
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
                                            <i class="fa-regular fa-clipboard icon"></i>
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
                                            <i class="fa-regular fa-clipboard icon"></i>
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
                                    <i class="fa-regular fa-clock icon"></i>
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
                                    <i class="fa-regular fa-clipboard icon"></i>
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
@endsection
