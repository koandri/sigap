@extends('layouts.app')

@section('title', 'Task Details - ' . $task->task_number)

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Facility Management</div>
                <h2 class="page-title">
                    <i class="fa fa-tasks"></i>&nbsp; Task Details
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('facility.tasks.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left"></i>&nbsp; Back to Tasks
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        
        @include('layouts.alerts')

        <div class="row">
            <!-- Task Information -->
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Task Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Task Number</label>
                                    <div><strong>{{ $task->task_number }}</strong></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Location</label>
                                    <div>
                                        <i class="fa fa-map-marker-alt text-muted"></i>&nbsp;
                                        {{ $task->location->name }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Scheduled Date</label>
                                    <div>{{ $task->scheduled_date->format('l, F d, Y') }}</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Schedule</label>
                                    <div>
                                        @if($task->cleaning_schedule_id > 0)
                                            <a href="{{ route('facility.schedules.show', $task->cleaning_schedule_id) }}">
                                                {{ $task->cleaningSchedule->name }}
                                            </a>
                                        @else
                                            <span class="badge bg-purple">Ad-hoc Task</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Item Name</label>
                                    <div><strong>{{ $task->item_name }}</strong></div>
                                </div>
                                @if($task->asset)
                                <div class="mb-3">
                                    <label class="form-label text-muted">Asset</label>
                                    <div>
                                        <a href="{{ route('assets.show', $task->asset) }}" class="badge bg-azure">
                                            {{ $task->asset->code }} - {{ $task->asset->name }}
                                        </a>
                                    </div>
                                </div>
                                @endif
                                @if($task->item_description)
                                <div class="mb-3">
                                    <label class="form-label text-muted">Description</label>
                                    <div>{{ $task->item_description }}</div>
                                </div>
                                @endif
                                @if($task->skip_reason)
                                <div class="mb-3">
                                    <label class="form-label text-muted">Skip Reason</label>
                                    <div class="alert alert-warning mb-0">
                                        <i class="fa fa-exclamation-triangle"></i>&nbsp; {{ $task->skip_reason }}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assignment & Completion Info -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Assignment & Completion</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Assigned To</label>
                                    <div>
                                        @if($task->assignedUser)
                                            <div class="d-flex align-items-center">
                                                <span class="avatar avatar-sm me-2" style="background-image: url({{ $task->assignedUser->profile_photo_url ?? '' }})"></span>
                                                {{ $task->assignedUser->name }}
                                            </div>
                                        @else
                                            <span class="text-muted">Unassigned</span>
                                        @endif
                                    </div>
                                </div>
                                @if($task->started_by)
                                <div class="mb-3">
                                    <label class="form-label text-muted">Started By</label>
                                    <div>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-sm me-2" style="background-image: url({{ $task->startedByUser->profile_photo_url ?? '' }})"></span>
                                            {{ $task->startedByUser->name }}
                                        </div>
                                        <small class="text-muted">{{ $task->started_at->format('M d, Y H:i') }}</small>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if($task->completed_by)
                                <div class="mb-3">
                                    <label class="form-label text-muted">Completed By</label>
                                    <div>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-sm me-2" style="background-image: url({{ $task->completedByUser->profile_photo_url ?? '' }})"></span>
                                            {{ $task->completedByUser->name }}
                                        </div>
                                        <small class="text-muted">{{ $task->completed_at->format('M d, Y H:i') }}</small>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submission Details -->
                @if($task->submission)
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Submission Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Before Photo</label>
                                @if($task->submission->before_photo && isset($task->submission->before_photo['file_path']))
                                <a href="{{ Storage::disk('sigap')->url($task->submission->before_photo['file_path']) }}" data-lightbox="task-photos" data-title="Before Photo">
                                    <img src="{{ Storage::disk('sigap')->url($task->submission->before_photo['file_path']) }}" 
                                         class="img-fluid rounded" 
                                         alt="Before Photo"
                                         style="max-height: 300px;">
                                </a>
                                @if(isset($task->submission->before_photo['gps_data']))
                                <div class="text-muted small mt-1">
                                    <i class="fa fa-map-marker-alt"></i>&nbsp;
                                    GPS: {{ $task->submission->before_photo['gps_data']['latitude'] ?? 'N/A' }}, 
                                    {{ $task->submission->before_photo['gps_data']['longitude'] ?? 'N/A' }}
                                </div>
                                @endif
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">After Photo</label>
                                @if($task->submission->after_photo && isset($task->submission->after_photo['file_path']))
                                <a href="{{ Storage::disk('sigap')->url($task->submission->after_photo['file_path']) }}" data-lightbox="task-photos" data-title="After Photo">
                                    <img src="{{ Storage::disk('sigap')->url($task->submission->after_photo['file_path']) }}" 
                                         class="img-fluid rounded" 
                                         alt="After Photo"
                                         style="max-height: 300px;">
                                </a>
                                @if(isset($task->submission->after_photo['gps_data']))
                                <div class="text-muted small mt-1">
                                    <i class="fa fa-map-marker-alt"></i>&nbsp;
                                    GPS: {{ $task->submission->after_photo['gps_data']['latitude'] ?? 'N/A' }}, 
                                    {{ $task->submission->after_photo['gps_data']['longitude'] ?? 'N/A' }}
                                </div>
                                @endif
                                @endif
                            </div>
                        </div>
                        @if($task->submission->notes)
                        <div class="mb-0">
                            <label class="form-label">Notes</label>
                            <div class="alert alert-info mb-0">
                                {{ $task->submission->notes }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Approval Details -->
                @if($task->submission->approval)
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Approval Status</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Status</label>
                                    <div>
                                        @if($task->submission->approval->status === 'approved')
                                            <span class="badge bg-success"><i class="fa fa-check"></i>&nbsp; Approved</span>
                                        @elseif($task->submission->approval->status === 'rejected')
                                            <span class="badge bg-danger"><i class="fa fa-times"></i>&nbsp; Rejected</span>
                                        @else
                                            <span class="badge bg-warning"><i class="fa fa-clock"></i>&nbsp; Pending</span>
                                        @endif
                                        @if($task->submission->approval->is_flagged_for_review)
                                            <span class="badge bg-orange ms-1"><i class="fa fa-flag"></i>&nbsp; Flagged for Review</span>
                                        @endif
                                    </div>
                                </div>
                                @if($task->submission->approval->approved_by)
                                <div class="mb-3">
                                    <label class="form-label text-muted">Reviewed By</label>
                                    <div>{{ $task->submission->approval->approvedByUser->name }}</div>
                                    <small class="text-muted">{{ $task->submission->approval->reviewed_at->format('M d, Y H:i') }}</small>
                                </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if($task->submission->approval->status === 'pending')
                                <div class="mb-3">
                                    <label class="form-label text-muted">SLA Status</label>
                                    <div>
                                        <span class="badge bg-{{ $task->submission->approval->sla_color }}">
                                            @if($task->submission->approval->hours_overdue > 0)
                                                {{ number_format($task->submission->approval->hours_overdue, 1) }} hours overdue
                                            @else
                                                On Time
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                @endif
                                @if($task->submission->approval->notes)
                                <div class="mb-3">
                                    <label class="form-label text-muted">Approval Notes</label>
                                    <div class="alert alert-secondary mb-0">
                                        {{ $task->submission->approval->notes }}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @can('facility.submissions.review')
                        @if($task->submission->approval->status === 'pending')
                        <hr>
                        <div class="text-center">
                            <a href="{{ route('facility.approvals.review', $task->submission->approval) }}" class="btn btn-primary">
                                <i class="fa fa-eye"></i>&nbsp; Review Submission
                            </a>
                        </div>
                        @endif
                        @endcan
                    </div>
                </div>
                @endif
                @endif
            </div>

            <!-- Status Sidebar -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Status</h3>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            @if($task->status === 'completed' || $task->status === 'approved')
                                <span class="badge bg-success" style="font-size: 1.5rem; padding: 0.5rem 1rem;">
                                    <i class="fa fa-check"></i>&nbsp; {{ ucfirst($task->status) }}
                                </span>
                            @elseif($task->status === 'in-progress')
                                <span class="badge bg-info" style="font-size: 1.5rem; padding: 0.5rem 1rem;">
                                    <i class="fa fa-spinner"></i>&nbsp; In Progress
                                </span>
                            @elseif($task->status === 'pending')
                                <span class="badge bg-warning" style="font-size: 1.5rem; padding: 0.5rem 1rem;">
                                    <i class="fa fa-clock"></i>&nbsp; Pending
                                </span>
                            @elseif($task->status === 'missed')
                                <span class="badge bg-danger" style="font-size: 1.5rem; padding: 0.5rem 1rem;">
                                    <i class="fa fa-exclamation-triangle"></i>&nbsp; Missed
                                </span>
                            @elseif($task->status === 'rejected')
                                <span class="badge bg-danger" style="font-size: 1.5rem; padding: 0.5rem 1rem;">
                                    <i class="fa fa-times"></i>&nbsp; Rejected
                                </span>
                            @elseif($task->status === 'skipped')
                                <span class="badge bg-secondary" style="font-size: 1.5rem; padding: 0.5rem 1rem;">
                                    <i class="fa fa-forward"></i>&nbsp; Skipped
                                </span>
                            @endif
                        </div>
                        <hr>
                        <div class="list-group list-group-transparent">
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col">Created</div>
                                    <div class="col-auto text-end">
                                        <small class="text-muted">{{ $task->created_at->format('M d, Y') }}</small>
                                    </div>
                                </div>
                            </div>
                            @if($task->started_at)
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col">Started</div>
                                    <div class="col-auto text-end">
                                        <small class="text-muted">{{ $task->started_at->format('M d, Y H:i') }}</small>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if($task->completed_at)
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col">Completed</div>
                                    <div class="col-auto text-end">
                                        <small class="text-muted">{{ $task->completed_at->format('M d, Y H:i') }}</small>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if($task->submission && $task->submission->approval && $task->submission->approval->reviewed_at)
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col">Reviewed</div>
                                    <div class="col-auto text-end">
                                        <small class="text-muted">{{ $task->submission->approval->reviewed_at->format('M d, Y H:i') }}</small>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/lightbox.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('assets/css/lightbox.min.css') }}">
@endpush
@endsection

