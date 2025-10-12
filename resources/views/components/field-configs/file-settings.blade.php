@props(['field' => null, 'hasSubmissions' => false])

<!-- File Upload Settings -->
@if($field && $field->field_type === 'file' || !$field)
<div id="fileSettingsSection" style="{{ $field && $field->field_type === 'file' ? '' : 'display: none;' }}">
    <div class="hr-text hr-text-start">File Upload Settings</div>
    
    @php
        $fileRules = $field ? ($field->validation_rules ?? []) : [];
        $allowMultiple = $fileRules['allow_multiple'] ?? false;
        $maxFiles = $fileRules['max_files'] ?? 1;
        $allowedExtensions = implode(',', $fileRules['allowed_extensions'] ?? []);
        $maxFileSize = ($fileRules['max_file_size'] ?? 10240) / 1024; // Convert KB to MB
    @endphp
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="allow_multiple" name="allow_multiple" value="1" {{ $allowMultiple ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }}>
                    <label class="form-check-label" for="allow_multiple">
                        <strong>Allow Multiple Files</strong>
                        <br>
                        <small class="text-muted">Users can upload multiple files</small>
                    </label>
                </div>
                @if($hasSubmissions && $allowMultiple)
                    <input type="hidden" name="allow_multiple" value="1">
                @endif
            </div>
            
            <div class="mb-3" id="maxFilesDiv" style="{{ $allowMultiple ? '' : 'display: none;' }}">
                <label for="max_files" class="form-label">Maximum Number of Files</label>
                <input type="number" class="form-control" id="max_files" name="max_files" value="{{ $maxFiles }}" min="2" max="10" {{ $hasSubmissions ? 'readonly' : '' }}>
                @if($hasSubmissions)
                    <input type="hidden" name="max_files" value="{{ $maxFiles }}">
                @endif
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="mb-3">
                <label for="allowed_extensions" class="form-label">Allowed File Types</label>
                <input type="text" class="form-control" id="allowed_extensions" name="allowed_extensions" value="{{ $allowedExtensions }}" {{ $hasSubmissions ? 'readonly' : '' }}>
                @if($hasSubmissions)
                    <input type="hidden" name="allowed_extensions" value="{{ $allowedExtensions }}">
                @endif
            </div>
            
            <div class="mb-3">
                <label for="max_file_size" class="form-label">Max File Size (MB)</label>
                <input type="number" class="form-control" id="max_file_size" name="max_file_size" value="{{ $maxFileSize }}" min="1" max="50" {{ $hasSubmissions ? 'readonly' : '' }}>
                @if($hasSubmissions)
                    <input type="hidden" name="max_file_size" value="{{ $maxFileSize }}">
                @endif
            </div>
        </div>
    </div>
</div>
@endif
