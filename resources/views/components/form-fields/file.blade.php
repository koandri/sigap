@props(['field', 'value' => '', 'prefillData' => []])

@php
    $fieldValue = old('fields.'.$field->field_code, $prefillData[$field->field_code] ?? $value);
    $isMultiple = $field->validation_rules['multiple'] ?? false;
    $maxFiles = $field->validation_rules['max_files'] ?? 1;
    $allowedTypes = $field->validation_rules['allowed_types'] ?? [];
    $maxSize = $field->validation_rules['max_size'] ?? 10240; // 10MB default
@endphp

<input type="file" class="form-control" id="{{ $field->field_code }}" name="fields[{{ $field->field_code }}]{{ $isMultiple ? '[]' : '' }}" {{ $isMultiple ? 'multiple' : '' }} {{ $field->is_required ? 'required' : '' }} data-max-files="{{ $maxFiles }}" data-allowed-types="{{ json_encode($allowedTypes) }}" data-max-size="{{ $maxSize }}" accept="{{ implode(',', $allowedTypes) }}">

@if($fieldValue)
    <div class="mt-2">
        <small class="text-muted">Current file: {{ $fieldValue }}</small>
    </div>
@endif