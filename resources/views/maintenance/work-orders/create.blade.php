@extends('layouts.app')

@section('title', 'Create Work Order')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    Create Work Order
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row">
            <div class="col-md-8">
                <form action="{{ route('maintenance.work-orders.store') }}" method="POST">
                    @csrf
                    
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
                                                <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
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
                                                <option value="{{ $type->id }}" {{ old('maintenance_type_id') == $type->id ? 'selected' : '' }}>
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
                                            <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                            <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                                            <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                            <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                        </select>
                                        @error('priority')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Assigned To</label>
                                        <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                                            <option value="">Select User</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('assigned_to')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Scheduled Date</label>
                                        <input type="datetime-local" name="scheduled_date" class="form-control @error('scheduled_date') is-invalid @enderror" value="{{ old('scheduled_date') }}">
                                        @error('scheduled_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Estimated Hours</label>
                                        <input type="number" name="estimated_hours" class="form-control @error('estimated_hours') is-invalid @enderror" value="{{ old('estimated_hours') }}" step="0.5" min="0">
                                        @error('estimated_hours')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4" required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col">
                                    <a href="{{ route('maintenance.work-orders.index') }}" class="btn btn-secondary">Cancel</a>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary">Create Work Order</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

