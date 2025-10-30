@extends('layouts.app')

@section('title', 'Printed Form Details')

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
                        Form Number: {{ $printedForm->form_number }}
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="{{ route('printed-forms.track', $printedForm->id) }}" class="btn btn-primary">
                        <i class="far fa-qrcode"></i>
                        Track Form
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <!-- Form Information -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Form Information</h3>
                        </div>
                        <div class="card-body">
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
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Issued To</div>
                                    <div class="datagrid-content">{{ $printedForm->issuedTo->name }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Issued Date</div>
                                    <div class="datagrid-content">{{ $printedForm->issued_at->format('Y-m-d H:i') }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Status</div>
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
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Request Details -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Related Form Request</h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Request ID</div>
                                    <div class="datagrid-content">
                                        <a href="{{ route('form-requests.show', $printedForm->formRequestItem->formRequest) }}">
                                            #{{ $printedForm->formRequestItem->formRequest->id }}
                                        </a>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Requester</div>
                                    <div class="datagrid-content">{{ $printedForm->formRequestItem->formRequest->requester->name }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Request Date</div>
                                    <div class="datagrid-content">{{ $printedForm->formRequestItem->formRequest->request_date->format('Y-m-d H:i') }}</div>
                                </div>
                                @if($printedForm->returned_at)
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Returned Date</div>
                                    <div class="datagrid-content">{{ $printedForm->returned_at->format('Y-m-d H:i') }}</div>
                                </div>
                                @endif
                                @if($printedForm->received_at)
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Received Date</div>
                                    <div class="datagrid-content">{{ $printedForm->received_at->format('Y-m-d H:i') }}</div>
                                </div>
                                @endif
                                @if($printedForm->scanned_at)
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Scanned Date</div>
                                    <div class="datagrid-content">{{ $printedForm->scanned_at->format('Y-m-d H:i') }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($printedForm->scanned_file_path)
                <!-- Scanned Document -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Scanned Document</h3>
                        </div>
                        <div class="card-body">
                                <a href="{{ route('printed-forms.view-scanned', $printedForm->id) }}" class="btn btn-primary" target="_blank">
                                <i class="far fa-file-pdf"></i>
                                View Scanned Document
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="btn-list">
                                @if($printedForm->isInCirculation() && auth()->id() == $printedForm->issued_to)
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#returnModal">
                                    <i class="far fa-undo"></i>
                                    Return Form
                                </button>
                                @endif

                                @can('process', $printedForm->formRequestItem->formRequest)
                                    @if($printedForm->isReturned() && !$printedForm->isReceived())
                                    <form method="POST" action="{{ route('printed-forms.receive', $printedForm->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success">
                                            <i class="far fa-check"></i>
                                            Mark as Received
                                        </button>
                                    </form>
                                    @endif

                                    @if($printedForm->isReceived() && !$printedForm->scanned_file_path)
                                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                        <i class="far fa-upload"></i>
                                        Upload Scanned Form
                                    </button>
                                    @endif
                                @endcan

                                <a href="{{ route('document-versions.view', $printedForm->documentVersion->id) }}" class="btn btn-outline-primary">
                                    <i class="far fa-file"></i>
                                    View Original Document
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Return Form Modal -->
<div class="modal modal-blur fade" id="returnModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('printed-forms.return', $printedForm->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Return Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Return Status</label>
                        <select name="status" class="form-select" required>
                            <option value="">Select status...</option>
                            <option value="returned">Returned</option>
                            <option value="lost">Lost</option>
                            <option value="spoilt">Spoilt</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Scanned Form Modal -->
<div class="modal modal-blur fade" id="uploadModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('printed-forms.upload-scan', $printedForm->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload Scanned Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Scanned PDF File</label>
                        <input type="file" name="scanned_file" class="form-control" accept=".pdf" required>
                        <small class="form-hint">Maximum file size: 10MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

