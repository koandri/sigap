@props(['field', 'value' => '', 'prefillData' => []])

@php
    $fieldValue = old('fields.'.$field->field_code, $prefillData[$field->field_code] ?? $value);
    $selectedValues = is_array($fieldValue) ? $fieldValue : json_decode($fieldValue, true) ?? [];
    $hasApiSource = $field->hasApiSource();
    $apiUrl = $hasApiSource ? route('api.field.options', [
        'form' => $field->formVersion->form->id,
        'version' => $field->formVersion->id,
        'field' => $field->id
    ]) : null;
@endphp

<select class="form-select" id="{{ $field->field_code }}" name="fields[{{ $field->field_code }}][]" multiple {{ $field->is_required ? 'required' : '' }}
        @if($hasApiSource) 
            data-has-api-source="true" 
            data-api-url="{{ $apiUrl }}"
        @endif>
    
    @if($hasApiSource)
        <!-- Options will be loaded dynamically via JavaScript -->
    @else
        @foreach($field->options as $option)
            <option value="{{ $option->option_value }}" {{ in_array($option->option_value, $selectedValues) ? 'selected' : '' }}>
                {{ $option->option_label }}
            </option>
        @endforeach
    @endif
</select>