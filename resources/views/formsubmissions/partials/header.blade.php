@props(['submission'])

<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="far fa-file-lines"></i>&nbsp;
                Submission Details
            </h3>
            <div class="card-actions">
                <!-- Manual Workflow Trigger (Admin Only) -->
                @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) && 
                    $submission->status === 'submitted' && 
                    $submission->needsApproval() && 
                    !$submission->approvalLogs()->exists())
                    <form action="{{ route('formsubmissions.start-workflow', $submission) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" 
                                class="btn btn-sm btn-warning" 
                                onclick="return confirm('Start approval workflow for this submission?')"
                                title="Manually start approval workflow">
                            <i class="far fa-play"></i>&nbsp;Start Workflow
                        </button>
                    </form>
                @endif
                
                <a href="{{ route('formsubmissions.print', $submission) }}" 
                   target="_blank" 
                   class="btn btn-sm btn-outline-secondary">
                    <i class="far fa-print"></i>&nbsp;Print
                </a>
                <a href="{{ route('formsubmissions.submissions') }}" 
                   class="btn btn-sm btn-secondary">
                    <i class="far fa-arrow-left"></i>&nbsp;Back
                </a>
            </div>
        </div>
    </div>
</div>