@extends('layouts.app')

@section('title', 'Cleaning Schedule Details')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Facility Management</div>
                <h2 class="page-title">{{ $schedule->name }}</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                @can('facility.schedules.edit')
                    <a href="{{ route('facility.schedules.edit', $schedule) }}" class="btn btn-primary">
                        <i class="fa fa-edit"></i>&nbsp; Edit Schedule
                    </a>
                @endcan
                <a href="{{ route('facility.schedules.index') }}" class="btn btn-outline-primary">
                    <i class="fa fa-arrow-left"></i>&nbsp; Back to List
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
                            <div class="col-md-4">
                                <label class="form-label text-muted">Location</label>
                                <div class="fw-bold">{{ $schedule->location->name }}</div>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label text-muted">Frequency</label>
                                <div class="fw-bold">
                                    <i class="fa fa-clock me-1"></i>&nbsp;
                                    {{ $schedule->frequency_description }}
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

                <!-- Alerts (if any) -->
                @if($schedule->alerts->count() > 0)
                    <div class="card mb-3">
                        <div class="card-header bg-warning-lt">
                            <h3 class="card-title">
                                <i class="fa fa-exclamation-triangle"></i>&nbsp; Active Alerts
                            </h3>
                        </div>
                        <div class="card-body">
                            @foreach($schedule->alerts as $alert)
                                <div class="alert alert-warning mb-2">
                                    <div class="d-flex">
                                        <div>
                                            <i class="fa fa-exclamation-circle"></i>&nbsp;
                                        </div>
                                        <div class="ms-2">
                                            <strong>{{ $alert->alert_type === 'asset_inactive' ? 'Asset Inactive' : 'Asset Disposed' }}</strong>
                                            @if($alert->asset)
                                                <p class="mb-0">Asset: {{ $alert->asset->code }} - {{ $alert->asset->name }}</p>
                                            @endif
                                            <small class="text-muted">
                                                Detected: {{ $alert->detected_at->format('d M Y, g:ia') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <p class="text-muted small mb-0">
                                <i class="fa fa-info-circle"></i>&nbsp; 
                                These alerts indicate issues with assets linked to this schedule. 
                                Tasks for these items will be automatically skipped until resolved.
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Cleaning Items -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Cleaning Items</h3>
                        <div class="card-actions">
                            <span class="text-muted">{{ $schedule->items->count() }} item(s)</span>
                        </div>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($schedule->items->sortBy('order') as $item)
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="fw-bold">{{ $item->item_name }}</div>
                                        @if($item->item_description)
                                            <div class="text-muted small">{{ $item->item_description }}</div>
                                        @endif
                                        @if($item->asset)
                                            <div class="mt-1">
                                                <span class="badge bg-blue-lt">
                                                    <i class="fa fa-box"></i>&nbsp; 
                                                    {{ $item->asset->code }} - {{ $item->asset->name }}
                                                </span>
                                                @if(!$item->asset->is_active)
                                                    <span class="badge bg-warning">Inactive</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-auto">
                                        <span class="text-muted">#{{ $item->order + 1 }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Recent Tasks -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Tasks</h3>
                    </div>
                    @if($recentTasks->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Task Number</th>
                                        <th>Item</th>
                                        <th>Scheduled</th>
                                        <th>Assigned To</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentTasks as $task)
                                        <tr>
                                            <td>
                                                <a href="{{ route('facility.tasks.show', $task) }}">
                                                    {{ $task->task_number }}
                                                </a>
                                            </td>
                                            <td>{{ $task->item_name }}</td>
                                            <td>
                                                {{ $task->scheduled_date->format('d M Y') }}
                                                @if($task->scheduled_date->format('H:i') !== '00:00')
                                                    <br><small class="text-muted">{{ $task->scheduled_date->format('g:ia') }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $task->assignedUser->name }}</td>
                                            <td>
                                                @if($task->status === 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($task->status === 'approved')
                                                    <span class="badge bg-info">Approved</span>
                                                @elseif($task->status === 'in-progress')
                                                    <span class="badge bg-warning">In Progress</span>
                                                @elseif($task->status === 'missed')
                                                    <span class="badge bg-danger">Missed</span>
                                                @else
                                                    <span class="badge bg-secondary">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="card-body">
                            <div class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-3x mb-3"></i>&nbsp;
                                <p>No tasks have been generated yet for this schedule.</p>
                                <small>Tasks are automatically generated daily at midnight based on the frequency settings.</small>
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
                            <div class="text-muted small mb-1">Total Items</div>
                            <div class="h3 mb-0">{{ $schedule->items->count() }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Total Tasks Generated</div>
                            <div class="h3 mb-0">{{ $schedule->tasks->count() }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Completed Tasks</div>
                            <div class="h3 mb-0">
                                {{ $schedule->tasks->whereIn('status', ['completed', 'approved'])->count() }}
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="text-muted small mb-1">Active Alerts</div>
                            <div class="h3 mb-0 {{ $schedule->alerts->count() > 0 ? 'text-warning' : 'text-success' }}">
                                {{ $schedule->alerts->count() }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Next Generation Info -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fa fa-calendar-plus"></i>&nbsp; Task Generation
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">
                            <strong>Frequency:</strong><br>
                            {{ $schedule->frequency_description }}
                        </p>
                        <p class="text-muted small mb-2">
                            <strong>Automatic Generation:</strong><br>
                            Tasks are generated daily at midnight (00:00 Asia/Jakarta time)
                        </p>
                        <p class="text-muted small mb-0">
                            <strong>Tasks per Generation:</strong><br>
                            @if($schedule->frequency_type->value === 'hourly')
                                Multiple tasks per day based on interval and time range
                            @else
                                {{ $schedule->items->count() }} task(s) when conditions are met
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @can('facility.schedules.edit')
                                <a href="{{ route('facility.schedules.edit', $schedule) }}" class="btn btn-primary">
                                    <i class="fa fa-edit"></i>&nbsp; Edit Schedule
                                </a>
                            @endcan
                            @can('facility.tasks.view')
                                <a href="{{ route('facility.tasks.index') }}?schedule={{ $schedule->id }}" class="btn btn-outline-primary">
                                    <i class="fa fa-list"></i>&nbsp; View All Tasks
                                </a>
                            @endcan
                            @can('facility.schedules.delete')
                                <form action="{{ route('facility.schedules.destroy', $schedule) }}" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this schedule? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100">
                                        <i class="fa fa-trash"></i>&nbsp; Delete Schedule
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

