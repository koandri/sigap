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
            <div class="row row-deck row-cards mb-3">
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
            <div class="row row-deck row-cards mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-6 col-sm-4 col-md-2 col-xl-auto">
                                    <a href="{{ route('documents.create') }}" class="btn btn-outline-primary w-100">
                                        <i class="far fa-plus"></i>&nbsp;
                                        New Document
                                    </a>
                                </div>
                                <div class="col-6 col-sm-4 col-md-2 col-xl-auto">
                                    <a href="{{ route('form-requests.create') }}" class="btn btn-outline-success w-100">
                                        <i class="far fa-file-alt"></i>&nbsp;
                                        Request Forms
                                    </a>
                                </div>
                                <div class="col-6 col-sm-4 col-md-2 col-xl-auto">
                                    <a href="{{ route('my-document-access') }}" class="btn btn-outline-info w-100">
                                        <i class="far fa-eye"></i>&nbsp;
                                        My Documents
                                    </a>
                                </div>
                                @php
                                    $pendingDocApprovals = auth()->user() ? 
                                        \App\Models\DocumentVersionApproval::where('status', 'pending')
                                            ->where('approver_id', auth()->id())
                                            ->count() : 0;
                                @endphp
                                <div class="col-6 col-sm-4 col-md-2 col-xl-auto">
                                    <a href="{{ route('document-approvals.index') }}" class="btn btn-outline-warning w-100 position-relative">
                                        <i class="far fa-check-double"></i>&nbsp;
                                        Document Approvals
                                        @if($pendingDocApprovals > 0)
                                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">{{ $pendingDocApprovals }}</span>
                                        @endif
                                    </a>
                                </div>
                                @can('approve', App\Models\DocumentAccessRequest::class)
                                @php
                                    $pendingAccessRequests = auth()->user() ? 
                                        \App\Models\DocumentAccessRequest::where('status', 'pending')
                                            ->count() : 0;
                                @endphp
                                <div class="col-6 col-sm-4 col-md-2 col-xl-auto">
                                    <a href="{{ route('document-access-requests.pending') }}" class="btn btn-outline-danger w-100 position-relative">
                                        <i class="far fa-user-lock"></i>&nbsp;
                                        Access Requests
                                        @if($pendingAccessRequests > 0)
                                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">{{ $pendingAccessRequests }}</span>
                                        @endif
                                    </a>
                                </div>
                                @endcan
                                <div class="col-6 col-sm-4 col-md-2 col-xl-auto">
                                    <a href="{{ route('reports.document-management.masterlist') }}" class="btn btn-outline-secondary w-100">
                                        <i class="far fa-list"></i>&nbsp;
                                        Masterlist
                                    </a>
                                </div>
                                <div class="col-6 col-sm-4 col-md-2 col-xl-auto">
                                    <a href="{{ route('reports.document-management.sla') }}" class="btn btn-outline-secondary w-100">
                                        <i class="far fa-chart-line"></i>&nbsp;
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
                            @if($recentActivities->count() > 0)
                                <span class="badge bg-blue-lt ms-auto">{{ $recentActivities->count() }} activities</span>
                            @endif
                        </div>
                        <div class="card-body p-0">
                            @if($recentActivities->count() > 0)
                                <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                                    @foreach($recentActivities as $activity)
                                        <div class="list-group-item list-group-item-action">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <div class="text-body">{{ $activity['message'] }}</div>
                                                    <div class="text-muted mt-1">
                                                        <small>
                                                            <i class="far fa-clock me-1"></i>&nbsp;
                                                            {{ \Carbon\Carbon::parse($activity['timestamp'])->diffForHumans() }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty py-5">
                                    <div class="empty-icon">
                                        <i class="far fa-chart-line"></i>&nbsp;
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
