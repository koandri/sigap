@props(['field', 'value' => '', 'prefillData' => []])

@php
    $fieldValue = old('fields.'.$field->field_code, $prefillData[$field->field_code] ?? $value);
    $selectedValues = is_array($fieldValue) ? $fieldValue : json_decode($fieldValue, true) ?? [];
@endphp

<div class="form-check-group">
    @foreach($field->options as $option)
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="fields[{{ $field->field_code }}][]" id="{{ $field->field_code }}_{{ $option->option_value }}" value="{{ $option->option_value }}" {{ in_array($option->option_value, $selectedValues) ? 'checked' : '' }} {{ $field->is_required ? 'required' : '' }}>
            <label class="form-check-label" for="{{ $field->field_code }}_{{ $option->option_value }}">
                {{ $option->option_label }}
            </label>
        </div>
    @endforeach
</div>