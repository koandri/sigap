@props(['field', 'value' => '', 'prefillData' => []])

@php
    $fieldValue = old('fields.'.$field->field_code, $prefillData[$field->field_code] ?? $value);
    $validationRules = $field->validation_rules ?? [];
    
    // Set default step values
    $step = $field->field_type === 'decimal' ? '0.01' : '1';
    
    // Apply validation rules if they exist
    if (isset($validationRules['step'])) {
        $step = $validationRules['step'];
    }
    
    // Set min and max attributes
    $minAttr = '';
    $maxAttr = '';
    
    if (isset($validationRules['min'])) {
        $minAttr = 'min="' . $validationRules['min'] . '"';
    }
    
    if (isset($validationRules['max'])) {
        $maxAttr = 'max="' . $validationRules['max'] . '"';
    }
    
    // For decimal fields, apply decimal places validation
    $decimalPlaces = null;
    if ($field->field_type === 'decimal' && isset($validationRules['decimal_places'])) {
        $decimalPlaces = $validationRules['decimal_places'];
    }
@endphp

<input type="number" 
       step="{{ $step }}" 
       {{ $minAttr }}
       {{ $maxAttr }}
       @if($decimalPlaces !== null) data-decimal-places="{{ $decimalPlaces }}" @endif
       class="form-control" 
       id="{{ $field->field_code }}" 
       name="fields[{{ $field->field_code }}]" 
       value="{{ $fieldValue }}" 
       placeholder="{{ $field->placeholder }}" 
       {{ $field->is_required ? 'required' : '' }}>