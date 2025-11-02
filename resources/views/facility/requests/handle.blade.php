@extends('layouts.app')

@section('title', 'Handle Request - ' . $cleaningRequest->request_number)

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Facility Management</div>
                <h2 class="page-title">
                    <i class="fa fa-check-circle"></i>&nbsp; Handle Request
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('facility.requests.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left"></i>&nbsp; Back to Requests
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        
        @include('layouts.alerts')

        <div class="row">
            <!-- Request Details -->
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Request Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted">Request Number</label>
                            <div><strong>{{ $cleaningRequest->request_number }}</strong></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Request Type</label>
                            <div>
                                @if($cleaningRequest->request_type === 'cleaning')
                                    <span class="badge bg-blue"><i class="fa fa-broom"></i>&nbsp; Cleaning</span>
                                @else
                                    <span class="badge bg-orange"><i class="fa fa-wrench"></i>&nbsp; Repair</span>
                                @endif
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Requester</label>
                            <div><strong>{{ $cleaningRequest->requester_name }}</strong></div>
                            <small class="text-muted">
                                <i class="fa fa-phone"></i>&nbsp; {{ $cleaningRequest->requester_phone }}
                            </small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Location</label>
                            <div>
                                <i class="fa fa-map-marker-alt text-muted"></i>&nbsp;
                                {{ $cleaningRequest->location->name }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Description</label>
                            <div class="alert alert-info mb-0">
                                {{ $cleaningRequest->description }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Submitted</label>
                            <div>{{ $cleaningRequest->created_at->format('l, F d, Y H:i') }}</div>
                        </div>
                        @if($cleaningRequest->photo)
                        <div class="mb-0">
                            <label class="form-label text-muted">Photo</label>
                            <a href="{{ Storage::disk('sigap')->url($cleaningRequest->photo) }}" data-lightbox="request-photo">
                                <img src="{{ Storage::disk('sigap')->url($cleaningRequest->photo) }}" 
                                     class="img-fluid rounded" 
                                     alt="Request Photo"
                                     style="max-height: 300px;">
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Handling Form -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Handle Request</h3>
                    </div>
                    <form action="{{ route('facility.requests.handle', $cleaningRequest) }}" method="POST">
                        @csrf
                        <div class="card-body">
                            
                            @if($cleaningRequest->request_type === 'cleaning')
                                <!-- Cleaning Request Handling -->
                                <div class="alert alert-blue">
                                    <i class="fa fa-info-circle"></i>&nbsp;
                                    This will create a new cleaning task assigned to the selected cleaner.
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Item Name</label>
                                    <input type="text" 
                                           name="item_name" 
                                           class="form-control @error('item_name') is-invalid @enderror" 
                                           value="{{ old('item_name', 'Cleaning - ' . $cleaningRequest->location->name) }}" 
                                           required>
                                    @error('item_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-hint">Name for the cleaning task</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Scheduled Date</label>
                                    <input type="date" 
                                           name="scheduled_date" 
                                           class="form-control @error('scheduled_date') is-invalid @enderror" 
                                           value="{{ old('scheduled_date', today()->toDateString()) }}" 
                                           min="{{ today()->toDateString() }}"
                                           required>
                                    @error('scheduled_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Assign To</label>
                                    <select name="assigned_to" 
                                            class="form-select @error('assigned_to') is-invalid @enderror" 
                                            required>
                                        <option value="">Select Cleaner...</option>
                                        @foreach($cleaners as $cleaner)
                                            <option value="{{ $cleaner->id }}" {{ old('assigned_to') == $cleaner->id ? 'selected' : '' }}>
                                                {{ $cleaner->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('assigned_to')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            @else
                                <!-- Repair Request Handling -->
                                <div class="alert alert-orange">
                                    <i class="fa fa-info-circle"></i>&nbsp;
                                    This will create a new work order in the Maintenance module.
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Priority</label>
                                    <select name="priority" 
                                            class="form-select @error('priority') is-invalid @enderror" 
                                            required>
                                        <option value="">Select Priority...</option>
                                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                        <option value="critical" {{ old('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Work Order Description</label>
                                    <textarea name="description" 
                                              class="form-control @error('description') is-invalid @enderror" 
                                              rows="4">{{ old('description', $cleaningRequest->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-hint">You can edit the description if needed</small>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label">Handling Notes</label>
                                <textarea name="handling_notes" 
                                          class="form-control @error('handling_notes') is-invalid @enderror" 
                                          rows="3">{{ old('handling_notes') }}</textarea>
                                @error('handling_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">Optional notes about handling this request</small>
                            </div>

                        </div>
                        <div class="card-footer text-end">
                            <div class="d-flex">
                                <a href="{{ route('facility.requests.index') }}" class="btn btn-link">Cancel</a>
                                <button type="submit" class="btn btn-primary ms-auto">
                                    <i class="fa fa-check"></i>&nbsp;
                                    @if($cleaningRequest->request_type === 'cleaning')
                                        Create Cleaning Task
                                    @else
                                        Create Work Order
                                    @endif
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/lightbox.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('assets/css/lightbox.min.css') }}">
@endpush
@endsection

