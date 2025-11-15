@extends('layouts.app')

@section('title', 'Weekly Cleaning Report')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Facility Management Reports</div>
                <h2 class="page-title">
                    <i class="fa fa-calendar-week"></i>&nbsp; Weekly Cleaning Report
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        
        @include('layouts.alerts')

        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('reports.facility.weekly') }}" id="weeklyReportForm">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <label class="form-label required">Week Starting (Monday)</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fa fa-calendar"></i>&nbsp;
                                </span>
                                <input type="date" 
                                       name="date" 
                                       id="weekDate"
                                       class="form-control" 
                                       value="{{ $weekStart->toDateString() }}" 
                                       placeholder="Select a Monday"
                                       autocomplete="off"
                                       data-litepicker-manual="true"
                                       required>
                            </div>
                            <small class="form-hint">Select the Monday for the week you want to view</small>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Locations (optional)</label>
                            <select name="locations[]" id="locationSelect" class="form-select" multiple>
                                @foreach($allLocations as $loc)
                                    <option value="{{ $loc->id }}" {{ in_array($loc->id, $locationIds) ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-hint">Leave empty for all locations</small>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa fa-search"></i>&nbsp; View
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Info -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-calendar-week"></i>&nbsp; Weekly Report
                </h3>
                <div class="card-actions">
                    <a href="{{ route('reports.facility.weekly-pdf', ['date' => $weekStart->toDateString(), 'locations' => $locationIds]) }}" 
                       class="btn btn-primary btn-sm" 
                       target="_blank"
                       title="Open print-friendly view. Use your browser's print function (Ctrl+P / Cmd+P) to save as PDF">
                        <i class="fa fa-print"></i>&nbsp; Print / Export PDF
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <p class="text-muted mb-3">
                            <strong>Period:</strong> {{ $weekStart->format('F d, Y') }} - {{ $weekEnd->format('F d, Y') }}
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <div>
                                <span class="badge bg-success text-white me-2" style="font-size: 1.2rem;">✓</span> All tasks completed
                            </div>
                            <div>
                                <span class="badge bg-warning text-white me-2" style="font-size: 1.2rem;">⚠</span> Partially completed
                            </div>
                            <div>
                                <span class="badge bg-danger text-white me-2" style="font-size: 1.2rem;">✗</span> No tasks completed
                            </div>
                            <div>
                                <span class="badge bg-secondary text-white me-2" style="font-size: 1.2rem;">-</span> No tasks scheduled
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weekly Grid -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Weekly Overview</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-vcenter card-table">
                    <thead>
                        <tr>
                            <th class="w-25">Locations</th>
                            @for($i = 0; $i < 7; $i++)
                                @php
                                    $dayDate = $weekStart->copy()->addDays($i);
                                @endphp
                                <th class="text-center">
                                    <div>{{ $dayDate->format('D') }}</div>
                                    <small class="text-muted">{{ $dayDate->format('M d') }}</small>
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gridData as $row)
                        <tr>
                            <td>
                                <strong>{{ $row['location']->name }}</strong>
                            </td>
                            @foreach($row['days'] as $day)
                            <td class="text-center" 
                                @if($day['indicator'] !== '-')
                                style="cursor: pointer; background-color: {{ $day['indicator'] === '✓' ? '#d4edda' : ($day['indicator'] === '⚠' ? '#fff3cd' : '#f8d7da') }};"
                                onclick="showCellDetails('{{ $day['date'] }}', {{ $row['location']->id }}, '{{ $row['location']->name }}')"
                                @else
                                style="background-color: #f8f9fa;"
                                @endif
                                >
                                <div style="font-size: 2rem;">
                                    @if($day['indicator'] === '✓')
                                        <span class="text-success">✓</span>
                                    @elseif($day['indicator'] === '⚠')
                                        <span class="text-warning">⚠</span>
                                    @elseif($day['indicator'] === '✗')
                                        <span class="text-danger">✗</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                                @if($day['indicator'] !== '-')
                                <small class="text-muted">
                                    {{ $day['completed'] }}/{{ $day['total'] }}
                                </small>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <div class="empty">
                                    <div class="empty-icon">
                                        <i class="fa fa-inbox fa-3x"></i>&nbsp;
                                    </div>
                                    <p class="empty-title">No locations found</p>
                                    <p class="empty-subtitle text-muted">
                                        Please check your location filter.
                                    </p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Cell Details Modal -->
<div class="modal fade" id="cellDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cellDetailsTitle">Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="cellDetailsBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('css')
<link rel="stylesheet" href="{{ asset('assets/tabler/libs/tom-select/dist/css/tom-select.bootstrap5.min.css') }}">
<style>
    .ts-control {
        background-color: #ffffff !important;
        border: 1px solid #dadce0 !important;
        min-height: calc(1.5em + 0.75rem + 2px) !important;
        padding: 0.375rem 0.75rem !important;
    }
    
    .ts-dropdown {
        background-color: #ffffff !important;
        border: 1px solid #dadce0 !important;
        border-radius: 4px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
    }
    
    .ts-dropdown .ts-dropdown-content {
        background-color: #ffffff !important;
    }
    
    .ts-dropdown .option {
        background-color: #ffffff !important;
        color: #212529 !important;
        padding: 0.375rem 0.75rem !important;
    }
    
    .ts-dropdown .option:hover,
    .ts-dropdown .option.active {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
    
    .ts-dropdown .option.selected {
        background-color: #0d6efd !important;
        color: #fff !important;
    }
    
    /* Litepicker - Style non-Monday days */
    .litepicker .container__days .day-item.is-locked {
        opacity: 0.3 !important;
        cursor: not-allowed !important;
        text-decoration: line-through !important;
        pointer-events: none !important;
        color: #ccc !important;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/tabler/libs/tom-select/dist/js/tom-select.complete.min.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize TomSelect for locations
    new TomSelect('#locationSelect', {
        plugins: ['remove_button'],
        placeholder: 'Select locations...',
        maxOptions: null,
        closeAfterSelect: true
    });
    
    // Initialize date picker for Mondays only
    const picker = new Litepicker({
        element: document.getElementById('weekDate'),
        format: 'YYYY-MM-DD',
        singleMode: true,
        maxDate: new Date(),
        firstDay: 1, // Week starts on Monday
        lockDays: [
            // Lock all days except Monday using a filter function
            (date) => {
                // Return true to lock the day
                return date.getDay() !== 1;
            }
        ],
        setup: (picker) => {
            picker.on('preselect', (date1, date2) => {
                // Additional validation before selection
                const selectedDate = date1.dateInstance;
                if (selectedDate.getDay() !== 1) {
                    alert('Please select a Monday');
                    picker.clearSelection();
                    return false;
                }
            });
            
            // Apply visual styling after calendar is rendered
            picker.on('render', (ui) => {
                applyLockedDaysStyle();
            });
            
            picker.on('show', (ui) => {
                // Small delay to ensure DOM is ready
                setTimeout(() => {
                    applyLockedDaysStyle();
                }, 10);
            });
        },
        lang: 'en-US',
        buttonText: {
            previousMonth: '<',
            nextMonth: '>',
        }
    });
    
    // Function to apply locked styling to non-Monday days
    function applyLockedDaysStyle() {
        const dayElements = document.querySelectorAll('.litepicker .container__days .day-item');
        dayElements.forEach((element) => {
            const dateAttr = element.getAttribute('data-time');
            if (dateAttr) {
                const date = new Date(parseInt(dateAttr));
                // If not Monday (day 1), add is-locked class
                if (date.getDay() !== 1) {
                    element.classList.add('is-locked');
                }
            }
        });
    }
});

function showCellDetails(date, locationId, locationName) {
    const modal = new bootstrap.Modal(document.getElementById('cellDetailsModal'));
    const title = document.getElementById('cellDetailsTitle');
    const body = document.getElementById('cellDetailsBody');
    
    // Set title
    title.textContent = `${locationName} - ${date}`;
    
    // Show loading
    body.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Fetch data
    fetch(`{{ route('reports.facility.cell-details') }}?date=${date}&location_id=${locationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.tasks.length === 0) {
                body.innerHTML = `
                    <div class="empty">
                        <div class="empty-icon"><i class="fa fa-inbox fa-2x"></i>&nbsp;</div>
                        <p class="empty-title">No tasks found</p>
                    </div>
                `;
                return;
            }
            
            let html = '<div class="table-responsive"><table class="table table-vcenter">';
            html += '<thead><tr>';
            html += '<th>Task #</th>';
            html += '<th>Item</th>';
            html += '<th>Assigned To</th>';
            html += '<th>Status</th>';
            html += '<th>Completed</th>';
            html += '</tr></thead><tbody>';
            
            data.tasks.forEach(task => {
                let statusBadge = '';
                if (task.status === 'completed' || task.status === 'approved') {
                    statusBadge = '<span class="badge bg-success text-white">✓ ' + task.status + '</span>';
                } else if (task.status === 'in-progress') {
                    statusBadge = '<span class="badge bg-info text-white">⟳ In Progress</span>';
                } else if (task.status === 'pending') {
                    statusBadge = '<span class="badge bg-warning text-white">⏱ Pending</span>';
                } else if (task.status === 'missed') {
                    statusBadge = '<span class="badge bg-danger text-white">⚠ Missed</span>';
                } else {
                    statusBadge = '<span class="badge bg-secondary text-white">' + task.status + '</span>';
                }
                
                html += '<tr>';
                html += '<td>' + task.task_number + '</td>';
                html += '<td><strong>' + task.item_name + '</strong></td>';
                html += '<td>' + (task.assigned_to || '-') + '</td>';
                html += '<td>' + statusBadge + '</td>';
                html += '<td>' + (task.completed_by ? task.completed_by + ' at ' + task.completed_at : '-') + '</td>';
                html += '</tr>';
            });
            
            html += '</tbody></table></div>';
            body.innerHTML = html;
        })
        .catch(error => {
            body.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i>&nbsp; Failed to load task details.
                </div>
            `;
        });
}
</script>
@endpush
@endsection

