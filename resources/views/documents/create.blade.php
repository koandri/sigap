@extends('layouts.app')

@section('title', 'Create Document')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Create Document
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                        <i class="far fa-arrow-left"></i>&nbsp;
                        Back to Documents
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-12">
                    <form method="POST" action="{{ route('documents.store') }}" class="card">
                        @csrf
                        <div class="card-header">
                            <h3 class="card-title">Document Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Document Number</label>
                                        <input type="text" name="document_number" class="form-control @error('document_number') is-invalid @enderror" 
                                                value="{{ old('document_number') }}" placeholder="Enter document number">
                                        @error('document_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Document Type</label>
                                        <select name="document_type" id="document-type-select" class="form-select @error('document_type') is-invalid @enderror">
                                            <option value="">Select document type</option>
                                            @foreach($documentTypes as $type)
                                                <option value="{{ $type->value }}" {{ old('document_type') == $type->value ? 'selected' : '' }}>
                                                    {{ $type->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('document_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Correspondence Templates Table -->
                            <div id="correspondence-templates-section" class="mb-4" style="display: none;">
                                <hr class="my-4">
                                <h4 class="mb-3">Available Correspondence Templates</h4>
                                @if($correspondenceTemplates->isEmpty())
                                    <div class="alert alert-info">
                                        <i class="far fa-info-circle me-2"></i>&nbsp;
                                        No correspondence templates available. You can create new Internal Memo or Outgoing Letter documents.
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-vcenter card-table">
                                            <thead>
                                                <tr>
                                                    <th>Document Number</th>
                                                    <th>Title</th>
                                                    <th>Type</th>
                                                    <th>Department</th>
                                                    <th>Version</th>
                                                    <th>Created By</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($correspondenceTemplates as $template)
                                                    <tr>
                                                        <td>{{ $template->document_number }}</td>
                                                        <td>{{ $template->title }}</td>
                                                        <td>
                                                            <span class="badge bg-blue-lt">{{ $template->document_type->label() }}</span>
                                                        </td>
                                                        <td>{{ $template->department?->name ?? 'N/A' }}</td>
                                                        <td>
                                                            <span class="badge bg-success">v{{ $template->activeVersion->version_number }}</span>
                                                        </td>
                                                        <td>{{ $template->creator->name }}</td>
                                                        <td>
                                                            <a href="{{ route('documents.show', $template) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                                <i class="far fa-eye"></i>&nbsp;
                                                                View
                                                            </a>
                                                            @can('create', App\Models\DocumentInstance::class)
                                                                <a href="{{ route('correspondences.create', ['template_id' => $template->id]) }}" class="btn btn-sm btn-outline-success">
                                                                    <i class="far fa-envelope"></i>&nbsp;
                                                                    Use Template
                                                                </a>
                                                            @endcan
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label required">Title</label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                                        value="{{ old('title') }}" placeholder="Enter document title">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                            rows="3" placeholder="Enter document description">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Department</label>
                                        <select name="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                            <option value="">Select department</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                    {{ $department->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('department_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Accessible Departments</label>
                                        <select name="accessible_departments[]" id="accessible-departments-select" class="form-select @error('accessible_departments') is-invalid @enderror" multiple>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}" {{ in_array($department->id, old('accessible_departments', [])) ? 'selected' : '' }}>
                                                    {{ $department->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="form-hint">Select multiple departments that can access this document</div>
                                        @error('accessible_departments')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12">
                                    <hr class="my-4">
                                    <h4 class="mb-3">Physical Location</h4>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Room Number</label>
                                        <input type="text" name="physical_location[room_no]" class="form-control" 
                                                value="{{ old('physical_location.room_no') }}" placeholder="e.g., R001">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Shelf Number</label>
                                        <input type="text" name="physical_location[shelf_no]" class="form-control" 
                                                value="{{ old('physical_location.shelf_no') }}" placeholder="e.g., S001">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Folder Number</label>
                                        <input type="text" name="physical_location[folder_no]" class="form-control" 
                                                value="{{ old('physical_location.folder_no') }}" placeholder="e.g., F001">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="far fa-save"></i>&nbsp;
                                        Create Document
                                    </button>
                                    <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/tabler/libs/tom-select/dist/css/tom-select.bootstrap5.min.css') }}" />
<style>
    .ts-control {
        background-color: #ffffff !important;
        border: 1px solid #dadce0 !important;
        min-height: calc(1.5em + 0.75rem + 2px) !important;
        padding: 0.375rem 0.75rem !important;
    }
    
    .ts-dropdown {
        background-color: #ffffff !important;
        border: 1px solid #dadce0 !important;
        border-radius: 4px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
    }
    
    .ts-dropdown .ts-dropdown-content {
        background-color: #ffffff !important;
    }
    
    .ts-dropdown .option {
        background-color: #ffffff !important;
        color: #212529 !important;
        padding: 0.375rem 0.75rem !important;
    }
    
    .ts-dropdown .option:hover,
    .ts-dropdown .option.active {
        background-color: #f8f9fa !important;
        color: #212529 !important;
    }
    
    .ts-dropdown .option.selected {
        background-color: #e9ecef !important;
        color: #212529 !important;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/tabler/libs/tom-select/dist/js/tom-select.complete.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new TomSelect('#accessible-departments-select', {
        plugins: ['remove_button'],
        placeholder: 'Select departments',
        maxOptions: null,
        allowEmptyOption: false
    });
    
    // Show/hide correspondence templates table based on document type selection
    const documentTypeSelect = document.getElementById('document-type-select');
    const correspondenceSection = document.getElementById('correspondence-templates-section');
    
    function toggleCorrespondenceSection() {
        const selectedType = documentTypeSelect.value;
        if (selectedType === 'internal_memo' || selectedType === 'outgoing_letter') {
            correspondenceSection.style.display = 'block';
        } else {
            correspondenceSection.style.display = 'none';
        }
    }
    
    // Initialize on page load (handles both fresh page load and validation errors)
    toggleCorrespondenceSection();
    
    // Update on change
    documentTypeSelect.addEventListener('change', toggleCorrespondenceSection);
});
</script>
@endpush
