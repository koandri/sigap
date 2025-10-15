<!-- Close Work Order Modal -->
<div class="modal modal-blur fade" id="closeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Close Work Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('maintenance.work-orders.close', $workOrder) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h4>Satisfaction Review</h4>
                        <p class="mb-0">Please review the completed work and provide your feedback.</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Closure Action</label>
                        <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column">
                            <label class="form-selectgroup-item flex-fill">
                                <input type="radio" name="action" value="close" class="form-selectgroup-input" 
                                       {{ old('action') == 'close' ? 'checked' : '' }}>
                                <div class="form-selectgroup-label">
                                    <div class="form-selectgroup-label-content">
                                        <span class="form-selectgroup-label-title">Close Work Order</span>
                                        <span class="form-selectgroup-label-desc">Work is satisfactory and complete</span>
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
                        <label class="form-label">Closing Notes</label>
                        <textarea name="closing_notes" class="form-control" rows="4" 
                                  placeholder="Provide your feedback on the completed work, satisfaction level, or any concerns">{{ old('closing_notes') }}</textarea>
                        @error('closing_notes')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Closure</button>
                </div>
            </form>
        </div>
    </div>
</div>
