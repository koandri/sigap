@extends('layouts.app')

@section('title', 'DMS SLA Dashboard')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        SLA Dashboard
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- SLA Metrics -->
            <div class="row row-deck row-cards mb-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Request to Acknowledgment</div>
                            </div>
                            <div class="h1 mb-3">
                                {{ $slaMetrics['request_to_acknowledgment'] ?? 'N/A' }}
                                <small class="text-muted">hours</small>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                            <div class="text-muted">Target: 2 hours</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Acknowledgment to Ready</div>
                            </div>
                            <div class="h1 mb-3">
                                {{ $slaMetrics['acknowledgment_to_ready'] ?? 'N/A' }}
                                <small class="text-muted">hours</small>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-info" style="width: 100%"></div>
                            </div>
                            <div class="text-muted">Target: Until Friday 11am</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Ready to Collected</div>
                            </div>
                            <div class="h1 mb-3">
                                {{ $slaMetrics['ready_to_collected'] ?? 'N/A' }}
                                <small class="text-muted">hours</small>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-warning" style="width: 100%"></div>
                            </div>
                            <div class="text-muted">Target: Immediate</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Total Processing Time</div>
                            </div>
                            <div class="h1 mb-3">
                                {{ $slaMetrics['total_processing_time'] ?? 'N/A' }}
                                <small class="text-muted">hours</small>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-primary" style="width: 100%"></div>
                            </div>
                            <div class="text-muted">End-to-end</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Circulating Forms -->
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Currently Circulating Forms</h3>
                        </div>
                        <div class="card-body">
                            @if($circulatingForms->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>Form Number</th>
                                                <th>Form Name</th>
                                                <th>Issued To</th>
                                                <th>Issue Date</th>
                                                <th>Status</th>
                                                <th>Circulation Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($circulatingForms as $form)
                                                <tr>
                                                    <td>{{ $form->form_number }}</td>
                                                    <td>{{ $form->form_name }}</td>
                                                    <td>{{ $form->issuedTo->name }}</td>
                                                    <td>{{ $form->issue_date }}</td>
                                                    <td>
                                                        <span class="badge bg-info text-white">{{ $form->status->label() }}</span>
                                                    </td>
                                                    <td>
                                                        {{ $form->issued_at->diffForHumans() }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="empty">
                                    <div class="empty-icon">
                                        <i class="far fa-file-alt"></i>&nbsp;
                                    </div>
                                    <p class="empty-title">No circulating forms</p>
                                    <p class="empty-subtitle text-muted">
                                        All forms have been returned or are not yet issued.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overdue Requests -->
            @if($overdueRequests->count() > 0)
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title text-warning">
                                <i class="far fa-exclamation-triangle"></i>&nbsp;
                                Overdue Requests
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>Request ID</th>
                                            <th>Requester</th>
                                            <th>Request Date</th>
                                            <th>Status</th>
                                            <th>Overdue Time</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($overdueRequests as $request)
                                            <tr>
                                                <td>{{ $request->id }}</td>
                                                <td>{{ $request->requester->name }}</td>
                                                <td>{{ formatDate($request->request_date) }}</td>
                                                <td>
                                                    <span class="badge bg-warning">{{ $request->status->label() }}</span>
                                                </td>
                                                <td>
                                                    <span class="text-warning">
                                                        {{ $request->request_date->diffForHumans() }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('form-requests.show', $request) }}" class="btn btn-sm btn-outline-primary">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
