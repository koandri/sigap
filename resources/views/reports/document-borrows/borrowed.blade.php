@extends('layouts.app')

@section('title', 'Borrowed Documents Report')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dms.dashboard') }}">DMS</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Borrowed Documents Report</li>
                        </ol>
                    </nav>
                    <h2 class="page-title">
                        Borrowed Documents Report
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
                            <div class="subheader">Currently Borrowed</div>
                            <div class="h1 mb-0">{{ $stats['total_borrowed'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card {{ $stats['total_overdue'] > 0 ? 'border-danger' : '' }}">
                        <div class="card-body">
                            <div class="subheader {{ $stats['total_overdue'] > 0 ? 'text-danger' : '' }}">Overdue</div>
                            <div class="h1 mb-0 {{ $stats['total_overdue'] > 0 ? 'text-danger' : '' }}">{{ $stats['total_overdue'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Pending Approvals</div>
                            <div class="h1 mb-0">{{ $stats['pending_approvals'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card {{ $stats['due_soon'] > 0 ? 'border-warning' : '' }}">
                        <div class="card-body">
                            <div class="subheader {{ $stats['due_soon'] > 0 ? 'text-warning' : '' }}">Due Soon (1 day)</div>
                            <div class="h1 mb-0 {{ $stats['due_soon'] > 0 ? 'text-warning' : '' }}">{{ $stats['due_soon'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.dms.borrowed-documents') }}">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="checked_out" {{ $filters['status'] == 'checked_out' ? 'selected' : '' }}>Currently Checked Out</option>
                                    <option value="all_active" {{ $filters['status'] == 'all_active' ? 'selected' : '' }}>All Active (Pending/Approved/Checked Out)</option>
                                    <option value="returned" {{ $filters['status'] == 'returned' ? 'selected' : '' }}>Returned</option>
                                    <option value="all" {{ $filters['status'] == 'all' ? 'selected' : '' }}>All Records</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Search by document or borrower..." value="{{ $filters['search'] }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="far fa-search"></i>&nbsp;
                                    Filter
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('reports.dms.borrowed-documents') }}" class="btn btn-secondary w-100">
                                    <i class="far fa-undo"></i>&nbsp;
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Report Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Borrowed Documents</h3>
                    <div class="card-actions">
                        <span class="badge bg-blue-lt">{{ $borrows->total() }} records</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($borrows->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Document</th>
                                        <th>Borrower</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                        <th>Checkout Date</th>
                                        <th>Returned Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($borrows as $borrow)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $borrow->document->title }}</div>
                                                <div class="text-muted">{{ $borrow->document->document_number }}</div>
                                                <span class="badge bg-blue-lt">{{ $borrow->document->document_type->label() }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $borrow->user->name }}</div>
                                                <div class="text-muted">{{ $borrow->user->email }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $borrow->status->color() }}">
                                                    <i class="far {{ $borrow->status->icon() }}"></i>&nbsp;
                                                    {{ $borrow->status->label() }}
                                                </span>
                                                @if($borrow->is_overdue)
                                                    <span class="badge bg-danger ms-1">
                                                        {{ $borrow->days_overdue }} days overdue
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($borrow->due_date)
                                                    {{ $borrow->due_date->format('d M Y') }}
                                                @else
                                                    <span class="text-muted">No due date</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($borrow->checkout_at)
                                                    {{ $borrow->checkout_at->format('d M Y H:i') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($borrow->returned_at)
                                                    {{ $borrow->returned_at->format('d M Y H:i') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('document-borrows.show', $borrow) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="far fa-eye"></i>&nbsp;
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($borrows->hasPages())
                        <div class="mt-3">
                            {{ $borrows->appends($filters)->links() }}
                        </div>
                        @endif
                    @else
                        <div class="empty">
                            <div class="empty-icon">
                                <i class="far fa-book"></i>
                            </div>
                            <p class="empty-title">No records found</p>
                            <p class="empty-subtitle text-muted">
                                No borrowed documents match your filter criteria.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

