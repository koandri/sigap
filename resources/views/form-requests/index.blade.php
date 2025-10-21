@extends('layouts.app')

@section('title', 'Form Requests')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Form Requests
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="{{ route('form-requests.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus"></i>
                        New Request
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Form Requests List -->
            <div class="card">
                <div class="card-body">
                    @if($formRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Requester</th>
                                        <th>Request Date</th>
                                        <th>Status</th>
                                        <th>Forms</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($formRequests as $request)
                                        <tr>
                                            <td>{{ $request->id }}</td>
                                            <td>{{ $request->requester->name }}</td>
                                            <td>{{ $request->request_date->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <span class="badge {{ $request->isPending() ? 'bg-warning' : ($request->isCompleted() ? 'bg-success' : 'bg-info') }}">
                                                    {{ $request->status->label() }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-muted">
                                                    {{ $request->total_forms }} forms, {{ $request->total_quantity }} copies
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-list">
                                                    <a href="{{ route('form-requests.show', $request) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="ti ti-eye"></i>
                                                        View
                                                    </a>
                                                    @if($request->isPending() && auth()->user()->hasRole(['Super Admin', 'Owner', 'Document Control']))
                                                        <form method="POST" action="{{ route('form-requests.acknowledge', $request) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                                <i class="ti ti-check"></i>
                                                                Acknowledge
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty">
                            <div class="empty-icon">
                                <i class="ti ti-file-text"></i>
                            </div>
                            <p class="empty-title">No form requests found</p>
                            <p class="empty-subtitle text-muted">
                                Get started by creating a new form request.
                            </p>
                            <div class="empty-action">
                                <a href="{{ route('form-requests.create') }}" class="btn btn-primary">
                                    <i class="ti ti-plus"></i>
                                    Create Request
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
