@extends('layouts.app')

@section('title', 'Review Borrow Request')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('document-borrows.pending') }}">Pending Approvals</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Review Request</li>
                        </ol>
                    </nav>
                    <h2 class="page-title">
                        Review Borrow Request
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-md-8">
                    <!-- Document Information -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Document Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Document Title</div>
                                    <div class="datagrid-content">{{ $borrow->document->title }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Document Number</div>
                                    <div class="datagrid-content">{{ $borrow->document->document_number }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Document Type</div>
                                    <div class="datagrid-content">
                                        <span class="badge bg-blue-lt">{{ $borrow->document->document_type->label() }}</span>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Physical Location</div>
                                    <div class="datagrid-content">{{ $borrow->document->physical_location_string }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Request Information -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Request Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Requested By</div>
                                    <div class="datagrid-content">
                                        <strong>{{ $borrow->user->name }}</strong>
                                        <div class="text-muted">{{ $borrow->user->email }}</div>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Department</div>
                                    <div class="datagrid-content">{{ $borrow->user->getDepartmentNames() ?: 'N/A' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Requested At</div>
                                    <div class="datagrid-content">
                                        {{ $borrow->created_at->format('d M Y H:i') }}
                                        <div class="text-muted">{{ $borrow->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Requested Due Date</div>
                                    <div class="datagrid-content">
                                        @if($borrow->due_date)
                                            {{ $borrow->due_date->format('d M Y') }}
                                        @else
                                            <span class="text-muted">No due date requested</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($borrow->notes)
                            <div class="mt-3">
                                <label class="form-label">Notes from Requester</label>
                                <div class="form-control-plaintext bg-light p-3 rounded">
                                    {{ $borrow->notes }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Rejection Form -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Reject Request</h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('document-borrows.reject', $borrow) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="rejection_reason" class="form-label required">Rejection Reason</label>
                                    <textarea name="rejection_reason" 
                                              id="rejection_reason" 
                                              class="form-control @error('rejection_reason') is-invalid @enderror" 
                                              rows="3" 
                                              placeholder="Please provide a reason for rejecting this request..."
                                              required>{{ old('rejection_reason') }}</textarea>
                                    @error('rejection_reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this request?');">
                                    <i class="far fa-times"></i>&nbsp;
                                    Reject Request
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Quick Actions -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('document-borrows.approve', $borrow) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 mb-2" onclick="return confirm('Approve this borrow request?');">
                                    <i class="far fa-check"></i>&nbsp;
                                    Approve Request
                                </button>
                            </form>
                            <a href="{{ route('document-borrows.pending') }}" class="btn btn-secondary w-100">
                                <i class="far fa-arrow-left"></i>&nbsp;
                                Back to Pending List
                            </a>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Current Status</h3>
                        </div>
                        <div class="card-body text-center">
                            <span class="badge bg-{{ $borrow->status->color() }} fs-4 p-3">
                                <i class="far {{ $borrow->status->icon() }}"></i>&nbsp;
                                {{ $borrow->status->label() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

