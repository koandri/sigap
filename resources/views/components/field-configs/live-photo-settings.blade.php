@props(['field' => null, 'hasSubmissions' => false])

@if($field && $field->field_type === 'live_photo' || !$field)
<div id="livePhotoSettingsSection" style="{{ $field && $field->field_type === 'live_photo' ? '' : 'display: none;' }}">
    <div class="hr-text hr-text-start">Live Photo Settings</div>
    
    @php
        $photoRules = $field ? ($field->validation_rules ?? []) : [];
        $maxPhotos = $photoRules['max_photos'] ?? 1;
        $photoQuality = $photoRules['photo_quality'] ?? 0.8;
        $requireLocation = $photoRules['require_location'] ?? false;
    @endphp
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="max_photos" class="form-label">Maximum Photos</label>
                <input type="number" class="form-control" id="max_photos" name="max_photos" value="{{ $maxPhotos }}" min="1" max="5" {{ $hasSubmissions ? 'readonly' : '' }}>
                <small class="form-text text-muted">Maximum number of photos that can be captured</small>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="mb-3">
                <label for="photo_quality" class="form-label">Photo Quality</label>
                <select class="form-select" id="photo_quality" name="photo_quality" {{ $hasSubmissions ? 'disabled' : '' }}>
                    <option value="0.6" {{ $photoQuality == 0.6 ? 'selected' : '' }}>Low (0.6)</option>
                    <option value="0.8" {{ $photoQuality == 0.8 ? 'selected' : '' }}>Medium (0.8)</option>
                    <option value="0.9" {{ $photoQuality == 0.9 ? 'selected' : '' }}>High (0.9)</option>
                    <option value="1.0" {{ $photoQuality == 1.0 ? 'selected' : '' }}>Maximum (1.0)</option>
                </select>
                <small class="form-text text-muted">Higher quality = larger file size</small>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="require_location" name="require_location" value="1" {{ $requireLocation ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }}>
                    <label class="form-check-label" for="require_location">
                        <strong>Require Location Data</strong>
                        <br>
                        <small class="text-muted">Capture GPS coordinates with photos (requires location permission)</small>
                    </label>
                </div>
                @if($hasSubmissions && $requireLocation)
                    <input type="hidden" name="require_location" value="1">
                @endif
            </div>
        </div>
    </div>
    
    <div class="alert alert-info">
        <div class="alert-icon">
            <i class="fa-regular fa-camera"></i>
        </div>
        <div>
            <h4 class="alert-heading">Live Photo Field</h4>
            <div class="alert-description">
                This field type forces users to take photos using their device's rear camera in real-time. 
                Photos cannot be selected from gallery or taken with front camera.
            </div>
        </div>
    </div>
</div>
@endif
