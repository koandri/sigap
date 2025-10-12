@props(['field' => null, 'hasSubmissions' => false])

<!-- Basic Field Information -->
<div class="row mb-3">
    <label for="field_code" class="col-sm-2 col-form-label required">Field Code</label>
    <div class="col-sm-10">
        <input type="text" 
               class="form-control" 
               id="field_code" 
               name="field_code" 
               value="{{ old('field_code', $field?->field_code) }}" 
               placeholder="e.g., employee_name" 
               pattern="[a-z][a-z0-9_]*" 
               {{ $hasSubmissions ? 'readonly' : 'required' }}>
        <small class="form-text">Lowercase letters, numbers, and underscores only. Must start with letter.</small>
    </div>
</div>

<div class="row mb-3">
    <label for="field_label" class="col-sm-2 col-form-label required">Field Label</label>
    <div class="col-sm-10">
        <input type="text" 
               class="form-control" 
               id="field_label" 
               name="field_label" 
               value="{{ old('field_label', $field?->field_label) }}" 
               placeholder="e.g., Employee Name" 
               required>
    </div>
</div>

<div class="row mb-3">
    <label for="field_type" class="col-sm-2 col-form-label required">Field Type</label>
    <div class="col-sm-10">
        <select class="form-select" id="field_type" name="field_type" {{ $hasSubmissions ? 'disabled' : 'required' }}>
            <option value="">Select field type...</option>
            <option value="text_short" {{ old('field_type', $field?->field_type) == 'text_short' ? 'selected' : '' }}>Short Text</option>
            <option value="text_long" {{ old('field_type', $field?->field_type) == 'text_long' ? 'selected' : '' }}>Long Text (WYSIWYG)</option>
            <option value="number" {{ old('field_type', $field?->field_type) == 'number' ? 'selected' : '' }}>Number</option>
            <option value="decimal" {{ old('field_type', $field?->field_type) == 'decimal' ? 'selected' : '' }}>Decimal</option>
            <option value="date" {{ old('field_type', $field?->field_type) == 'date' ? 'selected' : '' }}>Date</option>
            <option value="datetime" {{ old('field_type', $field?->field_type) == 'datetime' ? 'selected' : '' }}>Date & Time</option>
            <option value="select_single" {{ old('field_type', $field?->field_type) == 'select_single' ? 'selected' : '' }}>Single Select</option>
            <option value="select_multiple" {{ old('field_type', $field?->field_type) == 'select_multiple' ? 'selected' : '' }}>Multiple Select</option>
            <option value="radio" {{ old('field_type', $field?->field_type) == 'radio' ? 'selected' : '' }}>Radio Buttons</option>
            <option value="checkbox" {{ old('field_type', $field?->field_type) == 'checkbox' ? 'selected' : '' }}>Checkboxes</option>
            <option value="boolean" {{ old('field_type', $field?->field_type) == 'boolean' ? 'selected' : '' }}>Yes/No Toggle</option>
            <option value="file" {{ old('field_type', $field?->field_type) == 'file' ? 'selected' : '' }}>File Upload</option>
            <option value="signature" {{ old('field_type', $field?->field_type) == 'signature' ? 'selected' : '' }}>Signature Pad</option>
            <option value="calculated" {{ old('field_type', $field?->field_type) == 'calculated' ? 'selected' : '' }}>Calculated Field</option>
            <option value="hidden" {{ old('field_type', $field?->field_type) == 'hidden' ? 'selected' : '' }}>Hidden Field</option>
        </select>
        @if($hasSubmissions)
            <input type="hidden" name="field_type" value="{{ $field?->field_type }}">
        @endif
    </div>
</div>

<div class="row mb-3">
    <label for="placeholder" class="col-sm-2 col-form-label">Placeholder</label>
    <div class="col-sm-10">
        <input type="text" 
               class="form-control" 
               id="placeholder" 
               name="placeholder" 
               value="{{ old('placeholder', $field?->placeholder) }}" 
               placeholder="e.g., Enter employee name">
    </div>
</div>

<div class="row mb-3">
    <label for="help_text" class="col-sm-2 col-form-label">Help Text</label>
    <div class="col-sm-10">
        <textarea class="form-control" id="help_text" name="help_text" rows="2" placeholder="Additional help text for users">{{ old('help_text', $field?->help_text) }}</textarea>
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-2"></div>
    <div class="col-sm-10">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1" {{ old('is_required', $field?->is_required) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_required">
                Required field
            </label>
        </div>
    </div>
</div>