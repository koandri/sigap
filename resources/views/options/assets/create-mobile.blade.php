@extends('layouts.app')

@section('title', 'Create Asset - Mobile Camera')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    Create Asset - Mobile Camera
                </h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('options.assets.create') }}" class="btn btn-outline-primary">
                    <i class="far fa-desktop me-1"></i> Standard Form
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <form action="{{ route('options.assets.store-mobile') }}" method="POST" id="asset-mobile-form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="specifications" id="specifications-input" value="">
            
            <!-- Photo Capture Section -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Capture Photos</h3>
                </div>
                <div class="card-body">
                    <!-- Camera Preview -->
                    <div class="camera-preview mb-3" id="camera-preview" style="display: none;">
                        <video id="video" autoplay playsinline style="width: 100%; max-width: 100%; height: auto; border-radius: 8px; background: #000;"></video>
                        <canvas id="canvas" style="display: none;"></canvas>
                    </div>
                    
                    <!-- Camera Controls -->
                    <div class="btn-group mb-3" role="group">
                        <button type="button" class="btn btn-primary" id="start-camera-btn">
                            <i class="far fa-camera me-1"></i> Start Camera
                        </button>
                        <button type="button" class="btn btn-success" id="capture-photo-btn" style="display: none;">
                            <i class="far fa-camera-retro me-1"></i> Capture Photo
                        </button>
                        <button type="button" class="btn btn-secondary" id="stop-camera-btn" style="display: none;">
                            <i class="far fa-stop me-1"></i> Stop Camera
                        </button>
                    </div>
                    
                    <script>
                    // Inline script to ensure function is available immediately
                    (function() {
                        if (!window.startCamera) {
                            window.startCamera = async function() {
                                const btn = document.getElementById('start-camera-btn');
                                const video = document.getElementById('video');
                                const preview = document.getElementById('camera-preview');
                                const captureBtn = document.getElementById('capture-photo-btn');
                                const stopBtn = document.getElementById('stop-camera-btn');
                                
                                if (!btn || !video || !preview) {
                                    alert('Camera elements not found');
                                    return;
                                }
                                
                                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                                    alert('Camera not supported');
                                    return;
                                }
                                
                                try {
                                    const stream = await navigator.mediaDevices.getUserMedia({
                                        video: { facingMode: 'environment' }
                                    });
                                    video.srcObject = stream;
                                    preview.style.display = 'block';
                                    btn.style.display = 'none';
                                    if (captureBtn) captureBtn.style.display = 'inline-block';
                                    if (stopBtn) stopBtn.style.display = 'inline-block';
                                    window.assetCameraStream = stream;
                                } catch (e) {
                                    alert('Camera error: ' + (e.message || 'Unknown'));
                                }
                            };
                        }
                    })();
                    </script>
                    
                    <!-- Photo Gallery -->
                    <div id="photo-gallery" class="row g-2 mb-3" style="display: none;">
                        <!-- Photos will be dynamically added here -->
                    </div>
                    
                    <!-- AI Analysis Button -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-info" id="analyze-photos-btn" disabled>
                            <i class="far fa-magic me-1"></i> Analyze All Photos with AI
                        </button>
                        <div id="analyze-loading" class="mt-2" style="display: none;">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Analyzing...</span>
                            </div>
                            <span class="ms-2">Analyzing images with AI...</span>
                        </div>
                        <div id="analyze-error" class="alert alert-danger mt-2" style="display: none; word-wrap: break-word; white-space: pre-wrap; font-size: 0.9rem;"></div>
                    </div>
                    
                    <!-- GPS Status -->
                    <div class="gps-status mt-2" id="gps-status" style="display: none;">
                        <small class="text-muted">
                            <i class="far fa-location-dot text-success"></i>
                            <span id="gps-status-text">GPS permission granted</span>
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Asset Information Form -->
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
                    
                    <!-- Fetch Specifications Button -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-success" id="fetch-specs-btn" disabled>
                            <i class="far fa-search me-1"></i> Fetch Specifications from Web
                        </button>
                        <div id="fetch-specs-loading" class="mt-2" style="display: none;">
                            <div class="spinner-border spinner-border-sm text-success" role="status">
                                <span class="visually-hidden">Fetching...</span>
                            </div>
                            <span class="ms-2">Searching for specifications...</span>
                        </div>
                        <div id="fetch-specs-error" class="alert alert-danger mt-2" style="display: none;"></div>
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
                        <label class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <span class="form-check-label">Active</span>
                        </label>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="btn-list justify-content-end">
                        <a href="{{ route('options.assets.index') }}" class="btn">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="submit-btn">Create Asset</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<link href="{{ asset('assets/tabler/dist/libs/tom-select/dist/css/tom-select.bootstrap5.css') }}" rel="stylesheet"/>
<script src="{{ asset('assets/tabler/dist/libs/tom-select/dist/js/tom-select.base.min.js') }}"></script>
<script>
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

// Global variables - define immediately
window.assetCameraStream = null;
window.assetCapturedPhotos = [];
window.assetCurrentLocation = null;
window.assetMaxPhotos = 10;
window.assetPhotoQuality = 0.8;

// Request geolocation permission
window.requestAssetGeolocation = async function() {
    const gpsStatus = document.getElementById('gps-status');
    const gpsStatusText = document.getElementById('gps-status-text');
    
    if (navigator.geolocation && gpsStatus && gpsStatusText) {
        try {
            const position = await new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                });
            });
            
            window.assetCurrentLocation = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            };
            
            gpsStatus.style.display = 'block';
            gpsStatusText.textContent = 'GPS: ' + window.assetCurrentLocation.latitude.toFixed(6) + ', ' + window.assetCurrentLocation.longitude.toFixed(6);
        } catch (error) {
            if (gpsStatus && gpsStatusText) {
                gpsStatus.style.display = 'block';
                gpsStatusText.textContent = 'GPS: Not available';
            }
        }
    }
};

// Define startCamera function IMMEDIATELY at the top - using function declaration
async function startCameraFunction() {
    const startCameraBtn = document.getElementById('start-camera-btn');
    const capturePhotoBtn = document.getElementById('capture-photo-btn');
    const stopCameraBtn = document.getElementById('stop-camera-btn');
    const cameraPreview = document.getElementById('camera-preview');
    const video = document.getElementById('video');
    
    if (!startCameraBtn || !video || !cameraPreview) {
        alert('Camera elements not found. Please refresh the page.');
        return;
    }
    
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert('Camera API is not supported in this browser. Please use a modern browser with camera support.');
        return;
    }
    
    try {
        // Request geolocation (non-blocking)
        if (window.requestAssetGeolocation) {
            window.requestAssetGeolocation().catch(function(err) {
                // Silently fail
            });
        }
        
        // Try to get rear camera first, fallback to any camera
        let constraints = {
            video: {
                facingMode: 'environment',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        };
        
        try {
            window.assetCameraStream = await navigator.mediaDevices.getUserMedia(constraints);
        } catch (rearCameraError) {
            // Fallback to any available camera
            constraints.video = {
                facingMode: 'user',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            };
            window.assetCameraStream = await navigator.mediaDevices.getUserMedia(constraints);
        }
        
        video.srcObject = window.assetCameraStream;
        cameraPreview.style.display = 'block';
        if (startCameraBtn) startCameraBtn.style.display = 'none';
        if (capturePhotoBtn) capturePhotoBtn.style.display = 'inline-block';
        if (stopCameraBtn) stopCameraBtn.style.display = 'inline-block';
    } catch (error) {
        let errorMessage = 'Unable to access camera. ';
        if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
            errorMessage += 'Please grant camera permissions in your browser settings.';
        } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
            errorMessage += 'No camera found on this device.';
        } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
            errorMessage += 'Camera is already in use by another application.';
        } else {
            errorMessage += 'Error: ' + (error.message || 'Unknown error');
        }
        alert(errorMessage);
    }
}

// Assign to window object
window.startCamera = startCameraFunction;

// Stop camera function - define immediately
window.stopCamera = function() {
    const startCameraBtn = document.getElementById('start-camera-btn');
    const capturePhotoBtn = document.getElementById('capture-photo-btn');
    const stopCameraBtn = document.getElementById('stop-camera-btn');
    const cameraPreview = document.getElementById('camera-preview');
    
    if (window.assetCameraStream) {
        window.assetCameraStream.getTracks().forEach(function(track) {
            track.stop();
        });
        window.assetCameraStream = null;
    }
    if (cameraPreview) cameraPreview.style.display = 'none';
    if (startCameraBtn) startCameraBtn.style.display = 'inline-block';
    if (capturePhotoBtn) capturePhotoBtn.style.display = 'none';
    if (stopCameraBtn) stopCameraBtn.style.display = 'none';
};

document.addEventListener('DOMContentLoaded', function() {
    // Initialize TomSelect for location
    try {
        const locationSelect = document.getElementById('location-select');
        if (locationSelect) {
            new TomSelect('#location-select', {
                placeholder: '-- Select Location --',
                allowEmptyOption: true
            });
        }
    } catch (e) {
        // Silently fail
    }
    
    // Get all elements
    const startCameraBtn = document.getElementById('start-camera-btn');
    const capturePhotoBtn = document.getElementById('capture-photo-btn');
    const stopCameraBtn = document.getElementById('stop-camera-btn');
    const analyzePhotosBtn = document.getElementById('analyze-photos-btn');
    const fetchSpecsBtn = document.getElementById('fetch-specs-btn');
    const cameraPreview = document.getElementById('camera-preview');
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const photoGallery = document.getElementById('photo-gallery');
    const analyzeLoading = document.getElementById('analyze-loading');
    const analyzeError = document.getElementById('analyze-error');
    const fetchSpecsLoading = document.getElementById('fetch-specs-loading');
    const fetchSpecsError = document.getElementById('fetch-specs-error');
    
    // Attach event listener to start camera button
    if (startCameraBtn) {
        startCameraBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (window.startCamera) {
                window.startCamera();
            }
        });
    }
    
    // Attach stop camera event listener
    if (stopCameraBtn) {
        stopCameraBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (window.stopCamera) {
                window.stopCamera();
            }
        });
    }
    
    // Capture photo
    if (capturePhotoBtn && video && canvas) {
        capturePhotoBtn.addEventListener('click', async function() {
            if (window.assetCapturedPhotos.length >= window.assetMaxPhotos) {
                alert('Maximum ' + window.assetMaxPhotos + ' photos allowed.');
                return;
            }
            
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);
            
            canvas.toBlob(async function(blob) {
                const reader = new FileReader();
                reader.onload = function() {
                    const photoData = {
                        image: reader.result,
                        gps: window.assetCurrentLocation
                    };
                    window.assetCapturedPhotos.push(photoData);
                    displayPhoto(photoData, window.assetCapturedPhotos.length - 1);
                    updatePhotoInputs();
                    if (analyzePhotosBtn) analyzePhotosBtn.disabled = false;
                };
                reader.readAsDataURL(blob);
            }, 'image/jpeg', window.assetPhotoQuality);
        });
    }
    
    // Display photo in gallery
    function displayPhoto(photoData, index) {
        const col = document.createElement('div');
        col.className = 'col-md-3 col-sm-4 col-6';
        col.innerHTML = `
            <div class="card">
                <img src="${photoData.image}" class="card-img-top" style="height: 150px; object-fit: cover;" alt="Photo ${index + 1}">
                <div class="card-body p-2">
                    <button type="button" class="btn btn-sm btn-danger w-100" onclick="removePhoto(${index})">
                        <i class="far fa-times me-1"></i> Remove
                    </button>
                </div>
            </div>
        `;
        photoGallery.appendChild(col);
        photoGallery.style.display = 'block';
    }
    
    // Remove photo
    window.removePhoto = function(index) {
        window.assetCapturedPhotos.splice(index, 1);
        updatePhotoGallery();
        updatePhotoInputs();
        if (window.assetCapturedPhotos.length === 0) {
            if (analyzePhotosBtn) analyzePhotosBtn.disabled = true;
            if (photoGallery) photoGallery.style.display = 'none';
        }
    };
    
    // Update photo gallery
    function updatePhotoGallery() {
        if (!photoGallery) return;
        photoGallery.innerHTML = '';
        window.assetCapturedPhotos.forEach(function(photo, index) {
            displayPhoto(photo, index);
        });
    }
    
    // Update hidden inputs for photos
    function updatePhotoInputs() {
        const form = document.getElementById('asset-mobile-form');
        if (!form) return;
        
        // Remove existing inputs
        document.querySelectorAll('input[name="photos[]"]').forEach(function(input) {
            input.remove();
        });
        document.querySelectorAll('input[name="gps_data[]"]').forEach(function(input) {
            input.remove();
        });
        
        // Add new inputs
        window.assetCapturedPhotos.forEach(function(photo, index) {
            const photoInput = document.createElement('input');
            photoInput.type = 'hidden';
            photoInput.name = 'photos[]';
            photoInput.value = photo.image;
            form.appendChild(photoInput);
            
            if (photo.gps) {
                const gpsInput = document.createElement('input');
                gpsInput.type = 'hidden';
                gpsInput.name = 'gps_data[]';
                gpsInput.value = JSON.stringify(photo.gps);
                form.appendChild(gpsInput);
            }
        });
    }
    
    // Analyze photos with AI
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
    
    if (analyzePhotosBtn) {
        analyzePhotosBtn.addEventListener('click', async function() {
            if (window.assetCapturedPhotos.length === 0) return;
            
            analyzePhotosBtn.disabled = true;
            analyzeLoading.style.display = 'block';
            analyzeError.style.display = 'none';
            analyzeLoading.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"></div><span class="ms-2">Resizing images...</span>';
            
            try {
                // Resize images before sending to API
                const resizedImages = [];
                for (let i = 0; i < window.assetCapturedPhotos.length; i++) {
                    // Use 0.6 quality to reduce payload size for mobile
                    const resized = await resizeImageForAPI(window.assetCapturedPhotos[i].image, 1024, 1024, 0.6);
                    resizedImages.push(resized);
                }
                
                analyzeLoading.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"></div><span class="ms-2">Analyzing images with AI...</span>';
                
                // Get CSRF token from meta tag
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                
                if (!csrfToken) {
                    throw new Error('CSRF token not found. Please refresh the page and try again.');
                }
                
                // Use relative URL to ensure it works on mobile devices accessing via IP
                const requestUrl = '/options/assets/analyze-images';
                
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
                    
                    // Enable fetch specs button if manufacturer and model are available
                    if (data.manufacturer && data.model) {
                        if (fetchSpecsBtn) fetchSpecsBtn.disabled = false;
                    }
                } else {
                    const errorMsg = data.error || 'Failed to analyze images';
                    analyzeError.className = 'alert alert-danger mt-2';
                    analyzeError.textContent = 'Error: ' + errorMsg;
                    analyzeError.style.display = 'block';
                    // Scroll to error on mobile
                    analyzeError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } catch (error) {
                analyzeLoading.style.display = 'none';
                analyzePhotosBtn.disabled = false;
                
                let errorMessage = 'An error occurred.';
                let errorDetails = '';
                let technicalDetails = '';
                
                // Check for common network errors
                if (error instanceof TypeError && (error.message === 'Load failed' || error.message === 'Failed to fetch')) {
                    errorMessage = 'Network Error: Could not reach the server.';
                    errorDetails = '<strong>Possible causes:</strong><ul class="mb-0 ps-3"><li>Total photo size is too large (try fewer photos)</li><li>Server connection timed out</li><li>Device lost internet connection</li><li>SSL/HTTPS certificate issue</li></ul>';
                } else {
                    errorMessage = error.message || error.toString();
                }
                
                analyzeError.className = 'alert alert-danger mt-2';
                analyzeError.innerHTML = `
                    <strong>${errorMessage}</strong>
                    ${errorDetails ? `<div class="mt-2 small">${errorDetails}</div>` : ''}
                `;
                analyzeError.style.display = 'block';
                
                // Scroll to error on mobile
                setTimeout(function() {
                    analyzeError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);
            }
        });
    }
    
    // Fetch specifications
    fetchSpecsBtn.addEventListener('click', async function() {
        const manufacturer = document.getElementById('asset-manufacturer').value;
        const model = document.getElementById('asset-model').value;
        
        if (!manufacturer || !model) {
            alert('Please enter both manufacturer and model before fetching specifications.');
            return;
        }
        
        fetchSpecsBtn.disabled = true;
        fetchSpecsLoading.style.display = 'block';
        fetchSpecsError.style.display = 'none';
        
        try {
            // Get CSRF token from meta tag
            const csrfToken2 = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            
            const response = await fetch('{{ route("options.assets.fetch-specifications") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken2
                },
                body: JSON.stringify({
                    manufacturer: manufacturer,
                    model: model
                })
            });
            
            const data = await response.json();
            fetchSpecsLoading.style.display = 'none';
            fetchSpecsBtn.disabled = false;
            
            if (data.success && data.specifications) {
                // Store specifications in hidden field
                document.getElementById('specifications-input').value = JSON.stringify(data.specifications);
                // Display specifications in the textarea
                displaySpecifications(data.specifications);
                fetchSpecsError.className = 'alert alert-success mt-2';
                fetchSpecsError.textContent = 'Specifications fetched successfully! They will be saved when you submit the form.';
                fetchSpecsError.style.display = 'block';
            } else {
                fetchSpecsError.textContent = data.error || 'Failed to fetch specifications';
                fetchSpecsError.style.display = 'block';
            }
        } catch (error) {
            fetchSpecsLoading.style.display = 'none';
            fetchSpecsBtn.disabled = false;
            fetchSpecsError.textContent = 'An error occurred. Please try again.';
            fetchSpecsError.style.display = 'block';
        }
    });
    
    // Enable fetch specs button when both manufacturer and model are filled
    document.getElementById('asset-manufacturer').addEventListener('input', checkSpecsButton);
    document.getElementById('asset-model').addEventListener('input', checkSpecsButton);
    
    function checkSpecsButton() {
        const manufacturer = document.getElementById('asset-manufacturer').value;
        const model = document.getElementById('asset-model').value;
        fetchSpecsBtn.disabled = !(manufacturer && model);
    }
    
    // Form validation and specifications conversion
    const assetForm = document.getElementById('asset-mobile-form');
    if (assetForm) {
        assetForm.addEventListener('submit', function(e) {
            if (window.assetCapturedPhotos.length === 0) {
                e.preventDefault();
                alert('Please capture at least one photo before submitting.');
                return false;
            }
            
            // Convert specifications textarea to JSON before form submission
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
                    }
                }
            }
        });
    }
    
    // Clean up on page unload
    window.addEventListener('beforeunload', function() {
        if (window.stopCamera) {
            window.stopCamera();
        }
    });
});
</script>
@endpush

