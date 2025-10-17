@extends('layouts.app')

@section('title', 'Edit Work Order')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    Edit Work Order #{{ $workOrder->wo_number }}
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row">
            <div class="col-md-8">
                <form action="{{ route('maintenance.work-orders.update', $workOrder) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Work Order Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Asset</label>
                                        <select name="asset_id" class="form-select @error('asset_id') is-invalid @enderror" required>
                                            <option value="">Select Asset</option>
                                            @foreach($assets as $asset)
                                                <option value="{{ $asset->id }}" {{ (old('asset_id', $workOrder->asset_id) == $asset->id) ? 'selected' : '' }}>
                                                    {{ $asset->name }} ({{ $asset->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('asset_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Maintenance Type</label>
                                        <select name="maintenance_type_id" class="form-select @error('maintenance_type_id') is-invalid @enderror" required>
                                            <option value="">Select Type</option>
                                            @foreach($maintenanceTypes as $type)
                                                <option value="{{ $type->id }}" {{ (old('maintenance_type_id', $workOrder->maintenance_type_id) == $type->id) ? 'selected' : '' }}>
                                                    {{ $type->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('maintenance_type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Priority</label>
                                        <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                            <option value="low" {{ old('priority', $workOrder->priority) === 'low' ? 'selected' : '' }}>Low</option>
                                            <option value="medium" {{ old('priority', $workOrder->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                                            <option value="high" {{ old('priority', $workOrder->priority) === 'high' ? 'selected' : '' }}>High</option>
                                            <option value="urgent" {{ old('priority', $workOrder->priority) === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                        </select>
                                        @error('priority')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <input type="text" class="form-control" value="{{ ucfirst(str_replace('-', ' ', $workOrder->status)) }}" disabled>
                                        <div class="form-text">Status cannot be changed here</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Assigned To</label>
                                        <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                                            <option value="">Not Assigned</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ (old('assigned_to', $workOrder->assigned_to) == $user->id) ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('assigned_to')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Scheduled Date</label>
                                        <input type="date" name="scheduled_date" class="form-control @error('scheduled_date') is-invalid @enderror" value="{{ old('scheduled_date', $workOrder->scheduled_date ? $workOrder->scheduled_date->format('Y-m-d') : '') }}">
                                        @error('scheduled_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Estimated Hours</label>
                                        <input type="number" name="estimated_hours" class="form-control @error('estimated_hours') is-invalid @enderror" step="0.5" min="0" value="{{ old('estimated_hours', $workOrder->estimated_hours) }}" placeholder="0.0">
                                        @error('estimated_hours')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4" required>{{ old('description', $workOrder->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $workOrder->notes) }}</textarea>
                                <div class="form-text">Additional information or instructions</div>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col">
                                    <a href="{{ route('maintenance.work-orders.show', $workOrder) }}" class="btn btn-secondary">Cancel</a>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary">Update Work Order</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Work Order Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <div class="text-muted small">WO Number</div>
                            <strong>{{ $workOrder->wo_number }}</strong>
                        </div>
                        <div class="mb-2">
                            <div class="text-muted small">Requested By</div>
                            <strong>{{ $workOrder->requestedBy->name ?? 'N/A' }}</strong>
                        </div>
                        <div class="mb-2">
                            <div class="text-muted small">Created</div>
                            <strong>{{ $workOrder->created_at->format('d M Y H:i') }}</strong>
                        </div>
                        @if($workOrder->assigned_at)
                        <div class="mb-2">
                            <div class="text-muted small">Assigned By</div>
                            <strong>{{ $workOrder->assignedBy->name ?? 'N/A' }}</strong>
                        </div>
                        <div class="mb-2">
                            <div class="text-muted small">Assigned At</div>
                            <strong>{{ $workOrder->assigned_at->format('d M Y H:i') }}</strong>
                        </div>
                        @endif
                    </div>
                </div>

                @if($workOrder->photos->count() > 0)
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Initial Photos</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($workOrder->photos as $photo)
                                <div class="col-6">
                                    <a href="{{ Storage::url($photo->photo_path) }}" data-lightbox="work-order-{{ $workOrder->id }}" data-title="{{ $photo->caption }}">
                                        <img src="{{ Storage::url($photo->photo_path) }}" class="img-fluid rounded" alt="{{ $photo->caption }}">
                                    </a>
                                    @if($photo->caption)
                                        <div class="small text-muted mt-1">{{ $photo->caption }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/js/lightbox/lightbox.min.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/lightbox/lightbox.min.js') }}"></script>
@endpush

