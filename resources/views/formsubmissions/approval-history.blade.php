@extends('layouts.app')

@section('title', 'Approval History')

@push('css')
<style>
    .timeline-item {
        position: relative;
    }

    .timeline-line {
        width: 2px;
        height: 100px;
        background-color: #dee2e6;
        margin: 10px auto;
    }

    .timeline-icon {
        position: relative;
        z-index: 2;
        background: white;
        padding: 10px 0;
    }

    .table-danger {
        background-color: rgba(220, 53, 69, 0.1);
    }

    .table-warning {
        background-color: rgba(255, 193, 7, 0.1);
    }
</style>
@endpush

@section('content')
            <!-- BEGIN PAGE HEADER -->
            <div class="page-header d-print-none" aria-label="Page header">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <h2 class="page-title">@yield('title')</h2>
                            <p class="text-muted mb-0">
                                Submission: <strong>{{ $submission->submission_code }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE HEADER -->
            <!-- BEGIN PAGE BODY -->
            <div class="page-body">
                <div class="container-xl">
                    <div class="row">
                        @include('layouts.alerts')
                    </div>
                    
                    <!-- Submission Summary -->
                    <div class="row row-deck row-cards mb-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Submission Summary</h3>
                                    <div class="card-actions">
                                        <a href="{{ route('formsubmissions.show', $submission) }}" class="btn btn-sm btn-secondary">
                                            <i class="fa-regular fa-arrow-left"></i>&nbsp;Back to Submission
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card mb-4">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <h5>{{ $submission->formVersion->form->name }}</h5>
                                                            <p class="text-muted mb-0">
                                                                Form #: {{ $submission->formVersion->form->form_no }} v{{ $submission->formVersion->version_number }} |
                                                                Submitted by: {{ $submission->submitter->name }}
                                                            </p>
                                                        </div>
                                                        <div class="col-md-4 text-end">
                                                            <span class="badge badge-outline text-{{ $submission->status === 'approved' ? 'success' : ($submission->status === 'rejected' ? 'danger' : 'warning') }} fs-6">
                                                                {{ ucfirst(str_replace('_', ' ', $submission->status)) }}
                                                            </span>
                                                            @if($approvalSummary)
                                                                <div class="mt-2">
                                                                    
                                                                    <div class="progress" style="height: 8px;">
                                                                        <div class="progress-bar bg-success" style="width: {{ $approvalSummary['progress_percentage'] }}%"></div>
                                                                    </div>
                                                                    <small class="text-muted">Progress: {{ $approvalSummary['progress_percentage'] }}%</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>  
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Timeline -->
                    <div class="row row-deck row-cards mb-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fa-regular fa-clock-rotate-left"></i>&nbsp;Approval Timeline
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            @if($submission->approvalHistory->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 80px;">Status</th>
                                                            <th style="width: 300px;">Details</th>
                                                            <th>Comments</th>
                                                            <th style="width: 200px;">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($submission->approvalHistory->sortBy('created_at') as $index => $log)
                                                        <tr>
                                                            <td style="text-align: center;">
                                                                @switch($log->status)
                                                                    @case('approved')
                                                                        <i class="fa-regular fa-circle-check text-success" style="font-size: 2rem;"></i>
                                                                        @break
                                                                    @case('rejected')
                                                                        <i class="fa-regular fa-circle-x text-danger" style="font-size: 2rem;"></i>
                                                                        @break
                                                                    @case('pending')
                                                                        <i class="fa-regular fa-clock text-warning" style="font-size: 2rem;"></i>
                                                                        @break
                                                                    @case('escalated')
                                                                        <i class="fa-regular fa-circle-arrow-up text-info" style="font-size: 2rem;"></i>
                                                                        @break
                                                                    @default
                                                                        <i class="fa-regular fa-circle text-secondary" style="font-size: 2rem;"></i>
                                                                @endswitch
                                                                @if($index < $submission->approvalHistory->count() - 1)
                                                                    <div class="timeline-line"></div>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                {{ $log->step->step_name }}
                                                                <p class="text-muted mb-2">
                                                                    <strong>Assigned to:</strong> {{ $log->assignedUser->name }}
                                                                    @if($log->approver && $log->approved_by != $log->assigned_to)
                                                                        <br><strong>Processed by:</strong> {{ $log->approver->name }}
                                                                    @endif
                                                                </p>
                                                                <!-- Timestamps -->
                                                                <div class="row text-muted small">
                                                                    <div class="col-md-6">
                                                                        @if($log->assigned_at)
                                                                        <i class="fa-regular fa-user-tag"></i>
                                                                        Assigned: {{ $log->assigned_at->format('d M Y H:i') }}
                                                                        @endif
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        @if($log->action_at)
                                                                        <i class="fa-regular fa-calendar-check"></i>
                                                                        {{ ucfirst($log->status) }}: {{ $log->action_at->format('d M Y H:i') }}
                                                                        @elseif($log->due_at)
                                                                        <i class="fa-regular fa-calendar-xmark"></i>
                                                                        Due: {{ $log->due_at->format('d M Y H:i') }}
                                                                        @if($log->isOverdue())
                                                                        <span class="text-danger">(Overdue)</span>
                                                                        @endif
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                @if($log->comments)
                                                                {{ $log->comments }}
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <!-- Action Buttons for Pending -->
                                                                @if($log->status === 'pending' && $log->assigned_to === auth()->id())
                                                                <div class="btn-group btn-group-sm" role="group">
                                                                    <a href="#" class="btn btn-outline-danger" title="Approve" onclick="confirm('Approve this submission?'); event.preventDefault(); document.getElementById('approve-{{ $submission->id }}').submit();">
                                                                        <i class="fa-regular fa-circle-check"></i>&nbsp;Approve
                                                                    </a>
                                                                    <form id="approve-{{ $submission->id }}" action="{{ route('formsubmissions.approve', $submission) }}" method="POST" style="display: none;">
                                                                        @csrf
                                                                    </form>
                                                                    <a href="#" class="btn btn-outline-danger" title="Reject" onclick="showRejectModal('{{ $submission->id }}', '{{ $submission->submission_code }}')">
                                                                        <i class="fa-regular fa-circle-x"></i>&nbsp;Reject
                                                                    </a>
                                                                </div>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            @else
                                            <div class="text-center py-4">
                                                <i class="fa-regular fa-clock-rotate-left display-4 text-muted"></i>
                                                <p class="mt-3 text-muted">No approval history available</p>
                                            </div>
                                            @endif
                                        </div>  
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($approvalSummary)
                    <!-- Workflow Summary -->
                    <div class="row row-deck row-cards">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Workflow Summary</h3>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <div class="row">
                                        <div class="col-md-3 text-center">
                                            <h4 class="text-success">{{ $approvalSummary['completed_steps'] }}</h4>
                                            <small class="text-muted">Completed Steps</small>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <h4 class="text-warning">{{ $approvalSummary['pending_steps'] }}</h4>
                                            <small class="text-muted">Pending Steps</small>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <h4 class="text-danger">{{ $approvalSummary['rejected_steps'] }}</h4>
                                            <small class="text-muted">Rejected Steps</small>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <h4 class="text-info">{{ $approvalSummary['overdue_steps'] }}</h4>
                                            <small class="text-muted">Overdue Steps</small>
                                        </div>  
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection


@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Approval History</h2>
                <p class="text-muted mb-0">
                    Submission: <strong>{{ $submission->submission_code }}</strong>
                </p>
            </div>
            
        </div>

        

        

        
    </div>
</div>

<!-- Reject Modal (same as pending approvals) -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Submission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning alert-dismissible" role="alert">
                        <div class="alert-icon">
                            <i class="fa-regular fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <div class="alert-description">
                                You are about to reject submission: <strong id="rejectSubmissionCode"></strong>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reject_comments" class="form-label required">
                            Rejection Reason
                        </label>
                        <textarea class="form-control" id="reject_comments" name="comments" rows="4" placeholder="Please provide detailed reason for rejection..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="action" value="reject" class="btn btn-danger">
                        <i class="fa-regular fa-circle-x"></i>&nbsp;Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showRejectModal(submissionId, submissionCode) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectForm');
        const codeSpan = document.getElementById('rejectSubmissionCode');
        
        // Set form action
        form.action = `/formsubmissions/${submissionId}/approve`;
        
        // Set submission code
        codeSpan.textContent = submissionCode;
        
        // Clear comments
        document.getElementById('reject_comments').value = '';
        
        // Show modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
</script>
@endpush