@extends('layouts.app')

@section('title', 'Maintenance Schedule Details')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Maintenance Management</div>
                <h2 class="page-title">Maintenance Schedule Details</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                @can('maintenance.schedules.manage')
                    <a href="{{ route('maintenance.schedules.edit', $schedule) }}" class="btn btn-primary">
                        <i class="far fa-edit"></i>&nbsp; Edit Schedule
                    </a>
                @endcan
                <a href="{{ route('maintenance.schedules.index') }}" class="btn btn-outline-primary">
                    <i class="far fa-arrow-left"></i>&nbsp; Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        
        @include('layouts.alerts')

        <div class="row">
            <div class="col-lg-8">
                <!-- Schedule Information -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Schedule Information</h3>
                        <div class="card-actions">
                            @if($schedule->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Asset</label>
                                <div class="fw-bold">
                                    <a href="{{ route('options.assets.show', $schedule->asset) }}">
                                        {{ $schedule->asset->name }}
                                    </a>
                                    @if($schedule->asset->code)
                                        <span class="text-muted">({{ $schedule->asset->code }})</span>
                                    @endif
                                </div>
                                @if($schedule->asset->assetCategory)
                                    <small class="text-muted">{{ $schedule->asset->assetCategory->name }}</small>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Maintenance Type</label>
                                <div class="fw-bold">
                                    <span class="badge" style="background-color: {{ $schedule->maintenanceType->color }}">
                                        {{ $schedule->maintenanceType->name }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Frequency</label>
                                <div class="fw-bold">
                                    <i class="far fa-clock me-1"></i>&nbsp;
                                    {{ $schedule->frequency_description }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Assigned To</label>
                                <div class="fw-bold">
                                    {{ $schedule->assignedUser?->name ?? 'Unassigned' }}
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Next Due Date</label>
                                <div class="fw-bold">
                                    <span class="text-{{ $schedule->next_due_date < now() ? 'danger' : ($schedule->next_due_date < now()->addDays(7) ? 'warning' : 'muted') }}">
                                        {{ $schedule->next_due_date->format('d M Y, g:ia') }}
                                    </span>
                                    @if($schedule->next_due_date < now())
                                        <span class="badge bg-danger ms-2">Overdue</span>
                                    @elseif($schedule->next_due_date < now()->addDays(7))
                                        <span class="badge bg-warning ms-2">Upcoming</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Last Performed</label>
                                <div class="fw-bold">
                                    {{ $schedule->last_performed_at ? $schedule->last_performed_at->format('d M Y, g:ia') : 'Never' }}
                                </div>
                            </div>
                        </div>

                        @if($schedule->description)
                            <div class="mb-0">
                                <label class="form-label text-muted">Description</label>
                                <div>{{ $schedule->description }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Checklist -->
                @if($schedule->checklist && count($schedule->checklist) > 0)
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Checklist Items</h3>
                            <div class="card-actions">
                                <span class="text-muted">{{ count($schedule->checklist) }} item(s)</span>
                            </div>
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach($schedule->checklist as $index => $item)
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-blue-lt me-2">{{ $index + 1 }}</span>
                                                <div>{{ $item }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Related Work Orders -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Related Work Orders</h3>
                    </div>
                    @if($workOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Work Order Number</th>
                                        <th>Scheduled Date</th>
                                        <th>Assigned To</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($workOrders as $workOrder)
                                        <tr>
                                            <td>
                                                <a href="{{ route('maintenance.work-orders.show', $workOrder) }}">
                                                    {{ $workOrder->wo_number }}
                                                </a>
                                            </td>
                                            <td>
                                                {{ $workOrder->scheduled_date ? $workOrder->scheduled_date->format('d M Y') : '-' }}
                                            </td>
                                            <td>{{ $workOrder->assignedUser?->name ?? 'Unassigned' }}</td>
                                            <td>
                                                @if($workOrder->status === 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($workOrder->status === 'verified')
                                                    <span class="badge bg-info">Verified</span>
                                                @elseif($workOrder->status === 'pending-verification')
                                                    <span class="badge bg-warning">Pending Verification</span>
                                                @elseif($workOrder->status === 'in-progress')
                                                    <span class="badge bg-primary">In Progress</span>
                                                @elseif($workOrder->status === 'assigned')
                                                    <span class="badge bg-blue">Assigned</span>
                                                @elseif($workOrder->status === 'rework')
                                                    <span class="badge bg-orange">Rework</span>
                                                @elseif($workOrder->status === 'cancelled')
                                                    <span class="badge bg-secondary">Cancelled</span>
                                                @else
                                                    <span class="badge bg-secondary">Submitted</span>
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
                        <div class="card-body">
                            <div class="text-center text-muted py-4">
                                <i class="far fa-inbox fa-3x mb-3"></i>&nbsp;
                                <p>No work orders have been generated yet for this schedule.</p>
                                <small>Work orders are automatically generated when the schedule becomes due, or you can manually trigger one.</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Schedule Statistics -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Statistics</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Status</div>
                            <div class="h3 mb-0">
                                <span class="badge bg-{{ $schedule->is_active ? 'success' : 'secondary' }}">
                                    {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Checklist Items</div>
                            <div class="h3 mb-0">{{ count($schedule->checklist ?? []) }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Total Work Orders</div>
                            <div class="h3 mb-0">{{ $workOrders->count() }}</div>
                        </div>
                        <div class="mb-0">
                            <div class="text-muted small mb-1">Days Until Due</div>
                            <div class="h3 mb-0 {{ $schedule->next_due_date < now() ? 'text-danger' : ($schedule->next_due_date < now()->addDays(7) ? 'text-warning' : 'text-success') }}">
                                @php
                                    $now = now();
                                    $dueDate = $schedule->next_due_date;
                                    $diffInHours = round($now->diffInHours($dueDate, false));
                                    $diffInDays = round($now->diffInDays($dueDate, false));
                                    $diffInWeeks = round($now->diffInWeeks($dueDate, false));
                                    $diffInMonths = round($now->diffInMonths($dueDate, false));
                                    
                                    if ($dueDate < $now) {
                                        // Overdue
                                        if (abs($diffInHours) < 24) {
                                            $hours = abs($diffInHours);
                                            $display = $hours . ' hour' . ($hours != 1 ? 's' : '') . ' overdue';
                                        } elseif (abs($diffInDays) < 7) {
                                            $days = abs($diffInDays);
                                            $display = $days . ' day' . ($days != 1 ? 's' : '') . ' overdue';
                                        } elseif (abs($diffInWeeks) < 4) {
                                            $weeks = abs($diffInWeeks);
                                            $display = $weeks . ' week' . ($weeks != 1 ? 's' : '') . ' overdue';
                                        } else {
                                            $months = abs($diffInMonths);
                                            $display = $months . ' month' . ($months != 1 ? 's' : '') . ' overdue';
                                        }
                                    } else {
                                        // Not yet due
                                        if ($diffInHours < 1) {
                                            $display = 'Due now';
                                        } elseif ($diffInHours < 20) {
                                            $display = 'Due in ' . $diffInHours . ' hour' . ($diffInHours != 1 ? 's' : '');
                                        } elseif ($diffInHours < 24 || $diffInDays == 1) {
                                            $display = 'Due tomorrow';
                                        } elseif ($diffInDays < 7) {
                                            $display = 'Due in ' . $diffInDays . ' days';
                                        } elseif ($diffInWeeks < 4) {
                                            $display = 'Due in ' . $diffInWeeks . ' week' . ($diffInWeeks != 1 ? 's' : '');
                                        } elseif ($diffInMonths < 12) {
                                            $display = 'Due in ' . $diffInMonths . ' month' . ($diffInMonths != 1 ? 's' : '');
                                        } else {
                                            $diffInYears = round($now->diffInYears($dueDate, false));
                                            $display = 'Due in ' . $diffInYears . ' year' . ($diffInYears != 1 ? 's' : '');
                                        }
                                    }
                                @endphp
                                {{ $display }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Schedule Details -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="far fa-calendar-alt"></i>&nbsp; Schedule Details
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">
                            <strong>Frequency:</strong><br>
                            {{ $schedule->frequency_description }}
                        </p>
                        <p class="text-muted small mb-2">
                            <strong>Next Due:</strong><br>
                            {{ $schedule->next_due_date->format('d M Y, g:ia') }}
                        </p>
                        @if($schedule->last_performed_at)
                            <p class="text-muted small mb-0">
                                <strong>Last Performed:</strong><br>
                                {{ $schedule->last_performed_at->format('d M Y, g:ia') }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @can('maintenance.schedules.manage')
                                <a href="{{ route('maintenance.schedules.edit', $schedule) }}" class="btn btn-primary">
                                    <i class="far fa-edit"></i>&nbsp; Edit Schedule
                                </a>
                                @if($schedule->is_active)
                                    <form action="{{ route('maintenance.schedules.trigger', $schedule) }}" method="POST" 
                                          onsubmit="return confirm('Generate a work order for this schedule now?');">
                                        @csrf
                                        <button type="submit" class="btn btn-warning w-100">
                                            <i class="far fa-bolt"></i>&nbsp; Trigger Work Order
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('maintenance.schedules.destroy', $schedule) }}" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this schedule? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100">
                                        <i class="far fa-trash"></i>&nbsp; Delete Schedule
                                    </button>
                                </form>
                            @endcan
                            <a href="{{ route('maintenance.work-orders.index') }}?asset={{ $schedule->asset_id }}&type={{ $schedule->maintenance_type_id }}" class="btn btn-outline-primary">
                                <i class="far fa-list"></i>&nbsp; View All Work Orders
                            </a>
                            <a href="{{ route('options.assets.show', $schedule->asset) }}" class="btn btn-outline-secondary">
                                <i class="far fa-box"></i>&nbsp; View Asset
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

