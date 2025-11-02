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
                        <i class="far fa-plus"></i>&nbsp;
                        New Request
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('form-requests.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    @foreach(\App\Enums\FormRequestStatus::cases() as $status)
                                        <option value="{{ $status->value }}" {{ $filters['status'] == $status->value ? 'selected' : '' }}>
                                            {{ $status->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if($isAdmin)
                                <div class="col-md-3">
                                    <label class="form-label">Requester</label>
                                    <select name="requester" class="form-select">
                                        <option value="">All Requesters</option>
                                        @foreach($requesters as $requester)
                                            <option value="{{ $requester->id }}" {{ $filters['requester'] == $requester->id ? 'selected' : '' }}>
                                                {{ $requester->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-md-2">
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                            </div>
                            <div class="col-md-2 {{ $isAdmin ? '' : 'offset-md-3' }}">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="far fa-filter"></i>&nbsp;
                                        Filter
                                    </button>
                                    <a href="{{ route('form-requests.index') }}" class="btn btn-outline-secondary">
                                        <i class="far fa-times"></i>&nbsp;
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

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
                                            <td>{{ formatDate($request->request_date) }}</td>
                                            <td>
                                                <span class="badge {{ $request->isPending() ? 'bg-warning' : ($request->isCompleted() ? 'bg-success' : 'bg-info') }} text-white">
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
                                                        <i class="far fa-eye"></i>&nbsp;
                                                        View
                                                    </a>
                                                    @can('update', $request)
                                                        @if($request->isPending())
                                                            <a href="{{ route('form-requests.edit', $request) }}" class="btn btn-sm btn-outline-info">
                                                                <i class="far fa-edit"></i>&nbsp;
                                                                Edit
                                                            </a>
                                                        @endif
                                                    @endcan
                                                    @if($request->isPending() && auth()->user()->hasRole(['Super Admin', 'Owner', 'Document Control']))
                                                        <form method="POST" action="{{ route('form-requests.acknowledge', $request) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                                <i class="far fa-check"></i>&nbsp;
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
                        
                        @if($formRequests->hasPages())
                        <div class="mt-3">
                            {{ $formRequests->links() }}
                        </div>
                        @endif
                    @else
                        <div class="empty">
                            <div class="empty-icon">
                                <i class="far fa-file-alt"></i>&nbsp;
                            </div>
                            <p class="empty-title">No form requests found</p>
                            <p class="empty-subtitle text-muted">
                                Get started by creating a new form request.
                            </p>
                            <div class="empty-action">
                                <a href="{{ route('form-requests.create') }}" class="btn btn-primary">
                                    <i class="far fa-plus"></i>&nbsp;
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
