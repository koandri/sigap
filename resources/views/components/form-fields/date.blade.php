@props(['field', 'value' => '', 'prefillData' => []])

@php
    $fieldValue = old('fields.'.$field->field_code, $prefillData[$field->field_code] ?? $value);
    $minDate = '';
    $maxDate = '';
    $disabledDates = [];
    
    if ($field->validation_rules) {
        $rules = $field->validation_rules;
        
        // Calculate min date
        if (isset($rules['date_min'])) {
            $minRule = $rules['date_min'];
            if ($minRule['type'] === 'fixed') {
                $minDate = $minRule['value'];
            } elseif ($minRule['type'] === 'today') {
                $minDate = date('Y-m-d');
            } elseif ($minRule['type'] === 'today_minus') {
                $minDate = date('Y-m-d', strtotime('-' . ($minRule['days'] ?? 0) . ' days'));
            } elseif ($minRule['type'] === 'today_plus') {
                $minDate = date('Y-m-d', strtotime('+' . ($minRule['days'] ?? 0) . ' days'));
            }
        }
        
        // Calculate max date
        if (isset($rules['date_max'])) {
            $maxRule = $rules['date_max'];
            if ($maxRule['type'] === 'fixed') {
                $maxDate = $maxRule['value'];
            } elseif ($maxRule['type'] === 'today') {
                $maxDate = date('Y-m-d');
            } elseif ($maxRule['type'] === 'today_minus') {
                $maxDate = date('Y-m-d', strtotime('-' . ($maxRule['days'] ?? 0) . ' days'));
            } elseif ($maxRule['type'] === 'today_plus') {
                $maxDate = date('Y-m-d', strtotime('+' . ($maxRule['days'] ?? 0) . ' days'));
            }
        }
        
        // Handle disabled dates
        if (isset($rules['disabled_dates'])) {
            $disabledDates = $rules['disabled_dates'];
        }
    }
@endphp

<input type="date" 
       class="form-control" 
       id="{{ $field->field_code }}" 
       name="fields[{{ $field->field_code }}]" 
       value="{{ $fieldValue }}" 
       @if($minDate) min="{{ $minDate }}" @endif
       @if($maxDate) max="{{ $maxDate }}" @endif
       @if(!empty($disabledDates)) data-disabled-dates="{{ json_encode($disabledDates) }}" @endif
       {{ $field->is_required ? 'required' : '' }}>