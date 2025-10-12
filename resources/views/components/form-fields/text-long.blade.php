@props(['field', 'value' => '', 'prefillData' => []])

@php
    $fieldValue = old('fields.'.$field->field_code, $prefillData[$field->field_code] ?? $value);
@endphp

<div class="wysiwyg-container">
    <textarea class="form-control wysiwyg-editor" id="{{ $field->field_code }}" name="fields[{{ $field->field_code }}]" rows="6" placeholder="{{ $field->placeholder }}" data-field-code="{{ $field->field_code }}" {{ $field->is_required ? 'required' : '' }} data-required="{{ $field->is_required ? 'true' : 'false' }}">{{ $fieldValue }}</textarea>
</div>