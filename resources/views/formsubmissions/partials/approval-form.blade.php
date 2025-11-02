@props(['submission', 'pendingApproval'])

<div class="card mt-3">
    <div class="card-header">
        <h4 class="card-title">
            <i class="far fa-gavel"></i>&nbsp;
            Approval Action Required
        </h4>
    </div>
    <div class="card-body">
        <form action="{{ route('formsubmissions.approve', $submission) }}" method="POST" id="approvalForm">
            @csrf
            @method('PATCH')
            
            <input type="hidden" name="approval_step_id" value="{{ $pendingApproval->id }}">
            
            <!-- Approval Decision -->
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label">Decision <span class="text-danger">*</span></label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="decision" id="approve" value="approve" required>
                        <label class="form-check-label text-success" for="approve">
                            <i class="far fa-check-circle"></i>&nbsp;
                            <strong>Approve</strong>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="decision" id="reject" value="reject" required>
                        <label class="form-check-label text-danger" for="reject">
                            <i class="far fa-times-circle"></i>&nbsp;
                            <strong>Reject</strong>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Comments -->
            <div class="row mb-3">
                <div class="col-12">
                    <label for="comments" class="form-label">
                        Comments
                        <span class="form-check-description">(Optional - will be visible to submitter)</span>
                    </label>
                    <textarea class="form-control" 
                              name="comments" 
                              id="comments" 
                              rows="3" 
                              placeholder="Add any comments about your decision..."></textarea>
                </div>
            </div>

            <!-- Rejection Reason (shown when reject is selected) -->
            <div class="row mb-3" id="rejectionReason" style="display: none;">
                <div class="col-12">
                    <label for="rejection_reason" class="form-label">
                        Rejection Reason <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" name="rejection_reason" id="rejection_reason">
                        <option value="">Select a reason...</option>
                        <option value="incomplete_information">Incomplete Information</option>
                        <option value="incorrect_data">Incorrect Data</option>
                        <option value="missing_documents">Missing Documents</option>
                        <option value="policy_violation">Policy Violation</option>
                        <option value="other">Other (specify in comments)</option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row">
                <div class="col-12">
                    <div class="btn-group" role="group">
                        <button type="submit" class="btn btn-sm btn-success" id="submitApproval">
                            <i class="far fa-check"></i>&nbsp;Submit
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetApprovalForm()">
                            <i class="far fa-undo"></i>&nbsp;Reset
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Wait for page to be fully loaded to prevent layout forcing
window.addEventListener('load', function() {
    // Show/hide rejection reason based on decision
    const decisionRadios = document.querySelectorAll('input[name="decision"]');
    const rejectionReason = document.getElementById('rejectionReason');
    const rejectionReasonSelect = document.getElementById('rejection_reason');
    
    decisionRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'reject') {
                rejectionReason.style.display = 'block';
                rejectionReasonSelect.required = true;
            } else {
                rejectionReason.style.display = 'none';
                rejectionReasonSelect.required = false;
                rejectionReasonSelect.value = '';
            }
        });
    });

    // Form submission handling
    const approvalForm = document.getElementById('approvalForm');
    const submitButton = document.getElementById('submitApproval');
    
    approvalForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const decision = formData.get('decision');
        
        // Validate rejection reason if rejecting
        if (decision === 'reject' && !formData.get('rejection_reason')) {
            showToast('Please select a rejection reason.', 'error');
            return;
        }
        
        // Confirm action
        const actionText = decision === 'approve' ? 'approve' : 'reject';
        if (!confirm(`Are you sure you want to ${actionText} this submission?`)) {
            return;
        }
        
        // Disable submit button and show loading
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="far fa-spinner fa-spin"></i>&nbsp; &nbsp;Processing...';
        
        // Submit form
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Decision submitted successfully!', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showToast(data.message || 'An error occurred. Please try again.', 'error');
            }
        })
        .catch(error => {
            showToast('An error occurred. Please try again.', 'error');
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="far fa-check"></i>&nbsp;Submit Decision';
        });
    });
});

function resetApprovalForm() {
    const form = document.getElementById('approvalForm');
    form.reset();
    document.getElementById('rejectionReason').style.display = 'none';
    document.getElementById('rejection_reason').required = false;
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        top: 2rem;
        right: 2rem;
        background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        z-index: 9999;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}
</script>