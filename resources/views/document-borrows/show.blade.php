@extends('layouts.app')

@section('title', 'Borrow Details')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('document-borrows.index') }}">My Borrows</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Borrow Details</li>
                        </ol>
                    </nav>
                    <h2 class="page-title">
                        Borrow Details
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    @can('checkout', $borrow)
                        <form action="{{ route('document-borrows.checkout', $borrow) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Mark this document as checked out?');">
                                <i class="far fa-book-reader"></i>&nbsp;
                                Mark as Checked Out
                            </button>
                        </form>
                    @endcan
                    @can('return', $borrow)
                        <form action="{{ route('document-borrows.return', $borrow) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Mark this document as returned?');">
                                <i class="far fa-check-circle"></i>&nbsp;
                                Mark as Returned
                            </button>
                        </form>
                    @endcan
                    @can('cancel', $borrow)
                        <form action="{{ route('document-borrows.cancel', $borrow) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to cancel this borrow request?');">
                                <i class="far fa-times"></i>&nbsp;
                                Cancel Request
                            </button>
                        </form>
                    @endcan
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

                    <!-- Borrow Timeline -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Borrow Timeline</h3>
                        </div>
                        <div class="card-body">
                            <ul class="timeline">
                                <li class="timeline-event">
                                    <div class="timeline-event-icon bg-primary">
                                        <i class="far fa-paper-plane"></i>
                                    </div>
                                    <div class="card timeline-event-card">
                                        <div class="card-body">
                                            <div class="text-muted float-end">{{ $borrow->created_at->format('d/m/Y H:i') }}</div>
                                            <h4>Request Submitted</h4>
                                            <p class="text-muted mb-0">
                                                Requested by {{ $borrow->user->name }}
                                                @if($borrow->notes)
                                                    <br><small>Notes: {{ $borrow->notes }}</small>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </li>

                                @if($borrow->approved_at)
                                <li class="timeline-event">
                                    <div class="timeline-event-icon bg-{{ $borrow->isRejected() ? 'danger' : 'success' }}">
                                        <i class="far fa-{{ $borrow->isRejected() ? 'times' : 'check' }}"></i>
                                    </div>
                                    <div class="card timeline-event-card">
                                        <div class="card-body">
                                            <div class="text-muted float-end">{{ $borrow->approved_at->format('d/m/Y H:i') }}</div>
                                            <h4>{{ $borrow->isRejected() ? 'Request Rejected' : 'Request Approved' }}</h4>
                                            <p class="text-muted mb-0">
                                                By {{ $borrow->approver?->name ?? 'System' }}
                                                @if($borrow->rejection_reason)
                                                    <br><small class="text-danger">Reason: {{ $borrow->rejection_reason }}</small>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </li>
                                @endif

                                @if($borrow->checkout_at)
                                <li class="timeline-event">
                                    <div class="timeline-event-icon bg-info">
                                        <i class="far fa-book-reader"></i>
                                    </div>
                                    <div class="card timeline-event-card">
                                        <div class="card-body">
                                            <div class="text-muted float-end">{{ $borrow->checkout_at->format('d/m/Y H:i') }}</div>
                                            <h4>Document Checked Out</h4>
                                            <p class="text-muted mb-0">
                                                Physical document collected
                                            </p>
                                        </div>
                                    </div>
                                </li>
                                @endif

                                @if($borrow->returned_at)
                                <li class="timeline-event">
                                    <div class="timeline-event-icon bg-success">
                                        <i class="far fa-check-circle"></i>
                                    </div>
                                    <div class="card timeline-event-card">
                                        <div class="card-body">
                                            <div class="text-muted float-end">{{ $borrow->returned_at->format('d/m/Y H:i') }}</div>
                                            <h4>Document Returned</h4>
                                            <p class="text-muted mb-0">
                                                Physical document returned successfully
                                            </p>
                                        </div>
                                    </div>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Status Card -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Status</h3>
                        </div>
                        <div class="card-body text-center">
                            <span class="badge bg-{{ $borrow->status->color() }} fs-4 p-3">
                                <i class="far {{ $borrow->status->icon() }}"></i>&nbsp;
                                {{ $borrow->status->label() }}
                            </span>

                            @if($borrow->is_overdue)
                                <div class="alert alert-danger mt-3">
                                    <i class="far fa-exclamation-triangle"></i>&nbsp;
                                    <strong>OVERDUE!</strong><br>
                                    This document is {{ $borrow->days_overdue }} days overdue.
                                </div>
                            @elseif($borrow->isCheckedOut() && $borrow->days_until_due !== null)
                                <div class="alert alert-{{ $borrow->days_until_due <= 1 ? 'warning' : 'info' }} mt-3">
                                    <i class="far fa-clock"></i>&nbsp;
                                    @if($borrow->days_until_due == 0)
                                        <strong>Due today!</strong>
                                    @elseif($borrow->days_until_due == 1)
                                        <strong>Due tomorrow!</strong>
                                    @else
                                        Due in {{ $borrow->days_until_due }} days
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Borrow Details -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Borrow Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Borrower</label>
                                <div class="form-control-plaintext">{{ $borrow->user->name }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Due Date</label>
                                <div class="form-control-plaintext">
                                    @if($borrow->due_date)
                                        {{ $borrow->due_date->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted">No due date</span>
                                    @endif
                                </div>
                            </div>
                            @if($borrow->approver)
                            <div class="mb-3">
                                <label class="form-label">Approved/Rejected By</label>
                                <div class="form-control-plaintext">{{ $borrow->approver->name }}</div>
                            </div>
                            @endif
                            @if($borrow->notes)
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <div class="form-control-plaintext">{{ $borrow->notes }}</div>
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

