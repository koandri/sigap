@extends('layouts.app')

@section('title', 'Work Order: ' . $workOrder->wo_number)

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    Work Order: {{ $workOrder->wo_number }}
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('maintenance.work-orders.index') }}" class="btn btn-secondary">
                        Back to List
                    </a>
                    @can('maintenance.work-orders.create')
                    <a href="{{ route('maintenance.work-orders.edit', $workOrder) }}" class="btn btn-outline-primary">
                        Edit
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row">
            <div class="col-md-8">
                <!-- Work Order Details -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Work Order Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">WO Number</label>
                                    <div class="form-control-plaintext">{{ $workOrder->wo_number }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div>
                                        <span class="badge bg-{{ $workOrder->status === 'completed' ? 'success' : ($workOrder->status === 'in-progress' ? 'warning' : ($workOrder->status === 'cancelled' ? 'danger' : 'secondary')) }}">
                                            {{ ucfirst($workOrder->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Asset</label>
                                    <div class="form-control-plaintext">
                                        <a href="{{ route('maintenance.assets.show', $workOrder->asset) }}" class="text-decoration-none">
                                            {{ $workOrder->asset->name }} ({{ $workOrder->asset->code }})
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Maintenance Type</label>
                                    <div class="form-control-plaintext">{{ $workOrder->maintenanceType->name }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Priority</label>
                                    <div>
                                        <span class="badge bg-{{ $workOrder->priority === 'urgent' ? 'danger' : ($workOrder->priority === 'high' ? 'warning' : ($workOrder->priority === 'medium' ? 'info' : 'secondary')) }}">
                                            {{ ucfirst($workOrder->priority) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Assigned To</label>
                                    <div class="form-control-plaintext">{{ $workOrder->assignedUser?->name ?? 'Unassigned' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Scheduled Date</label>
                                    <div class="form-control-plaintext">
                                        {{ $workOrder->scheduled_date ? $workOrder->scheduled_date->format('M d, Y H:i') : 'Not scheduled' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Completed Date</label>
                                    <div class="form-control-plaintext">
                                        {{ $workOrder->completed_date ? $workOrder->completed_date->format('M d, Y H:i') : 'Not completed' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Estimated Hours</label>
                                    <div class="form-control-plaintext">{{ $workOrder->estimated_hours ?? 'Not specified' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Actual Hours</label>
                                    <div class="form-control-plaintext">{{ $workOrder->actual_hours ?? 'Not completed' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <div class="form-control-plaintext">{{ $workOrder->description }}</div>
                        </div>

                        @if($workOrder->notes)
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <div class="form-control-plaintext">{{ $workOrder->notes }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Parts Used -->
                @if($workOrder->parts->count() > 0)
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Parts Used</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Quantity Used</th>
                                        <th>Warehouse</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($workOrder->parts as $part)
                                    <tr>
                                        <td>{{ $part->item->name }}</td>
                                        <td>{{ $part->quantity_used }}</td>
                                        <td>{{ $part->warehouse->name }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Maintenance Logs -->
                @if($workOrder->maintenanceLogs->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Maintenance History</h3>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @foreach($workOrder->maintenanceLogs as $log)
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-title">{{ $log->performedBy->name }}</span>
                                        <span class="timeline-time">{{ $log->performed_at->format('M d, Y H:i') }}</span>
                                    </div>
                                    <div class="timeline-body">
                                        <p><strong>Action Taken:</strong> {{ $log->action_taken }}</p>
                                        @if($log->findings)
                                            <p><strong>Findings:</strong> {{ $log->findings }}</p>
                                        @endif
                                        @if($log->recommendations)
                                            <p><strong>Recommendations:</strong> {{ $log->recommendations }}</p>
                                        @endif
                                        @if($log->cost > 0)
                                            <p><strong>Cost:</strong> ${{ number_format($log->cost, 2) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Status Update -->
            <div class="col-md-4">
                @if($workOrder->status !== 'completed' && $workOrder->status !== 'cancelled')
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Update Status</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('maintenance.work-orders.updateStatus', $workOrder) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="pending" {{ $workOrder->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in-progress" {{ $workOrder->status === 'in-progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ $workOrder->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $workOrder->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Actual Hours</label>
                                <input type="number" name="actual_hours" class="form-control" value="{{ $workOrder->actual_hours }}" step="0.5" min="0">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3">{{ $workOrder->notes }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Update Status</button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

