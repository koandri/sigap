@extends('layouts.app')

@section('title', 'Submit Task')

@push('css')
<style>
.camera-preview {
    position: relative;
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
    background: #000;
    border-radius: 8px;
    overflow: hidden;
}

.camera-preview video {
    width: 100%;
    height: auto;
    display: block;
}

.photo-preview {
    position: relative;
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
}

.photo-preview img {
    width: 100%;
    height: auto;
    border-radius: 8px;
}

.step-indicator {
    font-size: 0.875rem;
    font-weight: 600;
}

.step-completed {
    color: #2fb344;
}

.btn-lg-mobile {
    padding: 1rem 2rem;
    font-size: 1.125rem;
}
</style>
@endpush

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Task Submission</div>
                <h2 class="page-title">{{ $task->item_name }}</h2>
                <div class="text-muted">
                    <i class="fa fa-map-marker-alt"></i> {{ $task->location->name }}
                    @if($task->asset)
                        • Asset: {{ $task->asset->code }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        
        @include('layouts.alerts')

        <form action="{{ route('facility.tasks.submit.post', $task) }}" method="POST" id="taskSubmissionForm">
            @csrf

            <!-- Step Indicators -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="step-indicator" id="step1-indicator">
                                <i class="fa fa-camera fa-2x mb-2"></i>
                                <div>Before Photo</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="step-indicator" id="step2-indicator">
                                <i class="fa fa-broom fa-2x mb-2"></i>
                                <div>Complete Task</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="step-indicator" id="step3-indicator">
                                <i class="fa fa-camera fa-2x mb-2"></i>
                                <div>After Photo</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Before Photo Section -->
            <div class="card mb-3" id="beforePhotoSection">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-camera"></i> Step 1: Take Before Photo
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> Take a photo showing the area BEFORE you start cleaning.
                    </div>

                    <div class="camera-preview" id="beforeCameraPreview" style="display: none;">
                        <video id="beforeVideo" autoplay playsinline></video>
                        <canvas id="beforeCanvas" style="display: none;"></canvas>
                    </div>

                    <div class="photo-preview" id="beforePhotoPreview" style="display: none;">
                        <img id="beforePhotoImg" src="" alt="Before Photo">
                    </div>

                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-primary btn-lg-mobile" id="startBeforeCameraBtn">
                            <i class="fa fa-camera"></i> Start Camera
                        </button>
                        <button type="button" class="btn btn-success btn-lg-mobile" id="captureBeforeBtn" style="display: none;">
                            <i class="fa fa-camera-retro"></i> Capture Photo
                        </button>
                        <button type="button" class="btn btn-secondary" id="retakeBeforeBtn" style="display: none;">
                            <i class="fa fa-redo"></i> Retake
                        </button>
                    </div>

                    <div class="mt-2 text-center" id="gpsStatusBefore" style="display: none;">
                        <small class="text-success">
                            <i class="fa fa-map-marker-alt"></i> <span id="gpsTextBefore">GPS active</span>
                        </small>
                    </div>

                    <input type="hidden" name="before_photo" id="beforePhotoData" required>
                    <input type="hidden" name="before_gps[latitude]" id="beforeGpsLat">
                    <input type="hidden" name="before_gps[longitude]" id="beforeGpsLng">
                </div>
            </div>

            <!-- After Photo Section -->
            <div class="card mb-3" id="afterPhotoSection" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-camera"></i> Step 2: Take After Photo
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fa fa-check-circle"></i> Take a photo showing the area AFTER cleaning.
                    </div>

                    <div class="camera-preview" id="afterCameraPreview" style="display: none;">
                        <video id="afterVideo" autoplay playsinline></video>
                        <canvas id="afterCanvas" style="display: none;"></canvas>
                    </div>

                    <div class="photo-preview" id="afterPhotoPreview" style="display: none;">
                        <img id="afterPhotoImg" src="" alt="After Photo">
                    </div>

                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-primary btn-lg-mobile" id="startAfterCameraBtn">
                            <i class="fa fa-camera"></i> Start Camera
                        </button>
                        <button type="button" class="btn btn-success btn-lg-mobile" id="captureAfterBtn" style="display: none;">
                            <i class="fa fa-camera-retro"></i> Capture Photo
                        </button>
                        <button type="button" class="btn btn-secondary" id="retakeAfterBtn" style="display: none;">
                            <i class="fa fa-redo"></i> Retake
                        </button>
                    </div>

                    <div class="mt-2 text-center" id="gpsStatusAfter" style="display: none;">
                        <small class="text-success">
                            <i class="fa fa-map-marker-alt"></i> <span id="gpsTextAfter">GPS active</span>
                        </small>
                    </div>

                    <input type="hidden" name="after_photo" id="afterPhotoData" required>
                    <input type="hidden" name="after_gps[latitude]" id="afterGpsLat">
                    <input type="hidden" name="after_gps[longitude]" id="afterGpsLng">
                </div>
            </div>

            <!-- Notes Section -->
            <div class="card mb-3" id="notesSection" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-sticky-note"></i> Step 3: Add Notes (Optional)
                    </h3>
                </div>
                <div class="card-body">
                    <textarea name="notes" class="form-control" rows="4" placeholder="Any additional comments or observations..."></textarea>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center mb-3" id="submitSection" style="display: none;">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fa fa-check"></i> Submit Task
                </button>
            </div>

        </form>

    </div>
</div>

@push('scripts')
<script>
let beforeStream = null;
let afterStream = null;
let beforeLocation = null;
let afterLocation = null;

// Request geolocation
async function requestGeolocation() {
    if (!navigator.geolocation) {
        return null;
    }

    return new Promise((resolve) => {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                resolve({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy
                });
            },
            (error) => {
                console.warn('GPS error:', error);
                resolve(null);
            },
            {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            }
        );
    });
}

// Start Before Camera
document.getElementById('startBeforeCameraBtn').addEventListener('click', async function() {
    try {
        // Request GPS first
        beforeLocation = await requestGeolocation();
        if (beforeLocation) {
            document.getElementById('gpsStatusBefore').style.display = 'block';
            document.getElementById('gpsTextBefore').textContent = `GPS: ${beforeLocation.latitude.toFixed(6)}, ${beforeLocation.longitude.toFixed(6)}`;
            document.getElementById('beforeGpsLat').value = beforeLocation.latitude;
            document.getElementById('beforeGpsLng').value = beforeLocation.longitude;
        }

        // Request rear camera
        beforeStream = await navigator.mediaDevices.getUserMedia({
            video: { 
                facingMode: { exact: "environment" },
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            }
        });

        const video = document.getElementById('beforeVideo');
        video.srcObject = beforeStream;

        document.getElementById('beforeCameraPreview').style.display = 'block';
        document.getElementById('startBeforeCameraBtn').style.display = 'none';
        document.getElementById('captureBeforeBtn').style.display = 'inline-block';
    } catch (error) {
        alert('Camera access denied or not available. Please allow camera access.');
        console.error('Camera error:', error);
    }
});

// Capture Before Photo
document.getElementById('captureBeforeBtn').addEventListener('click', function() {
    const video = document.getElementById('beforeVideo');
    const canvas = document.getElementById('beforeCanvas');
    const ctx = canvas.getContext('2d');

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0);

    const photoData = canvas.toDataURL('image/jpeg', 0.9);
    document.getElementById('beforePhotoData').value = photoData;
    document.getElementById('beforePhotoImg').src = photoData;
    document.getElementById('beforePhotoPreview').style.display = 'block';

    // Stop camera
    if (beforeStream) {
        beforeStream.getTracks().forEach(track => track.stop());
    }
    document.getElementById('beforeCameraPreview').style.display = 'none';
    document.getElementById('captureBeforeBtn').style.display = 'none';
    document.getElementById('retakeBeforeBtn').style.display = 'inline-block';

    // Update step indicator
    document.getElementById('step1-indicator').classList.add('step-completed');
    
    // Show after photo section
    document.getElementById('afterPhotoSection').style.display = 'block';
});

// Retake Before Photo
document.getElementById('retakeBeforeBtn').addEventListener('click', function() {
    document.getElementById('beforePhotoPreview').style.display = 'none';
    document.getElementById('beforePhotoData').value = '';
    document.getElementById('retakeBeforeBtn').style.display = 'none';
    document.getElementById('startBeforeCameraBtn').style.display = 'inline-block';
    document.getElementById('step1-indicator').classList.remove('step-completed');
    document.getElementById('afterPhotoSection').style.display = 'none';
    document.getElementById('notesSection').style.display = 'none';
    document.getElementById('submitSection').style.display = 'none';
});

// Start After Camera
document.getElementById('startAfterCameraBtn').addEventListener('click', async function() {
    try {
        // Request GPS
        afterLocation = await requestGeolocation();
        if (afterLocation) {
            document.getElementById('gpsStatusAfter').style.display = 'block';
            document.getElementById('gpsTextAfter').textContent = `GPS: ${afterLocation.latitude.toFixed(6)}, ${afterLocation.longitude.toFixed(6)}`;
            document.getElementById('afterGpsLat').value = afterLocation.latitude;
            document.getElementById('afterGpsLng').value = afterLocation.longitude;
        }

        afterStream = await navigator.mediaDevices.getUserMedia({
            video: { 
                facingMode: { exact: "environment" },
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            }
        });

        const video = document.getElementById('afterVideo');
        video.srcObject = afterStream;

        document.getElementById('afterCameraPreview').style.display = 'block';
        document.getElementById('startAfterCameraBtn').style.display = 'none';
        document.getElementById('captureAfterBtn').style.display = 'inline-block';
    } catch (error) {
        alert('Camera access denied or not available.');
        console.error('Camera error:', error);
    }
});

// Capture After Photo
document.getElementById('captureAfterBtn').addEventListener('click', function() {
    const video = document.getElementById('afterVideo');
    const canvas = document.getElementById('afterCanvas');
    const ctx = canvas.getContext('2d');

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0);

    const photoData = canvas.toDataURL('image/jpeg', 0.9);
    document.getElementById('afterPhotoData').value = photoData;
    document.getElementById('afterPhotoImg').src = photoData;
    document.getElementById('afterPhotoPreview').style.display = 'block';

    // Stop camera
    if (afterStream) {
        afterStream.getTracks().forEach(track => track.stop());
    }
    document.getElementById('afterCameraPreview').style.display = 'none';
    document.getElementById('captureAfterBtn').style.display = 'none';
    document.getElementById('retakeAfterBtn').style.display = 'inline-block';

    // Update step indicator
    document.getElementById('step2-indicator').classList.add('step-completed');
    document.getElementById('step3-indicator').classList.add('step-completed');
    
    // Show notes and submit
    document.getElementById('notesSection').style.display = 'block';
    document.getElementById('submitSection').style.display = 'block';
});

// Retake After Photo
document.getElementById('retakeAfterBtn').addEventListener('click', function() {
    document.getElementById('afterPhotoPreview').style.display = 'none';
    document.getElementById('afterPhotoData').value = '';
    document.getElementById('retakeAfterBtn').style.display = 'none';
    document.getElementById('startAfterCameraBtn').style.display = 'inline-block';
    document.getElementById('step2-indicator').classList.remove('step-completed');
    document.getElementById('step3-indicator').classList.remove('step-completed');
    document.getElementById('notesSection').style.display = 'none';
    document.getElementById('submitSection').style.display = 'none';
});

// Clean up cameras on page unload
window.addEventListener('beforeunload', function() {
    if (beforeStream) {
        beforeStream.getTracks().forEach(track => track.stop());
    }
    if (afterStream) {
        afterStream.getTracks().forEach(track => track.stop());
    }
});
</script>
@endpush

@endsection

