@extends('layouts.app')

@section('title', 'Pending Borrow Approvals')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Pending Borrow Approvals
                    </h2>
                    <div class="text-muted">
                        Review and approve document borrow requests
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pending Requests</h3>
                    <div class="card-actions">
                        <span class="badge bg-warning">{{ $pendingBorrows->count() }} pending</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($pendingBorrows->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Document</th>
                                        <th>Requested By</th>
                                        <th>Due Date</th>
                                        <th>Requested At</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingBorrows as $borrow)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="fw-bold">{{ $borrow->document->title }}</div>
                                                        <div class="text-muted">{{ $borrow->document->document_number }}</div>
                                                        <span class="badge bg-blue-lt">{{ $borrow->document->document_type->label() }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $borrow->user->name }}</div>
                                                <div class="text-muted">{{ $borrow->user->email }}</div>
                                            </td>
                                            <td>
                                                @if($borrow->due_date)
                                                    {{ $borrow->due_date->format('d M Y') }}
                                                @else
                                                    <span class="text-muted">No due date</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $borrow->created_at->format('d M Y H:i') }}
                                                <div class="text-muted">{{ $borrow->created_at->diffForHumans() }}</div>
                                            </td>
                                            <td>
                                                @if($borrow->notes)
                                                    <span class="text-truncate" style="max-width: 150px;" title="{{ $borrow->notes }}">
                                                        {{ Str::limit($borrow->notes, 50) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('document-borrows.review', $borrow) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="far fa-eye"></i>&nbsp;
                                                        Review
                                                    </a>
                                                    <form action="{{ route('document-borrows.approve', $borrow) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this borrow request?');">
                                                            <i class="far fa-check"></i>&nbsp;
                                                            Approve
                                                        </button>
                                                    </form>
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
                                <i class="far fa-check-circle"></i>
                            </div>
                            <p class="empty-title">No pending requests</p>
                            <p class="empty-subtitle text-muted">
                                All borrow requests have been processed.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

