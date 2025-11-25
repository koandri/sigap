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
            <div class="col-auto">
                <a href="{{ route('options.assets.create-mobile') }}" class="btn btn-outline-primary">
                    <i class="far fa-camera me-1"></i> Use Mobile Camera
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row">
            <div class="col-12">
                <form action="{{ route('options.assets.store') }}" method="POST" enctype="multipart/form-data">
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
                                        <input type="text" name="name" id="asset-name" class="form-control @error('name') is-invalid @enderror" 
                                               value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Code <span class="text-muted">(Auto-generated if left empty)</span></label>
                                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                                               value="{{ old('code') }}" 
                                               placeholder="Leave empty for auto-generation">
                                        <small class="form-hint">Format: {CATEGORY}-{YYMMDD}-{SEQUENCE} (e.g., PROD-241120-0001)</small>
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
                                        <select name="asset_category_id" id="asset-category" class="form-select @error('asset_category_id') is-invalid @enderror" required>
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
                                            <option value="disposed" {{ old('status') === 'disposed' ? 'selected' : '' }}>Disposed</option>
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
                                        <input type="text" name="serial_number" id="asset-serial" class="form-control @error('serial_number') is-invalid @enderror" 
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
                                        <input type="text" name="manufacturer" id="asset-manufacturer" class="form-control @error('manufacturer') is-invalid @enderror" 
                                               value="{{ old('manufacturer') }}">
                                        @error('manufacturer')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Model</label>
                                        <input type="text" name="model" id="asset-model" class="form-control @error('model') is-invalid @enderror" 
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
                                <label class="form-label">Asset Photos</label>
                                <input type="file" name="photos[]" id="asset-photos-input" class="form-control @error('photos.*') is-invalid @enderror" 
                                       accept="image/*" multiple>
                                <small class="form-hint">You can select multiple photos (max 10). First photo will be set as primary.</small>
                                <input type="hidden" name="specifications" id="specifications-input" value="">
                                @error('photos.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('photos')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                                <!-- Image Preview and AI Analysis -->
                                <div id="photo-preview-container" class="mt-3" style="display: none;">
                                    <div class="row g-2 mb-3" id="photo-preview-gallery"></div>
                                    
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-primary" id="analyze-photos-btn" disabled>
                                            <i class="far fa-robot me-1"></i> Analyze with AI
                                        </button>
                                        <div id="analyze-loading" class="mt-2" style="display: none;">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                <span class="visually-hidden">Analyzing...</span>
                                            </div>
                                            <span class="ms-2">Analyzing images with AI...</span>
                                        </div>
                                        <div id="analyze-error" class="alert alert-danger mt-2" style="display: none; word-wrap: break-word; white-space: pre-wrap; font-size: 0.9rem;"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Specifications -->
                            <div class="mb-3">
                                <label class="form-label">Specifications</label>
                                <textarea name="specifications_text" id="specifications-textarea" class="form-control @error('specifications') is-invalid @enderror" 
                                          rows="4" placeholder="Enter specifications as JSON or key-value pairs. AI analysis will auto-populate this field.">{{ old('specifications_text') }}</textarea>
                                <small class="form-hint">Specifications will be automatically populated when you analyze images with AI. You can also manually edit this field.</small>
                                @error('specifications')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <!-- Component and Lifetime Information -->
                        <div class="card-header">
                            <h3 class="card-title">Component & Lifetime Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Parent Asset</label>
                                        <select name="parent_asset_id" id="parent-asset-select" class="form-select @error('parent_asset_id') is-invalid @enderror">
                                            <option value="">None (Standalone Asset)</option>
                                            @foreach($assets ?? [] as $parentAsset)
                                                <option value="{{ $parentAsset->id }}" {{ old('parent_asset_id') == $parentAsset->id ? 'selected' : '' }}>
                                                    {{ $parentAsset->name }} ({{ $parentAsset->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('parent_asset_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Select if this asset is a component of another asset</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Component Type</label>
                                        <select name="component_type" id="component-type-select" class="form-select @error('component_type') is-invalid @enderror">
                                            <option value="">Not a Component</option>
                                            <option value="consumable" {{ old('component_type') == 'consumable' ? 'selected' : '' }}>Consumable</option>
                                            <option value="replaceable" {{ old('component_type') == 'replaceable' ? 'selected' : '' }}>Replaceable</option>
                                            <option value="integral" {{ old('component_type') == 'integral' ? 'selected' : '' }}>Integral</option>
                                        </select>
                                        @error('component_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Installed Date</label>
                                        <input type="date" name="installed_date" class="form-control @error('installed_date') is-invalid @enderror" 
                                               value="{{ old('installed_date') }}">
                                        @error('installed_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">When the asset/component was first installed/used</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Start Usage Value</label>
                                        <input type="number" step="0.01" min="0" name="installed_usage_value" class="form-control @error('installed_usage_value') is-invalid @enderror" 
                                               value="{{ old('installed_usage_value') }}" placeholder="e.g., 50000 for Start KM">
                                        @error('installed_usage_value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Parent asset's usage when component was installed (e.g., car's kilometers)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Usage Type</label>
                                        <select name="usage_type_id" id="usage-type-select" class="form-select @error('usage_type_id') is-invalid @enderror">
                                            <option value="">Select Usage Type</option>
                                            <!-- Will be populated via JavaScript based on category selection -->
                                        </select>
                                        @error('usage_type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Differentiate usage within the same category (e.g., Delivery Truck vs Passenger Car)</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Lifetime Unit</label>
                                        <select name="lifetime_unit" id="lifetime-unit-select" class="form-select @error('lifetime_unit') is-invalid @enderror">
                                            <option value="">Select Unit</option>
                                            <option value="days" {{ old('lifetime_unit') == 'days' ? 'selected' : '' }}>Days</option>
                                            <option value="kilometers" {{ old('lifetime_unit') == 'kilometers' ? 'selected' : '' }}>Kilometers</option>
                                            <option value="machine_hours" {{ old('lifetime_unit') == 'machine_hours' ? 'selected' : '' }}>Machine Hours</option>
                                            <option value="cycles" {{ old('lifetime_unit') == 'cycles' ? 'selected' : '' }}>Cycles</option>
                                        </select>
                                        @error('lifetime_unit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Expected Lifetime Value</label>
                                        <input type="number" step="0.01" min="0" name="expected_lifetime_value" class="form-control @error('expected_lifetime_value') is-invalid @enderror" 
                                               value="{{ old('expected_lifetime_value') }}">
                                        @error('expected_lifetime_value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Expected lifetime in the selected unit</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="btn-list justify-content-end">
                                <a href="{{ route('options.assets.index') }}" class="btn">Cancel</a>
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

@push('css')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet"/>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
// Usage types data for JavaScript
const usageTypesData = @json($categories->mapWithKeys(function($category) {
    return [$category->id => $category->usageTypes->map(function($usageType) {
        return ['id' => $usageType->id, 'name' => $usageType->name];
    })];
}));

// Function to display specifications (defined globally)
function displaySpecifications(specs) {
    const textarea = document.getElementById('specifications-textarea');
    
    if (!textarea) {
        return;
    }
    
    if (!specs || Object.keys(specs).length === 0) {
        return;
    }
    
    let textareaValue = '';
    
    Object.entries(specs).forEach(([key, value]) => {
        if (value) {
            if (textareaValue) textareaValue += '\n';
            textareaValue += `${key}: ${value}`;
        }
    });
    
    textarea.value = textareaValue;
}

document.addEventListener('DOMContentLoaded', function() {
    new TomSelect('#location-select', {
        placeholder: '-- Select Location --',
        allowEmptyOption: true
    });

    // Handle category change to populate usage types
    const categorySelect = document.getElementById('asset-category');
    const usageTypeSelect = document.getElementById('usage-type-select');
    
    if (categorySelect && usageTypeSelect) {
        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            usageTypeSelect.innerHTML = '<option value="">Select Usage Type</option>';
            
            if (categoryId && usageTypesData[categoryId]) {
                usageTypesData[categoryId].forEach(function(usageType) {
                    const option = document.createElement('option');
                    option.value = usageType.id;
                    option.textContent = usageType.name;
                    usageTypeSelect.appendChild(option);
                });
            }
        });
        
        // Trigger change if category is pre-selected
        if (categorySelect.value) {
            categorySelect.dispatchEvent(new Event('change'));
        }
    }
    
    // Image preview and AI analysis
    const photoInput = document.getElementById('asset-photos-input');
    const photoPreviewContainer = document.getElementById('photo-preview-container');
    const photoPreviewGallery = document.getElementById('photo-preview-gallery');
    const analyzePhotosBtn = document.getElementById('analyze-photos-btn');
    const analyzeLoading = document.getElementById('analyze-loading');
    const analyzeError = document.getElementById('analyze-error');
    let selectedImages = [];
    
    // Handle file selection
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            selectedImages = [];
            
            if (photoPreviewGallery) {
                photoPreviewGallery.innerHTML = '';
            }
            
            if (files.length > 0) {
                if (photoPreviewContainer) {
                    photoPreviewContainer.style.display = 'block';
                }
                if (analyzePhotosBtn) {
                    analyzePhotosBtn.disabled = false;
                }
                
                files.forEach((file, index) => {
                    if (index >= 10) return; // Max 10 images
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imageData = e.target.result;
                        selectedImages.push(imageData);
                        
                        // Create preview
                        if (photoPreviewGallery) {
                            const col = document.createElement('div');
                            col.className = 'col-md-3 col-sm-4 col-6';
                            col.innerHTML = `
                                <div class="card">
                                    <img src="${imageData}" class="card-img-top" style="height: 150px; object-fit: cover;" alt="Preview ${index + 1}">
                                    <div class="card-body p-2">
                                        <small class="text-muted">Photo ${index + 1}</small>
                                    </div>
                                </div>
                            `;
                            photoPreviewGallery.appendChild(col);
                        }
                    };
                    reader.readAsDataURL(file);
                });
            } else {
                if (photoPreviewContainer) {
                    photoPreviewContainer.style.display = 'none';
                }
                if (analyzePhotosBtn) {
                    analyzePhotosBtn.disabled = true;
                }
            }
        });
    }
    
    // Helper function to resize image for API
    function resizeImageForAPI(dataUrl, maxWidth = 1024, maxHeight = 1024, quality = 0.7) {
        return new Promise(function(resolve) {
            const img = new Image();
            img.onload = function() {
                let width = img.width;
                let height = img.height;
                
                // Calculate new dimensions
                if (width > maxWidth || height > maxHeight) {
                    if (width > height) {
                        height = (height * maxWidth) / width;
                        width = maxWidth;
                    } else {
                        width = (width * maxHeight) / height;
                        height = maxHeight;
                    }
                }
                
                // Create canvas and resize
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                
                // Convert to base64
                const resizedDataUrl = canvas.toDataURL('image/jpeg', quality);
                resolve(resizedDataUrl);
            };
            img.onerror = function() {
                // If resize fails, return original
                resolve(dataUrl);
            };
            img.src = dataUrl;
        });
    }
    
    // Handle AI analysis
    if (analyzePhotosBtn) {
        analyzePhotosBtn.addEventListener('click', async function() {
            if (selectedImages.length === 0) return;
            
            analyzePhotosBtn.disabled = true;
            analyzeLoading.style.display = 'block';
            analyzeError.style.display = 'none';
            analyzeLoading.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"></div><span class="ms-2">Resizing images...</span>';
            
            try {
                // Resize images before sending to API
                const resizedImages = [];
                for (let i = 0; i < selectedImages.length; i++) {
                    const resized = await resizeImageForAPI(selectedImages[i], 1024, 1024, 0.7);
                    resizedImages.push(resized);
                }
                
                analyzeLoading.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"></div><span class="ms-2">Analyzing images with AI...</span>';
                
                // Get CSRF token from meta tag
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                
                if (!csrfToken) {
                    throw new Error('CSRF token not found. Please refresh the page and try again.');
                }
                
                const requestUrl = '{{ route("options.assets.analyze-images") }}';
                
                const response = await fetch(requestUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ images: resizedImages })
                });
                
                if (!response.ok) {
                    let errorData;
                    try {
                        const text = await response.text();
                        try {
                            errorData = JSON.parse(text);
                        } catch (e) {
                            errorData = { error: text || `HTTP ${response.status}: ${response.statusText}` };
                        }
                    } catch (e) {
                        errorData = { error: `HTTP ${response.status}: ${response.statusText}` };
                    }
                    const errorMsg = errorData.error || errorData.message || `HTTP ${response.status}: ${response.statusText}`;
                    throw new Error(errorMsg);
                }
                
                const data = await response.json();
                
                analyzeLoading.style.display = 'none';
                analyzePhotosBtn.disabled = false;
                
                if (data.success) {
                    // Auto-fill form fields
                    if (data.suggested_name) {
                        document.getElementById('asset-name').value = data.suggested_name;
                    }
                    if (data.suggested_category) {
                        const categorySelect = document.getElementById('asset-category');
                        for (let option of categorySelect.options) {
                            if (option.text.toLowerCase().includes(data.suggested_category.toLowerCase()) || 
                                data.suggested_category.toLowerCase().includes(option.text.toLowerCase())) {
                                categorySelect.value = option.value;
                                break;
                            }
                        }
                    }
                    if (data.manufacturer) {
                        document.getElementById('asset-manufacturer').value = data.manufacturer;
                    }
                    if (data.model) {
                        document.getElementById('asset-model').value = data.model;
                    }
                    if (data.serial_number) {
                        document.getElementById('asset-serial').value = data.serial_number;
                    }
                    
                    // Store AI specifications if available
                    if (data.specifications) {
                        document.getElementById('specifications-input').value = JSON.stringify(data.specifications);
                        // Display specifications in the specifications display area
                        displaySpecifications(data.specifications);
                    }
                    
                    // Show success message
                    let successMsg = 'Analysis complete! Form fields have been auto-filled.';
                    if (data.specifications && Object.keys(data.specifications).length > 0) {
                        successMsg += ' Specifications have been retrieved and will be saved.';
                    }
                    analyzeError.className = 'alert alert-success mt-2';
                    analyzeError.textContent = successMsg;
                    analyzeError.style.display = 'block';
                } else {
                    const errorMsg = data.error || 'Failed to analyze images';
                    analyzeError.className = 'alert alert-danger mt-2';
                    analyzeError.textContent = 'Error: ' + errorMsg;
                    analyzeError.style.display = 'block';
                }
            } catch (error) {
                analyzeLoading.style.display = 'none';
                analyzePhotosBtn.disabled = false;
                
                let errorMessage = 'An error occurred. Please try again.';
                if (error && error.message) {
                    errorMessage = error.message;
                } else if (error && error.toString) {
                    errorMessage = error.toString();
                } else if (typeof error === 'string') {
                    errorMessage = error;
                } else {
                    errorMessage = JSON.stringify(error);
                }
                
                analyzeError.className = 'alert alert-danger mt-2';
                analyzeError.textContent = 'Error: ' + errorMessage;
                analyzeError.style.display = 'block';
            }
        });
    }
    
    // Convert specifications textarea to JSON before form submission
    const form = document.querySelector('form[action*="assets"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            const textarea = document.getElementById('specifications-textarea');
            const hiddenInput = document.getElementById('specifications-input');
            
            if (textarea && textarea.value.trim()) {
                // If hidden input has JSON (from AI), use that, otherwise parse textarea
                if (hiddenInput && hiddenInput.value) {
                    // Already has JSON from AI
                    return;
                }
                
                // Try to parse as JSON first
                try {
                    const parsed = JSON.parse(textarea.value);
                    hiddenInput.value = JSON.stringify(parsed);
                } catch (e) {
                    // If not JSON, convert key-value pairs to object
                    const lines = textarea.value.split('\\n').filter(line => line.trim());
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
                    }
                }
            }
        });
    }
});
</script>
@endpush