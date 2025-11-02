@extends('layouts.app')

@section('title', 'Maintenance Schedules')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    Maintenance Schedules
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                @can('maintenance.schedules.manage')
                <div class="btn-list">
                    <a href="{{ route('maintenance.schedules.create') }}" class="btn btn-primary d-none d-sm-inline-block">
                        <i class="far fa-plus"></i>&nbsp;
                        Add Schedule
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
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Asset name or description">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Asset</label>
                        <select name="asset" class="form-select">
                            <option value="">All Assets</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}" {{ request('asset') == $asset->id ? 'selected' : '' }}>
                                    {{ $asset->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            @foreach($maintenanceTypes as $type)
                                <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Upcoming (7 days)</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('maintenance.schedules.index') }}" class="btn btn-outline-secondary">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Schedules Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Maintenance Schedules ({{ $schedules->total() }})</h3>
            </div>
            <div class="card-body">
                @if($schedules->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Asset</th>
                                    <th>Type</th>
                                    <th>Frequency</th>
                                    <th>Next Due</th>
                                    <th>Assigned To</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($schedules as $schedule)
                                <tr>
                                    <td>
                                        <a href="{{ route('options.assets.show', $schedule->asset) }}">
                                            {{ $schedule->asset->name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $schedule->maintenanceType->color }}">
                                            {{ $schedule->maintenanceType->name }}
                                        </span>
                                    </td>
                                    <td>{{ $schedule->frequency_description }}</td>
                                    <td>
                                        <span class="text-{{ $schedule->next_due_date < now() ? 'danger' : ($schedule->next_due_date < now()->addDays(7) ? 'warning' : 'muted') }}">
                                            {{ $schedule->next_due_date->format('M d, Y') }}
                                        </span>
                                    </td>
                                    <td>{{ $schedule->assignedUser?->name ?? 'Unassigned' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $schedule->is_active ? 'success' : 'secondary' }}">
                                            {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-list">
                                            <a href="{{ route('maintenance.schedules.show', $schedule) }}" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                            @can('maintenance.schedules.manage')
                                            <a href="{{ route('maintenance.schedules.edit', $schedule) }}" class="btn btn-sm btn-outline-secondary">
                                                Edit
                                            </a>
                                            @if($schedule->is_active)
                                            <form action="{{ route('maintenance.schedules.trigger', $schedule) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning" 
                                                        onclick="return confirm('Generate work order for this schedule?')">
                                                    Trigger
                                                </button>
                                            </form>
                                            @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $schedules->links() }}
                    </div>
                @else
                    <div class="empty">
                        <div class="empty-icon">
                            <i class="far fa-clipboard icon"></i>&nbsp;
                        </div>
                        <p class="empty-title">No maintenance schedules found</p>
                        <p class="empty-subtitle text-muted">
                            Create your first maintenance schedule to get started.
                        </p>
                        @can('maintenance.schedules.manage')
                        <div class="empty-action">
                            <a href="{{ route('maintenance.schedules.create') }}" class="btn btn-primary">
                                <i class="far fa-plus"></i>&nbsp;
                                Add Schedule
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
