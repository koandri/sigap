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
                                <a href="{{ route('options.assets.index') }}" class="text-reset">View all assets</a>
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
                                <a href="{{ route('maintenance.schedules.index') }}" class="text-reset">View schedules</a>
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
                                                <span class="badge bg-{{ $statusColors[$workOrder->status] ?? 'secondary' }} text-white">
                                                    {{ ucfirst(str_replace('-', ' ', $workOrder->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $workOrder->priority === 'urgent' ? 'danger' : ($workOrder->priority === 'high' ? 'warning' : 'secondary') }} text-white">
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
                                    <i class="far fa-clipboard icon"></i>
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
                        <div id="upcoming-maintenance-calendar" style="min-height: 400px;"></div>
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

@push('scripts')
<script src="{{ asset('assets/tabler/libs/fullcalendar/index.global.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('upcoming-maintenance-calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: 'today'
        },
        height: 'auto',
        events: {
            url: '{{ route("maintenance.calendar.events") }}',
            method: 'GET'
        },
        eventContent: function(arg) {
            // Render work orders as clickable links, schedules as plain text
            if (arg.event.extendedProps.type === 'workorder' && arg.event.extendedProps.url) {
                return {
                    html: '<a href="' + arg.event.extendedProps.url + '" class="fc-event-title text-white text-decoration-none">' + arg.event.title + '</a>'
                };
            } else {
                return {
                    html: '<div class="fc-event-title">' + arg.event.title + '</div>'
                };
            }
        },
        dayMaxEvents: 3,
        moreLinkClick: 'popover'
    });
    calendar.render();
});
</script>
@endpush

@push('styles')
<link href="{{ asset('assets/tabler/libs/fullcalendar/index.global.css') }}" rel="stylesheet">
<style>
    #upcoming-maintenance-calendar .fc-event {
        font-size: 0.75rem;
        padding: 1px 2px;
        margin-bottom: 1px;
    }
    #upcoming-maintenance-calendar .fc-event-title {
        font-size: 0.75rem;
        line-height: 1.2;
    }
    #upcoming-maintenance-calendar .fc-daygrid-event-dot {
        display: none;
    }
</style>
@endpush
