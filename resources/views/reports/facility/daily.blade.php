@extends('layouts.app')

@section('title', 'Daily Cleaning Report')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/tabler/libs/tom-select/dist/css/tom-select.bootstrap5.min.css') }}">
@endpush

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Facility Management Reports</div>
                <h2 class="page-title">
                    <i class="fa fa-calendar-day"></i> Daily Cleaning Report
                </h2>
            </div>
            @if($selectedLocations->isNotEmpty())
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="fa fa-print"></i> Print Report
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        
        @include('layouts.alerts')

        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('reports.facility.daily') }}">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <label class="form-label required">Location</label>
                            <select name="location_id[]" id="location-select" class="form-select" multiple required>
                                @foreach($allLocations as $loc)
                                    <option value="{{ $loc->id }}" {{ in_array($loc->id, $locationIds) ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label required">Date</label>
                            <input type="date" name="date" class="form-control" value="{{ $date }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa fa-search"></i> View Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($selectedLocations->isNotEmpty())
        <!-- Overall Statistics -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Tasks</div>
                        </div>
                        <div class="h1 mb-0">{{ $totalStats['total'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Completed</div>
                        </div>
                        <div class="h1 mb-0 text-success">{{ $totalStats['completed'] }}</div>
                        @if($totalStats['total'] > 0)
                        <div class="progress progress-sm mt-2">
                            <div class="progress-bar bg-success" style="width: {{ ($totalStats['completed'] / $totalStats['total']) * 100 }}%"></div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Pending</div>
                        </div>
                        <div class="h1 mb-0 text-warning">{{ $totalStats['pending'] }}</div>
                        @if($totalStats['total'] > 0)
                        <div class="progress progress-sm mt-2">
                            <div class="progress-bar bg-warning" style="width: {{ ($totalStats['pending'] / $totalStats['total']) * 100 }}%"></div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Missed</div>
                        </div>
                        <div class="h1 mb-0 text-danger">{{ $totalStats['missed'] }}</div>
                        @if($totalStats['total'] > 0)
                        <div class="progress progress-sm mt-2">
                            <div class="progress-bar bg-danger" style="width: {{ ($totalStats['missed'] / $totalStats['total']) * 100 }}%"></div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Reports -->
        @foreach($locationData as $data)
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-map-marker-alt"></i> {{ $data['location']->name }} - {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}
                </h3>
                <div class="card-actions">
                    <span class="badge bg-blue">{{ $data['stats']['total'] }} Tasks</span>
                    <span class="badge bg-success">{{ $data['stats']['completed'] }} Completed</span>
                    <span class="badge bg-warning">{{ $data['stats']['pending'] }} Pending</span>
                    <span class="badge bg-danger">{{ $data['stats']['missed'] }} Missed</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Task #</th>
                            <th>Item</th>
                            <th>Schedule</th>
                            <th>Assigned To</th>
                            <th>Completed By</th>
                            <th>Status</th>
                            <th>Photos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['tasks'] as $task)
                        <tr>
                            <td>
                                <a href="{{ route('facility.tasks.show', $task) }}" class="text-reset">
                                    {{ $task->task_number }}
                                </a>
                            </td>
                            <td>
                                <strong>{{ $task->item_name }}</strong>
                                @if($task->asset)
                                    <br><span class="badge bg-azure">{{ $task->asset->code }}</span>
                                @endif
                            </td>
                            <td>
                                @if($task->cleaning_schedule_id > 0)
                                    {{ $task->cleaningSchedule->name }}
                                @else
                                    <span class="badge bg-purple">Ad-hoc</span>
                                @endif
                            </td>
                            <td>
                                @if($task->assignedUser)
                                    {{ $task->assignedUser->name }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($task->completedByUser)
                                    <div>{{ $task->completedByUser->name }}</div>
                                    <small class="text-muted">{{ $task->completed_at->format('H:i') }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($task->status === 'completed' || $task->status === 'approved')
                                    <span class="badge bg-success"><i class="fa fa-check"></i> {{ ucfirst($task->status) }}</span>
                                @elseif($task->status === 'in-progress')
                                    <span class="badge bg-info"><i class="fa fa-spinner"></i> In Progress</span>
                                @elseif($task->status === 'pending')
                                    <span class="badge bg-warning"><i class="fa fa-clock"></i> Pending</span>
                                @elseif($task->status === 'missed')
                                    <span class="badge bg-danger"><i class="fa fa-exclamation-triangle"></i> Missed</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($task->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($task->submission)
                                    <a href="#" class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#photoModal{{ $task->id }}">
                                        <i class="fa fa-image"></i> View Photos
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Photo Modal -->
                        @if($task->submission)
                        <div class="modal fade" id="photoModal{{ $task->id }}" tabindex="-1">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Photos - {{ $task->task_number }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">Before Photo</label>
                                                @if($task->submission->before_photo && isset($task->submission->before_photo['file_path']))
                                                <a href="{{ Storage::disk('sigap')->url($task->submission->before_photo['file_path']) }}" data-lightbox="task-{{ $task->id }}">
                                                    <img src="{{ Storage::disk('sigap')->url($task->submission->before_photo['file_path']) }}" 
                                                         class="img-fluid rounded" 
                                                         alt="Before Photo">
                                                </a>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">After Photo</label>
                                                @if($task->submission->after_photo && isset($task->submission->after_photo['file_path']))
                                                <a href="{{ Storage::disk('sigap')->url($task->submission->after_photo['file_path']) }}" data-lightbox="task-{{ $task->id }}">
                                                    <img src="{{ Storage::disk('sigap')->url($task->submission->after_photo['file_path']) }}" 
                                                         class="img-fluid rounded" 
                                                         alt="After Photo">
                                                </a>
                                                @endif
                                            </div>
                                        </div>
                                        @if($task->submission->notes)
                                        <div class="mt-3">
                                            <label class="form-label">Notes</label>
                                            <div class="alert alert-info mb-0">
                                                {{ $task->submission->notes }}
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <div class="text-muted">
                                    <i class="fa fa-inbox"></i> No cleaning tasks scheduled for this location on the selected date.
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
        @else
        <!-- No Location Selected -->
        <div class="empty">
            <div class="empty-icon">
                <i class="fa fa-file-alt fa-3x"></i>
            </div>
            <p class="empty-title">Select Locations and Date</p>
            <p class="empty-subtitle text-muted">
                Please select one or more locations and a date to view the daily cleaning report.
            </p>
        </div>
        @endif

    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/lightbox.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('assets/css/lightbox.min.css') }}">
<script src="{{ asset('assets/tabler/libs/tom-select/dist/js/tom-select.base.min.js') }}"></script>
<script>
    new TomSelect('#location-select', {
        plugins: ['remove_button'],
        placeholder: 'Select one or more locations...',
        maxItems: null,
        hideSelected: true,
        closeAfterSelect: false
    });
</script>
@endpush
@endsection

