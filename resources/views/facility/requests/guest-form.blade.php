@extends('layouts.guest')

@section('title', 'Submit Cleaning/Repair Request')

@push('css')
<link href="{{ asset('assets/tabler/libs/tom-select/dist/css/tom-select.bootstrap5.css') }}" rel="stylesheet"/>
<style>
    .ts-dropdown {
        background: #fff !important;
        border: 1px solid #d1d5db !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
        z-index: 1000 !important;
    }
    
    .ts-dropdown .option {
        background: #fff !important;
    }
    
    .ts-dropdown .option:hover,
    .ts-dropdown .option.active {
        background: #f1f5f9 !important;
        color: #1e293b !important;
    }
</style>
@endpush

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center justify-content-center">
            <div class="col-lg-8">
                <div class="text-center">
                    <h2 class="page-title">
                        <i class="fa fa-clipboard-list"></i> Submit Request
                    </h2>
                    <p class="text-muted">
                        Report a cleaning issue or request a repair
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                @include('layouts.alerts')

                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('facility.requests.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label required">Your Name</label>
                                <input type="text" name="requester_name" class="form-control @error('requester_name') is-invalid @enderror" 
                                       value="{{ old('requester_name') }}" required>
                                @error('requester_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Mobile Phone Number</label>
                                <input type="tel" name="requester_phone" class="form-control @error('requester_phone') is-invalid @enderror" 
                                       value="{{ old('requester_phone') }}" placeholder="+62..." required>
                                @error('requester_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Location</label>
                                <select id="location-select" name="location_id" class="form-select @error('location_id') is-invalid @enderror" required>
                                    <option value="">Select location...</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('location_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Request Type</label>
                                <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column">
                                    <label class="form-selectgroup-item flex-fill">
                                        <input type="radio" name="request_type" value="cleaning" 
                                               class="form-selectgroup-input" 
                                               {{ old('request_type') === 'cleaning' ? 'checked' : '' }} required>
                                        <div class="form-selectgroup-label d-flex align-items-center p-3">
                                            <div class="me-3">
                                                <span class="form-selectgroup-check"></span>
                                            </div>
                                            <div>
                                                <div class="font-weight-medium">
                                                    <i class="fa fa-broom text-primary"></i> Cleaning Request
                                                </div>
                                                <div class="text-muted">Area needs cleaning or maintenance</div>
                                            </div>
                                        </div>
                                    </label>
                                    <label class="form-selectgroup-item flex-fill">
                                        <input type="radio" name="request_type" value="repair" 
                                               class="form-selectgroup-input"
                                               {{ old('request_type') === 'repair' ? 'checked' : '' }} required>
                                        <div class="form-selectgroup-label d-flex align-items-center p-3">
                                            <div class="me-3">
                                                <span class="form-selectgroup-check"></span>
                                            </div>
                                            <div>
                                                <div class="font-weight-medium">
                                                    <i class="fa fa-tools text-warning"></i> Repair Request
                                                </div>
                                                <div class="text-muted">Equipment or facility needs repair</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                @error('request_type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                          rows="5" placeholder="Please describe the issue in detail..." required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">Be as specific as possible to help us address your request quickly.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Photo (Optional)</label>
                                <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" 
                                       accept="image/*" capture="environment">
                                @error('photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">Upload a photo to help us understand the issue better.</small>
                            </div>

                            <div class="alert alert-info">
                                <div class="d-flex">
                                    <div>
                                        <i class="fa fa-info-circle fa-2x"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h4 class="alert-title">What happens next?</h4>
                                        <div class="text-muted">
                                            Our General Affairs staff will review your request and take appropriate action. 
                                            You will receive a request number that you can use to track your submission.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-lg flex-fill">
                                    <i class="fa fa-paper-plane"></i> Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/tabler/libs/tom-select/dist/js/tom-select.base.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize TomSelect for location dropdown
        new TomSelect('#location-select', {
            placeholder: 'Select location...',
            allowEmptyOption: true,
            create: false
        });
    });
</script>
@endpush

