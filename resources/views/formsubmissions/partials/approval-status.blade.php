@props(['submission', 'approvalSummary', 'canApprove', 'pendingApproval'])

<div class="col-12">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">
                <i class="fa-regular fa-circle-check"></i>&nbsp;Approval Status
            </h3>
            @if($approvalSummary)
                <x-status-badge :status="$submission->status" />
            @endif
        </div>
        
        <div class="card-body">
            @if($approvalSummary)
                <!-- Progress Bar -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted">Approval Progress</small>
                        <small class="text-muted">{{ $approvalSummary['progress_percentage'] }}%</small>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-success" 
                             style="width: {{ $approvalSummary['progress_percentage'] }}%"></div>
                    </div>
                </div>

                <!-- Status Summary -->
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="border rounded p-2">
                            <h4 class="mb-0 text-success">{{ $approvalSummary['completed_steps'] }}</h4>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-2">
                            <h4 class="mb-0 text-warning">{{ $approvalSummary['pending_steps'] }}</h4>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-2">
                            <h4 class="mb-0 text-danger">{{ $approvalSummary['rejected_steps'] }}</h4>
                            <small class="text-muted">Rejected</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-2">
                            <h4 class="mb-0 text-danger">{{ $approvalSummary['overdue_steps'] }}</h4>
                            <small class="text-muted">Overdue</small>
                        </div>
                    </div>
                </div>

                <!-- Current Step Info -->
                @if($approvalSummary['current_step'])
                    <div class="alert alert-info alert-dismissible mt-3" role="alert">
                        <div class="alert-icon">
                            <i class="fa-regular fa-clock"></i>
                        </div>
                        <div>
                            <div class="alert-description">
                                <strong>Current Step:</strong> {{ $approvalSummary['current_step']->step_name }}
                                <br>
                                <strong>Waiting for:</strong> {{ $approvalSummary['current_step']->getApproverDisplayName() }}
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Approval Action (if user can approve) -->
                @if($canApprove && $pendingApproval)
                    @include('formsubmissions.partials.approval-form', [
                        'submission' => $submission,
                        'pendingApproval' => $pendingApproval
                    ])
                @endif

                <!-- View Full Approval History -->
                <div class="text-center mt-3">
                    <a href="{{ route('formsubmissions.approval.history', $submission) }}" 
                       class="btn btn-sm btn-outline-primary">
                        <i class="fa-regular fa-clock-rotate-left"></i>&nbsp;View Full Approval History
                    </a>
                </div>
            @elseif($submission->needsApproval() && $submission->status === 'submitted' && !$submission->approvalLogs()->exists())
                <!-- Workflow Not Started Alert -->
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <div class="alert-icon">
                        <i class="fa-regular fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h4 class="alert-heading">Approval Workflow Not Started</h4>
                        <div class="alert-description">
                            <div>
                                This submission requires approval but the workflow has not been triggered.
                                <br>
                                <small>Contact an administrator to start the approval process.</small>
                            </div>
                            @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']))
                                <form action="{{ route('formsubmissions.start-workflow', $submission) }}" method="POST" class="d-inline mt-2">
                                    @csrf
                                    <button type="submit" 
                                            class="btn btn-sm btn-warning" 
                                            onclick="return confirm('Start approval workflow for this submission?')">
                                        <i class="fa-regular fa-play"></i>&nbsp;Start Workflow Now
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info alert-dismissible" role="alert">
                    <div class="alert-icon">
                        <i class="fa-regular fa-circle-info"></i>
                    </div>
                    <div>
                        <div class="alert-description">
                            This form does not require approval and was automatically approved.
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>