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
                                        <select name="asset_id" id="asset-select" class="form-select @error('asset_id') is-invalid @enderror" required>
                                            <option value="">Select Asset</option>
                                            @foreach($assets as $asset)
                                                <option value="{{ $asset->id }}" 
                                                        data-category="{{ $asset->assetCategory->name ?? '' }}"
                                                        {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
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
                                        <label class="form-label required">Frequency Type</label>
                                        <select name="frequency_type" id="frequency_type" class="form-select @error('frequency_type') is-invalid @enderror" required onchange="updateFrequencyFields()">
                                            <option value="">Select Type</option>
                                            <option value="hourly" {{ old('frequency_type') == 'hourly' ? 'selected' : '' }}>Hourly</option>
                                            <option value="daily" {{ old('frequency_type', 'daily') == 'daily' ? 'selected' : '' }}>Daily</option>
                                            <option value="weekly" {{ old('frequency_type') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                            <option value="monthly" {{ old('frequency_type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                            <option value="yearly" {{ old('frequency_type') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                        </select>
                                        @error('frequency_type')
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

                            <!-- Dynamic Frequency Configuration -->
                            <div id="frequency-config-container">
                                <!-- Hourly -->
                                <div id="hourly-config" class="frequency-config" style="display: none;">
                                    <div class="mb-3">
                                        <label class="form-label">Every X hours</label>
                                        <input type="number" name="frequency_config[interval]" class="form-control" value="1" min="1" max="24">
                                        <small class="form-hint">Check every 1-24 hours</small>
                                    </div>
                                </div>

                                <!-- Daily -->
                                <div id="daily-config" class="frequency-config" style="display: none;">
                                    <div class="mb-3">
                                        <label class="form-label">Every X days</label>
                                        <input type="number" name="frequency_config[interval]" class="form-control" value="1" min="1" max="365">
                                        <small class="form-hint">Repeat every 1-365 days</small>
                                    </div>
                                </div>

                                <!-- Weekly -->
                                <div id="weekly-config" class="frequency-config" style="display: none;">
                                    <div class="mb-3">
                                        <label class="form-label">Every X weeks</label>
                                        <input type="number" name="frequency_config[interval]" class="form-control" value="1" min="1" max="52">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">On days</label>
                                        <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column gap-2">
                                            <label class="form-selectgroup-item flex-fill">
                                                <input type="checkbox" name="frequency_config[days][]" value="1" class="form-selectgroup-input">
                                                <div class="form-selectgroup-label d-flex align-items-center p-3">
                                                    <div class="me-3"><span class="form-selectgroup-check"></span></div>
                                                    <div>Monday</div>
                                                </div>
                                            </label>
                                            <label class="form-selectgroup-item flex-fill">
                                                <input type="checkbox" name="frequency_config[days][]" value="2" class="form-selectgroup-input">
                                                <div class="form-selectgroup-label d-flex align-items-center p-3">
                                                    <div class="me-3"><span class="form-selectgroup-check"></span></div>
                                                    <div>Tuesday</div>
                                                </div>
                                            </label>
                                            <label class="form-selectgroup-item flex-fill">
                                                <input type="checkbox" name="frequency_config[days][]" value="3" class="form-selectgroup-input">
                                                <div class="form-selectgroup-label d-flex align-items-center p-3">
                                                    <div class="me-3"><span class="form-selectgroup-check"></span></div>
                                                    <div>Wednesday</div>
                                                </div>
                                            </label>
                                            <label class="form-selectgroup-item flex-fill">
                                                <input type="checkbox" name="frequency_config[days][]" value="4" class="form-selectgroup-input">
                                                <div class="form-selectgroup-label d-flex align-items-center p-3">
                                                    <div class="me-3"><span class="form-selectgroup-check"></span></div>
                                                    <div>Thursday</div>
                                                </div>
                                            </label>
                                            <label class="form-selectgroup-item flex-fill">
                                                <input type="checkbox" name="frequency_config[days][]" value="5" class="form-selectgroup-input">
                                                <div class="form-selectgroup-label d-flex align-items-center p-3">
                                                    <div class="me-3"><span class="form-selectgroup-check"></span></div>
                                                    <div>Friday</div>
                                                </div>
                                            </label>
                                            <label class="form-selectgroup-item flex-fill">
                                                <input type="checkbox" name="frequency_config[days][]" value="6" class="form-selectgroup-input">
                                                <div class="form-selectgroup-label d-flex align-items-center p-3">
                                                    <div class="me-3"><span class="form-selectgroup-check"></span></div>
                                                    <div>Saturday</div>
                                                </div>
                                            </label>
                                            <label class="form-selectgroup-item flex-fill">
                                                <input type="checkbox" name="frequency_config[days][]" value="7" class="form-selectgroup-input">
                                                <div class="form-selectgroup-label d-flex align-items-center p-3">
                                                    <div class="me-3"><span class="form-selectgroup-check"></span></div>
                                                    <div>Sunday</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Monthly -->
                                <div id="monthly-config" class="frequency-config" style="display: none;">
                                    <div class="mb-3">
                                        <label class="form-label">Every X months</label>
                                        <input type="number" name="frequency_config[interval]" class="form-control" value="1" min="1" max="12">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Repeat by</label>
                                        <select name="frequency_config[type]" class="form-select" onchange="updateMonthlyType(this.value)">
                                            <option value="date">Date of month</option>
                                            <option value="weekday">Day of week</option>
                                            <option value="last_day">Last day of month</option>
                                        </select>
                                    </div>
                                    <div id="monthly-date-config">
                                        <div class="mb-3">
                                            <label class="form-label">On day</label>
                                            <input type="number" name="frequency_config[date]" class="form-control" value="1" min="1" max="31">
                                            <small class="form-hint">1-31 (will adjust for shorter months)</small>
                                        </div>
                                    </div>
                                    <div id="monthly-weekday-config" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Week</label>
                                                    <select name="frequency_config[week]" class="form-select">
                                                        <option value="1">First</option>
                                                        <option value="2">Second</option>
                                                        <option value="3">Third</option>
                                                        <option value="4">Fourth</option>
                                                        <option value="5">Last</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Day</label>
                                                    <select name="frequency_config[day]" class="form-select">
                                                        <option value="1">Monday</option>
                                                        <option value="2">Tuesday</option>
                                                        <option value="3">Wednesday</option>
                                                        <option value="4">Thursday</option>
                                                        <option value="5">Friday</option>
                                                        <option value="6">Saturday</option>
                                                        <option value="7">Sunday</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Yearly -->
                                <div id="yearly-config" class="frequency-config" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Month</label>
                                                <select name="frequency_config[month]" class="form-select">
                                                    <option value="1">January</option>
                                                    <option value="2">February</option>
                                                    <option value="3">March</option>
                                                    <option value="4">April</option>
                                                    <option value="5">May</option>
                                                    <option value="6">June</option>
                                                    <option value="7">July</option>
                                                    <option value="8">August</option>
                                                    <option value="9">September</option>
                                                    <option value="10">October</option>
                                                    <option value="11">November</option>
                                                    <option value="12">December</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Day</label>
                                                <input type="number" name="frequency_config[date]" class="form-control" value="1" min="1" max="31">
                                            </div>
                                        </div>
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
                                <label class="form-label required">Checklist Items</label>
                                <div id="checklist-container">
                                    <div class="input-group mb-2">
                                        <input type="text" name="checklist[]" class="form-control" placeholder="Checklist item">
                                        <button type="button" class="btn btn-outline-danger" onclick="removeChecklistItem(this)">
                                            <i class="far fa-xmark"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addChecklistItem()">
                                    <i class="far fa-plus"></i>
                                    Add Checklist Item
                                </button>
                                @error('checklist')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
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
@endsection

@push('styles')
<link href="/assets/js/tom-select/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="/assets/js/tom-select/tom-select.base.min.js"></script>
<script>
// Initialize TomSelect for asset dropdown
document.addEventListener('DOMContentLoaded', function() {
    new TomSelect('#asset-select', {
        maxOptions: null,
        placeholder: 'Select Asset',
        render: {
            option: function(data, escape) {
                return '<div>' +
                    '<strong>' + escape(data.text) + '</strong>' +
                    (data.category ? '<div class="text-muted small">' + escape(data.category) + '</div>' : '') +
                    '</div>';
            }
        }
    });
});

function addChecklistItem() {
    const container = document.getElementById('checklist-container');
    const newItem = document.createElement('div');
    newItem.className = 'input-group mb-2';
    newItem.innerHTML = `
        <input type="text" name="checklist[]" class="form-control" placeholder="Checklist item">
        <button type="button" class="btn btn-outline-danger" onclick="removeChecklistItem(this)">
            <i class="far fa-xmark"></i>
        </button>
    `;
    container.appendChild(newItem);
}

function removeChecklistItem(button) {
    button.parentElement.remove();
}

function updateFrequencyFields() {
    const type = document.getElementById('frequency_type').value;
    const configs = document.querySelectorAll('.frequency-config');
    
    // Hide all configs
    configs.forEach(config => {
        config.style.display = 'none';
        // Disable inputs in hidden configs
        config.querySelectorAll('input, select').forEach(input => {
            input.disabled = true;
        });
    });
    
    // Show and enable selected config
    if (type) {
        const selectedConfig = document.getElementById(type + '-config');
        if (selectedConfig) {
            selectedConfig.style.display = 'block';
            selectedConfig.querySelectorAll('input, select').forEach(input => {
                input.disabled = false;
            });
        }
    }
}

function updateMonthlyType(type) {
    const dateConfig = document.getElementById('monthly-date-config');
    const weekdayConfig = document.getElementById('monthly-weekday-config');
    
    if (type === 'date') {
        dateConfig.style.display = 'block';
        weekdayConfig.style.display = 'none';
        dateConfig.querySelectorAll('input').forEach(input => input.disabled = false);
        weekdayConfig.querySelectorAll('input, select').forEach(input => input.disabled = true);
    } else if (type === 'weekday') {
        dateConfig.style.display = 'none';
        weekdayConfig.style.display = 'block';
        dateConfig.querySelectorAll('input').forEach(input => input.disabled = true);
        weekdayConfig.querySelectorAll('input, select').forEach(input => input.disabled = false);
    } else {
        dateConfig.style.display = 'none';
        weekdayConfig.style.display = 'none';
        dateConfig.querySelectorAll('input').forEach(input => input.disabled = true);
        weekdayConfig.querySelectorAll('input, select').forEach(input => input.disabled = true);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateFrequencyFields();
});
</script>
@endpush
