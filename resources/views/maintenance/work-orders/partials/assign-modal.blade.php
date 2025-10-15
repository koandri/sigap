<!-- Assign Work Order Modal -->
<div class="modal modal-blur fade" id="assignModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Work Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('maintenance.work-orders.assign', $workOrder) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Assign to Operator</label>
                                <select name="assigned_to" class="form-select" required>
                                    <option value="">Select Operator</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Estimated Start Date</label>
                                <input type="date" name="scheduled_date" class="form-control" 
                                       value="{{ old('scheduled_date', now()->format('Y-m-d')) }}" required>
                                @error('scheduled_date')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Estimated Hours</label>
                                <input type="number" name="estimated_hours" class="form-control" 
                                       value="{{ old('estimated_hours') }}" step="0.1" min="0">
                                @error('estimated_hours')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assignment Notes</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Any specific instructions or notes for the operator">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Work Order</button>
                </div>
            </form>
        </div>
    </div>
</div>
