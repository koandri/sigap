@extends('layouts.app')

@section('title', 'Cleaning Schedules')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Facility Management</div>
                <h2 class="page-title">Cleaning Schedules</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                @can('facility.schedules.create')
                <a href="{{ route('facility.schedules.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i>&nbsp; Create Schedule
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        
        @include('layouts.alerts')

        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Frequency</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th>Alerts</th>
                            <th class="w-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                        <tr>
                            <td>
                                <strong>{{ $schedule->name }}</strong>
                                @if($schedule->description)
                                    <div class="small text-muted">{{ Str::limit($schedule->description, 50) }}</div>
                                @endif
                            </td>
                            <td>
                                <i class="fa fa-map-marker-alt text-muted"></i>&nbsp;
                                {{ $schedule->location->name }}
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $schedule->frequency_type->label() }}
                                </span>
                                <div class="small text-muted">
                                    {{ $schedule->frequency_description }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-azure">
                                    {{ $schedule->items->count() }} item(s)
                                </span>
                            </td>
                            <td>
                                @if($schedule->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $unresolvedAlerts = $schedule->alerts()->unresolved()->count();
                                @endphp
                                @if($unresolvedAlerts > 0)
                                    <span class="badge bg-warning">
                                        <i class="fa fa-exclamation-triangle"></i>&nbsp; {{ $unresolvedAlerts }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('facility.schedules.show', $schedule) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-eye"></i>&nbsp;
                                    </a>
                                    @can('facility.schedules.edit')
                                    <a href="{{ route('facility.schedules.edit', $schedule) }}" 
                                       class="btn btn-sm btn-outline-warning">
                                        <i class="fa fa-edit"></i>&nbsp;
                                    </a>
                                    @endcan
                                    @can('facility.schedules.delete')
                                    <form action="{{ route('facility.schedules.destroy', $schedule) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this schedule? All associated tasks will also be deleted.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa fa-trash"></i>&nbsp;
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty">
                                    <div class="empty-icon">
                                        <i class="fa fa-calendar text-muted"></i>&nbsp;
                                    </div>
                                    <p class="empty-title">No cleaning schedules</p>
                                    <p class="empty-subtitle text-muted">
                                        Create your first cleaning schedule to start generating tasks automatically.
                                    </p>
                                    @can('facility.schedules.create')
                                    <div class="empty-action">
                                        <a href="{{ route('facility.schedules.create') }}" class="btn btn-primary">
                                            <i class="fa fa-plus"></i>&nbsp; Create Schedule
                                        </a>
                                    </div>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($schedules->hasPages())
            <div class="card-footer">
                {{ $schedules->links('layouts.pagination') }}
            </div>
            @endif
        </div>

        <!-- Info Box -->
        <div class="alert alert-info mt-3">
            <div class="d-flex">
                <div>
                    <i class="fa fa-info-circle fa-2x"></i>&nbsp;
                </div>
                <div class="ms-3">
                    <h4 class="alert-title">About Cleaning Schedules</h4>
                    <div class="text-muted">
                        <p class="mb-1">
                            Cleaning schedules define recurring cleaning tasks for specific locations. 
                            Tasks are automatically generated daily based on the frequency settings.
                        </p>
                        <p class="mb-0">
                            <strong>Supported frequencies:</strong> Daily, Weekly (specific days), Monthly (specific dates)
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

