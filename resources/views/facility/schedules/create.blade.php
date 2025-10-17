@extends('layouts.app')

@section('title', 'Create Cleaning Schedule')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Facility Management</div>
                <h2 class="page-title">Create Cleaning Schedule</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('facility.schedules.index') }}" class="btn btn-outline-primary">
                    <i class="fa fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        
        @include('layouts.alerts')

        <form action="{{ route('facility.schedules.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Basic Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label required">Schedule Name</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}" placeholder="e.g., Daily Office Cleaning" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Location</label>
                                <select name="location_id" class="form-select @error('location_id') is-invalid @enderror" required>
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
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                          rows="3" placeholder="Optional description...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Frequency Settings -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Frequency Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label required">Frequency Type</label>
                                <select name="frequency_type" id="frequencyType" class="form-select @error('frequency_type') is-invalid @enderror" required>
                                    <option value="">Select frequency...</option>
                                    <option value="daily" {{ old('frequency_type') === 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ old('frequency_type') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ old('frequency_type') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                                @error('frequency_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Daily Config -->
                            <div id="dailyConfig" class="frequency-config" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">Repeat Every</label>
                                    <div class="input-group">
                                        <input type="number" name="frequency_config[interval]" class="form-control" 
                                               value="{{ old('frequency_config.interval', 1) }}" min="1" max="365">
                                        <span class="input-group-text">day(s)</span>
                                    </div>
                                    <small class="form-hint">Leave as 1 for every day, or set to higher number (e.g., 3 = every 3 days)</small>
                                </div>
                            </div>

                            <!-- Weekly Config -->
                            <div id="weeklyConfig" class="frequency-config" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">Days of Week</label>
                                    <div class="form-selectgroup">
                                        @foreach(['1' => 'Mon', '2' => 'Tue', '3' => 'Wed', '4' => 'Thu', '5' => 'Fri', '6' => 'Sat', '0' => 'Sun'] as $day => $label)
                                            <label class="form-selectgroup-item">
                                                <input type="checkbox" name="frequency_config[days][]" value="{{ $day }}" 
                                                       class="form-selectgroup-input"
                                                       {{ is_array(old('frequency_config.days')) && in_array($day, old('frequency_config.days')) ? 'checked' : '' }}>
                                                <span class="form-selectgroup-label">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <small class="form-hint">Select one or more days</small>
                                </div>
                            </div>

                            <!-- Monthly Config -->
                            <div id="monthlyConfig" class="frequency-config" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">Days of Month</label>
                                    <div class="row g-2">
                                        @for($i = 1; $i <= 31; $i++)
                                            <div class="col-auto">
                                                <label class="form-selectgroup-item">
                                                    <input type="checkbox" name="frequency_config[dates][]" value="{{ $i }}" 
                                                           class="form-selectgroup-input"
                                                           {{ is_array(old('frequency_config.dates')) && in_array($i, old('frequency_config.dates')) ? 'checked' : '' }}>
                                                    <span class="form-selectgroup-label">{{ $i }}</span>
                                                </label>
                                            </div>
                                        @endfor
                                    </div>
                                    <small class="form-hint">Select one or more dates</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Items -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Cleaning Items</h3>
                            <div class="card-actions">
                                <button type="button" class="btn btn-primary btn-sm" onclick="addScheduleItem()">
                                    <i class="fa fa-plus"></i> Add Item
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="scheduleItems">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> Click "Add Item" to add cleaning items to this schedule.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Actions -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <span class="form-check-label">Schedule is Active</span>
                                </label>
                                <small class="form-hint d-block">
                                    Only active schedules will generate tasks automatically.
                                </small>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Create Schedule
                                </button>
                                <a href="{{ route('facility.schedules.index') }}" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Help -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fa fa-info-circle"></i> Help
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-2">
                                <strong>Cleaning Items:</strong> Add individual tasks that need to be done.
                            </p>
                            <p class="text-muted small mb-2">
                                <strong>Asset Link:</strong> Optionally link items to specific assets for tracking.
                            </p>
                            <p class="text-muted small mb-0">
                                <strong>Frequency:</strong> Tasks will be auto-generated daily based on your frequency settings.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

@push('scripts')
<script>
let itemCounter = 0;

// Show/hide frequency config based on type
document.getElementById('frequencyType').addEventListener('change', function() {
    document.querySelectorAll('.frequency-config').forEach(el => el.style.display = 'none');
    
    const selected = this.value;
    if (selected === 'daily') {
        document.getElementById('dailyConfig').style.display = 'block';
    } else if (selected === 'weekly') {
        document.getElementById('weeklyConfig').style.display = 'block';
    } else if (selected === 'monthly') {
        document.getElementById('monthlyConfig').style.display = 'block';
    }
});

// Trigger on page load if there's an old value
document.addEventListener('DOMContentLoaded', function() {
    const frequencyType = document.getElementById('frequencyType');
    if (frequencyType.value) {
        frequencyType.dispatchEvent(new Event('change'));
    }
});

function addScheduleItem() {
    itemCounter++;
    
    const itemHtml = `
        <div class="card mb-2" id="item-${itemCounter}">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Item Name</label>
                        <input type="text" name="items[${itemCounter}][item_name]" 
                               class="form-control" placeholder="e.g., Sweep floor" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Link to Asset (Optional)</label>
                        <select name="items[${itemCounter}][asset_id]" class="form-select">
                            <option value="">General cleaning item</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}">{{ $asset->code }} - {{ $asset->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="items[${itemCounter}][item_description]" 
                                  class="form-control" rows="2" 
                                  placeholder="Optional detailed instructions..."></textarea>
                    </div>
                    <div class="col-md-12">
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="removeScheduleItem(${itemCounter})">
                            <i class="fa fa-trash"></i> Remove Item
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    const container = document.getElementById('scheduleItems');
    const alert = container.querySelector('.alert');
    if (alert) alert.remove();
    
    container.insertAdjacentHTML('beforeend', itemHtml);
}

function removeScheduleItem(id) {
    const item = document.getElementById(`item-${id}`);
    if (item) {
        item.remove();
    }
    
    // Show alert if no items left
    const container = document.getElementById('scheduleItems');
    if (!container.querySelector('.card')) {
        container.innerHTML = `
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> Click "Add Item" to add cleaning items to this schedule.
            </div>
        `;
    }
}
</script>
@endpush
@endsection

