@extends('layouts.app')

@section('title', 'Edit Item')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Manufacturing
                </div>
                <h2 class="page-title">
                    Edit Item: {{ $item->name }}
                </h2>
                <div class="page-subtitle">
                    Only Quantity per Pack and Status can be modified. Other details are managed via Excel import.
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('manufacturing.items.index') }}" class="btn btn-outline-secondary">
                        <i class="far fa-arrow-left me-2"></i>&nbsp;
                        <span class="d-none d-sm-inline">Back to Items</span>
                        <span class="d-sm-none">Back</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')
        
        <div class="row row-deck row-cards">
            <div class="col-md-8 col-lg-10">
                <!-- Item Information (Read-only) -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Item Information (Read-only)</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <dt>Accurate ID:</dt>
                                    <dd class="text-muted">{{ $item->accurate_id }}</dd>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <dt>Short Name:</dt>
                                    <dd class="text-muted">{{ $item->shortname ?: '-' }}</dd>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <dt>Item Name:</dt>
                            <dd class="text-muted">{{ $item->name }}</dd>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <dt>Category:</dt>
                                    <dd class="text-muted">{{ $item->itemCategory->name }}</dd>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <dt>Unit:</dt>
                                    <dd class="text-muted">{{ $item->unit ?: '-' }}</dd>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <dt>Brand/Merk:</dt>
                            <dd class="text-muted">{{ $item->merk ?: '-' }}</dd>
                        </div>
                    </div>
                </div>

                <!-- Editable Fields -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Editable Fields</h3>
                    </div>
                    <form method="POST" action="{{ route('manufacturing.items.update', $item) }}">
                        @csrf
                        @method('PUT')
                        
                        <!-- Hidden fields to preserve existing data -->
                        <input type="hidden" name="accurate_id" value="{{ $item->accurate_id }}">
                        <input type="hidden" name="shortname" value="{{ $item->shortname }}">
                        <input type="hidden" name="name" value="{{ $item->name }}">
                        <input type="hidden" name="item_category_id" value="{{ $item->item_category_id }}">
                        <input type="hidden" name="unit" value="{{ $item->unit }}">
                        <input type="hidden" name="merk" value="{{ $item->merk }}">
                        
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Quantity per Pack (kg)</label>
                                        <input type="number" class="form-control @error('qty_kg_per_pack') is-invalid @enderror" 
                                               name="qty_kg_per_pack" value="{{ old('qty_kg_per_pack', $item->qty_kg_per_pack) }}" min="1" max="32767">
                                        @error('qty_kg_per_pack')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Weight in kilograms per packaging unit</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <div class="form-selectgroup">
                                            <label class="form-selectgroup-item">
                                                <input type="radio" name="is_active" value="1" class="form-selectgroup-input" {{ old('is_active', $item->is_active) == 1 ? 'checked' : '' }}>
                                                <span class="form-selectgroup-label">
                                                    <span class="form-selectgroup-check"></span>
                                                    <span class="form-selectgroup-label-content">
                                                        <span class="form-selectgroup-title strong">Active</span>
                                                    </span>
                                                </span>
                                            </label>
                                            <label class="form-selectgroup-item">
                                                <input type="radio" name="is_active" value="0" class="form-selectgroup-input" {{ old('is_active', $item->is_active) == 0 ? 'checked' : '' }}>
                                                <span class="form-selectgroup-label">
                                                    <span class="form-selectgroup-check"></span>
                                                    <span class="form-selectgroup-label-content">
                                                        <span class="form-selectgroup-title strong">Inactive</span>
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
                                <a href="{{ route('manufacturing.items.index') }}" class="btn btn-link">Cancel</a>
                                <button type="submit" class="btn btn-primary ms-auto">
                                    <i class="far fa-save me-2"></i>&nbsp;
                                    Update Item
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Information Panel -->
            <div class="col-md-4 col-lg-2">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="far fa-info-circle me-1"></i>&nbsp;
                                <strong>Limited Editing:</strong><br>
                                Only Quantity per Pack and Status can be modified here.
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="far fa-file-arrow-up me-1"></i>&nbsp;
                                <strong>Other Changes:</strong><br>
                                Use Excel import to modify other item details.
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="far fa-clock me-1"></i>&nbsp;
                                <strong>Last Updated:</strong><br>
                                {{ $item->updated_at->format('M d, Y H:i') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
