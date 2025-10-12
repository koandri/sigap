@props(['field', 'value' => '', 'prefillData' => []])

@php
    try {
        $oldValue = old('fields.'.$field->field_code);
    } catch (Exception $e) {
        $oldValue = null;
    }
    $fieldValue = $oldValue ?? $prefillData[$field->field_code] ?? $value;
@endphp

<input type="text" class="form-control" id="{{ $field->field_code }}" name="fields[{{ $field->field_code }}]" value="{{ $fieldValue }}" placeholder="{{ $field->placeholder }}" {{ $field->is_required ? 'required' : '' }}>