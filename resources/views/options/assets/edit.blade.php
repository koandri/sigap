@extends('layouts.app')

@section('title', 'Edit Asset')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    Edit Asset
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <form action="{{ route('options.assets.update', $asset) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
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
                                               value="{{ old('name', $asset->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Code</label>
                                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                                               value="{{ old('code', $asset->code) }}" required>
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
                                                <option value="{{ $category->id }}" {{ old('asset_category_id', $asset->asset_category_id) == $category->id ? 'selected' : '' }}>
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
                                            <option value="operational" {{ old('status', $asset->status) === 'operational' ? 'selected' : '' }}>Operational</option>
                                            <option value="down" {{ old('status', $asset->status) === 'down' ? 'selected' : '' }}>Down</option>
                                            <option value="maintenance" {{ old('status', $asset->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                            <option value="disposed" {{ old('status', $asset->status) === 'disposed' ? 'selected' : '' }}>Disposed</option>
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
                                                <option value="{{ $location->id }}" {{ old('location_id', $asset->location_id) == $location->id ? 'selected' : '' }}>
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
                                               value="{{ old('serial_number', $asset->serial_number) }}">
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
                                               value="{{ old('manufacturer', $asset->manufacturer) }}">
                                        @error('manufacturer')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Model</label>
                                        <input type="text" name="model" class="form-control @error('model') is-invalid @enderror" 
                                               value="{{ old('model', $asset->model) }}">
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
                                               value="{{ old('purchase_date', $asset->purchase_date?->format('Y-m-d')) }}">
                                        @error('purchase_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Warranty Expiry</label>
                                        <input type="date" name="warranty_expiry" class="form-control @error('warranty_expiry') is-invalid @enderror" 
                                               value="{{ old('warranty_expiry', $asset->warranty_expiry?->format('Y-m-d')) }}">
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
                                                <option value="{{ $department->id }}" {{ old('department_id', $asset->department_id) == $department->id ? 'selected' : '' }}>
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
                                                <option value="{{ $user->id }}" {{ old('user_id', $asset->user_id) == $user->id ? 'selected' : '' }}>
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

                            <!-- Existing Photos -->
                            @if($asset->photos->count() > 0)
                            <div class="mb-3">
                                <label class="form-label">Current Photos</label>
                                <div class="row g-2">
                                    @foreach($asset->photos as $photo)
                                    <div class="col-md-3 col-sm-4 col-6">
                                        <div class="card {{ $photo->is_primary ? 'border-primary' : '' }}">
                                            <img src="{{ Storage::disk('s3')->url($photo->photo_path) }}" 
                                                 class="card-img-top" 
                                                 style="height: 150px; object-fit: cover;" 
                                                 alt="Photo">
                                            <div class="card-body p-2">
                                                @if($photo->is_primary)
                                                    <span class="badge bg-primary">Primary</span>
                                                @endif
                                                <small class="text-muted d-block">
                                                    {{ $photo->captured_at ? $photo->captured_at->setTimezone('Asia/Jakarta')->format('d M Y') : '-' }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label">Add More Photos</label>
                                <input type="file" name="photos[]" class="form-control @error('photos.*') is-invalid @enderror" 
                                       accept="image/*" multiple>
                                <small class="form-hint">You can select multiple photos (max 10).</small>
                                @error('photos.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('photos')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Specifications -->
                            <div class="mb-3">
                                <label class="form-label">Specifications</label>
                                @if($asset->specifications && count($asset->specifications) > 0)
                                <div class="card bg-light mb-2">
                                    <div class="card-body">
                                        <div class="row g-2">
                                            @foreach($asset->specifications as $key => $value)
                                                @if(!empty($value))
                                                <div class="col-md-6">
                                                    <strong class="text-capitalize">{{ str_replace('_', ' ', $key) }}:</strong> {{ $value }}
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <textarea name="specifications_text" id="specifications-textarea" class="form-control @error('specifications') is-invalid @enderror" 
                                          rows="4" placeholder="Enter specifications as JSON or key-value pairs (one per line: key: value)">@if($asset->specifications)@foreach($asset->specifications as $key => $value){{ $key }}: {{ $value }}
@endforeach@endif</textarea>
                                <small class="form-hint">Edit specifications as key-value pairs (one per line: key: value) or as JSON.</small>
                                <input type="hidden" name="specifications" id="specifications-input" value="{{ $asset->specifications ? json_encode($asset->specifications) : '' }}">
                                @error('specifications')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-check">
                                    <input type="checkbox" name="is_active" class="form-check-input" value="1" 
                                           {{ old('is_active', $asset->is_active) ? 'checked' : '' }}>
                                    <span class="form-check-label">Active</span>
                                </label>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="btn-list justify-content-end">
                                <a href="{{ route('options.assets.show', $asset) }}" class="btn">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Asset</button>
                            </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('css')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet"/>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new TomSelect('#location-select', {
        placeholder: '-- Select Location --',
        allowEmptyOption: true
    });
    
    // Convert specifications textarea to JSON before form submission
    const form = document.querySelector('form[action*="assets"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            const textarea = document.getElementById('specifications-textarea');
            const hiddenInput = document.getElementById('specifications-input');
            
            if (textarea && textarea.value.trim()) {
                // Try to parse as JSON first
                try {
                    const parsed = JSON.parse(textarea.value);
                    hiddenInput.value = JSON.stringify(parsed);
                } catch (e) {
                    // If not JSON, convert key-value pairs to object
                    const lines = textarea.value.split('\n').filter(line => line.trim());
                    const specs = {};
                    lines.forEach(line => {
                        const colonIndex = line.indexOf(':');
                        if (colonIndex > 0) {
                            const key = line.substring(0, colonIndex).trim();
                            const value = line.substring(colonIndex + 1).trim();
                            if (key && value) {
                                specs[key] = value;
                            }
                        }
                    });
                    if (Object.keys(specs).length > 0) {
                        hiddenInput.value = JSON.stringify(specs);
                    } else {
                        hiddenInput.value = '';
                    }
                }
            } else {
                hiddenInput.value = '';
            }
        });
    }
});
</script>
@endpush

