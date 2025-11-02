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
                    <i class="fa fa-arrow-left"></i>&nbsp; Back to List
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
                                    <option value="hourly" {{ old('frequency_type') === 'hourly' ? 'selected' : '' }}>Hourly</option>
                                    <option value="daily" {{ old('frequency_type') === 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ old('frequency_type') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ old('frequency_type') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="yearly" {{ old('frequency_type') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                                </select>
                                @error('frequency_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Hourly Config -->
                            <div id="hourlyConfig" class="frequency-config" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">Repeat Every</label>
                                    <div class="input-group">
                                        <input type="number" name="frequency_config[interval]" class="form-control" 
                                               value="{{ old('frequency_config.interval', 1) }}" min="1" max="24">
                                        <span class="input-group-text">hour(s)</span>
                                    </div>
                                    <small class="form-hint">e.g., 1 = every hour, 2 = every 2 hours</small>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">Start Time</label>
                                            <input type="time" name="start_time" class="form-control" 
                                                   value="{{ old('start_time', '08:00') }}" required>
                                            <small class="form-hint">When to start generating tasks</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">End Time</label>
                                            <input type="time" name="end_time" class="form-control" 
                                                   value="{{ old('end_time', '18:00') }}" required>
                                            <small class="form-hint">Last task generation time</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i>&nbsp; 
                                    Example: Every 2 hours from 8:00 AM to 6:00 PM will generate tasks at: 8am, 10am, 12pm, 2pm, 4pm, 6pm
                                </div>
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
                                <div class="mb-3">
                                    <label class="form-label">Scheduled Time (Optional)</label>
                                    <input type="time" name="scheduled_time" class="form-control scheduled-time-input" 
                                           value="{{ old('scheduled_time') }}">
                                    <small class="form-hint">Specific time for daily task (e.g., 8:00 AM). Leave empty for any time.</small>
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
                                <div class="mb-3">
                                    <label class="form-label">Scheduled Time (Optional)</label>
                                    <input type="time" name="scheduled_time" class="form-control scheduled-time-input" 
                                           value="{{ old('scheduled_time') }}">
                                    <small class="form-hint">Specific time for weekly tasks (e.g., 3:00 PM). Leave empty for any time.</small>
                                </div>
                            </div>

                            <!-- Monthly Config -->
                            <div id="monthlyConfig" class="frequency-config" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">Days of Month</label>
                                    
                                    <!-- Dates 1-28 (Safe for all months) -->
                                    <div class="mb-2">
                                        <small class="text-muted d-block mb-1"><strong>Safe for all months:</strong></small>
                                        <div class="row g-2">
                                            @for($i = 1; $i <= 28; $i++)
                                                <div class="col-auto">
                                                    <label class="form-selectgroup-item">
                                                        <input type="checkbox" name="frequency_config[dates][]" value="{{ $i }}" 
                                                               class="form-selectgroup-input monthly-date-checkbox"
                                                               {{ is_array(old('frequency_config.dates')) && in_array($i, old('frequency_config.dates')) ? 'checked' : '' }}>
                                                        <span class="form-selectgroup-label">{{ $i }}</span>
                                                    </label>
                                                </div>
                                            @endfor
                                        </div>
                                    </div>
                                    
                                    <!-- Dates 29-31 (Not available in all months) -->
                                    <div class="mb-2">
                                        <small class="text-muted d-block mb-1">
                                            <strong>Not available in all months:</strong>
                                            <span class="text-warning">(will be skipped in months without these dates)</span>
                                        </small>
                                        <div class="row g-2">
                                            <div class="col-auto">
                                                <label class="form-selectgroup-item">
                                                    <input type="checkbox" name="frequency_config[dates][]" value="29" 
                                                           class="form-selectgroup-input monthly-date-checkbox"
                                                           {{ is_array(old('frequency_config.dates')) && in_array('29', old('frequency_config.dates')) ? 'checked' : '' }}
                                                           title="Skipped in Feb (non-leap years)">
                                                    <span class="form-selectgroup-label">29</span>
                                                </label>
                                                <small class="text-muted d-block text-center" style="font-size: 0.7rem;">Feb*</small>
                                            </div>
                                            <div class="col-auto">
                                                <label class="form-selectgroup-item">
                                                    <input type="checkbox" name="frequency_config[dates][]" value="30" 
                                                           class="form-selectgroup-input monthly-date-checkbox"
                                                           {{ is_array(old('frequency_config.dates')) && in_array('30', old('frequency_config.dates')) ? 'checked' : '' }}
                                                           title="Skipped in Feb">
                                                    <span class="form-selectgroup-label">30</span>
                                                </label>
                                                <small class="text-muted d-block text-center" style="font-size: 0.7rem;">Feb</small>
                                            </div>
                                            <div class="col-auto">
                                                <label class="form-selectgroup-item">
                                                    <input type="checkbox" name="frequency_config[dates][]" value="31" 
                                                           class="form-selectgroup-input monthly-date-checkbox"
                                                           {{ is_array(old('frequency_config.dates')) && in_array('31', old('frequency_config.dates')) ? 'checked' : '' }}
                                                           title="Skipped in Feb, Apr, Jun, Sep, Nov">
                                                    <span class="form-selectgroup-label">31</span>
                                                </label>
                                                <small class="text-muted d-block text-center" style="font-size: 0.7rem;">5 months</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="monthlyWarning" class="alert alert-warning" style="display: none;">
                                        <div class="d-flex">
                                            <div><i class="fa fa-exclamation-triangle"></i>&nbsp;</div>
                                            <div class="ms-2">
                                                <strong>Important:</strong>
                                                <ul class="mb-0 mt-1" id="monthlyWarningList"></ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <small class="form-hint">
                                        Select one or more dates. Tasks will be generated only for months that have the selected dates.
                                    </small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Scheduled Time (Optional)</label>
                                    <input type="time" name="scheduled_time" class="form-control scheduled-time-input" 
                                           value="{{ old('scheduled_time') }}">
                                    <small class="form-hint">Specific time for monthly tasks (e.g., 9:00 AM). Leave empty for any time.</small>
                                </div>
                            </div>

                            <!-- Yearly Config -->
                            <div id="yearlyConfig" class="frequency-config" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">Month</label>
                                            <select name="frequency_config[month]" class="form-select">
                                                @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $index => $month)
                                                    <option value="{{ $index + 1 }}" {{ old('frequency_config.month') == ($index + 1) ? 'selected' : '' }}>
                                                        {{ $month }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">Date</label>
                                            <select name="frequency_config[date]" class="form-select">
                                                @for($i = 1; $i <= 31; $i++)
                                                    <option value="{{ $i }}" {{ old('frequency_config.date') == $i ? 'selected' : '' }}>
                                                        {{ $i }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Scheduled Time (Optional)</label>
                                    <input type="time" name="scheduled_time" class="form-control scheduled-time-input" 
                                           value="{{ old('scheduled_time') }}">
                                    <small class="form-hint">Specific time for yearly task (e.g., 10:00 AM). Leave empty for any time.</small>
                                </div>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i>&nbsp; 
                                    Task will be generated once per year on the selected date.
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
                                    <i class="fa fa-plus"></i>&nbsp; Add Item
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="scheduleItems">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i>&nbsp; Click "Add Item" to add cleaning items to this schedule.
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
                                    <i class="fa fa-save"></i>&nbsp; Create Schedule
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
                                <i class="fa fa-info-circle"></i>&nbsp; Help
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-2">
                                <strong>Cleaning Items:</strong> Add individual tasks that need to be done.
                            </p>
                            <p class="text-muted small mb-2">
                                <strong>Asset Link:</strong> Optionally link items to specific assets for tracking.
                            </p>
                            <p class="text-muted small mb-2">
                                <strong>Frequency:</strong> Tasks will be auto-generated daily at midnight based on your settings.
                            </p>
                            <p class="text-muted small mb-0">
                                <strong>Time-based:</strong> Specify exact times for tasks (e.g., "Daily at 8am" or "Every 2 hours from 8am-6pm").
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
    
    // Clear all scheduled_time inputs that are not in the current frequency type
    document.querySelectorAll('.scheduled-time-input').forEach(input => {
        if (!input.closest('.frequency-config') || input.closest('.frequency-config').style.display === 'none') {
            input.value = '';
        }
    });
    
    const selected = this.value;
    if (selected === 'hourly') {
        document.getElementById('hourlyConfig').style.display = 'block';
    } else if (selected === 'daily') {
        document.getElementById('dailyConfig').style.display = 'block';
    } else if (selected === 'weekly') {
        document.getElementById('weeklyConfig').style.display = 'block';
    } else if (selected === 'monthly') {
        document.getElementById('monthlyConfig').style.display = 'block';
    } else if (selected === 'yearly') {
        document.getElementById('yearlyConfig').style.display = 'block';
    }
});

// Trigger on page load if there's an old value
document.addEventListener('DOMContentLoaded', function() {
    const frequencyType = document.getElementById('frequencyType');
    if (frequencyType.value) {
        frequencyType.dispatchEvent(new Event('change'));
    }
    
    // Set up monthly date warnings
    setupMonthlyWarnings();
});

// Handle monthly date selection warnings
function setupMonthlyWarnings() {
    const checkboxes = document.querySelectorAll('.monthly-date-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateMonthlyWarning);
    });
}

function updateMonthlyWarning() {
    const checkboxes = document.querySelectorAll('.monthly-date-checkbox:checked');
    const selectedDates = Array.from(checkboxes).map(cb => parseInt(cb.value));
    
    const warnings = [];
    
    if (selectedDates.includes(29)) {
        warnings.push('Date <strong>29</strong> will be skipped in February (non-leap years)');
    }
    if (selectedDates.includes(30)) {
        warnings.push('Date <strong>30</strong> will be skipped in February');
    }
    if (selectedDates.includes(31)) {
        warnings.push('Date <strong>31</strong> will be skipped in February, April, June, September, and November');
    }
    
    const warningDiv = document.getElementById('monthlyWarning');
    const warningList = document.getElementById('monthlyWarningList');
    
    if (warnings.length > 0) {
        warningList.innerHTML = warnings.map(w => `<li>${w}</li>`).join('');
        warningDiv.style.display = 'block';
    } else {
        warningDiv.style.display = 'none';
    }
}

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
                            <i class="fa fa-trash"></i>&nbsp; Remove Item
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
                <i class="fa fa-info-circle"></i>&nbsp; Click "Add Item" to add cleaning items to this schedule.
            </div>
        `;
    }
}
</script>
@endpush
@endsection

