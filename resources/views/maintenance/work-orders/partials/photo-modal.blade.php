<!-- Photo Upload Modal -->
<div class="modal modal-blur fade" id="photoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <form action="{{ route('maintenance.work-orders.upload-photo', $workOrder) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Photo</label>
                        <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*" required>
                        @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Photo Type</label>
                        <select name="photo_type" class="form-select @error('photo_type') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            <option value="progress">Progress</option>
                            <option value="before">Before</option>
                            <option value="after">After</option>
                            <option value="issue">Issue</option>
                        </select>
                        @error('photo_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Caption</label>
                        <textarea name="caption" class="form-control @error('caption') is-invalid @enderror" rows="3" placeholder="Enter a caption for this photo"></textarea>
                        @error('caption')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Photo</button>
                </div>
            </form>
        </div>
    </div>
</div>
