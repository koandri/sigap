@extends('layouts.app')

@section('title', 'Form Request #' . $formRequest->id)

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        Form Request
                    </div>
                    <h2 class="page-title">
                        Request #{{ $formRequest->id }}
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ route('form-requests.index') }}" class="btn btn-outline-secondary">
                            <i class="far fa-arrow-left"></i>
                            Back to Requests
                        </a>
                        
                        @can('process', $formRequest)
                            @if($formRequest->isAcknowledged() || $formRequest->isProcessing())
                                <a href="{{ route('form-requests.labels', $formRequest) }}" class="btn btn-info">
                                    <i class="far fa-print"></i>
                                    Print Labels
                                </a>
                            @endif
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @include('layouts.alerts')
            <div class="row">
                <!-- Request Details -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Request Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <div>
                                    <span class="badge {{ $formRequest->status->value === 'requested' ? 'bg-warning' : ($formRequest->status->value === 'completed' ? 'bg-success' : 'bg-info') }} text-white">
                                        {{ $formRequest->status->label() }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Requested By</label>
                                <div>{{ $formRequest->requester->name }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Request Date</label>
                                <div>{{ $formRequest->request_date->format('Y-m-d H:i') }}</div>
                            </div>
                            
                            @if($formRequest->acknowledged_at)
                                <div class="mb-3">
                                    <label class="form-label">Acknowledged At</label>
                                    <div>{{ $formRequest->acknowledged_at->format('Y-m-d H:i') }}</div>
                                </div>
                            @endif
                            
                            @if($formRequest->acknowledger)
                                <div class="mb-3">
                                    <label class="form-label">Acknowledged By</label>
                                    <div>{{ $formRequest->acknowledger->name }}</div>
                                </div>
                            @endif
                            
                            @if($formRequest->ready_at)
                                <div class="mb-3">
                                    <label class="form-label">Ready At</label>
                                    <div>{{ $formRequest->ready_at->format('Y-m-d H:i') }}</div>
                                </div>
                            @endif
                            
                            @if($formRequest->collected_at)
                                <div class="mb-3">
                                    <label class="form-label">Collected At</label>
                                    <div>{{ $formRequest->collected_at->format('Y-m-d H:i') }}</div>
                                </div>
                            @endif
                            
                            <div class="mb-3">
                                <label class="form-label">Total Forms</label>
                                <div>{{ $formRequest->total_forms }} form type(s)</div>
                            </div>
                            
                            <div class="mb-0">
                                <label class="form-label">Total Copies</label>
                                <div>{{ $formRequest->total_quantity }} copie(s)</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Actions</h3>
                        </div>
                        <div class="card-body">
                            @can('process', $formRequest)
                                @if($formRequest->isPending())
                                    <form method="POST" action="{{ route('form-requests.acknowledge', $formRequest) }}" class="mb-2">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="far fa-check"></i>
                                            Acknowledge Request
                                        </button>
                                    </form>
                                @endif
                                
                                @if($formRequest->isAcknowledged())
                                    <form method="POST" action="{{ route('form-requests.process', $formRequest) }}" class="mb-2">
                                        @csrf
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="far fa-wrench"></i>
                                            Start Processing
                                        </button>
                                    </form>
                                @endif
                                
                                @if($formRequest->isProcessing())
                                    <form method="POST" action="{{ route('form-requests.ready', $formRequest) }}" class="mb-2">
                                        @csrf
                                        <button type="submit" class="btn btn-info w-100">
                                            <i class="far fa-box"></i>
                                            Mark as Ready
                                        </button>
                                    </form>
                                @endif
                            @endcan
                            
                            @can('view', $formRequest)
                                @if($formRequest->isReady())
                                    <form method="POST" action="{{ route('form-requests.collect', $formRequest) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="far fa-check"></i>
                                            Mark as Collected
                                        </button>
                                    </form>
                                @endif
                            @endcan
                            
                            @if($formRequest->isCollected() || $formRequest->isCompleted())
                                <div class="alert alert-success mb-0">
                                    <i class="far fa-check"></i>
                                    This request has been completed.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Requested Forms -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Requested Forms</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>Form</th>
                                            <th>Document Number</th>
                                            <th>Version</th>
                                            <th class="text-end">Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($formRequest->items as $item)
                                            <tr>
                                                <td>
                                                    <div class="fw-bold">{{ $item->documentVersion->document->title }}</div>
                                                    <div class="text-muted">{{ $item->documentVersion->document->department->name }}</div>
                                                </td>
                                                <td>{{ $item->documentVersion->document->document_number }}</td>
                                                <td>
                                                    <span class="badge bg-secondary text-white">
                                                        v{{ $item->documentVersion->version_number }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="badge bg-primary text-white">
                                                        {{ $item->quantity }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-end">Total Copies:</th>
                                            <th class="text-end">
                                                <span class="badge bg-primary text-white">
                                                    {{ $formRequest->total_quantity }}
                                                </span>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Timeline -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Status Timeline</h3>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-event">
                                    <div class="timeline-event-icon bg-primary text-white">
                                        <i class="far fa-file-alt"></i>
                                    </div>
                                    <div class="card timeline-event-card">
                                        <div class="card-body">
                                            <div class="text-muted float-end">{{ $formRequest->request_date->format('Y-m-d H:i') }}</div>
                                            <h4>Request Submitted</h4>
                                            <p class="text-muted mb-0">Request submitted by {{ $formRequest->requester->name }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                @if($formRequest->acknowledged_at)
                                    <div class="timeline-event">
                                        <div class="timeline-event-icon bg-success text-white">
                                            <i class="far fa-check"></i>
                                        </div>
                                        <div class="card timeline-event-card">
                                            <div class="card-body">
                                                <div class="text-muted float-end">{{ $formRequest->acknowledged_at->format('Y-m-d H:i') }}</div>
                                                <h4>Request Acknowledged</h4>
                                                <p class="text-muted mb-0">Acknowledged by {{ $formRequest->acknowledger->name }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($formRequest->isProcessing() || $formRequest->isReady() || $formRequest->isCollected() || $formRequest->isCompleted())
                                    <div class="timeline-event">
                                        <div class="timeline-event-icon bg-info text-white">
                                            <i class="far fa-wrench"></i>
                                        </div>
                                        <div class="card timeline-event-card">
                                            <div class="card-body">
                                                <h4>Processing Started</h4>
                                                <p class="text-muted mb-0">Forms are being prepared</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($formRequest->ready_at)
                                    <div class="timeline-event">
                                        <div class="timeline-event-icon bg-warning text-white">
                                            <i class="far fa-box"></i>
                                        </div>
                                        <div class="card timeline-event-card">
                                            <div class="card-body">
                                                <div class="text-muted float-end">{{ $formRequest->ready_at->format('Y-m-d H:i') }}</div>
                                                <h4>Ready for Collection</h4>
                                                <p class="text-muted mb-0">Forms are ready to be collected</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($formRequest->collected_at)
                                    <div class="timeline-event">
                                        <div class="timeline-event-icon bg-success text-white">
                                            <i class="far fa-check"></i>
                                        </div>
                                        <div class="card timeline-event-card">
                                            <div class="card-body">
                                                <div class="text-muted float-end">{{ $formRequest->collected_at->format('Y-m-d H:i') }}</div>
                                                <h4>Forms Collected</h4>
                                                <p class="text-muted mb-0">Forms have been collected by the requester</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

