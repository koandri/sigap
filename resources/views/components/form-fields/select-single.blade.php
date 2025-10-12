@props(['field', 'value' => '', 'prefillData' => []])

@php
    try {
        $oldValue = old('fields.'.$field->field_code);
    } catch (Exception $e) {
        $oldValue = null;
    }
    $fieldValue = $oldValue ?? $prefillData[$field->field_code] ?? $value;
    $hasApiSource = $field->hasApiSource();
    $apiUrl = $hasApiSource ? route('api.field.options', [
        'form' => $field->formVersion->form->id,
        'version' => $field->formVersion->id,
        'field' => $field->id
    ]) : null;
@endphp

<select class="form-select" id="{{ $field->field_code }}" name="fields[{{ $field->field_code }}]" {{ $field->is_required ? 'required' : '' }}
        @if($hasApiSource) 
            data-has-api-source="true" 
            data-api-url="{{ $apiUrl }}"
        @endif>
    <option value="">Select an option...</option>
    
    @if($hasApiSource)
        <!-- Options will be loaded dynamically via JavaScript -->
    @else
        @foreach($field->options as $option)
            <option value="{{ $option->option_value }}" {{ $fieldValue == $option->option_value ? 'selected' : '' }}>
                {{ $option->option_label }}
            </option>
        @endforeach
    @endif
</select>