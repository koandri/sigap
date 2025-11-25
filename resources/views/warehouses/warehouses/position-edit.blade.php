@extends('layouts.app')

@section('title', 'Edit Position: ' . $position->position_code)

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.dashboard') }}">Manufacturing</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('warehouses.warehouses.index') }}">Warehouses</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('warehouses.warehouses.show', $warehouse) }}">{{ $warehouse->name }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('warehouses.warehouses.shelf-management', $warehouse) }}">Shelf Management</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('warehouses.warehouses.shelf-positions', [$warehouse, $shelf]) }}">{{ $shelf->shelf_code }} Positions</a></li>
                        <li class="breadcrumb-item active">Edit Position</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    Edit Position
                    <span class="text-muted">- {{ $position->full_location_code }}</span>
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('warehouses.warehouses.shelf-positions', [$warehouse, $shelf]) }}" class="btn btn-outline-secondary">
                        <i class="far fa-arrow-left me-2"></i>&nbsp;
                        Back to Positions
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')
        
        <form action="{{ route('warehouses.warehouses.position.update', [$warehouse, $shelf, $position]) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Position Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Position Code</label>
                                        <input type="text" class="form-control @error('position_code') is-invalid @enderror" 
                                               name="position_code" value="{{ old('position_code', $position->position_code) }}" 
                                               placeholder="e.g., 00, 01, 02" required maxlength="2">
                                        @error('position_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Code for this position (e.g., 00, 01, 02).</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Position Name</label>
                                        <input type="text" class="form-control @error('position_name') is-invalid @enderror" 
                                               name="position_name" value="{{ old('position_name', $position->position_name) }}" 
                                               placeholder="e.g., Main, Position 1, Front" required maxlength="20">
                                        @error('position_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Descriptive name for this position.</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Maximum Capacity</label>
                                        <input type="number" class="form-control @error('max_capacity') is-invalid @enderror" 
                                               name="max_capacity" value="{{ old('max_capacity', $position->max_capacity) }}" 
                                               min="1" max="10" required>
                                        @error('max_capacity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Maximum number of items this position can hold (1-10).</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <div class="form-selectgroup">
                                            <label class="form-selectgroup-item">
                                                <input type="radio" name="is_active" value="1" class="form-selectgroup-input" {{ old('is_active', $position->is_active) ? 'checked' : '' }}>
                                                <span class="form-selectgroup-label">
                                                    <span class="form-selectgroup-check"></span>
                                                    <span class="form-selectgroup-label-content">
                                                        <span class="form-selectgroup-title strong">Active</span>
                                                        <span class="form-selectgroup-description">Position is operational</span>
                                                    </span>
                                                </span>
                                            </label>
                                            <label class="form-selectgroup-item">
                                                <input type="radio" name="is_active" value="0" class="form-selectgroup-input" {{ old('is_active', $position->is_active) == '0' ? 'checked' : '' }}>
                                                <span class="form-selectgroup-label">
                                                    <span class="form-selectgroup-check"></span>
                                                    <span class="form-selectgroup-label-content">
                                                        <span class="form-selectgroup-title strong">Inactive</span>
                                                        <span class="form-selectgroup-description">Position is not operational</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <h4 class="alert-title">Full Location Code</h4>
                                <div class="text-muted">
                                    This position will have the full location code: <strong>{{ $shelf->shelf_code }}-{{ old('position_code', $position->position_code) }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <div class="d-flex">
                                <a href="{{ route('warehouses.warehouses.shelf-positions', [$warehouse, $shelf]) }}" class="btn btn-link">Cancel</a>
                                <button type="submit" class="btn btn-primary ms-auto">
                                    <i class="far fa-save me-2"></i>&nbsp;
                                    Update Position
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
