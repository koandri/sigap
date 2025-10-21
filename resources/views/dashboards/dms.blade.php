@extends('layouts.app')

@section('title', 'Document Management System Dashboard')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Document Management System
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Statistics Cards -->
            <div class="row row-deck row-cards">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Total Documents</div>
                            </div>
                            <div class="h1 mb-3">{{ $stats['total_documents'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Pending Approvals</div>
                            </div>
                            <div class="h1 mb-3">{{ $stats['pending_approvals'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Pending Form Requests</div>
                            </div>
                            <div class="h1 mb-3">{{ $stats['pending_form_requests'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Circulating Forms</div>
                            </div>
                            <div class="h1 mb-3">{{ $stats['circulating_forms'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-6 col-sm-4 col-md-2 col-xl-auto">
                                    <a href="{{ route('documents.create') }}" class="btn btn-outline-primary w-100">
                                        <i class="ti ti-plus"></i>
                                        New Document
                                    </a>
                                </div>
                                <div class="col-6 col-sm-4 col-md-2 col-xl-auto">
                                    <a href="{{ route('form-requests.create') }}" class="btn btn-outline-success w-100">
                                        <i class="ti ti-file-text"></i>
                                        Request Forms
                                    </a>
                                </div>
                                <div class="col-6 col-sm-4 col-md-2 col-xl-auto">
                                    <a href="{{ route('my-document-access') }}" class="btn btn-outline-info w-100">
                                        <i class="ti ti-eye"></i>
                                        My Documents
                                    </a>
                                </div>
                                <div class="col-6 col-sm-4 col-md-2 col-xl-auto">
                                    <a href="{{ route('documents.masterlist') }}" class="btn btn-outline-secondary w-100">
                                        <i class="ti ti-list"></i>
                                        Masterlist
                                    </a>
                                </div>
                                <div class="col-6 col-sm-4 col-md-2 col-xl-auto">
                                    <a href="{{ route('dms-sla') }}" class="btn btn-outline-warning w-100">
                                        <i class="ti ti-chart-line"></i>
                                        SLA Report
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Activities</h3>
                        </div>
                        <div class="card-body">
                            @if($recentActivities->count() > 0)
                                <div class="timeline">
                                    @foreach($recentActivities as $activity)
                                        <div class="timeline-item">
                                            <div class="timeline-marker"></div>
                                            <div class="timeline-content">
                                                <div class="timeline-header">
                                                    <span class="timeline-time">{{ \Carbon\Carbon::parse($activity['timestamp'])->diffForHumans() }}</span>
                                                </div>
                                                <div class="timeline-body">
                                                    {{ $activity['message'] }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty">
                                    <div class="empty-icon">
                                        <i class="ti ti-activity"></i>
                                    </div>
                                    <p class="empty-title">No recent activities</p>
                                    <p class="empty-subtitle text-muted">
                                        Recent document and form activities will appear here.
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
                                <i class="ti ti-alert-triangle"></i>
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
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($overdueRequests as $request)
                                            <tr>
                                                <td>{{ $request->id }}</td>
                                                <td>{{ $request->requester->name }}</td>
                                                <td>{{ $request->request_date->format('Y-m-d H:i') }}</td>
                                                <td>
                                                    <span class="badge bg-warning">{{ $request->status->label() }}</span>
                                                </td>
                                                <td>
                                                    <span class="text-warning">
                                                        {{ $request->request_date->diffForHumans() }}
                                                    </span>
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
