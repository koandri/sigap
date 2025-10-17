<!-- Asset Disposal Modal -->
<div class="modal modal-blur fade" id="disposalModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">⚠️ Mark Asset for Disposal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('maintenance.work-orders.dispose-asset', $workOrder) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This action will:
                        <ul class="mt-2 mb-0">
                            <li>Mark the asset as <strong>DISPOSED</strong></li>
                            <li>Deactivate the asset (set inactive)</li>
                            <li>Deactivate ALL active maintenance schedules for this asset</li>
                            <li>Notify all Engineering Staff members</li>
                            <li>Create an audit trail linking to this work order</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label required">Asset</label>
                        <input type="text" class="form-control" value="{{ $workOrder->asset->name }} ({{ $workOrder->asset->code }})" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label required">Active Schedules</label>
                        <input type="text" class="form-control" value="{{ $workOrder->asset->maintenanceSchedules()->where('is_active', true)->count() }} schedule(s) will be deactivated" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label required">Disposal Reason</label>
                        <textarea name="disposal_reason" class="form-control" rows="4" 
                                  placeholder="Explain why this asset is being disposed (e.g., beyond repair, obsolete, damaged beyond economic repair, etc.)" 
                                  required>{{ old('disposal_reason') }}</textarea>
                        @error('disposal_reason')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" name="confirm_disposal" class="form-check-input" value="1" required>
                            <span class="form-check-label">
                                I confirm that this asset should be marked as disposed and understand that all related maintenance schedules will be deactivated.
                            </span>
                        </label>
                        @error('confirm_disposal')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-trash"></i> Confirm Disposal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

