@props(['field', 'value' => '', 'prefillData' => []])

@php
    $fieldValue = old('fields.'.$field->field_code, $prefillData[$field->field_code] ?? $value);
@endphp

<div class="form-check form-switch">
    <input class="form-check-input" type="checkbox" id="{{ $field->field_code }}" name="fields[{{ $field->field_code }}]" value="1" {{ $fieldValue == '1' ? 'checked' : '' }} {{ $field->is_required ? 'required' : '' }}>
    <label class="form-check-label" for="{{ $field->field_code }}">
        {{ $field->placeholder ?: 'Yes' }}
    </label>
</div>