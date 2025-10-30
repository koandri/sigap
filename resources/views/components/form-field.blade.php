@props(['field', 'value' => '', 'prefillData' => []])

<div class="row mb-3">
    <label for="{{ $field->field_code }}" class="form-label {{ $field->is_required ? 'required' : '' }}">
        {{ $field->field_label }}
    </label>
    
    @switch($field->field_type)
        @case('text_short')
            <x-form-fields.text-short :field="$field" :value="$value" :prefillData="$prefillData" />
            @break
            
        @case('text_long')
            <x-form-fields.text-long :field="$field" :value="$value" :prefillData="$prefillData" />
            @break
            
        @case('number')
        @case('decimal')
            <x-form-fields.number :field="$field" :value="$value" :prefillData="$prefillData" />
            @break
            
        @case('date')
            <x-form-fields.date :field="$field" :value="$value" :prefillData="$prefillData" />
            @break

        @case('datetime')
            <x-form-fields.datetime :field="$field" :value="$value" :prefillData="$prefillData" />
            @break
            
        @case('select_single')
            <x-form-fields.select-single :field="$field" :value="$value" :prefillData="$prefillData" />
            @break
            
        @case('select_multiple')
            <x-form-fields.select-multiple :field="$field" :value="$value" :prefillData="$prefillData" />
            @break
            
        @case('radio')
            <x-form-fields.radio :field="$field" :value="$value" :prefillData="$prefillData" />
            @break
            
        @case('checkbox')
            <x-form-fields.checkbox :field="$field" :value="$value" :prefillData="$prefillData" />
            @break
            
        @case('boolean')
            <x-form-fields.boolean :field="$field" :value="$value" :prefillData="$prefillData" />
            @break
            
        @case('file')
            <x-form-fields.file :field="$field" :value="$value" :prefillData="$prefillData" />
            @break
            
        @case('signature')
            <x-form-fields.signature :field="$field" :value="$value" :prefillData="$prefillData" />
            @break
            
        @case('calculated')
            <x-form-fields.calculated :field="$field" :value="$value" :prefillData="$prefillData" />
            @break
            
        @case('hidden')
            <x-form-fields.hidden :field="$field" :value="$value" :prefillData="$prefillData" />
            @break
            
        @case('live_photo')
            <x-form-fields.live-photo :field="$field" :value="$value" :prefillData="$prefillData" />
            @break
            
        @default
            <div class="alert alert-warning">
                <i class="far fa-exclamation-triangle"></i>
                Unknown field type: {{ $field->field_type }}
            </div>
    @endswitch
</div>