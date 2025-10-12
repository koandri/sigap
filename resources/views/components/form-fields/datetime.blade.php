@props(['field', 'value' => '', 'prefillData' => []])

@php
    try {
        $oldValue = old('fields.'.$field->field_code);
    } catch (Exception $e) {
        $oldValue = null;
    }
    $fieldValue = $oldValue ?? $prefillData[$field->field_code] ?? $value;
    $minDateTime = '';
    $maxDateTime = '';
    $disabledDates = [];
    
    if ($field->validation_rules) {
        $rules = $field->validation_rules;
        
        // Calculate min datetime
        if (isset($rules['datetime_min'])) {
            $minRule = $rules['datetime_min'];
            if ($minRule['type'] === 'fixed') {
                $minDateTime = $minRule['value'];
            } elseif ($minRule['type'] === 'now') {
                $minDateTime = date('Y-m-d\TH:i');
            } elseif ($minRule['type'] === 'now_minus') {
                $minDateTime = date('Y-m-d\TH:i', strtotime('-' . ($minRule['hours'] ?? 0) . ' hours'));
            } elseif ($minRule['type'] === 'now_plus') {
                $minDateTime = date('Y-m-d\TH:i', strtotime('+' . ($minRule['hours'] ?? 0) . ' hours'));
            }
        }
        
        // Calculate max datetime
        if (isset($rules['datetime_max'])) {
            $maxRule = $rules['datetime_max'];
            if ($maxRule['type'] === 'fixed') {
                $maxDateTime = $maxRule['value'];
            } elseif ($maxRule['type'] === 'now') {
                $maxDateTime = date('Y-m-d\TH:i');
            } elseif ($maxRule['type'] === 'now_minus') {
                $maxDateTime = date('Y-m-d\TH:i', strtotime('-' . ($maxRule['hours'] ?? 0) . ' hours'));
            } elseif ($maxRule['type'] === 'now_plus') {
                $maxDateTime = date('Y-m-d\TH:i', strtotime('+' . ($maxRule['hours'] ?? 0) . ' hours'));
            }
        }
        
        // Handle disabled dates
        if (isset($rules['disabled_dates'])) {
            $disabledDates = $rules['disabled_dates'];
        }
    }
@endphp

<input type="datetime-local" 
       class="form-control" 
       id="{{ $field->field_code }}" 
       name="fields[{{ $field->field_code }}]" 
       value="{{ $fieldValue }}" 
       @if($minDateTime) min="{{ $minDateTime }}" @endif
       @if($maxDateTime) max="{{ $maxDateTime }}" @endif
       @if(!empty($disabledDates)) data-disabled-dates="{{ json_encode($disabledDates) }}" @endif
       {{ $field->is_required ? 'required' : '' }}>