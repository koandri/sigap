@extends('layouts.app')

@section('title', 'Overdue Documents Report')

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
                            <li class="breadcrumb-item active" aria-current="page">Overdue Documents Report</li>
                        </ol>
                    </nav>
                    <h2 class="page-title text-danger">
                        <i class="far fa-exclamation-triangle"></i>&nbsp;
                        Overdue Documents Report
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
                    <div class="card border-danger">
                        <div class="card-body">
                            <div class="subheader text-danger">Total Overdue</div>
                            <div class="h1 mb-0 text-danger">{{ $stats['total_overdue'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Currently Borrowed</div>
                            <div class="h1 mb-0">{{ $stats['total_borrowed'] }}</div>
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
                    <form method="GET" action="{{ route('reports.dms.overdue-documents') }}">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Search by document or borrower..." value="{{ $filters['search'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="far fa-search"></i>&nbsp;
                                    Search
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('reports.dms.overdue-documents') }}" class="btn btn-secondary w-100">
                                    <i class="far fa-undo"></i>&nbsp;
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Report Table -->
            <div class="card border-danger">
                <div class="card-header">
                    <h3 class="card-title text-danger">
                        <i class="far fa-exclamation-triangle"></i>&nbsp;
                        Overdue Documents
                    </h3>
                    <div class="card-actions">
                        <span class="badge bg-danger">{{ $borrows->total() }} overdue</span>
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
                                        <th>Contact</th>
                                        <th>Due Date</th>
                                        <th>Days Overdue</th>
                                        <th>Checkout Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($borrows as $borrow)
                                        <tr class="table-danger">
                                            <td>
                                                <div class="fw-bold">{{ $borrow->document->title }}</div>
                                                <div class="text-muted">{{ $borrow->document->document_number }}</div>
                                                <span class="badge bg-blue-lt">{{ $borrow->document->document_type->label() }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $borrow->user->name }}</div>
                                                <div class="text-muted">{{ $borrow->user->getDepartmentNames() ?: 'N/A' }}</div>
                                            </td>
                                            <td>
                                                <div>{{ $borrow->user->email }}</div>
                                                @if($borrow->user->mobilephone_no)
                                                    <div class="text-muted">{{ $borrow->user->mobilephone_no }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-danger fw-bold">
                                                    {{ $borrow->due_date->format('d M Y') }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger fs-5">
                                                    {{ $borrow->days_overdue }} days
                                                </span>
                                            </td>
                                            <td>
                                                {{ $borrow->checkout_at->format('d M Y H:i') }}
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('document-borrows.show', $borrow) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="far fa-eye"></i>&nbsp;
                                                        View
                                                    </a>
                                                    @can('return', $borrow)
                                                        <form action="{{ route('document-borrows.return', $borrow) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mark this document as returned?');">
                                                                <i class="far fa-check"></i>&nbsp;
                                                                Return
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
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
                            <div class="empty-icon text-success">
                                <i class="far fa-check-circle"></i>
                            </div>
                            <p class="empty-title text-success">No overdue documents!</p>
                            <p class="empty-subtitle text-muted">
                                All borrowed documents are within their due dates.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

