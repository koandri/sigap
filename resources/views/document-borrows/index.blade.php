@extends('layouts.app')

@section('title', 'My Document Borrows')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        My Document Borrows
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    <a href="{{ route('document-borrows.create') }}" class="btn btn-primary">
                        <i class="far fa-plus"></i>&nbsp;
                        Request to Borrow
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
                    <form method="GET" action="{{ route('document-borrows.index') }}">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    @foreach($statuses as $statusOption)
                                        <option value="{{ $statusOption->value }}" {{ $status == $statusOption->value ? 'selected' : '' }}>
                                            {{ $statusOption->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="far fa-filter"></i>&nbsp;
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Borrows List -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Borrow History</h3>
                </div>
                <div class="card-body">
                    @if($borrows->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Document</th>
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
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="fw-bold">{{ $borrow->document->title }}</div>
                                                        <div class="text-muted">{{ $borrow->document->document_number }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $borrow->status->color() }}">
                                                    <i class="far {{ $borrow->status->icon() }}"></i>&nbsp;
                                                    {{ $borrow->status->label() }}
                                                </span>
                                                @if($borrow->is_overdue)
                                                    <span class="badge bg-danger ms-1">
                                                        <i class="far fa-exclamation-triangle"></i>&nbsp;
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
                                                @can('cancel', $borrow)
                                                    <form action="{{ route('document-borrows.cancel', $borrow) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this borrow request?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="far fa-times"></i>&nbsp;
                                                            Cancel
                                                        </button>
                                                    </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($borrows->hasPages())
                        <div class="mt-3">
                            {{ $borrows->links() }}
                        </div>
                        @endif
                    @else
                        <div class="empty">
                            <div class="empty-icon">
                                <i class="far fa-book"></i>
                            </div>
                            <p class="empty-title">No borrow records</p>
                            <p class="empty-subtitle text-muted">
                                You haven't borrowed any documents yet.
                            </p>
                            <div class="empty-action">
                                <a href="{{ route('document-borrows.create') }}" class="btn btn-primary">
                                    <i class="far fa-plus"></i>&nbsp;
                                    Request to Borrow
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

