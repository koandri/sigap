@props(['field', 'value' => '', 'prefillData' => []])

@php
    $fieldValue = old('fields.'.$field->field_code, $prefillData[$field->field_code] ?? $value);
@endphp

<input type="hidden" id="{{ $field->field_code }}" name="fields[{{ $field->field_code }}]" value="{{ $fieldValue }}">