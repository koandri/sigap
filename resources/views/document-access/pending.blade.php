@extends('layouts.app')

@section('title', 'Pending Document Access Requests')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Pending Document Access Requests
                    </h2>
                    <div class="text-muted mt-1">
                        Review and approve/reject pending document access requests
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @include('layouts.alerts')
            
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Pending Requests</h3>
                        </div>
                        <div class="card-body">
                            @if($pendingRequests->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>Requester</th>
                                                <th>Document</th>
                                                <th>Version</th>
                                                <th>Requested Access Type</th>
                                                <th>Requested Expiry</th>
                                                <th>Requested At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pendingRequests as $request)
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold">{{ $request->user->name }}</div>
                                                        <div class="text-muted small">{{ $request->user->email }}</div>
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold">{{ $request->documentVersion->document->title }}</div>
                                                        <div class="text-muted small">{{ $request->documentVersion->document->document_number }}</div>
                                                        <div class="text-muted small">
                                                            <span class="badge bg-blue-lt">{{ $request->documentVersion->document->document_type->label() }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info text-white">v{{ $request->documentVersion->version_number }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary text-white">{{ $request->access_type->label() }}</span>
                                                    </td>
                                                    <td>
                                                        @if($request->requested_expiry_date)
                                                            <span title="{{ $request->requested_expiry_date->format('Y-m-d H:i:s') }}">
                                                                {{ $request->requested_expiry_date->format('Y-m-d H:i') }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">No expiry</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span title="{{ $request->requested_at->format('Y-m-d H:i:s') }}">
                                                            {{ $request->requested_at->diffForHumans() }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-list">
                                                            @can('approve', $request)
                                                                <!-- View Document Button -->
                                                                @if($request->documentVersion->file_path)
                                                                    <a href="{{ route('document-versions.view', $request->documentVersion) }}" 
                                                                       class="btn btn-sm btn-outline-primary" 
                                                                       target="_blank"
                                                                       title="View Document">
                                                                        <i class="far fa-eye"></i>&nbsp;
                                                                        View
                                                                    </a>
                                                                @endif
                                                                
                                                                <!-- Approve Button -->
                                                                <button type="button" 
                                                                        class="btn btn-sm btn-success" 
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#approveModal{{ $request->id }}">
                                                                    <i class="far fa-check"></i>&nbsp;
                                                                    Approve
                                                                </button>
                                                                
                                                                <!-- Reject Button -->
                                                                <button type="button" 
                                                                        class="btn btn-sm btn-danger" 
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#rejectModal{{ $request->id }}">
                                                                    <i class="far fa-times"></i>&nbsp;
                                                                    Reject
                                                                </button>
                                                            @endcan
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-- Approve Modal -->
                                                <div class="modal fade" id="approveModal{{ $request->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <form method="POST" action="{{ route('document-access-requests.approve', $request) }}">
                                                                @csrf
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Approve Document Access Request</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p class="mb-3">
                                                                        Approve access request for <strong>{{ $request->user->name }}</strong> 
                                                                        to access <strong>{{ $request->documentVersion->document->title }}</strong>?
                                                                    </p>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label required">Access Type</label>
                                                                        <select name="approved_access_type" class="form-select @error('approved_access_type') is-invalid @enderror" required>
                                                                            @foreach(\App\Enums\AccessType::cases() as $accessType)
                                                                                <option value="{{ $accessType->value }}" 
                                                                                        {{ old('approved_access_type', $request->access_type->value) == $accessType->value ? 'selected' : '' }}>
                                                                                    {{ $accessType->label() }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('approved_access_type')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label">Expiry Date (Optional)</label>
                                                                        <input type="datetime-local" 
                                                                               name="approved_expiry_date" 
                                                                               class="form-control @error('approved_expiry_date') is-invalid @enderror"
                                                                               value="{{ old('approved_expiry_date', $request->requested_expiry_date ? $request->requested_expiry_date->format('Y-m-d\TH:i') : '') }}"
                                                                               min="{{ now()->format('Y-m-d\TH:i') }}">
                                                                        @error('approved_expiry_date')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                        <div class="form-hint">
                                                                            Leave empty for no expiry. The approved expiry can differ from the requested expiry.
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label">Notes (Optional)</label>
                                                                        <textarea name="notes" class="form-control" rows="3" placeholder="Add any notes..."></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-success">
                                                                        <i class="far fa-check"></i>&nbsp;
                                                                        Approve
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Reject Modal -->
                                                <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <form method="POST" action="{{ route('document-access-requests.reject', $request) }}">
                                                                @csrf
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Reject Document Access Request</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p class="mb-3">
                                                                        Are you sure you want to reject the access request from <strong>{{ $request->user->name }}</strong> 
                                                                        for <strong>{{ $request->documentVersion->document->title }}</strong>?
                                                                    </p>
                                                                    <div class="mb-3">
                                                                        <label class="form-label required">Reason for Rejection</label>
                                                                        <textarea name="reason" 
                                                                                  class="form-control @error('reason') is-invalid @enderror" 
                                                                                  rows="3" 
                                                                                  placeholder="Please provide a reason for rejection..." 
                                                                                  required></textarea>
                                                                        @error('reason')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-danger">
                                                                        <i class="far fa-times"></i>&nbsp;
                                                                        Reject
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="empty">
                                    <div class="empty-icon">
                                        <i class="far fa-check-circle"></i>&nbsp;
                                    </div>
                                    <p class="empty-title">No pending requests</p>
                                    <p class="empty-subtitle text-muted">
                                        There are no document access requests waiting for your approval at this time.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection





















