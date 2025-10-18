@extends('layouts.app')

@section('title', 'Weekly Cleaning Report')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Facility Management Reports</div>
                <h2 class="page-title">
                    <i class="fa fa-calendar-week"></i> Weekly Cleaning Report
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('facility.reports.weekly-pdf', ['year' => $year, 'week' => $week, 'locations' => $locationIds]) }}" 
                       class="btn btn-primary" 
                       target="_blank">
                        <i class="fa fa-file-pdf"></i> Export PDF
                    </a>
                </div>
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
                <form method="GET" action="{{ route('facility.reports.weekly') }}">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Year</label>
                            <input type="number" 
                                   name="year" 
                                   class="form-control" 
                                   value="{{ $year }}" 
                                   min="2020" 
                                   max="2099">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Week</label>
                            <input type="number" 
                                   name="week" 
                                   class="form-control" 
                                   value="{{ $week }}" 
                                   min="1" 
                                   max="53">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Locations (optional)</label>
                            <select name="locations[]" class="form-select" multiple>
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
                                <i class="fa fa-search"></i> View
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Info -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h3>Week {{ $week }}, {{ $year }}</h3>
                        <p class="text-muted">
                            {{ $weekStart->format('F d, Y') }} - {{ $weekEnd->format('F d, Y') }}
                        </p>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="mb-2">
                            <span class="badge bg-success me-2" style="font-size: 1.2rem;">✓</span> All tasks completed
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-warning me-2" style="font-size: 1.2rem;">⚠</span> Partially completed
                        </div>
                        <div>
                            <span class="badge bg-danger me-2" style="font-size: 1.2rem;">✗</span> No tasks completed
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
                            <th class="w-25">Location</th>
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
                                style="cursor: pointer; background-color: {{ $day['indicator'] === '✓' ? '#d4edda' : ($day['indicator'] === '⚠' ? '#fff3cd' : '#f8d7da') }};"
                                onclick="showCellDetails('{{ $day['date'] }}', {{ $row['location']->id }}, '{{ $row['location']->name }}')">
                                <div style="font-size: 2rem;">
                                    @if($day['indicator'] === '✓')
                                        <span class="text-success">✓</span>
                                    @elseif($day['indicator'] === '⚠')
                                        <span class="text-warning">⚠</span>
                                    @else
                                        <span class="text-danger">✗</span>
                                    @endif
                                </div>
                                <small class="text-muted">
                                    {{ $day['completed'] }}/{{ $day['total'] }}
                                </small>
                            </td>
                            @endforeach
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <div class="empty">
                                    <div class="empty-icon">
                                        <i class="fa fa-inbox fa-3x"></i>
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

@push('scripts')
<script>
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
    fetch(`{{ route('facility.reports.cell-details') }}?date=${date}&location_id=${locationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.tasks.length === 0) {
                body.innerHTML = `
                    <div class="empty">
                        <div class="empty-icon"><i class="fa fa-inbox fa-2x"></i></div>
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
                    statusBadge = '<span class="badge bg-success">✓ ' + task.status + '</span>';
                } else if (task.status === 'in-progress') {
                    statusBadge = '<span class="badge bg-info">⟳ In Progress</span>';
                } else if (task.status === 'pending') {
                    statusBadge = '<span class="badge bg-warning">⏱ Pending</span>';
                } else if (task.status === 'missed') {
                    statusBadge = '<span class="badge bg-danger">⚠ Missed</span>';
                } else {
                    statusBadge = '<span class="badge bg-secondary">' + task.status + '</span>';
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
                    <i class="fa fa-exclamation-triangle"></i> Failed to load task details.
                </div>
            `;
        });
}
</script>
@endpush
@endsection

