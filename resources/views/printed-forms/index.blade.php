@extends('layouts.app')

@section('title', 'Printed Forms')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Printed Forms
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('printed-forms.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    @foreach(\App\Enums\PrintedFormStatus::cases() as $status)
                                        <option value="{{ $status->value }}" {{ $filters['status'] == $status->value ? 'selected' : '' }}>
                                            {{ $status->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if($isAdmin)
                                <div class="col-md-3">
                                    <label class="form-label">Issued To</label>
                                    <select name="issued_to" class="form-select">
                                        <option value="">All Users</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ $filters['issued_to'] == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-md-2">
                                <label class="form-label">Form Number</label>
                                <input type="text" name="form_number" class="form-control" value="{{ $filters['form_number'] ?? '' }}" placeholder="Search...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                            </div>
                            <div class="col-md-12 col-lg-auto">
                                <label class="form-label d-none d-lg-block">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="far fa-filter"></i>
                                        Filter
                                    </button>
                                    <a href="{{ route('printed-forms.index') }}" class="btn btn-outline-secondary">
                                        <i class="far fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Printed Forms List -->
            <div class="card">
                <div class="card-body">
                    @if($printedForms->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Form Number</th>
                                        <th>Document</th>
                                        <th>Issued To</th>
                                        <th>Issued Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($printedForms as $printedForm)
                                        <tr>
                                            <td>
                                                <span class="text-monospace">{{ $printedForm->form_number }}</span>
                                            </td>
                                            <td>
                                                <div>{{ $printedForm->documentVersion->document->document_number }}</div>
                                                <div class="text-muted small">{{ $printedForm->documentVersion->document->title }}</div>
                                            </td>
                                            <td>{{ $printedForm->issuedTo->name }}</td>
                                            <td>{{ $printedForm->issued_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                @php
                                                    $badgeClass = match($printedForm->status->value) {
                                                        'issued', 'circulating' => 'bg-info',
                                                        'received', 'scanned' => 'bg-success',
                                                        'returned' => 'bg-warning',
                                                        'lost', 'spoilt' => 'bg-danger',
                                                        default => 'bg-secondary',
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }} text-white">
                                                    {{ $printedForm->status->label() }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('printed-forms.show', $printedForm->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="far fa-eye"></i>
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $printedForms->appends($filters)->links() }}
                        </div>
                    @else
                        <div class="empty">
                            <div class="empty-icon">
                                <i class="far fa-file-alt"></i>
                            </div>
                            <p class="empty-title">No printed forms found</p>
                            <p class="empty-subtitle text-muted">
                                Printed forms will appear here once form requests are processed.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

