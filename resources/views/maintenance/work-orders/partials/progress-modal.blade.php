<!-- Log Progress Modal -->
<div class="modal modal-blur fade" id="progressModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Progress</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('maintenance.work-orders.log-progress', $workOrder) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Hours Worked</label>
                                <input type="number" name="hours_worked" class="form-control" 
                                       value="{{ old('hours_worked') }}" step="0.1" min="0.1" required>
                                @error('hours_worked')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Date/Time of Work</label>
                                <input type="datetime-local" name="logged_at" class="form-control" 
                                       value="{{ old('logged_at', now()->format('Y-m-d\TH:i')) }}">
                                @error('logged_at')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Completion Percentage</label>
                        <div class="d-flex align-items-center">
                            <input type="range" name="completion_percentage" class="form-range me-3" 
                                   min="0" max="100" value="{{ old('completion_percentage', 0) }}" 
                                   oninput="document.getElementById('completionValue').textContent = this.value + '%'">
                            <span id="completionValue" class="badge bg-primary">{{ old('completion_percentage', 0) }}%</span>
                        </div>
                        @error('completion_percentage')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Progress Notes</label>
                        <textarea name="progress_notes" class="form-control" rows="4" 
                                  placeholder="Describe what was accomplished, any issues encountered, next steps, etc." required>{{ old('progress_notes') }}</textarea>
                        @error('progress_notes')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Log Progress</button>
                </div>
            </form>
        </div>
    </div>
</div>
