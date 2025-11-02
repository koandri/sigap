@props(['field' => null, 'hasSubmissions' => false])

@php
    $validationRules = $field?->validation_rules ?? [];
@endphp

<!-- Validation Configuration -->
<div id="validation-config" class="field-config-section" style="display: none;">
    <h4 class="mb-3">Validation Rules</h4>
    
    <!-- Text/Number Validation -->
    <div id="text-validation" class="validation-section" style="display: none;">
        <div class="row mb-3">
            <label for="min_length" class="col-sm-2 col-form-label">Min Length</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" id="min_length" name="min_length" min="0" placeholder="0">
            </div>
        </div>
        
        <div class="row mb-3">
            <label for="max_length" class="col-sm-2 col-form-label">Max Length</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" id="max_length" name="max_length" min="1" placeholder="255">
            </div>
        </div>
    </div>

    <!-- Number Validation -->
    <div id="number-validation" class="validation-section" style="display: none;">
        <div class="row mb-3">
            <label for="min_value" class="col-sm-2 col-form-label">Min Value</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" id="min_value" name="min_value" step="any" placeholder="0" value="{{ old('min_value', $validationRules['min'] ?? '') }}">
            </div>
        </div>
        
        <div class="row mb-3">
            <label for="max_value" class="col-sm-2 col-form-label">Max Value</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" id="max_value" name="max_value" step="any" placeholder="100" value="{{ old('max_value', $validationRules['max'] ?? '') }}">
            </div>
        </div>
        
        <div class="row mb-3">
            <label for="step_value" class="col-sm-2 col-form-label">Step/Increment</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" id="step_value" name="step_value" step="any" placeholder="1" value="{{ old('step_value', $validationRules['step'] ?? '1') }}">
                <small class="form-text text-muted">The increment value for the number input (e.g., 1, 0.5, 0.1)</small>
            </div>
        </div>
    </div>

    <!-- Decimal Validation -->
    <div id="decimal-validation" class="validation-section" style="display: none;">
        <div class="row mb-3">
            <label for="decimal_min_value" class="col-sm-2 col-form-label">Min Value</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" id="decimal_min_value" name="decimal_min_value" step="any" placeholder="0" value="{{ old('decimal_min_value', $validationRules['min'] ?? '') }}">
            </div>
        </div>
        
        <div class="row mb-3">
            <label for="decimal_max_value" class="col-sm-2 col-form-label">Max Value</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" id="decimal_max_value" name="decimal_max_value" step="any" placeholder="100" value="{{ old('decimal_max_value', $validationRules['max'] ?? '') }}">
            </div>
        </div>
        
        <div class="row mb-3">
            <label for="decimal_places" class="col-sm-2 col-form-label">Decimal Places</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" id="decimal_places" name="decimal_places" min="0" max="10" placeholder="2" value="{{ old('decimal_places', $validationRules['decimal_places'] ?? '2') }}">
                <small class="form-text text-muted">Number of decimal places allowed (0-10)</small>
            </div>
        </div>
        
        <div class="row mb-3">
            <label for="decimal_step_value" class="col-sm-2 col-form-label">Step/Increment</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" id="decimal_step_value" name="decimal_step_value" step="any" placeholder="0.01" value="{{ old('decimal_step_value', $validationRules['step'] ?? '0.01') }}">
                <small class="form-text text-muted">The increment value for the decimal input (e.g., 0.01, 0.1, 0.5)</small>
            </div>
        </div>
    </div>

    <!-- Date Validation -->
    <div id="date-validation" class="validation-section" style="display: none;">
        <div class="row mb-3">
            <label for="date_min" class="col-sm-2 col-form-label">Min Date</label>
            <div class="col-sm-10">
                <select class="form-select" id="date_min_type" name="date_min_type">
                    <option value="">No minimum</option>
                    <option value="today">Today</option>
                    <option value="today_minus">Today minus days</option>
                    <option value="today_plus">Today plus days</option>
                    <option value="fixed">Fixed date</option>
                </select>
            </div>
        </div>
        
        <div id="date_min_config" style="display: none;">
            <!-- Date min config will be shown based on selection -->
        </div>
        
        <div class="row mb-3">
            <label for="date_max" class="col-sm-2 col-form-label">Max Date</label>
            <div class="col-sm-10">
                <select class="form-select" id="date_max_type" name="date_max_type">
                    <option value="">No maximum</option>
                    <option value="today">Today</option>
                    <option value="today_minus">Today minus days</option>
                    <option value="today_plus">Today plus days</option>
                    <option value="fixed">Fixed date</option>
                </select>
            </div>
        </div>
        
        <div id="date_max_config" style="display: none;">
            <!-- Date max config will be shown based on selection -->
        </div>
    </div>

    <!-- File Validation -->
    <div id="file-validation" class="validation-section" style="display: none;">
        <div class="row mb-3">
            <div class="col-sm-2"></div>
            <div class="col-sm-10">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="multiple_files" name="multiple_files" value="1">
                    <label class="form-check-label" for="multiple_files">
                        Allow multiple files
                    </label>
                </div>
            </div>
        </div>
        
        <div class="row mb-3">
            <label for="max_files" class="col-sm-2 col-form-label">Max Files</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" id="max_files" name="max_files" min="1" value="1" placeholder="1">
            </div>
        </div>
        
        <div class="row mb-3">
            <label for="allowed_types" class="col-sm-2 col-form-label">Allowed Types</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="allowed_types" name="allowed_types" placeholder="image/*,application/pdf">
                <small class="form-text">Comma-separated MIME types (e.g., image/*,application/pdf)</small>
            </div>
        </div>
        
        <div class="row mb-3">
            <label for="max_size" class="col-sm-2 col-form-label">Max Size (KB)</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" id="max_size" name="max_size" min="1" value="10240" placeholder="10240">
            </div>
        </div>
    </div>

    <!-- Signature Validation -->
    <div id="signature-validation" class="validation-section" style="display: none;">
        <div class="row mb-3">
            <label for="signature_width" class="col-sm-2 col-form-label">Width</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" id="signature_width" name="signature_width" min="100" value="400" placeholder="400">
            </div>
        </div>
        
        <div class="row mb-3">
            <label for="signature_height" class="col-sm-2 col-form-label">Height</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" id="signature_height" name="signature_height" min="50" value="200" placeholder="200">
            </div>
        </div>
    </div>

    <!-- Calculated Field Configuration -->
    <div id="calculated-config" class="validation-section" style="display: none;">
        <div class="row mb-3">
            <label for="calculation_formula" class="col-sm-2 col-form-label required">Formula</label>
            <div class="col-sm-10">
                <textarea class="form-control" id="calculation_formula" name="calculation_formula" rows="3" placeholder="e.g., {field1} + {field2} * 0.1" required></textarea>
                <small class="form-text">Use {field_code} to reference other fields</small>
            </div>
        </div>
        
        <div class="row mb-3">
            <label for="calculation_format" class="col-sm-2 col-form-label">Format</label>
            <div class="col-sm-10">
                <select class="form-select" id="calculation_format" name="calculation_format">
                    <option value="number">Number</option>
                    <option value="currency">Currency</option>
                    <option value="percentage">Percentage</option>
                </select>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-sm-2"></div>
            <div class="col-sm-10">
                <button type="button" class="btn btn-outline-info" id="test-formula">
                    <i class="far fa-flask"></i>&nbsp; Test Formula
                </button>
                <span id="formula-test-result" class="ms-2"></span>
            </div>
        </div>
    </div>
</div>