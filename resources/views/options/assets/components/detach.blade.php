@extends('layouts.app')

@section('title', 'Detach Component')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    <a href="{{ route('options.assets.show', $component) }}">{{ $component->name }}</a>
                </div>
                <h2 class="page-title">
                    Detach Component
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')

        <div class="row">
            <div class="col-12">
                <form class="card" action="{{ route('assets.components.detach.store', $component) }}" method="POST">
                    @csrf
                    <div class="card-header">
                        <h3 class="card-title">Detach Component: {{ $component->name }}</h3>
                    </div>
                    <div class="card-body">
                        @if($component->parentAsset)
                        <div class="alert alert-info">
                            <strong>Parent Asset:</strong> {{ $component->parentAsset->name }} ({{ $component->parentAsset->code }})
                        </div>
                        @endif

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label required">Action</label>
                            <div class="col-sm-9">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="dispose_asset" id="dispose_yes" value="1" {{ old('dispose_asset', '0') == '1' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="dispose_yes">
                                        Mark component as disposed (calculate lifetime)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="dispose_asset" id="dispose_no" value="0" {{ old('dispose_asset', '0') == '0' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="dispose_no">
                                        Keep component active (can be reattached later)
                                    </label>
                                </div>
                                @error('dispose_asset')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div id="disposal-fields" style="display: none;">
                            <div class="row mb-3">
                                <label for="disposed_date" class="col-sm-3 col-form-label">Disposed Date</label>
                                <div class="col-sm-9">
                                    <input type="date" name="disposed_date" id="disposed_date" class="form-control @error('disposed_date') is-invalid @enderror" value="{{ old('disposed_date', now()->format('Y-m-d')) }}">
                                    @error('disposed_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="disposed_usage_value" class="col-sm-3 col-form-label">End Usage Value <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="number" step="0.01" min="0" name="disposed_usage_value" id="disposed_usage_value" class="form-control @error('disposed_usage_value') is-invalid @enderror" value="{{ old('disposed_usage_value') }}" placeholder="e.g., 80000 for End KM">
                                    @error('disposed_usage_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($component->installed_usage_value !== null)
                                    <small class="form-hint">Start Usage: {{ number_format($component->installed_usage_value, 2) }}. End usage must be greater than or equal to start usage.</small>
                                    @else
                                    <small class="form-hint">Enter the parent asset's current usage value (e.g., car's kilometers when tyres are disposed)</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="notes" class="col-sm-3 col-form-label">Notes</label>
                            <div class="col-sm-9">
                                <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Detach Component</button>
                        <a href="{{ route('assets.components', $component->parentAsset) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const disposeYes = document.getElementById('dispose_yes');
    const disposeNo = document.getElementById('dispose_no');
    const disposalFields = document.getElementById('disposal-fields');
    const disposedUsageValue = document.getElementById('disposed_usage_value');

    function toggleDisposalFields() {
        if (disposeYes.checked) {
            disposalFields.style.display = 'block';
            if (disposedUsageValue) {
                disposedUsageValue.setAttribute('required', 'required');
            }
        } else {
            disposalFields.style.display = 'none';
            if (disposedUsageValue) {
                disposedUsageValue.removeAttribute('required');
                disposedUsageValue.value = '';
            }
        }
    }

    disposeYes.addEventListener('change', toggleDisposalFields);
    disposeNo.addEventListener('change', toggleDisposalFields);
    
    // Initial state
    toggleDisposalFields();
});
</script>
@endpush
@endsection














