@extends('layouts.app')

@section('title', 'Create Warehouse')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.dashboard') }}">Manufacturing</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.warehouses.index') }}">Warehouses</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    Create Warehouse
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('manufacturing.warehouses.index') }}" class="btn btn-outline-secondary">
                        <i class="far fa-arrow-left me-2"></i>&nbsp;
                        Back to Warehouses
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
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Warehouse Information</h3>
                    </div>
                    <form action="{{ route('manufacturing.warehouses.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Warehouse Code</label>
                                        <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                               name="code" value="{{ old('code') }}" 
                                               placeholder="e.g., WH-RM, WH-CS" required maxlength="10">
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Unique code for the warehouse (max 10 characters).</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Warehouse Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               name="name" value="{{ old('name') }}" 
                                               placeholder="e.g., Raw Materials Storage" required maxlength="50">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Descriptive name for the warehouse (max 50 characters).</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          name="description" rows="3" placeholder="Describe the warehouse purpose and what it stores...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">Optional description of warehouse purpose and contents.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-selectgroup">
                                    <label class="form-selectgroup-item">
                                        <input type="radio" name="is_active" value="1" class="form-selectgroup-input" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                        <span class="form-selectgroup-label">
                                            <span class="form-selectgroup-check"></span>
                                            <span class="form-selectgroup-label-content">
                                                <span class="form-selectgroup-title strong">Active</span>
                                                <span class="form-selectgroup-description">Warehouse is operational and can be used</span>
                                            </span>
                                        </span>
                                    </label>
                                    <label class="form-selectgroup-item">
                                        <input type="radio" name="is_active" value="0" class="form-selectgroup-input" {{ old('is_active') == '0' ? 'checked' : '' }}>
                                        <span class="form-selectgroup-label">
                                            <span class="form-selectgroup-check"></span>
                                            <span class="form-selectgroup-label-content">
                                                <span class="form-selectgroup-title strong">Inactive</span>
                                                <span class="form-selectgroup-description">Warehouse is not operational</span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <div class="d-flex">
                                <a href="{{ route('manufacturing.warehouses.index') }}" class="btn btn-link">Cancel</a>
                                <button type="submit" class="btn btn-primary ms-auto">
                                    <i class="far fa-save me-2"></i>&nbsp;
                                    Create Warehouse
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
