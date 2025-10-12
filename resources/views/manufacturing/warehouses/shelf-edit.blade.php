@extends('layouts.app')

@section('title', 'Edit Shelf: ' . $shelf->shelf_code)

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.dashboard') }}">Manufacturing</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.warehouses.index') }}">Warehouses</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.warehouses.show', $warehouse) }}">{{ $warehouse->name }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.warehouses.shelf-management', $warehouse) }}">Shelf Management</a></li>
                        <li class="breadcrumb-item active">Edit Shelf</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    Edit Shelf
                    <span class="text-muted">- {{ $shelf->shelf_code }}</span>
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('manufacturing.warehouses.shelf-management', $warehouse) }}" class="btn btn-outline-secondary">
                        <i class="fa-regular fa-arrow-left me-2"></i>
                        Back to Shelves
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')
        
        <form action="{{ route('manufacturing.warehouses.shelf.update', [$warehouse, $shelf]) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Shelf Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Shelf Code</label>
                                        <input type="text" class="form-control @error('shelf_code') is-invalid @enderror" 
                                               name="shelf_code" value="{{ old('shelf_code', $shelf->shelf_code) }}" 
                                               placeholder="e.g., A-01, B-02, C-03" required maxlength="10">
                                        @error('shelf_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Unique code for this shelf (e.g., A-01, B-02).</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Shelf Name</label>
                                        <input type="text" class="form-control @error('shelf_name') is-invalid @enderror" 
                                               name="shelf_name" value="{{ old('shelf_name', $shelf->shelf_name) }}" 
                                               placeholder="e.g., Section A-01, Main Storage" required maxlength="50">
                                        @error('shelf_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Descriptive name for this shelf.</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          name="description" rows="3" placeholder="Describe the shelf purpose and what it stores...">{{ old('description', $shelf->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">Optional description of shelf purpose and contents.</small>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Maximum Capacity</label>
                                        <input type="number" class="form-control @error('max_capacity') is-invalid @enderror" 
                                               name="max_capacity" value="{{ old('max_capacity', $shelf->max_capacity) }}" 
                                               min="1" max="20" required>
                                        @error('max_capacity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Maximum number of positions this shelf can hold (1-20).</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <div class="form-selectgroup">
                                            <label class="form-selectgroup-item">
                                                <input type="radio" name="is_active" value="1" class="form-selectgroup-input" {{ old('is_active', $shelf->is_active) ? 'checked' : '' }}>
                                                <span class="form-selectgroup-label">
                                                    <span class="form-selectgroup-check"></span>
                                                    <span class="form-selectgroup-label-content">
                                                        <span class="form-selectgroup-title strong">Active</span>
                                                        <span class="form-selectgroup-description">Shelf is operational</span>
                                                    </span>
                                                </span>
                                            </label>
                                            <label class="form-selectgroup-item">
                                                <input type="radio" name="is_active" value="0" class="form-selectgroup-input" {{ old('is_active', $shelf->is_active) == '0' ? 'checked' : '' }}>
                                                <span class="form-selectgroup-label">
                                                    <span class="form-selectgroup-check"></span>
                                                    <span class="form-selectgroup-label-content">
                                                        <span class="form-selectgroup-title strong">Inactive</span>
                                                        <span class="form-selectgroup-description">Shelf is not operational</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <div class="d-flex">
                                <a href="{{ route('manufacturing.warehouses.shelf-management', $warehouse) }}" class="btn btn-link">Cancel</a>
                                <button type="submit" class="btn btn-primary ms-auto">
                                    <i class="fa-regular fa-save me-2"></i>
                                    Update Shelf
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
