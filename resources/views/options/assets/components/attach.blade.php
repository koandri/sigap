@extends('layouts.app')

@section('title', 'Attach Component')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    <a href="{{ route('options.assets.show', $asset) }}">{{ $asset->name }}</a>
                </div>
                <h2 class="page-title">
                    Attach Component
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('assets.components', $asset) }}" class="btn btn-outline-secondary">
                    <i class="far fa-arrow-left"></i>&nbsp;
                    Back
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')

        <div class="row">
            <div class="col-12">
                <form class="card" action="{{ route('assets.components.store', $asset) }}" method="POST">
                    @csrf
                    <div class="card-header">
                        <h3 class="card-title">Attach Component to {{ $asset->name }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <label for="component_id" class="col-sm-3 col-form-label required">Component</label>
                            <div class="col-sm-9">
                                <select name="component_id" id="component_id" class="form-select @error('component_id') is-invalid @enderror" required>
                                    <option value="">Select a component...</option>
                                    @foreach($availableAssets as $availableAsset)
                                        <option value="{{ $availableAsset->id }}" {{ old('component_id') == $availableAsset->id ? 'selected' : '' }}>
                                            {{ $availableAsset->name }} ({{ $availableAsset->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('component_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="component_type" class="col-sm-3 col-form-label required">Component Type</label>
                            <div class="col-sm-9">
                                <select name="component_type" id="component_type" class="form-select @error('component_type') is-invalid @enderror" required>
                                    <option value="">Select type...</option>
                                    <option value="consumable" {{ old('component_type') == 'consumable' ? 'selected' : '' }}>Consumable</option>
                                    <option value="replaceable" {{ old('component_type') == 'replaceable' ? 'selected' : '' }}>Replaceable</option>
                                    <option value="integral" {{ old('component_type') == 'integral' ? 'selected' : '' }}>Integral</option>
                                </select>
                                @error('component_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">
                                    <strong>Consumable:</strong> Items that get used up (e.g., tyres, filters)<br>
                                    <strong>Replaceable:</strong> Items that can be swapped out (e.g., harddisks, batteries)<br>
                                    <strong>Integral:</strong> Items that are permanently part of the asset (e.g., GPS tracker)
                                </small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="installed_date" class="col-sm-3 col-form-label">Installed Date</label>
                            <div class="col-sm-9">
                                <input type="date" name="installed_date" id="installed_date" class="form-control @error('installed_date') is-invalid @enderror" value="{{ old('installed_date') }}">
                                @error('installed_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="installed_usage_value" class="col-sm-3 col-form-label">Start Usage Value</label>
                            <div class="col-sm-9">
                                <input type="number" step="0.01" min="0" name="installed_usage_value" id="installed_usage_value" class="form-control @error('installed_usage_value') is-invalid @enderror" value="{{ old('installed_usage_value') }}" placeholder="e.g., 50000 for Start KM">
                                @error('installed_usage_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">Enter the parent asset's current usage value (e.g., car's kilometers when tyres are installed)</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="installation_notes" class="col-sm-3 col-form-label">Installation Notes</label>
                            <div class="col-sm-9">
                                <textarea name="installation_notes" id="installation_notes" class="form-control @error('installation_notes') is-invalid @enderror" rows="3">{{ old('installation_notes') }}</textarea>
                                @error('installation_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Attach Component</button>
                        <a href="{{ route('assets.components', $asset) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet"/>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new TomSelect('#component_id', {
        placeholder: 'Select a component...',
        sortField: {
            field: 'text',
            direction: 'asc'
        },
        allowEmptyOption: false,
        create: false
    });
});
</script>
@endpush
@endsection

















