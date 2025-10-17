@extends('layouts.app')

@section('title', 'Maintenance Dashboard')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    Dashboard
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <!-- Statistics Cards -->
        <div class="row row-deck row-cards mb-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Assets</div>
                        </div>
                        <div class="h1 mb-3">{{ $totalAssets }}</div>
                        <div class="d-flex mb-2">
                            <div class="text-muted">
                                <a href="{{ route('maintenance.assets.index') }}" class="text-reset">View all assets</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Active Work Orders</div>
                        </div>
                        <div class="h1 mb-3">{{ $activeWorkOrders }}</div>
                        <div class="d-flex mb-2">
                            <div class="text-muted">
                                <a href="{{ route('maintenance.work-orders.index') }}" class="text-reset">View all work orders</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Overdue Schedules</div>
                        </div>
                        <div class="h1 mb-3 text-danger">{{ $overdueSchedules }}</div>
                        <div class="d-flex mb-2">
                            <div class="text-muted">
                                <a href="{{ route('maintenance.schedules.index') }}" class="text-reset">View schedules</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Upcoming (7 days)</div>
                        </div>
                        <div class="h1 mb-3 text-warning">{{ $upcomingSchedules }}</div>
                        <div class="d-flex mb-2">
                            <div class="text-muted">
                                <a href="{{ route('maintenance.calendar') }}" class="text-reset">View calendar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-deck row-cards mb-3">
            <!-- Recent Work Orders -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Work Orders</h3>
                    </div>
                    <div class="card-body">
                        @if($recentWorkOrders->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>WO Number</th>
                                            <th>Asset</th>
                                            <th>Status</th>
                                            <th>Priority</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentWorkOrders as $workOrder)
                                        <tr>
                                            <td>
                                                <a href="{{ route('maintenance.work-orders.show', $workOrder) }}">
                                                    {{ $workOrder->wo_number }}
                                                </a>
                                            </td>
                                            <td>{{ $workOrder->asset->name }}</td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'submitted' => 'secondary',
                                                        'assigned' => 'info',
                                                        'in-progress' => 'warning',
                                                        'pending-verification' => 'primary',
                                                        'verified' => 'success',
                                                        'completed' => 'success',
                                                        'rework' => 'danger',
                                                        'cancelled' => 'danger'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$workOrder->status] ?? 'secondary' }}">
                                                    {{ ucfirst(str_replace('-', ' ', $workOrder->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $workOrder->priority === 'urgent' ? 'danger' : ($workOrder->priority === 'high' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($workOrder->priority) }}
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
                                        <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"/>
                                        <rect x="9" y="3" width="6" height="4" rx="2"/>
                                    </svg>
                                </div>
                                <p class="empty-title">No work orders found</p>
                                <p class="empty-subtitle text-muted">
                                    Create your first work order to get started.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Upcoming Maintenance -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Upcoming Maintenance</h3>
                    </div>
                    <div class="card-body">
                        @if($upcomingMaintenance->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>Asset</th>
                                            <th>Type</th>
                                            <th>Due Date</th>
                                            <th>Assigned To</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($upcomingMaintenance as $schedule)
                                        <tr>
                                            <td>{{ $schedule->asset->name }}</td>
                                            <td>{{ $schedule->maintenanceType->name }}</td>
                                            <td>
                                                <span class="text-{{ $schedule->next_due_date < now() ? 'danger' : 'muted' }}">
                                                    {{ $schedule->next_due_date->format('M d, Y') }}
                                                </span>
                                            </td>
                                            <td>{{ $schedule->assignedUser?->name ?? 'Unassigned' }}</td>
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
                                <p class="empty-title">No upcoming maintenance</p>
                                <p class="empty-subtitle text-muted">
                                    All maintenance schedules are up to date.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Asset Status Chart -->
        <div class="row row-deck row-cards">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Asset Status Distribution</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <div class="text-center">
                                    <div class="h1 text-success">{{ $assetStatusCounts['operational'] ?? 0 }}</div>
                                    <div class="text-muted">Operational</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center">
                                    <div class="h1 text-warning">{{ $assetStatusCounts['maintenance'] ?? 0 }}</div>
                                    <div class="text-muted">Maintenance</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center">
                                    <div class="h1 text-danger">{{ $assetStatusCounts['down'] ?? 0 }}</div>
                                    <div class="text-muted">Down</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Work Order Priority Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Work Order Priority</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-3">
                                <div class="text-center">
                                    <div class="h1 text-danger">{{ $workOrderPriorityCounts['urgent'] ?? 0 }}</div>
                                    <div class="text-muted">Urgent</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-center">
                                    <div class="h1 text-warning">{{ $workOrderPriorityCounts['high'] ?? 0 }}</div>
                                    <div class="text-muted">High</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-center">
                                    <div class="h1 text-info">{{ $workOrderPriorityCounts['medium'] ?? 0 }}</div>
                                    <div class="text-muted">Medium</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-center">
                                    <div class="h1 text-secondary">{{ $workOrderPriorityCounts['low'] ?? 0 }}</div>
                                    <div class="text-muted">Low</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

