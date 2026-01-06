@extends('layouts.app')

@section('title', 'Request Document Access')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('documents.index') }}">Documents</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('documents.show', $document) }}">{{ $document->title }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Request Access</li>
                        </ol>
                    </nav>
                    <h2 class="page-title">
                        Request Document Access
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Access Request Form</h3>
                        </div>
                        <div class="card-body">
                            <!-- Document Information -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Document Title</label>
                                        <div class="form-control-plaintext">{{ $document->title }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Document Number</label>
                                        <div class="form-control-plaintext">{{ $document->document_number }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Document Type</label>
                                        <div class="form-control-plaintext">
                                            <span class="badge bg-blue-lt">{{ $document->document_type->label() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Accessible Departments</label>
                                        <div class="form-control-plaintext">
                                            @if($document->accessibleDepartments->count() > 0)
                                                @foreach($document->accessibleDepartments as $dept)
                                                    <span class="badge bg-blue-lt me-1">{{ $dept->name }}</span>
                                                @endforeach
                                            @else
                                                {{ $document->department?->name ?? 'N/A' }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Version</label>
                                        <div class="form-control-plaintext">v{{ $activeVersion->version_number }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <div class="form-control-plaintext">
                                            <span class="badge bg-green-lt">{{ $activeVersion->status->label() }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Access Request Form -->
                            <form action="{{ route('documents.request-access.store', $document) }}" method="POST">
                                @csrf
                                
                                <div class="mb-3">
                                    <label for="access_type" class="form-label required">Access Type</label>
                                    <select name="access_type" id="access_type" class="form-select @error('access_type') is-invalid @enderror" required>
                                        <option value="">Select access type...</option>
                                        @foreach($accessTypes as $accessType)
                                            <option value="{{ $accessType->value }}" {{ old('access_type') == $accessType->value ? 'selected' : '' }}>
                                                {{ $accessType->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('access_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-hint">
                                        <strong>One Time Access:</strong> View the document once only<br>
                                        <strong>Multiple Access:</strong> View the document multiple times
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="requested_expiry_date" class="form-label" id="expiry_date_label">Requested Expiry Date <span id="expiry_required_indicator"></span></label>
                                    <input type="text" 
                                           name="requested_expiry_date_display" 
                                           id="requested_expiry_date_display" 
                                           class="form-control @error('requested_expiry_date') is-invalid @enderror"
                                           value="{{ old('requested_expiry_date') ? \Carbon\Carbon::parse(old('requested_expiry_date'))->format('d/m/Y H:i') : '' }}"
                                           data-date-format="DD/MM/YYYY HH:mm"
                                           placeholder="DD/MM/YYYY HH:mm"
                                           autocomplete="off">
                                    <input type="hidden" 
                                           name="requested_expiry_date" 
                                           id="requested_expiry_date">
                                    @error('requested_expiry_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-hint" id="expiry_hint">
                                        Leave empty for no expiry date. The approver may modify this date.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="reason" class="form-label required">Reason for Access</label>
                                    <textarea name="reason" 
                                              id="reason" 
                                              class="form-control @error('reason') is-invalid @enderror" 
                                              rows="3" 
                                              placeholder="Please explain why you need access to this document..." 
                                              required>{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-footer">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="far fa-paper-plane"></i>&nbsp;
                                        Submit Access Request
                                    </button>
                                    <a href="{{ route('documents.show', $document) }}" class="btn btn-secondary">
                                        <i class="far fa-arrow-left"></i>&nbsp;
                                        Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Document Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <div class="text-muted">
                                    @if($document->description)
                                        {{ $document->description }}
                                    @else
                                        <em>No description provided</em>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">File Type</label>
                                <div class="text-muted">{{ strtoupper($activeVersion->file_type) }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">File Size</label>
                                <div class="text-muted">
                                    @php
                                        $fileSize = 0;
                                        if ($activeVersion->file_path && \Illuminate\Support\Facades\Storage::disk('s3')->exists($activeVersion->file_path)) {
                                            try {
                                                $fileSize = \Illuminate\Support\Facades\Storage::disk('s3')->size($activeVersion->file_path);
                                            } catch (\Exception $e) {
                                                $fileSize = 0;
                                            }
                                        }
                                        $fileSizeKB = $fileSize > 0 ? number_format($fileSize / 1024, 2) : 0;
                                    @endphp
                                    {{ $fileSizeKB }} KB
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Created</label>
                                <div class="text-muted">{{ $activeVersion->created_at->format('Y-m-d H:i') }}</div>
                            </div>

                            @if($activeVersion->updated_at != $activeVersion->created_at)
                                <div class="mb-3">
                                    <label class="form-label">Last Updated</label>
                                    <div class="text-muted">{{ $activeVersion->updated_at->format('Y-m-d H:i') }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Access Requirements</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <div class="alert-icon">
                                    <i class="far fa-circle-info"></i>&nbsp;
                                </div>
                                <div>
                                    <h4 class="alert-heading">Access Control Required</h4>
                                    <div class="alert-description">
                                    This document requires approval before access is granted. Your request will be reviewed by the document owner or department head.
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const accessTypeSelect = document.getElementById('access_type');
    const expiryDateDisplay = document.getElementById('requested_expiry_date_display');
    const expiryDateHidden = document.getElementById('requested_expiry_date');
    const expiryDateLabel = document.getElementById('expiry_date_label');
    const expiryRequiredIndicator = document.getElementById('expiry_required_indicator');
    const expiryHint = document.getElementById('expiry_hint');
    const form = expiryDateDisplay.closest('form');
    
    // Initialize LitePicker for datetime input
    if (typeof Litepicker !== 'undefined') {
        const minDate = new Date();
        minDate.setMinutes(minDate.getMinutes() + 1); // 1 minute from now
        
        const picker = new Litepicker({
            element: expiryDateDisplay,
            format: 'DD/MM/YYYY HH:mm',
            autoRefresh: true,
            allowRepick: true,
            minDate: minDate,
            timePicker: true,
            timePickerOptions: {
                format: 'HH:mm',
                step: 15
            },
            dropdowns: {
                months: true,
                years: true
            },
            buttonText: {
                previousMonth: '<',
                nextMonth: '>'
            }
        });
        
        // Convert datetime format when picker value changes
        expiryDateDisplay.addEventListener('change', function() {
            convertDateForSubmit();
        });
    }
    
    // Convert DD/MM/YYYY HH:mm to YYYY-MM-DD HH:mm for form submission
    function convertDateForSubmit() {
        const value = expiryDateDisplay.value.trim();
        if (!value) {
            expiryDateHidden.value = '';
            return;
        }
        
        // Parse DD/MM/YYYY HH:mm format
        // Expected format: "DD/MM/YYYY HH:mm" or "DD/MM/YYYY HH:mm:ss"
        const dateTimeMatch = value.match(/^(\d{2})\/(\d{2})\/(\d{4})\s+(\d{2}):(\d{2})(?::(\d{2}))?$/);
        if (dateTimeMatch) {
            const day = dateTimeMatch[1];
            const month = dateTimeMatch[2];
            const year = dateTimeMatch[3];
            const hours = dateTimeMatch[4];
            const minutes = dateTimeMatch[5];
            
            // Validate datetime
            const date = new Date(year, month - 1, day, hours, minutes);
            if (date.getDate() == day && 
                date.getMonth() == month - 1 && 
                date.getFullYear() == year &&
                date.getHours() == hours &&
                date.getMinutes() == minutes) {
                expiryDateHidden.value = `${year}-${month}-${day} ${hours}:${minutes}:00`;
            } else {
                expiryDateHidden.value = '';
            }
        } else {
            // Fallback: try to parse as just date (DD/MM/YYYY)
            const dateMatch = value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
            if (dateMatch) {
                const day = dateMatch[1];
                const month = dateMatch[2];
                const year = dateMatch[3];
                
                const date = new Date(year, month - 1, day);
                if (date.getDate() == day && date.getMonth() == month - 1 && date.getFullYear() == year) {
                    expiryDateHidden.value = `${year}-${month}-${day} 23:59:59`;
                } else {
                    expiryDateHidden.value = '';
                }
            } else {
                expiryDateHidden.value = '';
            }
        }
    }
    
    // Convert on form submit
    form.addEventListener('submit', function(e) {
        convertDateForSubmit();
    });
    
    function updateExpiryDateRequirement() {
        const selectedValue = accessTypeSelect.value;
        const isMultipleAccess = selectedValue === 'multiple';
        
        if (isMultipleAccess) {
            expiryDateDisplay.setAttribute('required', 'required');
            expiryRequiredIndicator.innerHTML = '<span class="text-danger">*</span>';
            expiryHint.textContent = 'Required for Multiple Access. The approver may modify this date.';
        } else {
            expiryDateDisplay.removeAttribute('required');
            expiryRequiredIndicator.innerHTML = '';
            expiryHint.textContent = 'Leave empty for no expiry date. The approver may modify this date.';
        }
    }
    
    // Set initial state based on old input
    updateExpiryDateRequirement();
    
    // Update when access type changes
    accessTypeSelect.addEventListener('change', updateExpiryDateRequirement);
    
    // Convert initial value if present
    if (expiryDateDisplay.value) {
        convertDateForSubmit();
    }
});
</script>
@endpush
@endsection
