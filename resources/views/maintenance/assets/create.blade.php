@extends('layouts.app')

@section('title', 'Create Asset')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    Create Asset
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row">
            <div class="col-md-8">
                <form action="{{ route('maintenance.assets.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Asset Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Name</label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                               value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Code</label>
                                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                                               value="{{ old('code') }}" required>
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Category</label>
                                        <select name="asset_category_id" class="form-select @error('asset_category_id') is-invalid @enderror" required>
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('asset_category_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('asset_category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Status</label>
                                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                            <option value="operational" {{ old('status') === 'operational' ? 'selected' : '' }}>Operational</option>
                                            <option value="down" {{ old('status') === 'down' ? 'selected' : '' }}>Down</option>
                                            <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Location</label>
                                        <select name="location_id" class="form-select @error('location_id') is-invalid @enderror" id="location-select">
                                            <option value="">-- Select Location --</option>
                                            @foreach($locations as $location)
                                                <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                                    {{ $location->name }} ({{ $location->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('location_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Serial Number</label>
                                        <input type="text" name="serial_number" class="form-control @error('serial_number') is-invalid @enderror" 
                                               value="{{ old('serial_number') }}">
                                        @error('serial_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Manufacturer</label>
                                        <input type="text" name="manufacturer" class="form-control @error('manufacturer') is-invalid @enderror" 
                                               value="{{ old('manufacturer') }}">
                                        @error('manufacturer')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Model</label>
                                        <input type="text" name="model" class="form-control @error('model') is-invalid @enderror" 
                                               value="{{ old('model') }}">
                                        @error('model')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Purchase Date</label>
                                        <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" 
                                               value="{{ old('purchase_date') }}">
                                        @error('purchase_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Warranty Expiry</label>
                                        <input type="date" name="warranty_expiry" class="form-control @error('warranty_expiry') is-invalid @enderror" 
                                               value="{{ old('warranty_expiry') }}">
                                        @error('warranty_expiry')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Department</label>
                                        <select name="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                            <option value="">Select Department</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                    {{ $department->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('department_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Assigned To</label>
                                        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror">
                                            <option value="">Select User</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('user_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Asset Image</label>
                                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" 
                                       accept="image/*">
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-check">
                                    <input type="checkbox" name="is_active" class="form-check-input" value="1" 
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <span class="form-check-label">Active</span>
                                </label>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="btn-list justify-content-end">
                                <a href="{{ route('maintenance.assets.index') }}" class="btn">Cancel</a>
                                <button type="submit" class="btn btn-primary">Create Asset</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<link href="{{ asset('assets/tabler/dist/libs/tom-select/dist/css/tom-select.bootstrap5.css') }}" rel="stylesheet"/>
<script src="{{ asset('assets/tabler/dist/libs/tom-select/dist/js/tom-select.base.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new TomSelect('#location-select', {
        placeholder: '-- Select Location --',
        allowEmptyOption: true
    });
});
</script>
@endsection