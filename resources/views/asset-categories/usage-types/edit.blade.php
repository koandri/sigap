@extends('layouts.app')

@section('title', 'Edit Usage Type')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    <a href="{{ route('options.asset-categories.show', $category) }}">{{ $category->name }}</a>
                </div>
                <h2 class="page-title">
                    Edit Usage Type
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
                <form class="card" action="{{ route('options.usage-types.update', $usageType) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-header">
                        <h3 class="card-title">Edit Usage Type: {{ $usageType->name }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <label for="name" class="col-sm-3 col-form-label required">Name</label>
                            <div class="col-sm-9">
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $usageType->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="description" class="col-sm-3 col-form-label">Description</label>
                            <div class="col-sm-9">
                                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $usageType->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="lifetime_unit" class="col-sm-3 col-form-label required">Lifetime Unit</label>
                            <div class="col-sm-9">
                                <select name="lifetime_unit" id="lifetime_unit" class="form-select @error('lifetime_unit') is-invalid @enderror" required>
                                    <option value="">Select unit...</option>
                                    <option value="days" {{ old('lifetime_unit', $usageType->lifetime_unit?->value) == 'days' ? 'selected' : '' }}>Days</option>
                                    <option value="kilometers" {{ old('lifetime_unit', $usageType->lifetime_unit?->value) == 'kilometers' ? 'selected' : '' }}>Kilometers</option>
                                    <option value="machine_hours" {{ old('lifetime_unit', $usageType->lifetime_unit?->value) == 'machine_hours' ? 'selected' : '' }}>Machine Hours</option>
                                    <option value="cycles" {{ old('lifetime_unit', $usageType->lifetime_unit?->value) == 'cycles' ? 'selected' : '' }}>Cycles</option>
                                </select>
                                @error('lifetime_unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="expected_average_lifetime" class="col-sm-3 col-form-label">Expected Average Lifetime</label>
                            <div class="col-sm-9">
                                <input type="number" step="0.01" min="0" name="expected_average_lifetime" id="expected_average_lifetime" class="form-control @error('expected_average_lifetime') is-invalid @enderror" value="{{ old('expected_average_lifetime', $usageType->expected_average_lifetime) }}">
                                @error('expected_average_lifetime')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="is_active" class="col-sm-3 col-form-label">Status</label>
                            <div class="col-sm-9">
                                <label class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $usageType->is_active) ? 'checked' : '' }}>
                                    <span class="form-check-label">Active</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Update Usage Type</button>
                        <a href="{{ route('options.asset-categories.usage-types.index', $category) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

