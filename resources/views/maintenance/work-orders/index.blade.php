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
                @can('maintenance.work-orders.create')
                <div class="btn-list">
                    <a href="{{ route('maintenance.work-orders.create') }}" class="btn btn-primary d-none d-sm-inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M12 5l0 14"/>
                            <path d="M5 12l14 0"/>
                        </svg>
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
        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="WO number or description">
                    </div>
                    <div class="col-md-2">
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
                    <div class="col-md-2">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="">All Priority</option>
                            <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                            <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Assigned To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('maintenance.work-orders.index') }}" class="btn btn-outline-secondary">Clear</a>
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
                                        <span class="badge bg-secondary">{{ $workOrder->maintenanceType->name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $workOrder->priority === 'urgent' ? 'danger' : ($workOrder->priority === 'high' ? 'warning' : ($workOrder->priority === 'medium' ? 'info' : 'secondary')) }}">
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
                                        <span class="badge bg-{{ $statusColors[$workOrder->status] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('-', ' ', $workOrder->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $workOrder->assignedUser?->name ?? 'Unassigned' }}</td>
                                    <td>{{ $workOrder->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-list">
                                            <a href="{{ route('maintenance.work-orders.show', $workOrder) }}" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                            @can('maintenance.work-orders.create')
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"/>
                                <rect x="9" y="3" width="6" height="4" rx="2"/>
                            </svg>
                        </div>
                        <p class="empty-title">No work orders found</p>
                        <p class="empty-subtitle text-muted">
                            Get started by creating your first work order.
                        </p>
                        @can('maintenance.work-orders.create')
                        <div class="empty-action">
                            <a href="{{ route('maintenance.work-orders.create') }}" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 5l0 14"/>
                                    <path d="M5 12l14 0"/>
                                </svg>
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

