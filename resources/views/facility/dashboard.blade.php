@extends('layouts.app')

@section('title', 'Facility Management Dashboard')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Facility Management</div>
                <h2 class="page-title">Dashboard</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <form method="GET" class="d-flex gap-2">
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}" required>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}" required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-filter"></i>&nbsp; Filter
                    </button>
                </form>
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
                            <div class="subheader">Total Tasks</div>
                        </div>
                        <div class="h1 mb-3">{{ $completionStats['total'] }}</div>
                        <div class="d-flex mb-2">
                            <div>Completion Rate</div>
                            <div class="ms-auto">
                                <span class="text-{{ $completionStats['completion_rate'] >= 80 ? 'green' : ($completionStats['completion_rate'] >= 60 ? 'yellow' : 'red') }} d-inline-flex align-items-center lh-1">
                                    {{ $completionStats['completion_rate'] }}%
                                </span>
                            </div>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-{{ $completionStats['completion_rate'] >= 80 ? 'success' : ($completionStats['completion_rate'] >= 60 ? 'warning' : 'danger') }}" 
                                 style="width: {{ $completionStats['completion_rate'] }}%" role="progressbar"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Completed</div>
                        <div class="h1 mb-0 text-success">{{ $completionStats['completed'] }}</div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Pending</div>
                        <div class="h1 mb-0 text-warning">{{ $completionStats['pending'] }}</div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Missed</div>
                        <div class="h1 mb-0 text-danger">{{ $completionStats['missed'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-deck row-cards">
            <!-- SLA Compliance -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">SLA Compliance</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <div class="text-muted">Compliance Rate</div>
                                    <div class="h2 text-{{ $slaStats['compliance_rate'] >= 90 ? 'success' : ($slaStats['compliance_rate'] >= 70 ? 'warning' : 'danger') }}">
                                        {{ $slaStats['compliance_rate'] }}%
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <div class="text-muted">Avg Approval Time</div>
                                    <div class="h2">{{ $slaStats['avg_approval_hours'] }}h</div>
                                </div>
                            </div>
                        </div>
                        <div class="text-muted">
                            {{ $slaStats['within_sla'] }} of {{ $slaStats['total_approvals'] }} approved within SLA
                        </div>
                    </div>
                </div>
            </div>

            <!-- Unresolved Alerts -->
            @if($unresolvedAlerts->count() > 0)
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fa fa-exclamation-triangle text-warning"></i>&nbsp; Schedule Maintenance Required
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($unresolvedAlerts->take(5) as $alert)
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="badge bg-warning"></span>
                                    </div>
                                    <div class="col text-truncate">
                                        <strong>{{ $alert->cleaningSchedule->name }}</strong>
                                        <div class="text-muted">
                                            {{ $alert->cleaningScheduleItem->item_name }} - 
                                            Asset: {{ $alert->asset?->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-muted small">{{ $alert->detected_at->diffForHumans() }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <a href="{{ route('facility.schedules.show', $alert->cleaning_schedule_id) }}" class="btn btn-sm btn-primary">
                                            Resolve
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Cleaner Ranking -->
        <div class="row row-deck row-cards mt-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Cleaner Performance Ranking</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Cleaner</th>
                                    <th>Total Tasks</th>
                                    <th>Completed</th>
                                    <th>Completion Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cleanerRanking as $index => $cleaner)
                                <tr>
                                    <td>
                                        @if($index === 0)
                                            <i class="fa fa-trophy text-warning"></i>&nbsp;
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </td>
                                    <td>{{ $cleaner['name'] }}</td>
                                    <td>{{ $cleaner['total_tasks'] }}</td>
                                    <td>{{ $cleaner['completed_tasks'] }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">{{ $cleaner['completion_rate'] }}%</div>
                                            <div class="progress flex-fill" style="width: 100px">
                                                <div class="progress-bar bg-{{ $cleaner['completion_rate'] >= 80 ? 'success' : ($cleaner['completion_rate'] >= 60 ? 'warning' : 'danger') }}" 
                                                     style="width: {{ $cleaner['completion_rate'] }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tasks by Location -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Tasks by Location</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($tasksByLocation as $location)
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <strong>{{ $location['location'] }}</strong>
                                        <div class="text-muted small">
                                            {{ $location['completed'] }}/{{ $location['total'] }} completed
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <span class="badge bg-{{ $location['completion_rate'] >= 80 ? 'success' : ($location['completion_rate'] >= 60 ? 'warning' : 'danger') }}">
                                            {{ $location['completion_rate'] }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="list-group-item text-center text-muted">
                                No data available
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Approvals -->
        @if(count($pendingApprovals) > 0)
        <div class="row row-deck row-cards mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Pending Approvals (Top 10)</h3>
                        <div class="card-actions">
                            <a href="{{ route('facility.approvals.index') }}" class="btn btn-primary btn-sm">
                                View All
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Location</th>
                                    <th>Submitted By</th>
                                    <th>Submitted At</th>
                                    <th>Hours Overdue</th>
                                    <th>SLA Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingApprovals as $approval)
                                <tr>
                                    <td>
                                        {{ $approval['task_number'] }}
                                        @if($approval['is_flagged'])
                                            <i class="fa fa-star text-warning" title="Flagged for review"></i>&nbsp;
                                        @endif
                                    </td>
                                    <td>{{ $approval['location'] }}</td>
                                    <td>{{ $approval['submitted_by'] }}</td>
                                    <td>{{ \Carbon\Carbon::parse($approval['submitted_at'])->format('M d, H:i') }}</td>
                                    <td>{{ number_format($approval['hours_overdue'], 1) }}h</td>
                                    <td>
                                        <span class="badge bg-{{ $approval['sla_color'] }}">
                                            {{ ucfirst($approval['sla_status']) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('facility.approvals.review', $approval['id']) }}" class="btn btn-sm btn-primary">
                                            Review
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection

