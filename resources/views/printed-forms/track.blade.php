@extends('layouts.app')

@section('title', 'Track Printed Form')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        <a href="{{ route('printed-forms.index') }}">Printed Forms</a>
                    </div>
                    <h2 class="page-title">
                        Track Form: {{ $printedForm->form_number }}
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="{{ route('printed-forms.show', $printedForm->id) }}" class="btn btn-outline-primary">
                        <i class="far fa-arrow-left"></i>
                        Back to Details
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <!-- QR Code -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">QR Code</h3>
                        </div>
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <div class="alert alert-info">
                                    <strong>Tracking URL:</strong><br>
                                    <a href="{{ route('printed-forms.show', $printedForm->id) }}" class="text-break">
                                        {{ route('printed-forms.show', $printedForm->id) }}
                                    </a>
                                </div>
                            </div>
                            <p class="text-muted">Use this URL to quickly access this form's details</p>
                        </div>
                    </div>
                </div>

                <!-- Form Timeline -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Form Timeline</h3>
                        </div>
                        <div class="card-body">
                            <ul class="steps steps-vertical">
                                <li class="step-item {{ $printedForm->issued_at ? 'active' : '' }}">
                                    <div class="h4 m-0">Form Issued</div>
                                    @if($printedForm->issued_at)
                                    <div class="text-muted">{{ $printedForm->issued_at->format('Y-m-d H:i') }}</div>
                                    <div class="text-muted small">Issued to: {{ $printedForm->issuedTo->name }}</div>
                                    @endif
                                </li>

                                @if($printedForm->status->value == 'circulating')
                                <li class="step-item active">
                                    <div class="h4 m-0">In Circulation</div>
                                    <div class="text-muted">Currently being used</div>
                                </li>
                                @endif

                                @if($printedForm->returned_at)
                                <li class="step-item active">
                                    <div class="h4 m-0">Form Returned</div>
                                    <div class="text-muted">{{ $printedForm->returned_at->format('Y-m-d H:i') }}</div>
                                    <div class="text-muted small">Status: {{ $printedForm->status->label() }}</div>
                                </li>
                                @endif

                                @if($printedForm->received_at)
                                <li class="step-item active">
                                    <div class="h4 m-0">Form Received</div>
                                    <div class="text-muted">{{ $printedForm->received_at->format('Y-m-d H:i') }}</div>
                                    <div class="text-muted small">Received by Document Control</div>
                                </li>
                                @endif

                                @if($printedForm->scanned_at)
                                <li class="step-item active">
                                    <div class="h4 m-0">Form Scanned</div>
                                    <div class="text-muted">{{ $printedForm->scanned_at->format('Y-m-d H:i') }}</div>
                                    <div class="text-muted small">Digital copy archived</div>
                                </li>
                                @endif

                                @if(!$printedForm->returned_at && $printedForm->status->value != 'circulating')
                                <li class="step-item">
                                    <div class="h4 m-0">Pending Return</div>
                                    <div class="text-muted">Waiting for form to be returned</div>
                                </li>
                                @endif

                                @if($printedForm->returned_at && !$printedForm->received_at)
                                <li class="step-item">
                                    <div class="h4 m-0">Pending Receipt</div>
                                    <div class="text-muted">Waiting for Document Control to receive</div>
                                </li>
                                @endif

                                @if($printedForm->received_at && !$printedForm->scanned_at && !$printedForm->isProblematic())
                                <li class="step-item">
                                    <div class="h4 m-0">Pending Scanning</div>
                                    <div class="text-muted">Waiting for document scanning</div>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Form Information -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Form Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="datagrid">
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Form Number</div>
                                            <div class="datagrid-content">
                                                <span class="text-monospace">{{ $printedForm->form_number }}</span>
                                            </div>
                                        </div>
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Document</div>
                                            <div class="datagrid-content">
                                                <div>{{ $printedForm->documentVersion->document->document_number }}</div>
                                                <div class="text-muted small">{{ $printedForm->documentVersion->document->title }}</div>
                                            </div>
                                        </div>
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Version</div>
                                            <div class="datagrid-content">{{ $printedForm->documentVersion->version }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="datagrid">
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Current Status</div>
                                            <div class="datagrid-content">
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
                                            </div>
                                        </div>
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Issued To</div>
                                            <div class="datagrid-content">{{ $printedForm->issuedTo->name }}</div>
                                        </div>
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Request ID</div>
                                            <div class="datagrid-content">
                                                <a href="{{ route('form-requests.show', $printedForm->formRequestItem->formRequest) }}">
                                                    #{{ $printedForm->formRequestItem->formRequest->id }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

