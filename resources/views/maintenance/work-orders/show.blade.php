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
                    @can('update', $workOrder)
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
        <!-- Actions Section - Full Width at Top (only show if not completed) -->
        @if($workOrder->status !== 'completed')
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Actions</h3>
                    </div>
                    <div class="card-body">
                        @if($workOrder->status === 'submitted')
                            @can('assign', $workOrder)
                            <button type="button" class="btn btn-primary me-2 mb-2" data-bs-toggle="modal" data-bs-target="#assignModal">
                                Assign Work Order
                            </button>
                            @endcan
                        @elseif($workOrder->status === 'assigned')
                            @can('work', $workOrder)
                            <form action="{{ route('maintenance.work-orders.start', $workOrder) }}" method="POST" class="d-inline me-2 mb-2">
                                @csrf
                                <button type="submit" class="btn btn-success">Start Work</button>
                            </form>
                            @endcan
                        @elseif($workOrder->status === 'in-progress')
                            @can('work', $workOrder)
                            <button type="button" class="btn btn-info me-2 mb-2" data-bs-toggle="modal" data-bs-target="#progressModal">
                                Log Progress
                            </button>
                            <button type="button" class="btn btn-outline-primary me-2 mb-2" data-bs-toggle="modal" data-bs-target="#actionModal">
                                Add Action
                            </button>
                            <button type="button" class="btn btn-outline-secondary me-2 mb-2" data-bs-toggle="modal" data-bs-target="#photoModal">
                                Upload Photo
                            </button>
                            <form action="{{ route('maintenance.work-orders.submit-verification', $workOrder) }}" method="POST" class="d-inline me-2 mb-2">
                                @csrf
                                <button type="submit" class="btn btn-warning">Submit for Verification</button>
                            </form>
                            @endcan
                        @elseif($workOrder->status === 'pending-verification')
                            @can('verify', $workOrder)
                            <button type="button" class="btn btn-primary me-2 mb-2" data-bs-toggle="modal" data-bs-target="#verifyModal">
                                Verify Work Order
                            </button>
                            @endcan
                        @elseif($workOrder->status === 'verified')
                            @can('close', $workOrder)
                            <button type="button" class="btn btn-success me-2 mb-2" data-bs-toggle="modal" data-bs-target="#closeModal">
                                Close Work Order
                            </button>
                            @endcan
                        @elseif($workOrder->status === 'rework')
                            @can('work', $workOrder)
                            <form action="{{ route('maintenance.work-orders.start', $workOrder) }}" method="POST" class="d-inline me-2 mb-2">
                                @csrf
                                <button type="submit" class="btn btn-warning">Resume Work</button>
                            </form>
                            @endcan
                            @cannot('work', $workOrder)
                                <div class="alert alert-warning">
                                    <strong>Rework Required</strong><br>
                                    This work order has been sent back for rework.
                                </div>
                            @endcannot
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Main Content - Full Width -->
        <div class="row">
            <div class="col-12">
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
                                        <span class="badge bg-{{ $workOrder->priority === 'urgent' ? 'danger' : ($workOrder->priority === 'high' ? 'warning' : ($workOrder->priority === 'medium' ? 'info' : 'secondary')) }} text-white">
                                            {{ ucfirst($workOrder->priority) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Requested By</label>
                                    <div class="form-control-plaintext">{{ $workOrder->requestedBy->name }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Assignment Details -->
                        @if($workOrder->assigned_to)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Assigned To</label>
                                    <div class="form-control-plaintext">{{ $workOrder->assignedUser->name }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Assigned By</label>
                                    <div class="form-control-plaintext">{{ $workOrder->assignedBy->name }}</div>
                                </div>
                            </div>
                        </div>
                        @endif

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
                                    <div class="form-control-plaintext">
                                        {{ $workOrder->actual_hours ?? ($workOrder->progressLogs->sum('hours_worked') ?: 'Not started') }}
                                    </div>
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

                        @if($workOrder->verification_notes)
                        <div class="mb-3">
                            <label class="form-label">Verification Notes</label>
                            <div class="form-control-plaintext">{{ $workOrder->verification_notes }}</div>
                        </div>
                        @endif

                        <!-- Status Information -->
                        <div class="mt-4 pt-3 border-top">
                            <h6>Status Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><strong>Created:</strong> {{ $workOrder->created_at->format('M d, Y H:i') }}</li>
                                        @if($workOrder->assigned_at)
                                            <li><strong>Assigned:</strong> {{ $workOrder->assigned_at->format('M d, Y H:i') }}</li>
                                        @endif
                                        @if($workOrder->work_started_at)
                                            <li><strong>Work Started:</strong> {{ $workOrder->work_started_at->format('M d, Y H:i') }}</li>
                                        @endif
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        @if($workOrder->work_finished_at)
                                            <li><strong>Work Finished:</strong> {{ $workOrder->work_finished_at->format('M d, Y H:i') }}</li>
                                        @endif
                                        @if($workOrder->verified_at)
                                            <li><strong>Verified:</strong> {{ $workOrder->verified_at->format('M d, Y H:i') }}</li>
                                        @endif
                                        @if($workOrder->completed_date)
                                            <li><strong>Completed:</strong> {{ $workOrder->completed_date->format('M d, Y H:i') }}</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress Logs -->
                @if($workOrder->progressLogs->count() > 0)
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Progress Logs</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Date/Time</th>
                                        <th>Hours Worked</th>
                                        <th>Progress</th>
                                        <th>Logged By</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($workOrder->progressLogs as $log)
                                    <tr>
                                        <td>{{ $log->logged_at->format('M d, Y H:i') }}</td>
                                        <td>{{ $log->hours_worked }}h</td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: {{ $log->completion_percentage }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ $log->completion_percentage }}%</small>
                                        </td>
                                        <td>{{ $log->loggedBy->name }}</td>
                                        <td>{{ Str::limit($log->progress_notes, 50) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Actions Performed -->
                @if($workOrder->actions->count() > 0)
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Actions Performed</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Date/Time</th>
                                        <th>Action Type</th>
                                        <th>Description</th>
                                        <th>Performed By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($workOrder->actions as $action)
                                    <tr>
                                        <td>{{ $action->performed_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-primary text-white">
                                                {{ ucfirst(str_replace('-', ' ', $action->action_type)) }}
                                            </span>
                                        </td>
                                        <td>{{ $action->action_description }}</td>
                                        <td>{{ $action->performedBy->name }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Photos -->
                @if($workOrder->photos->count() > 0)
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Photos</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($workOrder->photos as $photo)
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <img src="{{ asset('storage/' . $photo->photo_path) }}" class="card-img-top" alt="Work Order Photo">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ ucfirst($photo->photo_type) }}</h6>
                                        @if($photo->caption)
                                            <p class="card-text">{{ $photo->caption }}</p>
                                        @endif
                                        <small class="text-muted">
                                            Uploaded by {{ $photo->uploadedBy->name }}<br>
                                            {{ $photo->created_at->format('M d, Y H:i') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

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
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Include Modals -->
@include('maintenance.work-orders.partials.assign-modal')
@include('maintenance.work-orders.partials.progress-modal')
@include('maintenance.work-orders.partials.action-modal')
@include('maintenance.work-orders.partials.photo-modal')
@include('maintenance.work-orders.partials.verify-modal')
@include('maintenance.work-orders.partials.close-modal')

@endsection