<!-- Add Action Modal -->
<div class="modal modal-blur fade" id="actionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('maintenance.work-orders.add-action', $workOrder) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Action Type</label>
                                <select name="action_type" class="form-select" required>
                                    <option value="">Select Action Type</option>
                                    <option value="spare-part-replacement" {{ old('action_type') == 'spare-part-replacement' ? 'selected' : '' }}>
                                        Spare Part Replacement
                                    </option>
                                    <option value="send-for-repair" {{ old('action_type') == 'send-for-repair' ? 'selected' : '' }}>
                                        Send for Repair
                                    </option>
                                    <option value="retire-equipment" {{ old('action_type') == 'retire-equipment' ? 'selected' : '' }}>
                                        Retire Equipment
                                    </option>
                                    <option value="cleaning" {{ old('action_type') == 'cleaning' ? 'selected' : '' }}>
                                        Cleaning
                                    </option>
                                    <option value="adjustment" {{ old('action_type') == 'adjustment' ? 'selected' : '' }}>
                                        Adjustment
                                    </option>
                                    <option value="calibration" {{ old('action_type') == 'calibration' ? 'selected' : '' }}>
                                        Calibration
                                    </option>
                                    <option value="other" {{ old('action_type') == 'other' ? 'selected' : '' }}>
                                        Other
                                    </option>
                                </select>
                                @error('action_type')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Date/Time Performed</label>
                                <input type="datetime-local" name="performed_at" class="form-control" 
                                       value="{{ old('performed_at', now()->format('Y-m-d\TH:i')) }}">
                                @error('performed_at')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Action Description</label>
                        <textarea name="action_description" class="form-control" rows="3" 
                                  placeholder="Describe what action was performed, what was done, etc." required>{{ old('action_description') }}</textarea>
                        @error('action_description')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea name="notes" class="form-control" rows="2" 
                                  placeholder="Any additional details, observations, or notes">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Action</button>
                </div>
            </form>
        </div>
    </div>
</div>
