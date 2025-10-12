@props(['field', 'value' => '', 'prefillData' => []])

@php
    $fieldValue = old('fields.'.$field->field_code, $prefillData[$field->field_code] ?? $value);
@endphp

<div class="form-check-group">
    @foreach($field->options as $option)
        <div class="form-check">
            <input class="form-check-input" type="radio" name="fields[{{ $field->field_code }}]" id="{{ $field->field_code }}_{{ $option->option_value }}" value="{{ $option->option_value }}" {{ $fieldValue == $option->option_value ? 'checked' : '' }} {{ $field->is_required ? 'required' : '' }}>
            <label class="form-check-label" for="{{ $field->field_code }}_{{ $option->option_value }}">
                {{ $option->option_label }}
            </label>
        </div>
    @endforeach
</div>