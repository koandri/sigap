@extends('layouts.app')

@section('title', 'Edit Warehouse: ' . $warehouse->name)

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
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    Edit Warehouse
                    @if($warehouse->is_active)
                        <span class="badge bg-green ms-2">Active</span>
                    @else
                        <span class="badge bg-red ms-2">Inactive</span>
                    @endif
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('manufacturing.warehouses.show', $warehouse) }}" class="btn">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')
        
        <form action="{{ route('manufacturing.warehouses.update', $warehouse) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row row-deck row-cards">
                <!-- Basic Information -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Basic Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Warehouse Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               name="name" value="{{ old('name', $warehouse->name) }}" 
                                               placeholder="e.g., Main Warehouse, Cold Storage" required maxlength="100">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">A descriptive name for the warehouse location.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Code</label>
                                        <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                               name="code" value="{{ old('code', $warehouse->code) }}" 
                                               placeholder="e.g., WH01, COLD, MAIN" maxlength="20">
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Optional short code for quick identification.</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          name="description" rows="3" placeholder="Describe the warehouse purpose and contents...">{{ old('description', $warehouse->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Settings -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">
                                    <input type="checkbox" class="form-check-input me-2" name="is_active" value="1" 
                                           {{ old('is_active', $warehouse->is_active) ? 'checked' : '' }}>
                                    Active Warehouse
                                </label>
                                <small class="form-hint d-block">Inactive warehouses cannot be used for new inventory operations.</small>
                            </div>
                            
                            @if($warehouse->shelves()->whereHas('shelfPositions.positionItems', function($q) {
                                $q->where('quantity', '>', 0);
                            })->exists())
                            <div class="alert alert-info">
                                <h4 class="alert-title">Inventory Notice</h4>
                                <div class="text-muted">
                                    This warehouse currently contains items in shelf positions. 
                                    Deactivating this warehouse will prevent new inventory operations but existing inventory will remain accessible.
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-footer text-end">
                            <div class="d-flex">
                                <a href="{{ route('manufacturing.warehouses.show', $warehouse) }}" class="btn btn-outline-secondary me-auto">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-regular fa-pen"></i>
                                    Update Warehouse
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
