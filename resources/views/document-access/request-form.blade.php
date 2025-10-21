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
                                        <label class="form-label">Department</label>
                                        <div class="form-control-plaintext">{{ $document->department->name }}</div>
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
                                    <label for="requested_expiry_date" class="form-label">Requested Expiry Date (Optional)</label>
                                    <input type="datetime-local" 
                                           name="requested_expiry_date" 
                                           id="requested_expiry_date" 
                                           class="form-control @error('requested_expiry_date') is-invalid @enderror"
                                           value="{{ old('requested_expiry_date') }}"
                                           min="{{ now()->format('Y-m-d\TH:i') }}">
                                    @error('requested_expiry_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-hint">
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
                                        <i class="ti ti-send"></i>
                                        Submit Access Request
                                    </button>
                                    <a href="{{ route('documents.show', $document) }}" class="btn btn-secondary">
                                        <i class="ti ti-arrow-left"></i>
                                        Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
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
                                <div class="text-muted">{{ number_format($activeVersion->file_size / 1024, 2) }} KB</div>
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
                                <i class="ti ti-info-circle"></i>
                                <strong>Access Control Required</strong><br>
                                This document requires approval before access is granted. Your request will be reviewed by the document owner or department head.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
