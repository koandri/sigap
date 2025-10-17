@extends('layouts.app')

@section('title', 'My Cleaning Tasks')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Facility Management</div>
                <h2 class="page-title">
                    <i class="fa fa-tasks"></i> My Tasks - {{ now()->format('l, F d, Y') }}
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        
        @include('layouts.alerts')

        <!-- My Assigned Tasks -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">My Assigned Tasks ({{ $myTasks->count() }})</h3>
            </div>
            <div class="card-body p-0">
                @if($myTasks->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($myTasks as $task)
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                @if($task->status === 'completed')
                                    <span class="badge bg-success"><i class="fa fa-check"></i></span>
                                @elseif($task->status === 'in-progress')
                                    <span class="badge bg-info"><i class="fa fa-spinner"></i></span>
                                @elseif($task->status === 'approved')
                                    <span class="badge bg-success"><i class="fa fa-check-double"></i></span>
                                @elseif($task->status === 'rejected')
                                    <span class="badge bg-danger"><i class="fa fa-times"></i></span>
                                @else
                                    <span class="badge bg-secondary"><i class="fa fa-clock"></i></span>
                                @endif
                            </div>
                            <div class="col">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <strong>{{ $task->item_name }}</strong>
                                        @if($task->asset)
                                            <span class="badge bg-azure ms-1">{{ $task->asset->code }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-muted small">
                                    <i class="fa fa-map-marker-alt"></i> {{ $task->location->name }}
                                    @if($task->item_description)
                                        <br>{{ $task->item_description }}
                                    @endif
                                </div>
                                @if($task->status === 'in-progress' && $task->started_by === auth()->id())
                                    <div class="text-warning small">
                                        <i class="fa fa-clock"></i> Started {{ $task->started_at->diffForHumans() }}
                                    </div>
                                @endif
                                @if($task->status === 'completed' || $task->status === 'approved')
                                    <div class="text-success small">
                                        <i class="fa fa-check"></i> Completed {{ $task->completed_at->diffForHumans() }}
                                    </div>
                                @endif
                            </div>
                            <div class="col-auto">
                                @if($task->status === 'pending')
                                    <form action="{{ route('facility.tasks.start', $task) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fa fa-play"></i> Start Task
                                        </button>
                                    </form>
                                @elseif($task->status === 'in-progress' && $task->started_by === auth()->id())
                                    <a href="{{ route('facility.tasks.submit', $task) }}" class="btn btn-success btn-sm">
                                        <i class="fa fa-camera"></i> Submit
                                    </a>
                                @elseif($task->status === 'completed' || $task->status === 'approved')
                                    <a href="{{ route('facility.tasks.show', $task) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                @elseif($task->status === 'in-progress')
                                    <span class="text-muted small">In progress by another cleaner</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="empty">
                    <div class="empty-icon">
                        <i class="fa fa-check-circle fa-3x text-success"></i>
                    </div>
                    <p class="empty-title">No assigned tasks</p>
                    <p class="empty-subtitle text-muted">
                        You have no tasks assigned for today.
                    </p>
                </div>
                @endif
            </div>
        </div>

        <!-- Available Tasks by Location -->
        @if($otherTasks->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Other Available Tasks</h3>
                <div class="card-subtitle">Tasks you can help with today</div>
            </div>
            <div class="card-body p-0">
                @foreach($otherTasks as $locationName => $tasks)
                <div class="mb-3">
                    <div class="list-group-header sticky-top">
                        <strong><i class="fa fa-map-marker-alt"></i> {{ $locationName }}</strong>
                        <span class="badge bg-secondary ms-2">{{ $tasks->count() }}</span>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($tasks as $task)
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col">
                                    <strong>{{ $task->item_name }}</strong>
                                    @if($task->asset)
                                        <span class="badge bg-azure ms-1">{{ $task->asset->code }}</span>
                                    @endif
                                    <div class="text-muted small">
                                        Assigned to: {{ $task->assignedUser->name }}
                                        @if($task->item_description)
                                            <br>{{ $task->item_description }}
                                        @endif
                                    </div>
                                </div>
                                <div class="col-auto">
                                    @if($task->canBeStartedBy(auth()->id()))
                                        <form action="{{ route('facility.tasks.start', $task) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                                <i class="fa fa-hand-paper"></i> I'll Do This
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge bg-secondary">Not Available</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-body">
                <div class="empty">
                    <div class="empty-icon">
                        <i class="fa fa-check-circle fa-3x text-success"></i>
                    </div>
                    <p class="empty-title">All tasks covered!</p>
                    <p class="empty-subtitle text-muted">
                        All tasks for today have been assigned or completed.
                    </p>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection

