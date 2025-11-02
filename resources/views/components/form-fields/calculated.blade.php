@props(['field', 'value' => '', 'prefillData' => []])

@php
    $fieldValue = old('fields.'.$field->field_code, $prefillData[$field->field_code] ?? $value);
    $calculationRules = $field->validation_rules ?? [];
    $format = $calculationRules['format'] ?? 'number';
    $rawValue = $fieldValue ? (float)$fieldValue : 0;
    $calculationService = app(\App\Services\CalculationService::class);
    $formattedValue = $calculationService->formatValue($rawValue, $format);
@endphp

<div class="calculated-field">
    <div class="input-group">
        <input type="text" class="form-control" id="{{ $field->field_code }}" name="fields[{{ $field->field_code }}]" value="{{ $formattedValue }}" readonly style="background-color: #f8f9fa;">
        <span class="input-group-text">
            <i class="far fa-calculator"></i>&nbsp;
        </span>
    </div>
    <div class="form-check-description mt-1">
        <small class="text-muted">
            Formula: <code>{{ $field->calculation_formula }}</code>
        </small>
    </div>
</div>