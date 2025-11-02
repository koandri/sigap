@props(['field', 'value' => '', 'prefillData' => []])

@php
    $fieldValue = old('fields.'.$field->field_code, $prefillData[$field->field_code] ?? $value);
    $maxPhotos = $field->validation_rules['max_photos'] ?? 1;
    $photoQuality = $field->validation_rules['photo_quality'] ?? 0.8;
@endphp

@once
@push('css')
<style>
.live-photo-container {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}

.camera-preview {
    margin-bottom: 15px;
}

.camera-preview video {
    width: 100%;
    max-width: 400px;
    height: auto;
    border-radius: 8px;
    background: #000;
}

.photo-controls {
    margin-bottom: 15px;
}

.photo-controls .btn {
    margin: 0 5px;
}

.captured-photos {
    margin-top: 15px;
}

.photo-preview img {
    max-width: 300px;
    max-height: 300px;
    object-fit: cover;
}
</style>
@endpush
@endonce

<div class="live-photo-container" data-field-code="{{ $field->field_code }}">
    <div class="camera-preview" id="camera-preview-{{ $field->field_code }}" style="display: none;">
        <video id="video-{{ $field->field_code }}" autoplay playsinline></video>
        <canvas id="canvas-{{ $field->field_code }}" style="display: none;"></canvas>
    </div>
    
    <div class="photo-controls">
        <button type="button" class="btn btn-primary" id="start-camera-{{ $field->field_code }}">
            <i class="fa-solid fa-camera"></i>&nbsp; Start Camera
        </button>
        <button type="button" class="btn btn-success" id="capture-photo-{{ $field->field_code }}" style="display: none;">
            <i class="fa-solid fa-camera-retro"></i>&nbsp; Capture Photo
        </button>
        <button type="button" class="btn btn-secondary" id="retake-photo-{{ $field->field_code }}" style="display: none;">
            <i class="fa-solid fa-redo"></i>&nbsp; Retake
        </button>
    </div>
    
    <!-- GPS Status Indicator -->
    <div class="gps-status mt-2" id="gps-status-{{ $field->field_code }}" style="display: none;">
        <small class="text-muted">
            <i class="fa-solid fa-location-dot text-success"></i>&nbsp;
            <span id="gps-status-text-{{ $field->field_code }}">GPS permission granted</span>
        </small>
    </div>
    
    <div class="captured-photos" id="captured-photos-{{ $field->field_code }}">
        @if($fieldValue)
            <div class="photo-preview">
                <img src="{{ $fieldValue }}" alt="Captured Photo" class="img-fluid rounded">
            </div>
        @endif
    </div>
    
    <!-- Hidden input to store the photo data -->
    <input type="hidden" 
           id="photo-data-{{ $field->field_code }}" 
           name="fields[{{ $field->field_code }}]" 
           value="{{ $fieldValue }}"
           {{ $field->is_required ? 'required' : '' }}>
</div>

@push('scripts')
<script>
// Wait for page to be fully loaded to prevent layout forcing
window.addEventListener('load', function() {
    const fieldCode = '{{ $field->field_code }}';
    const maxPhotos = {{ $maxPhotos }};
    const photoQuality = {{ $photoQuality }};
    
    let stream = null;
    let capturedPhotos = [];
    let currentLocation = null;
    
    const startCameraBtn = document.getElementById(`start-camera-${fieldCode}`);
    const capturePhotoBtn = document.getElementById(`capture-photo-${fieldCode}`);
    const retakePhotoBtn = document.getElementById(`retake-photo-${fieldCode}`);
    const cameraPreview = document.getElementById(`camera-preview-${fieldCode}`);
    const video = document.getElementById(`video-${fieldCode}`);
    const canvas = document.getElementById(`canvas-${fieldCode}`);
    const capturedPhotosDiv = document.getElementById(`captured-photos-${fieldCode}`);
    const photoDataInput = document.getElementById(`photo-data-${fieldCode}`);
    const gpsStatusDiv = document.getElementById(`gps-status-${fieldCode}`);
    const gpsStatusText = document.getElementById(`gps-status-text-${fieldCode}`);
    
    startCameraBtn.addEventListener('click', async function() {
        try {
            // Request geolocation permission first
            await requestGeolocation();
            
            // Request rear camera specifically
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { exact: 'environment' }, // Force rear camera
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            });
            
            video.srcObject = stream;
            cameraPreview.style.display = 'block';
            startCameraBtn.style.display = 'none';
            capturePhotoBtn.style.display = 'inline-block';
            
        } catch (error) {
            alert('Unable to access camera. Please ensure you have granted camera permissions and are using a device with a rear camera.');
        }
    });
    
    capturePhotoBtn.addEventListener('click', async function() {
        if (capturedPhotos.length >= maxPhotos) {
            alert(`Maximum ${maxPhotos} photo(s) allowed.`);
            return;
        }
        
        // Set canvas dimensions to match video
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Draw current video frame to canvas
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        
        // Convert to base64 with GPS coordinates embedded
        const photoData = await capturePhotoWithGPS(canvas);
        const photoWithGPS = {
            image: photoData,
            gps: currentLocation
        };
        capturedPhotos.push(photoWithGPS);
        
        // Display captured photo
        displayCapturedPhoto(photoWithGPS);
        
        // Update hidden input
        updatePhotoDataInput();
        
        // Show retake button
        retakePhotoBtn.style.display = 'inline-block';
        
        // Stop camera after capture
        stopCamera();
    });
    
    retakePhotoBtn.addEventListener('click', function() {
        // Remove last photo
        capturedPhotos.pop();
        
        // Update display
        updatePhotoDisplay();
        updatePhotoDataInput();
        
        // Hide retake button if no photos
        if (capturedPhotos.length === 0) {
            retakePhotoBtn.style.display = 'none';
        }
        
        // Restart camera
        startCamera();
    });
    
    // Request geolocation permission
    async function requestGeolocation() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                resolve(null);
                return;
            }
            
            
            const options = {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000 // Cache for 1 minute
            };
            
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    currentLocation = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy
                    };
                    // Show GPS status
                    if (gpsStatusDiv && gpsStatusText) {
                        gpsStatusDiv.style.display = 'block';
                        gpsStatusText.innerHTML = `GPS: ${currentLocation.latitude.toFixed(6)}, ${currentLocation.longitude.toFixed(6)}`;
                        gpsStatusDiv.querySelector('i').className = 'fa-solid fa-location-dot text-success';
                    }
                    resolve(currentLocation);
                },
                (error) => {
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            break;
                        case error.POSITION_UNAVAILABLE:
                            break;
                        case error.TIMEOUT:
                            break;
                    }
                    currentLocation = null;
                    // Show GPS error status
                    if (gpsStatusDiv && gpsStatusText) {
                        gpsStatusDiv.style.display = 'block';
                        gpsStatusText.innerHTML = 'GPS permission denied or unavailable';
                        gpsStatusDiv.querySelector('i').className = 'fa-solid fa-location-slash text-warning';
                    }
                    resolve(null);
                },
                options
            );
        });
    }
    
    // Capture photo with GPS coordinates embedded
    async function capturePhotoWithGPS(canvas) {
        // Get current location if available
        if (!currentLocation) {
            await requestGeolocation();
        }
        
        // Convert canvas to blob
        return new Promise((resolve) => {
            canvas.toBlob(async (blob) => {
                if (currentLocation) {
                    // Create a new image with EXIF data including GPS coordinates
                    const reader = new FileReader();
                    reader.onload = function() {
                        // For now, we'll store the GPS data separately
                        // The backend will handle embedding it into the image
                        const base64 = reader.result;
                        resolve(base64);
                    };
                    reader.readAsDataURL(blob);
                } else {
                    // No GPS data available
                    canvas.toBlob((blob) => {
                        const reader = new FileReader();
                        reader.onload = function() {
                            resolve(reader.result);
                        };
                        reader.readAsDataURL(blob);
                    }, 'image/jpeg', photoQuality);
                }
            }, 'image/jpeg', photoQuality);
        });
    }
    
    function displayCapturedPhoto(photoData) {
        const photoDiv = document.createElement('div');
        photoDiv.className = 'photo-preview mb-2';
        
        const imageSrc = typeof photoData === 'string' ? photoData : photoData.image;
        const gpsInfo = typeof photoData === 'object' && photoData.gps ? 
            `<br><small class="text-success"><i class="fa-solid fa-location-dot"></i>&nbsp; GPS: ${photoData.gps.latitude.toFixed(6)}, ${photoData.gps.longitude.toFixed(6)}</small>` : 
            '<br><small class="text-warning"><i class="fa-solid fa-location-slash"></i>&nbsp; No GPS data</small>';
        
        photoDiv.innerHTML = `
            <img src="${imageSrc}" alt="Captured Photo" class="img-fluid rounded">
            <div class="mt-1">
                <small class="text-muted">Photo ${capturedPhotos.length} captured</small>
                ${gpsInfo}
            </div>
        `;
        capturedPhotosDiv.appendChild(photoDiv);
    }
    
    function updatePhotoDisplay() {
        capturedPhotosDiv.innerHTML = '';
        capturedPhotos.forEach((photoData, index) => {
            displayCapturedPhoto(photoData);
        });
    }
    
    function updatePhotoDataInput() {
        // Remove existing array inputs first
        document.querySelectorAll(`input[name="fields[${fieldCode}][]"]`).forEach(input => {
            input.remove();
        });
        
        // Check if field allows multiple photos
        const allowsMultiplePhotos = {{ $maxPhotos }} > 1;
        
        if (capturedPhotos.length === 0) {
            photoDataInput.value = '';
            if (allowsMultiplePhotos) {
                photoDataInput.name = `fields[${fieldCode}][]`;
            } else {
                photoDataInput.name = `fields[${fieldCode}]`;
            }
        } else if (allowsMultiplePhotos) {
            // Field allows multiple photos - always use array format
            photoDataInput.value = '';
            photoDataInput.name = `fields[${fieldCode}]`;
            
            capturedPhotos.forEach((photoData, index) => {
                const arrayInput = document.createElement('input');
                arrayInput.type = 'hidden';
                arrayInput.name = `fields[${fieldCode}][]`;
                // Send the entire photo object (image + GPS) as JSON
                arrayInput.value = JSON.stringify(photoData);
                photoDataInput.parentNode.appendChild(arrayInput);
            });
        } else {
            // Field only allows single photo - use regular input
            photoDataInput.value = JSON.stringify(capturedPhotos[0]);
            photoDataInput.name = `fields[${fieldCode}]`;
        }
    }
    
    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        cameraPreview.style.display = 'none';
        startCameraBtn.style.display = 'inline-block';
        capturePhotoBtn.style.display = 'none';
    }
    
    function startCamera() {
        startCameraBtn.click();
    }
    
    // Clean up on page unload
    window.addEventListener('beforeunload', stopCamera);
});
</script>
@endpush
