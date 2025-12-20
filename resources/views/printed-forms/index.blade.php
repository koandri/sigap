@extends('layouts.app')

@section('title', 'Printed Forms')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/tabler/libs/tom-select/dist/css/tom-select.bootstrap5.min.css') }}">
<style>
    .ts-dropdown {
        background-color: #ffffff !important;
        border: 1px solid #e0e0e0 !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
    }
    .ts-dropdown .option {
        background-color: #ffffff;
    }
    .ts-dropdown .option:hover,
    .ts-dropdown .option.active {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
    .ts-dropdown .option.selected {
        background-color: #0d6efd !important;
        color: #fff !important;
    }
    #scanned-forms-container .badge {
        display: inline-flex;
        align-items: center;
    }
    #scanned-forms-container .btn-close {
        padding: 0;
        width: 0.75rem;
        height: 0.75rem;
        background-size: 0.75rem;
    }
</style>
@endpush

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
            <form method="GET" action="{{ route('printed-forms.index') }}" id="filterForm" class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Filters</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3 row">
                        <label class="col-3 col-form-label required">Status</label>
                        <div class="col">
                            <select name="status[]" id="status-select" class="form-select" multiple>
                                @foreach(\App\Enums\PrintedFormStatus::cases() as $status)
                                    <option value="{{ $status->value }}" {{ in_array($status->value, is_array($filters['status'] ?? []) ? $filters['status'] : []) ? 'selected' : '' }}>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @if($isAdmin)                            
                    <div class="mb-3 row">
                        <label class="col-3 col-form-label required">Issued To</label>
                        <div class="col">
                            <select name="issued_to[]" id="issued-to-select" class="form-select" multiple>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ in_array($user->id, is_array($filters['issued_to'] ?? []) ? $filters['issued_to'] : []) ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif
                    <div class="mb-3 row">
                        <label class="col-3 col-form-label required">Form No</label>
                        <div class="col">
                            <input type="text" name="form_number" id="form-number-input" class="form-control" value="{{ $filters['form_number'] ?? '' }}" placeholder="Type or scan form number(s)...">
                            <input type="hidden" name="form_numbers" id="form-numbers-hidden" value="{{ $filters['form_numbers'] ?? '' }}">
                            <small class="form-hint">
                                <i class="far fa-barcode"></i>&nbsp;
                                You can scan barcodes or type multiple form numbers. Separate with spaces, commas, or press Enter. Example: <code>FR-001 FR-002 FR-003</code>
                            </small>
                            <div id="scanned-forms-container" class="mt-2"></div>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-3 col-form-label required">Date From</label>
                        <div class="col">
                            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-3 col-form-label required">Date To</label>
                        <div class="col">
                            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="far fa-filter"></i>&nbsp;Filter
                    </button>
                    <a href="{{ route('printed-forms.index') }}" class="btn btn-outline-secondary">
                        <i class="far fa-times"></i>&nbsp;
                    </a>
                </div>
            </form>

            <!-- Printed Forms List -->
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="card-title">Printed Forms</h3>
                        </div>
                        <div class="col-auto">
                            <div id="bulkActions" class="d-none">
                                @if($isAdmin)
                                <button type="button" class="btn btn-success d-none" id="bulkReceiveBtn" data-bs-toggle="modal" data-bs-target="#bulkReceiveModal">
                                    <i class="far fa-check"></i>&nbsp;
                                    Mark as Received (<span id="receiveSelectedCount">0</span>)
                                </button>
                                <button type="button" class="btn btn-info d-none" id="bulkUploadScansBtn" data-bs-toggle="modal" data-bs-target="#bulkUploadScansModal">
                                    <i class="far fa-upload"></i>&nbsp;
                                    Upload Scans (<span id="uploadSelectedCount">0</span>)
                                </button>
                                <button type="button" class="btn btn-secondary d-none" id="bulkUpdateLocationBtn" data-bs-toggle="modal" data-bs-target="#bulkUpdateLocationModal">
                                    <i class="far fa-map-marker-alt"></i>&nbsp;
                                    Update Location (<span id="locationSelectedCount">0</span>)
                                </button>
                                @endif
                                <button type="button" class="btn btn-warning" id="bulkUpdateBtn" data-bs-toggle="modal" data-bs-target="#bulkUpdateModal">
                                    <i class="far fa-edit"></i>&nbsp;
                                    Update Status (<span id="selectedCount">0</span>)
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="clearSelectionBtn">
                                    <i class="far fa-times"></i>&nbsp;
                                    Clear Selection
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($printedForms->count() > 0)
                        <form id="bulkReturnForm" method="POST" action="{{ route('printed-forms.bulk-return') }}">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th width="40">
                                                <input type="checkbox" class="form-check-input" id="selectAll">
                                            </th>
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
                                            <tr data-status="{{ $printedForm->status->value }}" 
                                                data-can-process="{{ $isAdmin ? 'true' : 'false' }}"
                                                data-has-scan="{{ $printedForm->scanned_file_path ? 'true' : 'false' }}"
                                                data-location="{{ $printedForm->physical_location ? json_encode($printedForm->physical_location) : '' }}">
                                                <td>
                                                    @can('returnForm', $printedForm)
                                                        @if($printedForm->status->value === 'circulating')
                                                            <input type="checkbox" class="form-check-input form-checkbox form-checkbox-return" name="form_ids[]" value="{{ $printedForm->id }}" data-action="return">
                                                        @endif
                                                    @endcan
                                                    @if($isAdmin)
                                                        @if($printedForm->status->value === 'returned' && !$printedForm->isProblematic())
                                                            <input type="checkbox" class="form-check-input form-checkbox form-checkbox-receive" name="form_ids[]" value="{{ $printedForm->id }}" data-action="receive">
                                                        @endif
                                                        @if($printedForm->status->value === 'received' && !$printedForm->scanned_file_path)
                                                            <input type="checkbox" class="form-check-input form-checkbox form-checkbox-upload" name="form_ids[]" value="{{ $printedForm->id }}" data-action="upload">
                                                        @endif
                                                        @if($printedForm->scanned_file_path)
                                                            <input type="checkbox" class="form-check-input form-checkbox form-checkbox-location" name="form_ids[]" value="{{ $printedForm->id }}" data-action="location">
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="text-monospace">{{ $printedForm->form_number }}</span>
                                                </td>
                                                <td>
                                                    <div>{{ $printedForm->documentVersion->document->document_number }}</div>
                                                    <div class="text-muted small">{{ $printedForm->documentVersion->document->title }}</div>
                                                </td>
                                                <td>{{ $printedForm->issuedTo->name }}</td>
                                                <td>{{ formatDate($printedForm->issued_at) }}</td>
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
                                                        <i class="far fa-eye"></i>&nbsp;
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>
                        
                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $printedForms->appends($filters)->links() }}
                        </div>
                    @else
                        <div class="empty">
                            <div class="empty-icon">
                                <i class="far fa-file-alt"></i>&nbsp;
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

<!-- Bulk Update Modal -->
<div class="modal modal-blur fade" id="bulkUpdateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" id="bulkUpdateSubmitForm" action="{{ route('printed-forms.bulk-return') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Update Form Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        You are about to update the status of <strong id="modalSelectedCount">0</strong> form(s).
                    </p>
                    
                    <div class="mb-3">
                        <label class="form-label required">Status</label>
                        <select name="status" id="bulkStatusSelect" class="form-select" required>
                            <option value="returned">Returned</option>
                            <option value="lost">Lost</option>
                            <option value="spoilt">Spoilt</option>
                        </select>
                        <small class="form-hint">Select the new status for the selected forms</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" id="notesLabel">Notes</label>
                        <textarea name="notes" id="bulkNotesTextarea" class="form-control" rows="3" placeholder="Enter notes..."></textarea>
                        <small class="form-hint" id="notesHint">Optional notes</small>
                    </div>
                    
                    <!-- Hidden container for form IDs -->
                    <div id="formIdsContainer"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="bulkUpdateSubmitBtn">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Receive Modal -->
<div class="modal modal-blur fade" id="bulkReceiveModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" id="bulkReceiveSubmitForm" action="{{ route('printed-forms.bulk-receive') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Mark Forms as Received</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        You are about to mark <strong id="modalReceiveSelectedCount">0</strong> returned form(s) as received.
                    </p>
                    
                    <!-- Hidden container for form IDs -->
                    <div id="receiveFormIdsContainer"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Mark as Received</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Upload Scans Modal -->
<div class="modal modal-blur fade" id="bulkUploadScansModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" id="bulkUploadScansSubmitForm" action="{{ route('printed-forms.bulk-upload-scans') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload Scanned Forms</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        You are uploading scanned PDFs for <strong id="modalUploadSelectedCount">0</strong> received form(s).
                    </p>
                    
                    <div class="alert alert-info">
                        <strong>Instructions:</strong> Select a PDF file for each form. Each form must have its own PDF file. Maximum 10MB per file.
                    </div>
                    
                    <div id="selectedFormsList" class="mb-3"></div>
                    
                    <!-- Hidden container for form IDs -->
                    <div id="uploadFormIdsContainer"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Scans</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Update Location Modal -->
<div class="modal modal-blur fade" id="bulkUpdateLocationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" id="bulkUpdateLocationSubmitForm" action="{{ route('printed-forms.bulk-update-location') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Update Physical Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        You are updating the physical location for <strong id="modalLocationSelectedCount">0</strong> scanned form(s).
                    </p>
                    
                    <div class="alert alert-info">
                        <strong>Instructions:</strong> Specify the physical location for each form. Each form can have a different location. All fields are optional. Leave empty to clear location.
                    </div>
                    
                    <div id="selectedFormsLocationList" class="mb-3"></div>
                    
                    <!-- Hidden container for form IDs -->
                    <div id="locationFormIdsContainer"></div>
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
<script src="{{ asset('assets/tabler/libs/tom-select/dist/js/tom-select.complete.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Tom Select for Status filter
    new TomSelect('#status-select', {
        placeholder: 'Select status(es)...',
        maxItems: null,
        plugins: ['remove_button'],
        hideSelected: true,
        closeAfterSelect: false,
        sortField: {
            field: 'text',
            direction: 'asc'
        }
    });

    @if($isAdmin)
    // Initialize Tom Select for Issued To filter
    new TomSelect('#issued-to-select', {
        placeholder: 'Select user(s)...',
        maxItems: null,
        plugins: ['remove_button'],
        hideSelected: true,
        closeAfterSelect: false,
        sortField: {
            field: 'text',
            direction: 'asc'
        }
    });
    @endif

    // Barcode Scanner Multi-Form-Number Functionality
    const formNumberInput = document.getElementById('form-number-input');
    const formNumbersHidden = document.getElementById('form-numbers-hidden');
    const scannedFormsContainer = document.getElementById('scanned-forms-container');
    let scannedFormNumbers = [];
    
    // Initialize with existing filter values
    if (formNumbersHidden && formNumbersHidden.value) {
        try {
            scannedFormNumbers = JSON.parse(formNumbersHidden.value);
            renderScannedForms();
        } catch(e) {
            // If not JSON, treat as single value
            if (formNumbersHidden.value) {
                scannedFormNumbers = [formNumbersHidden.value];
                renderScannedForms();
            }
        }
    } else if (formNumberInput && formNumberInput.value) {
        // Fallback to form_number if form_numbers doesn't exist
        scannedFormNumbers = [formNumberInput.value];
        renderScannedForms();
    }
    
    function renderScannedForms() {
        if (!scannedFormsContainer) return;
        
        if (scannedFormNumbers.length === 0) {
            scannedFormsContainer.innerHTML = '';
            return;
        }
        
        let html = '<div class="d-flex flex-wrap gap-2">';
        scannedFormNumbers.forEach((formNumber, index) => {
            html += `
                <span class="badge bg-primary-lt" style="font-size: 0.875rem; padding: 0.5rem 0.75rem;">
                    <i class="far fa-barcode me-1"></i>
                    ${escapeHtml(formNumber)}
                    <button type="button" class="btn-close btn-close-white ms-2" 
                            style="font-size: 0.75rem; opacity: 0.7;" 
                            data-index="${index}" 
                            aria-label="Remove"></button>
                </span>
            `;
        });
        html += '</div>';
        scannedFormsContainer.innerHTML = html;
        
        // Update hidden field
        if (formNumbersHidden) {
            formNumbersHidden.value = JSON.stringify(scannedFormNumbers);
        }
        
        // Attach remove handlers
        scannedFormsContainer.querySelectorAll('.btn-close').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                removeScannedForm(index);
            });
        });
    }
    
    function removeScannedForm(index) {
        scannedFormNumbers.splice(index, 1);
        renderScannedForms();
    }
    
    function addScannedForm(formNumber) {
        const trimmed = formNumber.trim();
        if (!trimmed) return;
        
        // Support multiple formats: space-separated, comma-separated, or newline-separated
        const separators = /[\s,;|]+/; // Split by space, comma, semicolon, or pipe
        const formNumbers = trimmed.split(separators)
            .map(num => num.trim())
            .filter(num => num.length > 0);
        
        let addedCount = 0;
        formNumbers.forEach(num => {
            // Avoid duplicates
            if (!scannedFormNumbers.includes(num)) {
                scannedFormNumbers.push(num);
                addedCount++;
            }
        });
        
        if (addedCount > 0) {
            renderScannedForms();
        }
        
        // Clear input
        if (formNumberInput) {
            formNumberInput.value = '';
        }
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Handle Enter key or barcode scanner input
    if (formNumberInput) {
        formNumberInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addScannedForm(this.value);
            }
        });
        
        // Optional: Auto-detect rapid input from barcode scanner
        let scanBuffer = '';
        let scanTimeout = null;
        
        formNumberInput.addEventListener('input', function(e) {
            scanBuffer = this.value;
            
            // Clear existing timeout
            if (scanTimeout) {
                clearTimeout(scanTimeout);
            }
            
            // Set timeout to detect end of scan (barcode scanners are typically very fast)
            // If input stops for more than 100ms, it's likely manual typing
            scanTimeout = setTimeout(() => {
                scanBuffer = '';
            }, 100);
        });
        
        // Detect when scanner sends "Enter" automatically
        formNumberInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (this.value.trim()) {
                    addScannedForm(this.value);
                }
            }
        });
    }
    
    // Clear scanned forms when clear filter button is clicked
    const clearFilterBtn = document.querySelector('a[href="{{ route('printed-forms.index') }}"]');
    if (clearFilterBtn) {
        clearFilterBtn.addEventListener('click', function(e) {
            scannedFormNumbers = [];
            if (formNumbersHidden) formNumbersHidden.value = '';
            if (formNumberInput) formNumberInput.value = '';
            if (scannedFormsContainer) scannedFormsContainer.innerHTML = '';
        });
    }
    const selectAll = document.getElementById('selectAll');
    const formCheckboxes = document.querySelectorAll('.form-checkbox');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCountSpan = document.getElementById('selectedCount');
    const modalSelectedCount = document.getElementById('modalSelectedCount');
    const clearSelectionBtn = document.getElementById('clearSelectionBtn');
    const bulkReturnForm = document.getElementById('bulkReturnForm');
    const bulkUpdateSubmitForm = document.getElementById('bulkUpdateSubmitForm');
    const bulkStatusSelect = document.getElementById('bulkStatusSelect');
    const bulkNotesTextarea = document.getElementById('bulkNotesTextarea');
    const notesLabel = document.getElementById('notesLabel');
    const notesHint = document.getElementById('notesHint');

    function updateBulkActions() {
        const checked = document.querySelectorAll('.form-checkbox:checked');
        const count = checked.length;
        
        // Count by action type
        const receiveChecked = document.querySelectorAll('.form-checkbox-receive:checked');
        const uploadChecked = document.querySelectorAll('.form-checkbox-upload:checked');
        const locationChecked = document.querySelectorAll('.form-checkbox-location:checked');
        const returnChecked = document.querySelectorAll('.form-checkbox-return:checked');
        
        if (count > 0) {
            bulkActions.classList.remove('d-none');
            selectedCountSpan.textContent = returnChecked.length;
            modalSelectedCount.textContent = returnChecked.length;
            
            // Show/hide action-specific buttons
            @if($isAdmin)
            const bulkReceiveBtn = document.getElementById('bulkReceiveBtn');
            const bulkUploadScansBtn = document.getElementById('bulkUploadScansBtn');
            const bulkUpdateLocationBtn = document.getElementById('bulkUpdateLocationBtn');
            const receiveSelectedCount = document.getElementById('receiveSelectedCount');
            const uploadSelectedCount = document.getElementById('uploadSelectedCount');
            const locationSelectedCount = document.getElementById('locationSelectedCount');
            
            if (bulkReceiveBtn && receiveSelectedCount) {
                if (receiveChecked.length > 0) {
                    bulkReceiveBtn.classList.remove('d-none');
                    receiveSelectedCount.textContent = receiveChecked.length;
                } else {
                    bulkReceiveBtn.classList.add('d-none');
                }
            }
            
            if (bulkUploadScansBtn && uploadSelectedCount) {
                if (uploadChecked.length > 0) {
                    bulkUploadScansBtn.classList.remove('d-none');
                    uploadSelectedCount.textContent = uploadChecked.length;
                } else {
                    bulkUploadScansBtn.classList.add('d-none');
                }
            }
            
            if (bulkUpdateLocationBtn && locationSelectedCount) {
                if (locationChecked.length > 0) {
                    bulkUpdateLocationBtn.classList.remove('d-none');
                    locationSelectedCount.textContent = locationChecked.length;
                } else {
                    bulkUpdateLocationBtn.classList.add('d-none');
                }
            }
            @endif
            
            // Show return button only if return checkboxes are checked
            if (returnChecked.length === 0) {
                bulkUpdateBtn.style.display = 'none';
            } else {
                bulkUpdateBtn.style.display = 'inline-block';
            }
        } else {
            bulkActions.classList.add('d-none');
            selectedCountSpan.textContent = '0';
            modalSelectedCount.textContent = '0';
            @if($isAdmin)
            const bulkReceiveBtn = document.getElementById('bulkReceiveBtn');
            const bulkUploadScansBtn = document.getElementById('bulkUploadScansBtn');
            const bulkUpdateLocationBtn = document.getElementById('bulkUpdateLocationBtn');
            if (bulkReceiveBtn) bulkReceiveBtn.classList.add('d-none');
            if (bulkUploadScansBtn) bulkUploadScansBtn.classList.add('d-none');
            if (bulkUpdateLocationBtn) bulkUpdateLocationBtn.classList.add('d-none');
            @endif
            bulkUpdateBtn.style.display = 'inline-block';
        }
        
        // Update select all checkbox state
        if (selectAll) {
            selectAll.checked = count === formCheckboxes.length && formCheckboxes.length > 0;
            selectAll.indeterminate = count > 0 && count < formCheckboxes.length;
        }
    }

    // Select all functionality
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            // Select all checkboxes when select all is checked
            formCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });
    }

    // Individual checkbox change
    formCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    // Clear selection
    if (clearSelectionBtn) {
        clearSelectionBtn.addEventListener('click', function() {
            formCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            if (selectAll) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            }
            updateBulkActions();
        });
    }

    // Toggle notes requirement based on status
    function toggleBulkNotesRequirement() {
        if (bulkStatusSelect && bulkNotesTextarea && notesLabel && notesHint) {
            const status = bulkStatusSelect.value;
            const isRequired = status === 'lost' || status === 'spoilt';
            
            bulkNotesTextarea.required = isRequired;
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
    
    if (bulkStatusSelect) {
        bulkStatusSelect.addEventListener('change', toggleBulkNotesRequirement);
        toggleBulkNotesRequirement(); // Initialize
    }

    // Bulk update form submission
    if (bulkUpdateSubmitForm) {
        bulkUpdateSubmitForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Get checked checkboxes
            const checked = Array.from(document.querySelectorAll('.form-checkbox:checked'));
            const formIds = checked.map(cb => cb.value);
            
            if (formIds.length === 0) {
                alert('Please select at least one form to update.');
                return false;
            }
            
            // Validate notes requirement for Lost/Spoilt status
            if (!bulkStatusSelect || !bulkNotesTextarea) {
                alert('Form elements not found. Please refresh the page and try again.');
                return false;
            }
            
            const status = bulkStatusSelect.value;
            const notes = bulkNotesTextarea.value.trim();
            
            if ((status === 'lost' || status === 'spoilt') && !notes) {
                alert('Notes are required when marking forms as Lost or Spoilt. Please provide a reason.');
                bulkNotesTextarea.focus();
                return false;
            }
            
            // Validate that all checked forms are in "circulating" status
            let invalidForms = [];
            checked.forEach(checkbox => {
                const row = checkbox.closest('tr');
                if (row) {
                    const statusBadge = row.querySelector('.badge');
                    if (statusBadge && !statusBadge.textContent.trim().toLowerCase().includes('circulating')) {
                        invalidForms.push(checkbox.value);
                    }
                }
            });
            
            if (invalidForms.length > 0) {
                alert('Some selected forms are not in "Circulating" status and cannot be updated. Please refresh the page and try again.');
                return false;
            }
            
            // Clear and add form IDs
            const container = document.getElementById('formIdsContainer');
            if (container) {
                container.innerHTML = '';
                formIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'form_ids[]';
                    input.value = id;
                    container.appendChild(input);
                });
            } else {
                // Fallback: remove existing and add new
                const existingInputs = bulkUpdateSubmitForm.querySelectorAll('input[name="form_ids[]"]');
                existingInputs.forEach(input => input.remove());
                formIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'form_ids[]';
                    input.value = id;
                    bulkUpdateSubmitForm.appendChild(input);
                });
            }
            
            // Submit the form
            this.submit();
            return false;
        });
    }

    // Bulk Receive form submission
    const bulkReceiveSubmitForm = document.getElementById('bulkReceiveSubmitForm');
    if (bulkReceiveSubmitForm) {
        bulkReceiveSubmitForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const checked = Array.from(document.querySelectorAll('.form-checkbox-receive:checked'));
            const formIds = checked.map(cb => cb.value);
            
            if (formIds.length === 0) {
                alert('Please select at least one returned form to mark as received.');
                return false;
            }
            
            const container = document.getElementById('receiveFormIdsContainer');
            if (container) {
                container.innerHTML = '';
                formIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'form_ids[]';
                    input.value = id;
                    container.appendChild(input);
                });
            }
            
            this.submit();
            return false;
        });
    }

    // Bulk Upload Scans form submission
    const bulkUploadScansSubmitForm = document.getElementById('bulkUploadScansSubmitForm');
    if (bulkUploadScansSubmitForm) {
        bulkUploadScansSubmitForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const checked = Array.from(document.querySelectorAll('.form-checkbox-upload:checked'));
            const formIds = checked.map(cb => cb.value);
            
            if (formIds.length === 0) {
                alert('Please select at least one received form to upload scans for.');
                return false;
            }
            
            // Validate that each form has a file
            let missingFiles = [];
            formIds.forEach(formId => {
                const fileInput = document.querySelector(`input[type="file"][data-form-id="${formId}"]`);
                if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                    const row = document.querySelector(`.form-checkbox-upload[value="${formId}"]`)?.closest('tr');
                    const formNumber = row ? row.querySelector('.text-monospace')?.textContent : formId;
                    missingFiles.push(formNumber);
                }
            });
            
            if (missingFiles.length > 0) {
                alert('Please select a PDF file for each form. Missing files for: ' + missingFiles.join(', '));
                return false;
            }
            
            const container = document.getElementById('uploadFormIdsContainer');
            if (container) {
                container.innerHTML = '';
                formIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'form_ids[]';
                    input.value = id;
                    container.appendChild(input);
                });
            }
            
            this.submit();
            return false;
        });
        
        // Show selected forms with file inputs when modal opens
        const bulkUploadScansModal = document.getElementById('bulkUploadScansModal');
        if (bulkUploadScansModal) {
            bulkUploadScansModal.addEventListener('show.bs.modal', function() {
                const checked = Array.from(document.querySelectorAll('.form-checkbox-upload:checked'));
                const formsList = document.getElementById('selectedFormsList');
                if (formsList) {
                    if (checked.length > 0) {
                        let html = '<div class="card"><div class="card-body"><h5 class="card-title mb-3">Select PDF for each form:</h5>';
                        checked.forEach(checkbox => {
                            const formId = checkbox.value;
                            const row = checkbox.closest('tr');
                            const formNumber = row ? row.querySelector('.text-monospace')?.textContent : formId;
                            const documentInfo = row ? row.querySelector('td:nth-child(3)')?.innerHTML : '';
                            
                            html += `<div class="mb-3">
                                <label class="form-label required">${formNumber}</label>`;
                            if (documentInfo) {
                                html += `<div class="text-muted small mb-2">${documentInfo}</div>`;
                            }
                            html += `<input type="file" name="scanned_files[${formId}]" 
                                    class="form-control" 
                                    data-form-id="${formId}"
                                    accept=".pdf" 
                                    required>
                                <small class="form-hint">Maximum 10MB</small>
                            </div>`;
                        });
                        html += '</div></div>';
                        formsList.innerHTML = html;
                    } else {
                        formsList.innerHTML = '';
                    }
                }
            });
            
            // Clear forms list when modal is hidden
            bulkUploadScansModal.addEventListener('hidden.bs.modal', function() {
                const formsList = document.getElementById('selectedFormsList');
                if (formsList) {
                    formsList.innerHTML = '';
                }
            });
        }
    }

    // Bulk Update Location form submission
    const bulkUpdateLocationSubmitForm = document.getElementById('bulkUpdateLocationSubmitForm');
    if (bulkUpdateLocationSubmitForm) {
        bulkUpdateLocationSubmitForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const checked = Array.from(document.querySelectorAll('.form-checkbox-location:checked'));
            const formIds = checked.map(cb => cb.value);
            
            if (formIds.length === 0) {
                alert('Please select at least one scanned form to update location for.');
                return false;
            }
            
            const container = document.getElementById('locationFormIdsContainer');
            if (container) {
                container.innerHTML = '';
                formIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'form_ids[]';
                    input.value = id;
                    container.appendChild(input);
                });
            }
            
            // Verify that all forms have location inputs (even if empty)
            formIds.forEach(formId => {
                const locationInput = document.querySelector(`input[name="physical_locations[${formId}][room_no]"]`);
                if (!locationInput) {
                    console.error(`Missing location input for form ID: ${formId}`);
                    alert(`Warning: Missing location input for form ID ${formId}. Please refresh and try again.`);
                    return false;
                }
            });
            
            this.submit();
            return false;
        });
        
        // Show selected forms with location inputs when modal opens
        const bulkUpdateLocationModal = document.getElementById('bulkUpdateLocationModal');
        if (bulkUpdateLocationModal) {
            bulkUpdateLocationModal.addEventListener('show.bs.modal', function() {
                const checked = Array.from(document.querySelectorAll('.form-checkbox-location:checked'));
                const formsList = document.getElementById('selectedFormsLocationList');
                if (formsList) {
                    if (checked.length > 0) {
                        let html = '<div class="card"><div class="card-body"><h5 class="card-title mb-3">Specify location for each form:</h5>';
                        checked.forEach(checkbox => {
                            const formId = checkbox.value;
                            const row = checkbox.closest('tr');
                            const formNumber = row ? row.querySelector('.text-monospace')?.textContent : formId;
                            const documentInfo = row ? row.querySelector('td:nth-child(3)')?.innerHTML : '';
                            
                            // Get current location if exists
                            const statusBadge = row ? row.querySelector('.badge') : null;
                            let currentLocation = null;
                            if (row) {
                                const locationData = row.getAttribute('data-location');
                                if (locationData) {
                                    try {
                                        currentLocation = JSON.parse(locationData);
                                    } catch(e) {
                                        currentLocation = null;
                                    }
                                }
                            }
                            
                            html += `<div class="mb-4 p-3 border rounded">
                                <div class="mb-2">
                                    <strong>${formNumber}</strong>`;
                            if (documentInfo) {
                                html += `<div class="text-muted small">${documentInfo}</div>`;
                            }
                            html += `</div>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Room Number</label>
                                        <input type="text" 
                                            name="physical_locations[${formId}][room_no]" 
                                            class="form-control form-control-sm" 
                                            value="${currentLocation?.room_no || ''}" 
                                            placeholder="e.g., R001">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Cabinet Number</label>
                                        <input type="text" 
                                            name="physical_locations[${formId}][cabinet_no]" 
                                            class="form-control form-control-sm" 
                                            value="${currentLocation?.cabinet_no || ''}" 
                                            placeholder="e.g., C001">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Shelf Number</label>
                                        <input type="text" 
                                            name="physical_locations[${formId}][shelf_no]" 
                                            class="form-control form-control-sm" 
                                            value="${currentLocation?.shelf_no || ''}" 
                                            placeholder="e.g., S001">
                                    </div>
                                </div>
                                <small class="form-hint text-muted">Leave all fields empty to clear location</small>
                            </div>`;
                        });
                        html += '</div></div>';
                        formsList.innerHTML = html;
                    } else {
                        formsList.innerHTML = '';
                    }
                }
            });
            
            // Clear forms list when modal is hidden
            bulkUpdateLocationModal.addEventListener('hidden.bs.modal', function() {
                const formsList = document.getElementById('selectedFormsLocationList');
                if (formsList) {
                    formsList.innerHTML = '';
                }
            });
        }
    }

    // Update modal counts when they open
    @if($isAdmin)
    const bulkReceiveModal = document.getElementById('bulkReceiveModal');
    if (bulkReceiveModal) {
        bulkReceiveModal.addEventListener('show.bs.modal', function() {
            const checked = document.querySelectorAll('.form-checkbox-receive:checked');
            const countSpan = document.getElementById('modalReceiveSelectedCount');
            if (countSpan) countSpan.textContent = checked.length;
        });
    }
    
    const bulkUploadScansModal = document.getElementById('bulkUploadScansModal');
    if (bulkUploadScansModal) {
        bulkUploadScansModal.addEventListener('show.bs.modal', function() {
            const checked = document.querySelectorAll('.form-checkbox-upload:checked');
            const countSpan = document.getElementById('modalUploadSelectedCount');
            if (countSpan) countSpan.textContent = checked.length;
        });
    }
    
    const bulkUpdateLocationModal = document.getElementById('bulkUpdateLocationModal');
    if (bulkUpdateLocationModal) {
        bulkUpdateLocationModal.addEventListener('show.bs.modal', function() {
            const checked = document.querySelectorAll('.form-checkbox-location:checked');
            const countSpan = document.getElementById('modalLocationSelectedCount');
            if (countSpan) countSpan.textContent = checked.length;
        });
    }
    @endif

    // Initial update
    updateBulkActions();
});
</script>
@endpush
@endsection

