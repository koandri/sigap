<!-- Verify Work Order Modal -->
<div class="modal modal-blur fade" id="verifyModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Verify Work Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('maintenance.work-orders.verify', $workOrder) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h4>Review Checklist</h4>
                        <ul class="mb-0">
                            <li>Work completed according to requirements</li>
                            <li>All safety procedures followed</li>
                            <li>Equipment properly tested and functioning</li>
                            <li>Documentation complete and accurate</li>
                            <li>Work area cleaned and secured</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Verification Action</label>
                        <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column">
                            <label class="form-selectgroup-item flex-fill">
                                <input type="radio" name="action" value="approve" class="form-selectgroup-input" 
                                       {{ old('action') == 'approve' ? 'checked' : '' }}>
                                <div class="form-selectgroup-label">
                                    <div class="form-selectgroup-label-content">
                                        <span class="form-selectgroup-label-title">Approve</span>
                                        <span class="form-selectgroup-label-desc">Work meets standards and is approved</span>
                                    </div>
                                </div>
                            </label>
                            <label class="form-selectgroup-item flex-fill">
                                <input type="radio" name="action" value="rework" class="form-selectgroup-input" 
                                       {{ old('action') == 'rework' ? 'checked' : '' }}>
                                <div class="form-selectgroup-label">
                                    <div class="form-selectgroup-label-content">
                                        <span class="form-selectgroup-label-title">Request Rework</span>
                                        <span class="form-selectgroup-label-desc">Work needs additional attention</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                        @error('action')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Verification Notes</label>
                        <textarea name="verification_notes" class="form-control" rows="4" 
                                  placeholder="Provide detailed feedback on the work performed, any issues found, or approval comments">{{ old('verification_notes') }}</textarea>
                        @error('verification_notes')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Verification</button>
                </div>
            </form>
        </div>
    </div>
</div>
