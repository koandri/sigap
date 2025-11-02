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
                            <i class="far fa-arrow-left"></i>&nbsp;
                            Back to Requests
                        </a>
                        
                        @can('update', $formRequest)
                            @if($formRequest->isPending())
                                <a href="{{ route('form-requests.edit', $formRequest) }}" class="btn btn-primary">
                                    <i class="far fa-edit"></i>&nbsp;
                                    Edit Request
                                </a>
                            @endif
                        @endcan
                        
                        @can('process', $formRequest)
                            @php
                                // Check if forms have been issued (have issued_at timestamp)
                                $printedForms = $formRequest->items->flatMap(function ($item) {
                                    return $item->printedForms;
                                });
                                $formsIssued = $printedForms->contains(function ($printedForm) {
                                    return $printedForm->issued_at !== null;
                                });
                                
                                // Only show Print Labels when:
                                // 1. Status is Processing (Processing Started), OR
                                // 2. Forms have been issued (Forms Issued)
                                // But NOT when status is Ready or later
                                $canPrintLabels = ($formRequest->isProcessing() || $formsIssued) 
                                    && !$formRequest->isReady() 
                                    && !$formRequest->isCollected() 
                                    && !$formRequest->isCompleted();
                            @endphp
                            @if($canPrintLabels)
                                <a href="{{ route('form-requests.labels', $formRequest) }}" class="btn btn-info">
                                    <i class="far fa-print"></i>&nbsp;
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
                                <div>{{ formatDate($formRequest->request_date) }}</div>
                            </div>
                            
                            @if($formRequest->acknowledged_at)
                                <div class="mb-3">
                                    <label class="form-label">Acknowledged At</label>
                                    <div>{{ formatDate($formRequest->acknowledged_at) }}</div>
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
                                    <div>{{ formatDate($formRequest->ready_at) }}</div>
                                </div>
                            @endif
                            
                            @if($formRequest->collected_at)
                                <div class="mb-3">
                                    <label class="form-label">Collected At</label>
                                    <div>{{ formatDate($formRequest->collected_at) }}</div>
                                </div>
                            @endif
                            
                            <div class="mb-3">
                                <label class="form-label">Total Forms</label>
                                <div>{{ $formRequest->total_forms }} form type(s)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Total Copies</label>
                                <div>{{ $formRequest->total_quantity }} copie(s)</div>
                            </div>
                            
                            @if(!$formRequest->isCollected() && !$formRequest->isCompleted() && ($formRequest->isPending() || $formRequest->isAcknowledged() || $formRequest->isProcessing() || $formRequest->isReady()))
                                <hr class="my-3">
                                <div class="mb-0">
                                    <label class="form-label fw-bold">Actions</label>
                                    <div class="mt-2">
                                        @can('process', $formRequest)
                                            @if($formRequest->isPending())
                                                <form method="POST" action="{{ route('form-requests.acknowledge', $formRequest) }}" class="mb-2">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success w-100">
                                                        <i class="far fa-check"></i>&nbsp;
                                                        Acknowledge Request
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if($formRequest->isAcknowledged())
                                                <form method="POST" action="{{ route('form-requests.process', $formRequest) }}" class="mb-2">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary w-100">
                                                        <i class="far fa-wrench"></i>&nbsp;
                                                        Start Processing
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if($formRequest->isProcessing())
                                                <form method="POST" action="{{ route('form-requests.ready', $formRequest) }}" class="mb-2">
                                                    @csrf
                                                    <button type="submit" class="btn btn-info w-100">
                                                        <i class="far fa-box"></i>&nbsp;
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
                                                        <i class="far fa-check"></i>&nbsp;
                                                        Mark as Collected
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
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
                                                    <div class="text-muted">{{ $item->documentVersion->document->department?->name ?? 'N/A' }}</div>
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
                            @php
                                // Collect all printed forms
                                $printedForms = $formRequest->items->flatMap->printedForms;
                                
                                // Build timeline events array
                                $timelineEvents = [];
                                
                                // Form Request events
                                $timelineEvents[] = [
                                    'date' => $formRequest->request_date,
                                    'type' => 'request_submitted',
                                    'icon' => 'far fa-file-alt',
                                    'icon_bg' => 'bg-primary',
                                    'title' => 'Request Submitted',
                                    'description' => 'Request submitted by ' . $formRequest->requester->name,
                                ];
                                
                                if ($formRequest->acknowledged_at) {
                                    $timelineEvents[] = [
                                        'date' => $formRequest->acknowledged_at,
                                        'type' => 'request_acknowledged',
                                        'icon' => 'far fa-check',
                                        'icon_bg' => 'bg-success',
                                        'title' => 'Request Acknowledged',
                                        'description' => 'Acknowledged by ' . $formRequest->acknowledger->name,
                                    ];
                                }
                                
                                if ($formRequest->isProcessing() || $formRequest->isReady() || $formRequest->isCollected() || $formRequest->isCompleted()) {
                                    // Use acknowledged_at if available, otherwise request_date
                                    $processingDate = $formRequest->acknowledged_at ?? $formRequest->request_date->copy();
                                    $timelineEvents[] = [
                                        'date' => $processingDate,
                                        'type' => 'processing_started',
                                        'icon' => 'far fa-wrench',
                                        'icon_bg' => 'bg-info',
                                        'title' => 'Processing Started',
                                        'description' => 'Forms are being prepared',
                                    ];
                                }
                                
                                // Forms issued (when printed forms were created)
                                $issuedForms = $printedForms->whereNotNull('issued_at');
                                if ($issuedForms->isNotEmpty()) {
                                    $earliestIssued = $issuedForms->min('issued_at');
                                    $issuedCount = $issuedForms->count();
                                    $timelineEvents[] = [
                                        'date' => $earliestIssued,
                                        'type' => 'forms_issued',
                                        'icon' => 'far fa-print',
                                        'icon_bg' => 'bg-info',
                                        'title' => 'Forms Issued',
                                        'description' => $issuedCount . ' form(s) issued and printed',
                                    ];
                                }
                                
                                if ($formRequest->ready_at) {
                                    $timelineEvents[] = [
                                        'date' => $formRequest->ready_at,
                                        'type' => 'ready_for_collection',
                                        'icon' => 'far fa-box',
                                        'icon_bg' => 'bg-warning',
                                        'title' => 'Ready for Collection',
                                        'description' => 'Forms are ready to be collected',
                                    ];
                                }
                                
                                if ($formRequest->collected_at) {
                                    $timelineEvents[] = [
                                        'date' => $formRequest->collected_at,
                                        'type' => 'forms_collected',
                                        'icon' => 'far fa-check',
                                        'icon_bg' => 'bg-success',
                                        'title' => 'Forms Collected',
                                        'description' => 'Forms have been collected by the requester',
                                    ];
                                }
                                
                                // Forms returned
                                $returnedForms = $printedForms->whereNotNull('returned_at');
                                if ($returnedForms->isNotEmpty()) {
                                    $earliestReturned = $returnedForms->min('returned_at');
                                    $returnedCount = $returnedForms->count();
                                    $timelineEvents[] = [
                                        'date' => $earliestReturned,
                                        'type' => 'forms_returned',
                                        'icon' => 'far fa-arrow-turn-down-left',
                                        'icon_bg' => 'bg-warning',
                                        'title' => 'Forms Returned',
                                        'description' => $returnedCount . ' form(s) returned from circulation',
                                    ];
                                }
                                
                                // Forms received
                                $receivedForms = $printedForms->whereNotNull('received_at');
                                if ($receivedForms->isNotEmpty()) {
                                    $earliestReceived = $receivedForms->min('received_at');
                                    $receivedCount = $receivedForms->count();
                                    $timelineEvents[] = [
                                        'date' => $earliestReceived,
                                        'type' => 'forms_received',
                                        'icon' => 'far fa-inbox',
                                        'icon_bg' => 'bg-primary',
                                        'title' => 'Forms Received',
                                        'description' => $receivedCount . ' form(s) received by Document Control',
                                    ];
                                }
                                
                                // Forms scanned
                                $scannedForms = $printedForms->whereNotNull('scanned_at');
                                if ($scannedForms->isNotEmpty()) {
                                    $earliestScanned = $scannedForms->min('scanned_at');
                                    $scannedCount = $scannedForms->count();
                                    $timelineEvents[] = [
                                        'date' => $earliestScanned,
                                        'type' => 'forms_scanned',
                                        'icon' => 'far fa-scanner',
                                        'icon_bg' => 'bg-info',
                                        'title' => 'Forms Scanned',
                                        'description' => $scannedCount . ' form(s) scanned and digitized',
                                    ];
                                }
                                
                                // Sort timeline events by date
                                usort($timelineEvents, function($a, $b) {
                                    return $a['date'] <=> $b['date'];
                                });
                                
                                // Determine the latest event (current status)
                                $latestEventIndex = count($timelineEvents) - 1;
                            @endphp
                            
                            <div class="mb-3">
                                <span class="badge bg-primary text-white">Timeline: Oldest → Latest</span>
                                <span class="badge bg-success text-white ms-2">Current Status Highlighted</span>
                            </div>
                            
                            <div class="timeline">
                                @foreach($timelineEvents as $index => $event)
                                    <div class="timeline-event">
                                        <div class="timeline-event-icon {{ $event['icon_bg'] }} text-white">
                                            <i class="{{ $event['icon'] }}"></i>&nbsp;
                                        </div>
                                        <div class="card timeline-event-card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <h4 class="mb-0">{{ $event['title'] }}</h4>
                                                        @if($index === $latestEventIndex)
                                                        <span class="badge bg-success text-white">Current Status</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-muted">{{ formatDate($event['date']) }}</div>
                                                </div>
                                                <p class="text-muted mb-0">{{ $event['description'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

