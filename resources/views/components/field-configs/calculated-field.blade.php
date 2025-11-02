@props(['field' => null, 'hasSubmissions' => false, 'form' => null, 'version' => null])

<!-- Calculated Field Settings -->
@if($field && $field->field_type === 'calculated' || !$hasSubmissions)
<div id="calculatedFieldSection" style="{{ $field && $field->field_type === 'calculated' ? '' : 'display: none;' }}">
    <div class="hr-text hr-text-start">Calculation Settings</div>
    
    @if($hasSubmissions && $field && $field->field_type === 'calculated')
    <div class="alert alert-warning alert-dismissible" role="alert">
        <div class="alert-icon">
            <i class="far fa-triangle-exclamation"></i>&nbsp;
        </div>
        <div>
            <h4 class="alert-heading">Limited Editing</h4>
            <div class="alert-description">
                This field has calculations in existing submissions. Only display format can be changed.
            </div>
        </div>
    </div>
    @endif

    <div class="alert alert-info alert-dismissible" role="alert">
        <div class="alert-icon">
            <i class="far fa-circle-info"></i>&nbsp;
        </div>
        <div>
            <h4 class="alert-heading">Formula Syntax</h4>
            <div class="alert-description">
                Use field codes in curly braces. Example: <code>{field_a} + {field_b}</code>
                <br>
                <strong>Supported operations:</strong> +, -, *, /, %, (, ), min(), max(), round(), abs()
            </div>
        </div>
    </div>
    
    @php
        $calculationRules = $field?->validation_rules ?? [];
        $currentFormat = $calculationRules['format'] ?? 'number';
        $autoCalculate = $calculationRules['auto_calculate'] ?? true;
    @endphp
    
    <div class="mb-3">
        <label for="calculation_formula" class="form-label required">
            Calculation Formula
        </label>
        <textarea class="form-control @error('calculation_formula') is-invalid @enderror" id="calculation_formula" name="calculation_formula" rows="3" placeholder="Example: {price} * {quantity} * {tax_rate}" style="font-family: monospace;" {{ $hasSubmissions ? 'readonly' : '' }}>{{ old('calculation_formula', $field?->calculation_formula) }}</textarea>
        
        @if($hasSubmissions && $field)
            <input type="hidden" name="calculation_formula" value="{{ $field->calculation_formula }}">
            <small class="text-warning">Cannot change formula after submissions</small>
        @endif
        @error('calculation_formula')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    @if($field)
    <div class="mb-3">
        <label class="form-label">Current Dependencies</label>
        <div class="card">
            <div class="card-body py-2">
                @if($field->calculation_dependencies)
                    @foreach($field->calculation_dependencies as $depCode)
                        @php
                            $depField = $version->fields()->where('field_code', $depCode)->first();
                        @endphp
                        <span class="badge badge-outline text-primary me-1">
                            {{ $depCode }}
                            @if($depField)
                                ({{ $depField->field_label }})
                            @else
                                <span class="text-warning">(Field not found)</span>
                            @endif
                        </span>
                    @endforeach
                @else
                    <span class="text-muted">No dependencies</span>
                @endif
            </div>
        </div>
    </div>
    @endif
    
    <div class="mb-3">
        <label class="form-label">Available Numeric Fields</label>
        <div class="card">
            <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                <div id="availableFields">
                    <div class="text-center py-2">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 mb-0 text-muted small">Loading available fields...</p>
                    </div>
                </div>
            </div>
        </div>
        <small class="text-muted">Only number, decimal, and numeric hidden fields can be used in calculations</small>
    </div>
    
    <div class="mb-3">
        <label for="calculation_format" class="form-label">Display Format</label>
        <select name="calculation_format" id="calculation_format" class="form-control">
            <option value="number" {{ $currentFormat == 'number' ? 'selected' : '' }}>
                Number (e.g., 1,234.56)
            </option>
            <option value="currency" {{ $currentFormat == 'currency' ? 'selected' : '' }}>
                Currency (e.g., Rp 1,234.56)
            </option>
            <option value="percentage" {{ $currentFormat == 'percentage' ? 'selected' : '' }}>
                Percentage (e.g., 12.34%)
            </option>
            <option value="decimal_2" {{ $currentFormat == 'decimal_2' ? 'selected' : '' }}>
                2 Decimal Places (e.g., 1234.56)
            </option>
            <option value="decimal_0" {{ $currentFormat == 'decimal_0' ? 'selected' : '' }}>
                No Decimal (e.g., 1235)
            </option>
        </select>
    </div>
    
    <div class="mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="auto_calculate" name="auto_calculate" value="1" {{ $autoCalculate ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }}>
            <label class="form-check-label" for="auto_calculate">
                <strong>Auto Calculate</strong>
                <br>
                <small class="text-muted">Update result automatically when dependent fields change</small>
            </label>
            
            @if($hasSubmissions && $autoCalculate)
                <input type="hidden" name="auto_calculate" value="1">
            @endif
        </div>
    </div>
    
    <!-- Formula Test Section -->
    @if(!$hasSubmissions)
    <div class="mb-3">
        <label class="form-label">Test Formula</label>
        <div class="card bg-light">
            <div class="card-body py-2">
                <div class="row g-2" id="formulaTestInputs">
                    <!-- Test inputs will be generated here -->
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-primary" onclick="testFormula()">
                        <i class="far fa-circle-play"></i>&nbsp;Test Calculation
                    </button>
                    <span id="testResult" class="ms-3"></span>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endif
