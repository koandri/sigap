@extends('layouts.app')

@section('title', 'Pending Approvals')

@section('title', 'View')

@extends('layouts.app')

@section('content')
            <!-- BEGIN PAGE HEADER -->
            <div class="page-header d-print-none" aria-label="Page header">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <h2 class="page-title">@yield('title')</h2>
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

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <h3 class="mb-0 text-primary">{{ $stats['total_pending'] }}</h3>
                                    <small class="text-muted">Total Pending</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-danger">
                                <div class="card-body text-center">
                                    <h3 class="mb-0 text-danger">{{ $stats['overdue'] }}</h3>
                                    <small class="text-muted">Overdue</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <h3 class="mb-0 text-warning">{{ $stats['due_today'] }}</h3>
                                    <small class="text-muted">Due Today</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <h3 class="mb-0 text-info">{{ $stats['urgent'] }}</h3>
                                    <small class="text-muted">Urgent (≤4h)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($groupedApprovals->count() > 0)
                    @foreach($groupedApprovals as $formName => $approvals)
                    <!-- Form Group Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fa-regular fa-file-lines"></i>&nbsp;{{ $formName }}
                                <span class="badge bg-secondary ms-2">{{ $approvals->count() }} pending</span>
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Submission</th>
                                            <th>Submitter</th>
                                            <th>Step</th>
                                            <th>Submitted</th>
                                            <th>Due Date</th>
                                            <th>Priority</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($approvals as $approval)
                                        <tr class="{{ $approval->isOverdue() ? 'table-danger' : ($approval->due_at && $approval->due_at->isToday() ? 'table-warning' : '') }}">
                                            <td>
                                                <strong>{{ $approval->submission->submission_code }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $approval->submission->formVersion->form->form_no }} 
                                                    (v{{ $approval->submission->formVersion->version_number }})
                                                </small>
                                            </td>
                                            <td>
                                                {{ $approval->submission->submitter->name }}
                                                <br>
                                                <small class="text-muted">
                                                    {{ $approval->submission->submitter->departments->pluck('code')->join(', ') }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $approval->step->step_name }}</span>
                                                <br>
                                                <small class="text-muted">Step {{ $approval->step->getStepPosition() }}</small>
                                            </td>
                                            <td>
                                                {{ $approval->submission->submitted_at->format('d M Y H:i') }}
                                                <br>
                                                <small class="text-muted">
                                                    {{ $approval->submission->submitted_at->diffForHumans() }}
                                                </small>
                                            </td>
                                            <td>
                                                @if($approval->due_at)
                                                    {{ $approval->due_at->format('d M Y H:i') }}
                                                    <br>
                                                    <small class="text-{{ $approval->isOverdue() ? 'danger' : 'muted' }}">
                                                        {{ $approval->getTimeRemaining() }}
                                                    </small>
                                                @else
                                                    <span class="text-muted">No deadline</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($approval->isOverdue())
                                                    <span class="badge bg-danger">Overdue</span>
                                                @elseif($approval->due_at && $approval->due_at->diffInHours(now()) <= 4)
                                                    <span class="badge bg-warning">Urgent</span>
                                                @elseif($approval->due_at && $approval->due_at->isToday())
                                                    <span class="badge bg-info">Due Today</span>
                                                @else
                                                    <span class="badge bg-secondary">Normal</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('formsubmissions.show', $approval->submission) }}" class="btn btn-outline-primary" title="View & Approve">
                                                        <i class="fa-regular fa-eye"></i>&nbsp;Review
                                                    </a>
                                                    
                                                    <!-- Quick Approve/Reject Buttons -->
                                                    <form action="{{ route('formsubmissions.approve', $approval->submission) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm" title="Quick Approve" onclick="return confirm('Quick approve this submission?')">
                                                            <i class="fa-regular fa-check"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <button type="button" class="btn btn-danger btn-sm" title="Reject with Comments" onclick="showRejectModal('{{ $approval->submission->id }}', '{{ $approval->submission->submission_code }}')">
                                                        <i class="fa-regular fa-xmark"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <!-- No Pending Approvals -->
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fa-regular fa-circle-check text-success" style="font-size: 4rem;"></i>
                            <h4 class="mt-3">All Caught Up!</h4>
                            <p class="text-muted">You have no pending approvals at the moment.</p>
                            <a href="{{ route('formsubmissions.submissions') }}" class="btn btn-primary">
                                <i class="fa-regular fa-list"></i>&nbsp;View All Submissions
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- Reject Modal -->
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
                                            <textarea class="form-control" id="reject_comments" name="comments" rows="4" placeholder="Please provide reason for rejection..." required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" name="action" value="reject" class="btn btn-danger">
                                            <i class="fa-regular fa-circle-x"></i>&nbsp;Reject Submission
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
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

    // Auto-refresh page every 5 minutes to check for new approvals
    setInterval(function() {
        // Only refresh if user is still on the page and page is visible
        if (!document.hidden) {
            window.location.reload();
        }
    }, 300000); // 5 minutes
    </script>
@endpush