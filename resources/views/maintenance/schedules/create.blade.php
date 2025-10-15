@extends('layouts.app')

@section('title', 'Create Maintenance Schedule')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    Create Maintenance Schedule
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row">
            <div class="col-md-8">
                <form action="{{ route('maintenance.schedules.store') }}" method="POST">
                    @csrf
                    
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Schedule Information</h3>
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
                                        <label class="form-label required">Frequency (Days)</label>
                                        <input type="number" name="frequency_days" class="form-control @error('frequency_days') is-invalid @enderror" 
                                               value="{{ old('frequency_days') }}" min="1" max="365" required>
                                        @error('frequency_days')
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

                            <div class="mb-3">
                                <label class="form-label required">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                          rows="3" required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Checklist Items</label>
                                <div id="checklist-container">
                                    <div class="input-group mb-2">
                                        <input type="text" name="checklist[]" class="form-control" placeholder="Checklist item">
                                        <button type="button" class="btn btn-outline-danger" onclick="removeChecklistItem(this)">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M18 6l-12 12"/>
                                                <path d="M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addChecklistItem()">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M12 5l0 14"/>
                                        <path d="M5 12l14 0"/>
                                    </svg>
                                    Add Checklist Item
                                </button>
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
                                <a href="{{ route('maintenance.schedules.index') }}" class="btn">Cancel</a>
                                <button type="submit" class="btn btn-primary">Create Schedule</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function addChecklistItem() {
    const container = document.getElementById('checklist-container');
    const newItem = document.createElement('div');
    newItem.className = 'input-group mb-2';
    newItem.innerHTML = `
        <input type="text" name="checklist[]" class="form-control" placeholder="Checklist item">
        <button type="button" class="btn btn-outline-danger" onclick="removeChecklistItem(this)">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M18 6l-12 12"/>
                <path d="M6 6l12 12"/>
            </svg>
        </button>
    `;
    container.appendChild(newItem);
}

function removeChecklistItem(button) {
    button.parentElement.remove();
}
</script>
@endpush
