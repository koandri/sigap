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
                @can('maintenance.assets.manage')
                <div class="btn-list">
                    <a href="{{ route('maintenance.assets.edit', $asset) }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/>
                            <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/>
                            <path d="M16 5l3 3"/>
                        </svg>
                        Edit Asset
                    </a>
                    <a href="{{ route('maintenance.assets.qr-code', $asset) }}" class="btn btn-outline-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <rect x="4" y="4" width="6" height="6" rx="1"/>
                            <rect x="14" y="4" width="6" height="6" rx="1"/>
                            <rect x="4" y="14" width="6" height="6" rx="1"/>
                            <rect x="14" y="14" width="6" height="6" rx="1"/>
                        </svg>
                        QR Code
                    </a>
                </div>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-deck row-cards">
            <!-- Asset Information -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Asset Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <div class="form-control-plaintext">{{ $asset->name }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Code</label>
                                    <div class="form-control-plaintext">{{ $asset->code }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <div class="form-control-plaintext">
                                        <a href="{{ route('maintenance.asset-categories.show', $asset->assetCategory) }}">
                                            {{ $asset->assetCategory->name }}
                                        </a>
                                    </div>
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Location</label>
                                    <div class="form-control-plaintext">{{ $asset->location ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Serial Number</label>
                                    <div class="form-control-plaintext">{{ $asset->serial_number ?? '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Manufacturer</label>
                                    <div class="form-control-plaintext">{{ $asset->manufacturer ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Model</label>
                                    <div class="form-control-plaintext">{{ $asset->model ?? '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Purchase Date</label>
                                    <div class="form-control-plaintext">{{ $asset->purchase_date ? $asset->purchase_date->format('d M Y') : '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Warranty Expiry</label>
                                    <div class="form-control-plaintext">{{ $asset->warranty_expiry ? $asset->warranty_expiry->format('d M Y') : '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Department</label>
                                    <div class="form-control-plaintext">{{ $asset->department?->name ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Assigned To</label>
                                    <div class="form-control-plaintext">{{ $asset->user?->name ?? 'Unassigned' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-{{ $asset->is_active ? 'success' : 'secondary' }} text-white">
                                    {{ $asset->is_active ? 'Active' : 'Inactive' }}
                                </span>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/>
                                        <path d="M12 7v5l3 3"/>
                                    </svg>
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
                                            <td>{{ $log->maintenance_date->format('d M Y') }}</td>
                                            <td>{{ $log->maintenanceType->name }}</td>
                                            <td>{{ Str::limit($log->description, 50) }}</td>
                                            <td>{{ $log->performedByUser->name }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty">
                                <div class="empty-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"/>
                                        <rect x="9" y="3" width="6" height="4" rx="2"/>
                                    </svg>
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

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Asset Image -->
                @if($asset->image_path)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Asset Image</h3>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ Storage::url($asset->image_path) }}" alt="{{ $asset->name }}" class="img-fluid rounded">
                    </div>
                </div>
                @endif

                <!-- Quick Stats -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Quick Stats</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-muted small">Maintenance Schedules</div>
                            <div class="h3 mb-0">{{ $asset->maintenanceSchedules->count() }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Total Maintenance Logs</div>
                            <div class="h3 mb-0">{{ $asset->maintenanceLogs->count() }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Work Orders</div>
                            <div class="h3 mb-0">{{ $asset->workOrders->count() }}</div>
                        </div>
                        @if($asset->purchase_date)
                        <div class="mb-3">
                            <div class="text-muted small">Age</div>
                            <div class="h3 mb-0">{{ $asset->purchase_date->diffForHumans(null, true) }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

