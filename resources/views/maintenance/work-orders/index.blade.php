@extends('layouts.app')

@section('title', 'Work Orders')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    Work Orders
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                @can('create', App\Models\WorkOrder::class)
                <div class="btn-list">
                    <a href="{{ route('maintenance.work-orders.create') }}" class="btn btn-primary d-none d-sm-inline-block">
                        <i class="far fa-plus"></i>&nbsp;
                        Create Work Order
                    </a>
                </div>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <!-- Upcoming Maintenance Schedules -->
        @if($upcomingSchedules->count() > 0)
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="far fa-calendar-alt me-2"></i>&nbsp;Upcoming Maintenance Schedules (Next 14 Days)
                </h3>
                <div class="card-actions">
                    <small class="text-muted">{{ $upcomingSchedules->count() }} schedule(s) due soon</small>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Due Date</th>
                                <th>Asset</th>
                                <th>Maintenance Type</th>
                                <th>Frequency</th>
                                <th>Assigned To</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingSchedules as $schedule)
                            <tr>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold">{{ $schedule->next_due_date->format('M d, Y') }}</span>
                                        <small class="text-muted">{{ $schedule->next_due_date->format('H:i') }}</small>
                                        @php
                                            $daysUntil = now()->diffInDays($schedule->next_due_date, false);
                                        @endphp
                                        @if($daysUntil < 0)
                                            <span class="badge bg-danger mt-1">Overdue</span>
                                        @elseif($daysUntil == 0)
                                            <span class="badge bg-warning mt-1">Today</span>
                                        @elseif($daysUntil == 1)
                                            <span class="badge bg-info mt-1">Tomorrow</span>
                                        @else
                                            <span class="badge bg-secondary mt-1">{{ $daysUntil }} days</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <div class="fw-bold">{{ $schedule->asset->name }}</div>
                                            <div class="text-muted small">{{ $schedule->asset->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary text-white">{{ $schedule->maintenanceType->name }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $schedule->frequency_description }}</small>
                                </td>
                                <td>{{ $schedule->assignedUser?->name ?? 'Unassigned' }}</td>
                                <td>
                                    @php
                                        // Check if there's already an open work order for this schedule
                                        $existingWO = \App\Models\WorkOrder::where('asset_id', $schedule->asset_id)
                                            ->where('maintenance_type_id', $schedule->maintenance_type_id)
                                            ->whereIn('status', ['submitted', 'assigned', 'in-progress', 'pending-verification'])
                                            ->first();
                                    @endphp
                                    
                                    @if($existingWO)
                                        <a href="{{ route('maintenance.work-orders.show', $existingWO) }}" class="badge bg-info text-white text-decoration-none">
                                            WO Exists
                                        </a>
                                    @else
                                        <span class="badge bg-light text-dark">Scheduled</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-list">
                                        @if(!$existingWO)
                                            @can('create', App\Models\WorkOrder::class)
                                            <form action="{{ route('maintenance.schedules.trigger', $schedule) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary" title="Generate Work Order Now">
                                                    <i class="far fa-plus me-1"></i>&nbsp;Create WO
                                                </button>
                                            </form>
                                            @endcan
                                        @else
                                            <a href="{{ route('maintenance.work-orders.show', $existingWO) }}" class="btn btn-sm btn-outline-primary">
                                                View WO
                                            </a>
                                        @endif
                                        <a href="{{ route('maintenance.schedules.show', $schedule) }}" class="btn btn-sm btn-outline-secondary" title="View Schedule Details">
                                            <i class="far fa-eye"></i>&nbsp;
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="far fa-filter me-2"></i>&nbsp;Filter Work Orders
                </h3>
            </div>
            <div class="card-body">
                <form method="GET">
                    <div class="row g-3">
                        <!-- Search -->
                        <div class="col-12 col-md-6 col-lg-4">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="WO number or description">
                        </div>
                        
                        <!-- Status -->
                        <div class="col-12 col-md-6 col-lg-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                                <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                                <option value="in-progress" {{ request('status') === 'in-progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="pending-verification" {{ request('status') === 'pending-verification' ? 'selected' : '' }}>Pending Verification</option>
                                <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="rework" {{ request('status') === 'rework' ? 'selected' : '' }}>Rework</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        
                        <!-- Priority -->
                        <div class="col-12 col-md-6 col-lg-4">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="">All Priority</option>
                                <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                            </select>
                        </div>
                        
                        <!-- Requested By -->
                        <div class="col-12 col-md-6 col-lg-4">
                            <label class="form-label">Requested By</label>
                            <select name="requested_by" class="form-select">
                                <option value="">All Users</option>
                                @foreach($requestedUsers as $user)
                                    <option value="{{ $user->id }}" {{ request('requested_by') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Assigned To -->
                        <div class="col-12 col-md-6 col-lg-4">
                            <label class="form-label">Assigned To</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">All Operators</option>
                                @foreach($assignedUsers as $user)
                                    <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="col-12 col-md-6 col-lg-4">
                            <label class="form-label d-none d-lg-block">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="far fa-search me-1"></i>&nbsp;
                                    Apply Filters
                                </button>
                                <a href="{{ route('maintenance.work-orders.index') }}" class="btn btn-outline-secondary">
                                    <i class="far fa-times"></i>&nbsp;
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Work Orders Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Work Orders ({{ $workOrders->total() }})</h3>
            </div>
            <div class="card-body">
                @if($workOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>WO Number</th>
                                    <th>Asset</th>
                                    <th>Type</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($workOrders as $workOrder)
                                <tr>
                                    <td>
                                        <a href="{{ route('maintenance.work-orders.show', $workOrder) }}" class="text-decoration-none">
                                            {{ $workOrder->wo_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <div class="fw-bold">{{ $workOrder->asset->name }}</div>
                                                <div class="text-muted">{{ $workOrder->asset->code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary text-white">{{ $workOrder->maintenanceType->name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $workOrder->priority === 'urgent' ? 'danger' : ($workOrder->priority === 'high' ? 'warning' : ($workOrder->priority === 'medium' ? 'info' : 'secondary')) }} text-white">
                                            {{ ucfirst($workOrder->priority) }}
                                        </span>
                                    </td>
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
                                        <span class="badge bg-{{ $statusColors[$workOrder->status] ?? 'secondary' }} text-white">
                                            {{ ucfirst(str_replace('-', ' ', $workOrder->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $workOrder->assignedUser?->name ?? 'Unassigned' }}</td>
                                    <td>{{ $workOrder->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-list">
                                            @can('view', $workOrder)
                                            <a href="{{ route('maintenance.work-orders.show', $workOrder) }}" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                            @endcan
                                            @can('update', $workOrder)
                                            <a href="{{ route('maintenance.work-orders.edit', $workOrder) }}" class="btn btn-sm btn-outline-secondary">
                                                Edit
                                            </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $workOrders->links() }}
                    </div>
                @else
                    <div class="empty">
                        <div class="empty-icon">
                            <i class="far fa-clipboard icon"></i>&nbsp;
                        </div>
                        <p class="empty-title">No work orders found</p>
                        <p class="empty-subtitle text-muted">
                            Get started by creating your first work order.
                        </p>
                        @can('create', App\Models\WorkOrder::class)
                        <div class="empty-action">
                            <a href="{{ route('maintenance.work-orders.create') }}" class="btn btn-primary">
                                <i class="far fa-plus"></i>&nbsp;
                                Create Work Order
                            </a>
                        </div>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

