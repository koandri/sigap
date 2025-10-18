@extends('layouts.app')

@section('title', 'All Cleaning Tasks')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Facility Management</div>
                <h2 class="page-title">
                    <i class="fa fa-tasks"></i> All Cleaning Tasks
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('facility.schedules.index') }}" class="btn btn-primary">
                        <i class="fa fa-calendar"></i> Manage Schedules
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        
        @include('layouts.alerts')

        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('facility.tasks.index') }}">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control" value="{{ $date }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Location</label>
                            <select name="location_id" class="form-select">
                                <option value="">All Locations</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ $locationId == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in-progress" {{ $status === 'in-progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="missed" {{ $status === 'missed' ? 'selected' : '' }}>Missed</option>
                                <option value="skipped" {{ $status === 'skipped' ? 'selected' : '' }}>Skipped</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('facility.tasks.index') }}" class="btn btn-outline-secondary">
                                    <i class="fa fa-redo"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics -->
        @if($tasks->total() > 0)
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Tasks</div>
                        </div>
                        <div class="h1 mb-0">{{ $tasks->total() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Pending</div>
                        </div>
                        <div class="h1 mb-0 text-warning">{{ $tasks->where('status', 'pending')->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Completed</div>
                        </div>
                        <div class="h1 mb-0 text-success">{{ $tasks->whereIn('status', ['completed', 'approved'])->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Missed</div>
                        </div>
                        <div class="h1 mb-0 text-danger">{{ $tasks->where('status', 'missed')->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Tasks Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tasks for {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-striped">
                    <thead>
                        <tr>
                            <th>Task Number</th>
                            <th>Location</th>
                            <th>Item</th>
                            <th>Schedule</th>
                            <th>Assigned To</th>
                            <th>Status</th>
                            <th class="w-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                        <tr>
                            <td>
                                <a href="{{ route('facility.tasks.show', $task) }}" class="text-reset">
                                    {{ $task->task_number }}
                                </a>
                            </td>
                            <td>
                                <i class="fa fa-map-marker-alt text-muted"></i>
                                {{ $task->location->name }}
                            </td>
                            <td>
                                <strong>{{ $task->item_name }}</strong>
                                @if($task->asset)
                                    <br><span class="badge bg-azure">{{ $task->asset->code }}</span>
                                @endif
                                @if($task->item_description)
                                    <br><span class="text-muted small">{{ Str::limit($task->item_description, 50) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($task->cleaning_schedule_id > 0)
                                    <a href="{{ route('facility.schedules.show', $task->cleaning_schedule_id) }}" class="text-reset">
                                        {{ $task->cleaningSchedule->name }}
                                    </a>
                                @else
                                    <span class="badge bg-purple">Ad-hoc Task</span>
                                @endif
                            </td>
                            <td>
                                @if($task->assignedUser)
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-xs me-2" style="background-image: url({{ $task->assignedUser->profile_photo_url ?? '' }})"></span>
                                        {{ $task->assignedUser->name }}
                                    </div>
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @if($task->status === 'completed' || $task->status === 'approved')
                                    <span class="badge bg-success"><i class="fa fa-check"></i> {{ ucfirst($task->status) }}</span>
                                @elseif($task->status === 'in-progress')
                                    <span class="badge bg-info"><i class="fa fa-spinner"></i> In Progress</span>
                                @elseif($task->status === 'pending')
                                    <span class="badge bg-warning"><i class="fa fa-clock"></i> Pending</span>
                                @elseif($task->status === 'missed')
                                    <span class="badge bg-danger"><i class="fa fa-exclamation-triangle"></i> Missed</span>
                                @elseif($task->status === 'rejected')
                                    <span class="badge bg-danger"><i class="fa fa-times"></i> Rejected</span>
                                @elseif($task->status === 'skipped')
                                    <span class="badge bg-secondary"><i class="fa fa-forward"></i> Skipped</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('facility.tasks.show', $task) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <div class="empty">
                                    <div class="empty-icon">
                                        <i class="fa fa-inbox fa-3x"></i>
                                    </div>
                                    <p class="empty-title">No tasks found</p>
                                    <p class="empty-subtitle text-muted">
                                        Try adjusting your filters or select a different date.
                                    </p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tasks->hasPages())
            <div class="card-footer">
                {{ $tasks->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection

