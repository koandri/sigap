@extends('layouts.app')

@section('title', 'Pending Approvals')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Facility Management</div>
                <h2 class="page-title">Pending Approvals</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <form method="POST" action="{{ route('facility.approvals.mass-approve') }}" 
                      onsubmit="return confirm('Are you sure you want to approve all pending submissions?');">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date }}">
                    @if($batchCheck['can_approve'])
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-check-double"></i>&nbsp; Mass Approve ({{ $totalPending }})
                        </button>
                    @else
                        <button type="button" class="btn btn-secondary" disabled 
                                title="{{ $batchCheck['message'] }}">
                            <i class="fa fa-lock"></i>&nbsp; Mass Approve
                        </button>
                    @endif
                </form>
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
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ $date }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">SLA Status</label>
                        <select name="sla_filter" class="form-select">
                            <option value="">All</option>
                            <option value="on-time" {{ $slaFilter === 'on-time' ? 'selected' : '' }}>On Time</option>
                            <option value="warning" {{ $slaFilter === 'warning' ? 'selected' : '' }}>Warning</option>
                            <option value="critical" {{ $slaFilter === 'critical' ? 'selected' : '' }}>Critical</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="flagged_only" 
                                   {{ $flaggedOnly ? 'checked' : '' }} id="flaggedOnly">
                            <label class="form-check-label" for="flaggedOnly">
                                Flagged for Review Only
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-filter"></i>&nbsp; Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Review Progress -->
        @if($flaggedCount > 0)
        <div class="alert alert-info mb-3">
            <div class="d-flex align-items-center">
                <div>
                    <strong>Review Progress:</strong> {{ $reviewedFlagged }} of {{ $flaggedCount }} flagged tasks reviewed
                    ({{ $flaggedCount > 0 ? round(($reviewedFlagged / $flaggedCount) * 100, 1) : 0 }}%)
                </div>
                <div class="ms-auto">
                    @if($batchCheck['can_approve'])
                        <span class="badge bg-success"><i class="fa fa-check"></i>&nbsp; Can Mass Approve</span>
                    @else
                        <span class="badge bg-warning">
                            <i class="fa fa-exclamation-triangle"></i>&nbsp; Need {{ ceil($flaggedCount * 0.1) - $reviewedFlagged }} more review(s)
                        </span>
                    @endif
                </div>
            </div>
            <div class="progress mt-2" style="height: 10px;">
                <div class="progress-bar" role="progressbar" 
                     style="width: {{ $flaggedCount > 0 ? ($reviewedFlagged / $flaggedCount) * 100 : 0 }}%"></div>
            </div>
        </div>
        @endif

        <!-- Approvals List -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Submissions ({{ $totalPending }})</h3>
            </div>
            @if($approvals->count() > 0)
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Task</th>
                            <th>Location</th>
                            <th>Item</th>
                            <th>Submitted By</th>
                            <th>Submitted At</th>
                            <th>SLA Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($approvals as $approval)
                        <tr class="{{ $approval->is_flagged_for_review ? 'table-warning' : '' }}">
                            <td>
                                @if($approval->is_flagged_for_review)
                                    <i class="fa fa-star text-warning" title="Flagged for mandatory review"></i>&nbsp;
                                @endif
                            </td>
                            <td>
                                <strong>{{ $approval->cleaningSubmission->cleaningTask->task_number }}</strong>
                            </td>
                            <td>{{ $approval->cleaningSubmission->cleaningTask->location->name }}</td>
                            <td>{{ $approval->cleaningSubmission->cleaningTask->item_name }}</td>
                            <td>{{ $approval->cleaningSubmission->submittedByUser->name }}</td>
                            <td>{{ $approval->cleaningSubmission->submitted_at->format('M d, H:i') }}</td>
                            <td>
                                <span class="badge bg-{{ $approval->sla_color }}">
                                    @if($approval->hours_overdue > 0)
                                        +{{ number_format($approval->hours_overdue, 1) }}h
                                    @else
                                        On Time
                                    @endif
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('facility.approvals.review', $approval) }}" class="btn btn-sm btn-primary">
                                    <i class="fa fa-eye"></i>&nbsp; Review
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="card-body">
                <div class="empty">
                    <div class="empty-icon">
                        <i class="fa fa-check-circle fa-3x text-success"></i>&nbsp;
                    </div>
                    <p class="empty-title">No pending approvals</p>
                    <p class="empty-subtitle text-muted">
                        All submissions for this date have been processed.
                    </p>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection

