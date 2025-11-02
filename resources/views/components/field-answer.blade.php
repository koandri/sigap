@props(['field', 'answer'])

@once
@push('css')
<style>
/* Responsive signature display sizing */
.signature-display-img {
    max-width: 100%;
    height: auto;
}

/* Desktop - make signature smaller */
@media (min-width: 768px) {
    .signature-display-img {
        max-width: 300px;
        max-height: 150px;
    }
}

@media (min-width: 992px) {
    .signature-display-img {
        max-width: 250px;
        max-height: 125px;
    }
}

@media (min-width: 1200px) {
    .signature-display-img {
        max-width: 200px;
        max-height: 100px;
    }
}

/* Mobile - keep original size */
@media (max-width: 767px) {
    .signature-display-img {
        max-width: 100%;
        max-height: none;
    }
}

/* Prevent horizontal overflow in form answers */
.file-preview-container {
    max-width: 100%;
    overflow: hidden;
}

.file-preview-container .row {
    margin-left: 0;
    margin-right: 0;
}

.file-preview-container .col-md-6,
.file-preview-container .col-lg-6 {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}

/* Ensure all images are responsive */
.file-preview-container img,
.signature-display img {
    max-width: 100% !important;
    height: auto !important;
}

/* Fix table responsiveness */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Ensure buttons don't cause overflow */
.btn-group {
    flex-wrap: wrap;
}

.btn-group .btn {
    flex: 1;
    min-width: 0;
}
</style>
@endpush
@endonce

@if($answer)
    @switch($field->field_type)
        @case('date')
            {{ \Carbon\Carbon::parse($answer->answer_value)->toDateString() }}
            @break

        @case('datetime')
            {{ \Carbon\Carbon::parse($answer->answer_value)->toDateTimeString() }} WIB
            @break

        @case('select_multiple')
        @case('checkbox')
            @php
                $values = json_decode($answer->answer_value, true);
            @endphp
            @if(is_array($values))
                <ul class="mb-0">
                    @foreach($values as $value)
                        @php
                            $option = $field->options->where('option_value', $value)->first();
                        @endphp
                        <li>{{ $option ? $option->option_label : $value }}</li>
                    @endforeach
                </ul>
            @else
                {{ $answer->answer_value }}
            @endif
            @break
        
        @case('select_single')
        @case('radio')
            @php
                $option = $field->options->where('option_value', $answer->answer_value)->first();
            @endphp
            {{ $option ? $option->option_label : $answer->answer_value }}
            @break
        
        @case('boolean')
            @if($answer->answer_value == '1')
                <span class="badge bg-success text-dark-fg">Yes</span>
            @else
                <span class="badge bg-secondary text-dark-fg">No</span>
            @endif
            @break
        
        @case('text_long')
            <div class="formatted-content">{!! $answer->answer_value !!}</div>
            @break

        @case('file')
            <x-file-preview :answer="$answer" :field="$field" />
            @break

        @case('calculated')
            @php
                $calculationRules = $field->validation_rules ?? [];
                $format = $calculationRules['format'] ?? 'number';
                $rawValue = $answer ? (float)$answer->answer_value : 0;
                $calculationService = app(\App\Services\CalculationService::class);
                $formattedValue = $calculationService->formatValue($rawValue, $format);
            @endphp
            <div class="calculated-result">
                <span class="text-primary">{{ $formattedValue }}</span>
                <span class="form-check-description">
                    <i class="far fa-calculator"></i>&nbsp;
                    Formula: <code>{{ $field->calculation_formula }}</code>
                    <br>
                    Raw value: {{ $rawValue }}
                </span>
            </div>
            @break

        @case('hidden')
            @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']))
                <div class="alert alert-light py-2">
                    <small>
                        <i class="far fa-eye-slash text-muted"></i>&nbsp; &nbsp;
                        <strong>Hidden:</strong> {{ $answer ? $answer->answer_value : '-' }}
                        <span class="text-muted">(Admin view only)</span>
                    </small>
                </div>
            @else
                <span class="text-muted fst-italic">Hidden field</span>
            @endif
            @break

        @case('signature')
            @if($answer && $answer->answer_value)
                <div class="signature-display">
                    <div class="text-center border rounded p-3" style="background: #f8f9fa;">
                        <img src="{{ Storage::disk('sigap')->url($answer->answer_value) }}" alt="Digital Signature" class="signature-display-img" style="border: 1px solid #dee2e6;">
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="far fa-person-circle-check"></i>&nbsp;
                            Signed by: {{ $answer->answer_metadata['signed_by'] ?? 'Unknown' }}
                            <br>
                            <i class="far fa-clock"></i>&nbsp;
                            Signed at: {{ isset($answer->answer_metadata['signed_at']) ? formatDate(\Carbon\Carbon::parse($answer->answer_metadata['signed_at']), 'd M Y H:i') : 'Unknown' }}
                        </small>
                    </div>
                </div>
            @else
                <div class="text-center py-3">
                    <i class="far fa-signature" style="font-size: 2rem;"></i>&nbsp;
                    <p class="text-muted mt-2">No signature provided</p>
                </div>
            @endif
            @break

        @case('live_photo')
            <x-live-photo-preview :answer="$answer" :field="$field" />
            @break
        
        @default
            {{ $answer->answer_value }}
    @endswitch
@else
    <span class="text-muted">-</span>
@endif