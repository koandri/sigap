@props(['field' => null, 'hasSubmissions' => false])

<!-- Hidden Field Settings -->
@if($field && $field->field_type === 'hidden' || !$hasSubmissions)
<div id="hiddenFieldSection" style="{{ $field && $field->field_type === 'hidden' ? '' : 'display: none;' }}">
    <div class="hr-text hr-text-start">Hidden Field Settings</div>
    
    @php
        $hiddenRules = $field?->validation_rules ?? [];
        $defaultValue = $hiddenRules['default_value'] ?? '';
        $valueType = $hiddenRules['value_type'] ?? 'static';
        $dynamicType = $hiddenRules['dynamic_type'] ?? '';
    @endphp
    
    <div class="alert alert-info alert-dismissible" role="alert">
        <div class="alert-icon">
            <i class="far fa-circle-info"></i>
        </div>
        <div>
            <h4 class="alert-heading">Hidden Field!</h4>
            <div class="alert-description">
                This field will not be visible to users but will be included in the form submission.
            </div>
        </div>
    </div>
    
    <div class="mb-3">
        <label for="default_value" class="form-label">Default Value</label>
        <input type="text" class="form-control @error('default_value') is-invalid @enderror" 
               id="default_value" name="default_value" value="{{ old('default_value', $defaultValue) }}" 
               placeholder="e.g., current_date, user_id, department_code"
               {{ $hasSubmissions ? 'readonly' : '' }}>
        @error('default_value')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        
        @if($hasSubmissions && $field)
            <input type="hidden" name="default_value" value="{{ $defaultValue }}">
            <small class="text-warning">Cannot change default value after submissions</small>
        @endif
    </div>
    
    <div class="mb-3">
        <label for="value_type" class="form-label">Value Type</label>
        <select name="value_type" id="value_type" class="form-control" {{ $hasSubmissions ? 'disabled' : '' }}>
            <option value="static" {{ $valueType == 'static' ? 'selected' : '' }}>Static Value</option>
            <option value="dynamic" {{ $valueType == 'dynamic' ? 'selected' : '' }}>Dynamic Value</option>
        </select>
        @if($hasSubmissions && $field)
            <input type="hidden" name="value_type" value="{{ $valueType }}">
        @endif
    </div>
    
    <!-- Dynamic Value Options -->
    <div id="dynamicValueOptions" style="{{ $valueType === 'dynamic' ? '' : 'display: none;' }}">
        <div class="mb-3">
            <label class="form-label">Dynamic Value Options</label>
            <div class="border rounded p-3">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input type="radio" name="dynamic_type" value="current_date" class="form-check-input" id="dyn_date" {{ $dynamicType == 'current_date' ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }}>
                            <label class="form-check-label" for="dyn_date">Current Date (Y-m-d)</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" name="dynamic_type" value="current_datetime" class="form-check-input" id="dyn_datetime" {{ $dynamicType == 'current_datetime' ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }}>
                            <label class="form-check-label" for="dyn_datetime">Current Date & Time</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" name="dynamic_type" value="user_id" class="form-check-input" id="dyn_user_id" {{ $dynamicType == 'user_id' ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }}>
                            <label class="form-check-label" for="dyn_user_id">Current User ID</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" name="dynamic_type" value="user_name" class="form-check-input" id="dyn_user_name" {{ $dynamicType == 'user_name' ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }}>
                            <label class="form-check-label" for="dyn_user_name">Current User Name</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input type="radio" name="dynamic_type" value="department_code" class="form-check-input" id="dyn_dept_code" {{ $dynamicType == 'department_code' ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }}>
                            <label class="form-check-label" for="dyn_dept_code">User Department Code</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" name="dynamic_type" value="department_name" class="form-check-input" id="dyn_dept_name" {{ $dynamicType == 'department_name' ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }}>
                            <label class="form-check-label" for="dyn_dept_name">User Department Name</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" name="dynamic_type" value="submission_code" class="form-check-input" id="dyn_sub_code" {{ $dynamicType == 'submission_code' ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }}>
                            <label class="form-check-label" for="dyn_sub_code">Submission Code</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" name="dynamic_type" value="random_number" class="form-check-input" id="dyn_random" {{ $dynamicType == 'random_number' ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }}>
                            <label class="form-check-label" for="dyn_random">Random Number</label>
                        </div>
                    </div>
                </div>
                
                @if($hasSubmissions && $field && $dynamicType)
                    <input type="hidden" name="dynamic_type" value="{{ $dynamicType }}">
                @endif
            </div>
        </div>
    </div>
</div>
@endif
