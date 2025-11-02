@extends('layouts.app')

@section('title', 'Document Approvals')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Document Version Approvals
                    </h2>
                    <div class="text-muted mt-1">
                        Review and approve pending document versions
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
                            <h3 class="card-title">Pending Approvals</h3>
                        </div>
                        <div class="card-body">
                            @if($pendingApprovals->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>Document</th>
                                                <th>Version</th>
                                                <th>Approval Tier</th>
                                                <th>Created By</th>
                                                <th>Submitted</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pendingApprovals as $approval)
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold">{{ $approval->documentVersion->document->title }}</div>
                                                        <div class="text-muted small">{{ $approval->documentVersion->document->document_number }}</div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info text-white">v{{ $approval->documentVersion->version_number }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-blue-lt">
                                                            {{ $approval->approval_tier->label() }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $approval->documentVersion->creator->name }}</td>
                                                    <td>
                                                        <span title="{{ $approval->created_at->format('Y-m-d H:i:s') }}">
                                                            {{ $approval->created_at->diffForHumans() }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-list">
                                                            @can('approve', $approval)
                                                                <!-- View Document Button -->
                                                                @if($approval->documentVersion->file_path)
                                                                    <a href="{{ route('document-versions.view', $approval->documentVersion) }}" 
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
                                                                        data-bs-target="#approveModal{{ $approval->id }}">
                                                                    <i class="far fa-check"></i>&nbsp;
                                                                    Approve
                                                                </button>
                                                                
                                                                <!-- Reject Button -->
                                                                <button type="button" 
                                                                        class="btn btn-sm btn-danger" 
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#rejectModal{{ $approval->id }}">
                                                                    <i class="far fa-times"></i>&nbsp;
                                                                    Reject
                                                                </button>
                                                            @endcan
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-- Approve Modal -->
                                                <div class="modal fade" id="approveModal{{ $approval->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <form method="POST" action="{{ route('document-approvals.approve', $approval) }}">
                                                                @csrf
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Approve Document Version</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p class="mb-3">
                                                                        Are you sure you want to approve <strong>{{ $approval->documentVersion->document->title }}</strong> 
                                                                        version <strong>{{ $approval->documentVersion->version_number }}</strong>?
                                                                    </p>
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
                                                <div class="modal fade" id="rejectModal{{ $approval->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <form method="POST" action="{{ route('document-approvals.reject', $approval) }}">
                                                                @csrf
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Reject Document Version</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p class="mb-3">
                                                                        Are you sure you want to reject <strong>{{ $approval->documentVersion->document->title }}</strong> 
                                                                        version <strong>{{ $approval->documentVersion->version_number }}</strong>?
                                                                    </p>
                                                                    <div class="mb-3">
                                                                        <label class="form-label required">Reason for Rejection</label>
                                                                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                                                                  rows="3" placeholder="Please provide a reason for rejection..." required></textarea>
                                                                        @error('notes')
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

                                <!-- Pagination -->
                                <div class="mt-3">
                                    {{ $pendingApprovals->links() }}
                                </div>
                            @else
                                <div class="empty">
                                    <div class="empty-icon">
                                        <i class="far fa-check-circle"></i>&nbsp;
                                    </div>
                                    <p class="empty-title">No pending approvals</p>
                                    <p class="empty-subtitle text-muted">
                                        There are no document versions waiting for your approval at this time.
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

