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
                                    <div class="datagrid-content">v{{ $printedForm->documentVersion->version_number }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Issued To</div>
                                    <div class="datagrid-content">{{ $printedForm->issuedTo->name }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Issued Date</div>
                                    <div class="datagrid-content">{{ formatDate($printedForm->issued_at) }}</div>
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
                                    <div class="datagrid-content">{{ formatDate($printedForm->formRequestItem->formRequest->request_date) }}</div>
                                </div>
                                @if($printedForm->returned_at)
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Returned Date</div>
                                    <div class="datagrid-content">{{ formatDate($printedForm->returned_at) }}</div>
                                </div>
                                @endif
                                @if($printedForm->physical_location)
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Physical Location</div>
                                    <div class="datagrid-content">{{ $printedForm->physical_location_string }}</div>
                                </div>
                                @endif
                                @if($printedForm->received_at)
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Received Date</div>
                                    <div class="datagrid-content">{{ formatDate($printedForm->received_at) }}</div>
                                </div>
                                @endif
                                @if($printedForm->scanned_at)
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Scanned Date</div>
                                    <div class="datagrid-content">{{ formatDate($printedForm->scanned_at) }}</div>
                                </div>
                                @endif
                                @if($printedForm->notes && $printedForm->isProblematic())
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Notes</div>
                                    <div class="datagrid-content">{{ $printedForm->notes }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Timeline -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Form Timeline</h3>
                        </div>
                        <div class="card-body">
                            @php
                                // Determine the latest status step for highlighting (only ONE step should be active)
                                // Priority: scanned > received > returned > circulating > issued
                                $activeStep = null;
                                if ($printedForm->scanned_at) {
                                    $activeStep = 'scanned';
                                } elseif ($printedForm->received_at) {
                                    $activeStep = 'received';
                                } elseif ($printedForm->returned_at) {
                                    $activeStep = 'returned';
                                } elseif ($printedForm->status->value == 'circulating') {
                                    $activeStep = 'circulating';
                                } elseif ($printedForm->issued_at) {
                                    $activeStep = 'issued';
                                }
                            @endphp
                            <div class="mb-3">
                                <span class="badge bg-primary text-white">Timeline: Oldest → Latest</span>
                                <span class="badge bg-success text-white ms-2">Current Status Highlighted</span>
                            </div>
                            <ul class="steps steps-vertical">
                                <li class="step-item {{ $activeStep == 'issued' ? 'active' : '' }}">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <div class="h4 m-0">Form Issued</div>
                                        @if($activeStep == 'issued')
                                        <span class="badge bg-success text-white">Current Status</span>
                                        @endif
                                    </div>
                                    @if($printedForm->issued_at)
                                    <div class="text-muted">{{ formatDate($printedForm->issued_at) }}</div>
                                    <div class="text-muted small">Issued to: {{ $printedForm->issuedTo->name }}</div>
                                    @endif
                                </li>

                                @if($printedForm->status->value == 'issued')
                                <li class="step-item">
                                    <div class="h4 m-0">Pending Collection</div>
                                    <div class="text-muted">Form ready for collection from Document Control</div>
                                </li>
                                @endif

                                @if($printedForm->status->value == 'circulating')
                                <li class="step-item {{ $activeStep == 'circulating' ? 'active' : '' }}">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <div class="h4 m-0">In Circulation</div>
                                        @if($activeStep == 'circulating')
                                        <span class="badge bg-success">Current Status</span>
                                        @endif
                                    </div>
                                    <div class="text-muted">Form collected and currently in use</div>
                                </li>
                                @endif

                                @if($printedForm->returned_at)
                                <li class="step-item {{ $activeStep == 'returned' ? 'active' : '' }}">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <div class="h4 m-0">
                                            @if($printedForm->status->value === 'lost')
                                                Form Lost
                                            @elseif($printedForm->status->value === 'spoilt')
                                                Form Spoilt
                                            @else
                                                Form Returned
                                            @endif
                                        </div>
                                        @if($activeStep == 'returned')
                                        <span class="badge bg-success text-white">Current Status</span>
                                        @endif
                                    </div>
                                    <div class="text-muted">{{ formatDate($printedForm->returned_at) }}</div>
                                    @if($printedForm->status->value !== 'returned')
                                    <div class="text-muted small">{{ $printedForm->status->label() }}</div>
                                    @endif
                                    @if($printedForm->notes && $printedForm->isProblematic())
                                    <div class="text-muted small mt-2">
                                        <strong>Notes:</strong> {{ $printedForm->notes }}
                                    </div>
                                    @endif
                                </li>
                                @elseif($printedForm->status->value == 'circulating')
                                <li class="step-item">
                                    <div class="h4 m-0">Pending Return</div>
                                    <div class="text-muted">Waiting for form to be returned</div>
                                </li>
                                @endif

                                @if($printedForm->received_at)
                                <li class="step-item {{ $activeStep == 'received' ? 'active' : '' }}">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <div class="h4 m-0">Form Received</div>
                                        @if($activeStep == 'received')
                                        <span class="badge bg-success text-white">Current Status</span>
                                        @endif
                                    </div>
                                    <div class="text-muted">{{ formatDate($printedForm->received_at) }}</div>
                                    <div class="text-muted small">Received by Document Control</div>
                                </li>
                                @endif

                                @if($printedForm->scanned_at)
                                <li class="step-item {{ $activeStep == 'scanned' ? 'active' : '' }}">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <div class="h4 m-0">Form Scanned</div>
                                        @if($activeStep == 'scanned')
                                        <span class="badge bg-success">Current Status</span>
                                        @endif
                                    </div>
                                    <div class="text-muted">{{ formatDate($printedForm->scanned_at) }}</div>
                                    <div class="text-muted small">Digital copy archived</div>
                                </li>
                                @endif

                                @if($printedForm->returned_at && !$printedForm->received_at && !$printedForm->isProblematic())
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

                @if($printedForm->scanned_file_path)
                <!-- Scanned Document -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Scanned Document</h3>
                        </div>
                        <div class="card-body">
                                <a href="{{ route('printed-forms.view-scanned', $printedForm->id) }}" class="btn btn-primary" target="_blank">
                                <i class="far fa-file-pdf"></i>&nbsp;
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
                                @can('returnForm', $printedForm)
                                    @if($printedForm->status->value === 'circulating')
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#returnModal">
                                        <i class="far fa-undo"></i>&nbsp;
                                        Return Form
                                    </button>
                                    @endif
                                @endcan

                                @can('process', $printedForm->formRequestItem->formRequest)
                                    @if($printedForm->isReturned() && !$printedForm->isReceived() && !$printedForm->isProblematic())
                                    <form method="POST" action="{{ route('printed-forms.receive', $printedForm->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success">
                                            <i class="far fa-check"></i>&nbsp;
                                            Mark as Received
                                        </button>
                                    </form>
                                    @endif

                                    @if($printedForm->isReceived() && !$printedForm->scanned_file_path)
                                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                        <i class="far fa-upload"></i>&nbsp;
                                        Upload Scanned Form
                                    </button>
                                    @endif

                                    @if($printedForm->scanned_file_path)
                                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#locationModal">
                                        <i class="far fa-map-marker-alt"></i>&nbsp;
                                        Update Physical Location
                                    </button>
                                    @endif
                                @endcan

                                <a href="{{ route('document-versions.view', $printedForm->documentVersion->id) }}" class="btn btn-outline-primary">
                                    <i class="far fa-file"></i>&nbsp;
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
                        <select name="status" id="returnStatusSelect" class="form-select" required>
                            <option value="returned">Returned</option>
                            <option value="lost">Lost</option>
                            <option value="spoilt">Spoilt</option>
                        </select>
                        <small class="form-hint">Select the status when returning this form</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" id="returnNotesLabel">Notes</label>
                        <textarea name="notes" id="returnNotesTextarea" class="form-control" rows="3" placeholder="Enter notes..."></textarea>
                        <small class="form-hint" id="returnNotesHint">Optional notes</small>
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

<!-- Update Physical Location Modal -->
<div class="modal modal-blur fade" id="locationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('printed-forms.update-location', $printedForm->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Update Physical Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Room Number</label>
                        <input type="text" name="physical_location[room_no]" class="form-control" 
                               value="{{ old('physical_location.room_no', $printedForm->physical_location['room_no'] ?? '') }}" 
                               placeholder="e.g., R001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cabinet Number</label>
                        <input type="text" name="physical_location[cabinet_no]" class="form-control" 
                               value="{{ old('physical_location.cabinet_no', $printedForm->physical_location['cabinet_no'] ?? '') }}" 
                               placeholder="e.g., C001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Shelf Number</label>
                        <input type="text" name="physical_location[shelf_no]" class="form-control" 
                               value="{{ old('physical_location.shelf_no', $printedForm->physical_location['shelf_no'] ?? '') }}" 
                               placeholder="e.g., S001">
                    </div>
                    <small class="form-hint">All fields are optional. Leave empty to clear location.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Location</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('returnStatusSelect');
    const notesTextarea = document.getElementById('returnNotesTextarea');
    const notesLabel = document.getElementById('returnNotesLabel');
    const notesHint = document.getElementById('returnNotesHint');
    const returnForm = document.querySelector('#returnModal form');
    
    function toggleNotesRequirement() {
        if (statusSelect && notesTextarea && notesLabel && notesHint) {
            const status = statusSelect.value;
            const isRequired = status === 'lost' || status === 'spoilt';
            
            notesTextarea.required = isRequired;
            if (isRequired) {
                notesLabel.classList.add('required');
                notesHint.textContent = 'Required: Please provide reason for loss or damage';
                notesHint.classList.remove('text-muted');
                notesHint.classList.add('text-danger');
            } else {
                notesLabel.classList.remove('required');
                notesHint.textContent = 'Optional notes';
                notesHint.classList.remove('text-danger');
                notesHint.classList.add('text-muted');
            }
        }
    }
    
    if (statusSelect) {
        statusSelect.addEventListener('change', toggleNotesRequirement);
        toggleNotesRequirement(); // Initialize on page load
    }
    
    // Validate notes on form submission
    if (returnForm) {
        returnForm.addEventListener('submit', function(e) {
            const status = statusSelect.value;
            const notes = notesTextarea.value.trim();
            if ((status === 'lost' || status === 'spoilt') && !notes) {
                e.preventDefault();
                alert('Notes are required when marking form as Lost or Spoilt. Please provide a reason.');
                notesTextarea.focus();
                return false;
            }
        });
    }
});
</script>
@endpush
@endsection

