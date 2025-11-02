@extends('layouts.app')

@section('title', 'Correspondence')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Correspondence
                    </h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('correspondences.create') }}" class="btn btn-primary">
                        <i class="far fa-envelope"></i>&nbsp;
                        Create Correspondence
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @include('layouts.alerts')

            @if(isset($pendingApprovalsCount) && $pendingApprovalsCount > 0)
            <div class="alert alert-warning alert-dismissible" role="alert">
                <div class="d-flex">
                    <div>
                        <i class="far fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h4 class="alert-title">Pending Approvals</h4>
                        <div class="text-secondary">
                            You have <strong>{{ $pendingApprovalsCount }}</strong> correspondence{{ $pendingApprovalsCount > 1 ? 's' : '' }} waiting for your approval.
                            <a href="{{ route('correspondences.index', ['status' => 'pending_approval']) }}" class="alert-link">View all pending approvals</a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">My Correspondence</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover">
                            <thead>
                                <tr>
                                    <th>Reference Number</th>
                                    <th>Template</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th class="w-1">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($instances as $instance)
                                <tr>
                                    <td>
                                        <strong>{{ $instance->instance_number }}</strong>
                                    </td>
                                    <td>
                                        {{ $instance->templateVersion->document->title }}
                                        <small class="text-muted d-block">
                                            v{{ $instance->templateVersion->version_number }}
                                        </small>
                                    </td>
                                    <td>{{ $instance->subject }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($instance->status->value) {
                                                'draft' => 'bg-secondary',
                                                'pending_approval' => 'bg-warning',
                                                'approved' => 'bg-success',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} text-white">
                                            {{ $instance->status->label() }}
                                        </span>
                                    </td>
                                    <td>{{ $instance->creator->name }}</td>
                                    <td>{{ formatDate($instance->created_at) }}</td>
                                    <td>
                                        <a href="{{ route('correspondences.show', $instance) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="far fa-eye"></i>&nbsp;
                                            View
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        No correspondence found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($instances->hasPages())
                <div class="card-footer">
                    {{ $instances->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

