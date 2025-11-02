@props(['field' => null, 'hasSubmissions' => false, 'isEdit' => false])

<!-- Basic Field Information -->
<div class="row mb-3">
    <label for="field_code" class="col-sm-2 col-form-label required">Field Code</label>
    <div class="col-sm-10">
        <input type="text" class="form-control" id="field_code" name="field_code" value="{{ old('field_code', $field?->field_code) }}" placeholder="e.g., employee_name" pattern="[a-z][a-z0-9_]*" {{ $hasSubmissions ? 'readonly' : 'required' }}>
        <small class="form-text text-muted">
            @if($hasSubmissions)
                Cannot change field code after submissions
            @else
                Lowercase letters, numbers, and underscores only. Must start with letter.
            @endif
        </small>
    </div>
</div>

<div class="row mb-3">
    <label for="field_type" class="col-sm-2 col-form-label required">Field Type</label>
    <div class="col-sm-10">
        <select class="form-select" id="field_type" name="field_type" {{ $hasSubmissions ? 'disabled' : 'required' }}>
            <option value="">-- Select Field Type --</option>
            @foreach(\App\Models\FormField::FIELD_TYPES as $value => $label)
            <option value="{{ $value }}" {{ old('field_type', $field?->field_type) == $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @if($hasSubmissions && $field)
            <input type="hidden" name="field_type" value="{{ $field->field_type }}">
            <small class="form-text text-muted">Cannot change field type after submissions</small>
        @endif
    </div>
</div>

<div class="row mb-3">
    <label for="field_label" class="col-sm-2 col-form-label required">Field Label</label>
    <div class="col-sm-10">
        <input type="text" class="form-control" id="field_label" name="field_label" value="{{ old('field_label', $field?->field_label) }}" placeholder="e.g., Employee Name" required>
        <small class="form-text text-muted">This will be displayed to users</small>
    </div>
</div>

<div class="row mb-3">
    <label for="placeholder" class="col-sm-2 col-form-label">Placeholder Text</label>
    <div class="col-sm-10">
        <input type="text" class="form-control" id="placeholder" name="placeholder" value="{{ old('placeholder', $field?->placeholder) }}" placeholder="e.g., Enter your full name">
    </div>
</div>

<div class="row mb-3">
    <label for="help_text" class="col-sm-2 col-form-label">Help Text</label>
    <div class="col-sm-10">
        <textarea class="form-control" id="help_text" name="help_text" rows="2" placeholder="Additional instructions for users">{{ old('help_text', $field?->help_text) }}</textarea>
    </div>
</div>

<!-- Required Field Section -->
<div class="mb-4" id="requiredFieldSection" style="{{ $field && in_array($field->field_type, ['calculated', 'hidden']) ? 'display: none;' : '' }}">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1" {{ old('is_required', $field?->is_required) ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }} {{ $field && in_array($field->field_type, ['calculated', 'hidden']) ? 'disabled' : '' }}>
        <label class="form-check-label" for="is_required">
            <strong>Required Field</strong>
            <br>
            <small class="text-muted">
                @if($hasSubmissions)
                    Cannot change requirement status after submissions
                @elseif($field && in_array($field->field_type, ['calculated', 'hidden']))
                    {{ ucfirst($field->field_type) }} fields cannot be required
                @else
                    User must fill this field
                @endif
            </small>
        </label>
        
        @if($hasSubmissions && $field && $field->is_required && !in_array($field->field_type, ['calculated', 'hidden']))
            <input type="hidden" name="is_required" value="1">
        @endif
    </div>
</div>

<!-- Add info alert for calculated/hidden fields -->
@if($field && in_array($field->field_type, ['calculated', 'hidden']))
<div class="alert alert-info alert-dismissible" role="alert">
    <div class="alert-icon">
        <i class="far fa-circle-info"></i>&nbsp;
    </div>
    <div>
        <h4 class="alert-heading">{{ ucfirst($field->field_type) }} Field</h4>
        <div class="alert-description">
            This field type is automatically handled by the system and cannot be marked as required.
        </div>
    </div>
</div>
@endif
