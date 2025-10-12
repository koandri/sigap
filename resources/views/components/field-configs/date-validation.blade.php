@props(['field' => null, 'hasSubmissions' => false])

<!-- Date/DateTime Validation Rules -->
@if($field && in_array($field->field_type, ['date', 'datetime']) || !$hasSubmissions)
<div id="dateValidationSection" style="{{ $field && in_array($field->field_type, ['date', 'datetime']) ? '' : 'display: none;' }}">
    <div class="hr-text hr-text-start">Date Restrictions</div>

    @if($hasSubmissions && $field && in_array($field->field_type, ['date', 'datetime']))
        <div class="alert alert-warning alert-dismissible" role="alert">
            <div class="alert-icon">
                <i class="fa-regular fa-triangle-exclamation"></i>
            </div>
            <div>
                <h4 class="alert-heading">Info!</h4>
                <div class="alert-description">
                    Some date restrictions cannot be modified because this form has existing submissions.
                </div>
            </div>
        </div>
    @endif
    
    @php
        $rules = $field?->validation_rules ?? [];
        $dateMinType = $rules['date_min']['type'] ?? '';
        $dateMaxType = $rules['date_max']['type'] ?? '';
        $allowedDays = $rules['allowed_days'] ?? ['0','1','2','3','4','5','6'];
        $disabledDates = isset($rules['disabled_dates']) ? implode("\n", $rules['disabled_dates']) : '';
    @endphp
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Minimum Date</label>
                <select name="date_min_type" id="date_min_type" class="form-control mb-2" {{ $hasSubmissions ? 'disabled' : '' }}>
                    <option value="" {{ $dateMinType == '' ? 'selected' : '' }}>No Minimum</option>
                    <option value="fixed" {{ $dateMinType == 'fixed' ? 'selected' : '' }}>Fixed Date</option>
                    <option value="today" {{ $dateMinType == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="today_minus" {{ $dateMinType == 'today_minus' ? 'selected' : '' }}>Today Minus Days</option>
                    <option value="today_plus" {{ $dateMinType == 'today_plus' ? 'selected' : '' }}>Today Plus Days</option>
                </select>
                
                <input type="date" name="date_min_fixed" id="date_min_fixed" class="form-control" value="{{ ($dateMinType == 'fixed') ? ($rules['date_min']['value'] ?? '') : '' }}" style="{{ $dateMinType == 'fixed' ? '' : 'display: none;' }}" {{ $hasSubmissions ? 'readonly' : '' }}>
                
                <input type="number" name="date_min_days" id="date_min_days" class="form-control" value="{{ in_array($dateMinType, ['today_minus', 'today_plus']) ? ($rules['date_min']['days'] ?? 0) : '' }}" style="{{ in_array($dateMinType, ['today_minus', 'today_plus']) ? '' : 'display: none;' }}" placeholder="Number of days" min="0" {{ $hasSubmissions ? 'readonly' : '' }}>
                
                @if($hasSubmissions)
                    @if($dateMinType)
                        <input type="hidden" name="date_min_type" value="{{ $dateMinType }}">
                        @if($dateMinType == 'fixed')
                        <input type="hidden" name="date_min_fixed" value="{{ $rules['date_min']['value'] ?? '' }}">
                        @elseif(in_array($dateMinType, ['today_minus', 'today_plus']))
                        <input type="hidden" name="date_min_days" value="{{ $rules['date_min']['days'] ?? 0 }}">
                        @endif
                    @endif
                @endif
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Maximum Date</label>
                <select name="date_max_type" id="date_max_type" class="form-control mb-2" {{ $hasSubmissions ? 'disabled' : '' }}>
                    <option value="" {{ $dateMaxType == '' ? 'selected' : '' }}>No Maximum</option>
                    <option value="fixed" {{ $dateMaxType == 'fixed' ? 'selected' : '' }}>Fixed Date</option>
                    <option value="today" {{ $dateMaxType == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="today_minus" {{ $dateMaxType == 'today_minus' ? 'selected' : '' }}>Today Minus Days</option>
                    <option value="today_plus" {{ $dateMaxType == 'today_plus' ? 'selected' : '' }}>Today Plus Days</option>
                </select>
                
                <input type="date" name="date_max_fixed" id="date_max_fixed" class="form-control" value="{{ ($dateMaxType == 'fixed') ? ($rules['date_max']['value'] ?? '') : '' }}" style="{{ $dateMaxType == 'fixed' ? '' : 'display: none;' }}" {{ $hasSubmissions ? 'readonly' : '' }}>

                <input type="number" name="date_max_days" id="date_max_days" class="form-control" value="{{ in_array($dateMaxType, ['today_minus', 'today_plus']) ? ($rules['date_max']['days'] ?? 0) : '' }}" style="{{ in_array($dateMaxType, ['today_minus', 'today_plus']) ? '' : 'display: none;' }}" placeholder="Number of days" min="0" {{ $hasSubmissions ? 'readonly' : '' }}>
                
                @if($hasSubmissions)
                    @if($dateMaxType)
                        <input type="hidden" name="date_max_type" value="{{ $dateMaxType }}">
                        @if($dateMaxType == 'fixed')
                        <input type="hidden" name="date_max_fixed" value="{{ $rules['date_max']['value'] ?? '' }}">
                        @elseif(in_array($dateMaxType, ['today_minus', 'today_plus']))
                        <input type="hidden" name="date_max_days" value="{{ $rules['date_max']['days'] ?? 0 }}">
                        @endif
                    @endif
                @endif
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Allowed Days of Week</label>
                @if($hasSubmissions)
                    <div class="alert alert-warning alert-dismissible" role="alert">
                        <div class="alert-icon">
                            <i class="fa-regular fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <div class="alert-description">
                                * Cannot change after submissions
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mb-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="selectWeekdays()">Weekdays</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="selectWeekends()">Weekends</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAllDays()">All Days</button>
                    </div>
                @endif
                
                <div class="border rounded p-2">
                    <div class="form-check">
                        <input type="checkbox" name="allowed_days[]" value="1" class="form-check-input" id="day_mon" {{ in_array('1', $allowedDays) ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }}>
                        <label class="form-check-label" for="day_mon">Monday</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="allowed_days[]" value="2" class="form-check-input" id="day_tue" {{ in_array('2', $allowedDays) ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }}>
                        <label class="form-check-label" for="day_tue">Tuesday</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="allowed_days[]" value="3"  class="form-check-input" id="day_wed"  {{ in_array('3', $allowedDays) ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }}>
                        <label class="form-check-label" for="day_wed">Wednesday</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="allowed_days[]" value="4" class="form-check-input" id="day_thu" {{ in_array('4', $allowedDays) ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }}>
                        <label class="form-check-label" for="day_thu">Thursday</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="allowed_days[]" value="5" class="form-check-input" id="day_fri" {{ in_array('5', $allowedDays) ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }}>
                        <label class="form-check-label" for="day_fri">Friday</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="allowed_days[]" value="6" class="form-check-input" id="day_sat" {{ in_array('6', $allowedDays) ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }}>
                        <label class="form-check-label" for="day_sat">Saturday</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="allowed_days[]" value="0" class="form-check-input" id="day_sun" {{ in_array('0', $allowedDays) ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }}>
                        <label class="form-check-label" for="day_sun">Sunday</label>
                    </div>
                    
                    @if($hasSubmissions && !empty($allowedDays))
                        @foreach($allowedDays as $day)
                            <input type="hidden" name="allowed_days[]" value="{{ $day }}">
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Disabled Dates</label>
                <textarea name="disabled_dates" class="form-control" rows="5" placeholder="Enter dates to disable, one per line&#10;Format: YYYY-MM-DD&#10;Example:&#10;2024-12-25&#10;2024-01-01" {{ $hasSubmissions ? 'readonly' : '' }}>{{ $disabledDates }}</textarea>
                <small class="text-muted">
                    Enter specific dates that should be disabled
                    @if($hasSubmissions)
                        <div class="alert alert-warning alert-dismissible py-1 px-2" role="alert">
                            <div>
                                <div class="alert-description">
                                    * Cannot change after submissions
                                </div>
                            </div>
                        </div>
                    @endif
                </small>
                
                @if($hasSubmissions && $disabledDates)
                    <input type="hidden" name="disabled_dates" value="{{ $disabledDates }}">
                @endif
            </div>
        </div>
    </div>
</div>
@endif
