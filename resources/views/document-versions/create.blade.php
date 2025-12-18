@extends('layouts.app')

@section('title', 'Create New Version: ' . $document->title)

@section('content')
<!-- BEGIN PAGE HEADER -->
<div class="page-header d-print-none" aria-label="Page header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">@yield('title')</h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('documents.show', $document) }}" class="btn btn-outline-secondary">
                    <i class="far fa-arrow-left"></i>&nbsp;
                    Back to Document
                </a>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE HEADER -->

<!-- BEGIN PAGE BODY -->
<div class="page-body">
    <div class="container-xl">
        <div class="row">
            @include('layouts.alerts')
        </div>
        
        <div class="row row-deck row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Create New Version</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('documents.versions.store', $document) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="mb-3">
                                <label class="form-label required">Creation Method</label>
                                <select name="creation_method" id="creation_method" class="form-select" required>
                                    <option value="">Select method...</option>
                                    <option value="scratch">Create from scratch</option>
                                    <option value="upload">Upload existing file</option>
                                    @if($versions->count() > 0)
                                    <option value="copy">Copy from existing version</option>
                                    @endif
                                </select>
                                @error('creation_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3" id="file_type_group" style="display: none;">
                                <label class="form-label required">File Type</label>
                                <select name="file_type" id="file_type" class="form-select">
                                    <option value="">Select file type...</option>
                                    <option value="docx">Word Document (DOCX)</option>
                                    <option value="xlsx">Excel Spreadsheet (XLSX)</option>
                                </select>
                                <div class="form-hint">Select the type of document you want to create</div>
                                @error('file_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3" id="source_file_group" style="display: none;">
                                <label class="form-label required">Source File</label>
                                <input type="file" name="source_file" id="source_file" class="form-control" accept=".docx,.xlsx,.pdf,.jpg,.jpeg,.png,.zip">
                                <div class="form-hint">Supported formats: DOCX, XLSX, PDF, JPG, JPEG, PNG</div>
                                @error('source_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3" id="source_version_group" style="display: none;">
                                <label class="form-label required">Source Version</label>
                                <select name="source_version_id" id="source_version_id" class="form-select">
                                    <option value="">Select version...</option>
                                    @foreach($versions as $version)
                                    <option value="{{ $version->id }}">v{{ $version->version_number }} - {{ formatDate($version->created_at, 'd M Y') }}</option>
                                    @endforeach
                                </select>
                                @error('source_version_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Revision Description</label>
                                <textarea name="revision_description" class="form-control" rows="3" placeholder="Describe the changes in this version..."></textarea>
                                @error('revision_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            @if($document->document_type->value === 'form')
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_ncr_paper" 
                                           name="is_ncr_paper" value="1"
                                           {{ old('is_ncr_paper') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_ncr_paper">
                                        <i class="far fa-copy me-1"></i>&nbsp;
                                        3-Ply NCR Paper (prints 3 labels)
                                    </label>
                                    <div class="form-text">
                                        Check this if the form is printed on 3-ply carbonless (NCR) paper
                                    </div>
                                </div>
                                @error('is_ncr_paper')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @endif
                            
                            <div class="form-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="far fa-plus"></i>&nbsp;
                                    Create Version
                                </button>
                                <a href="{{ route('documents.show', $document) }}" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE BODY -->
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const creationMethod = document.getElementById('creation_method');
    const fileTypeGroup = document.getElementById('file_type_group');
    const sourceFileGroup = document.getElementById('source_file_group');
    const sourceVersionGroup = document.getElementById('source_version_group');
    const sourceFileInput = document.getElementById('source_file');
    const form = document.querySelector('form[action*="versions.store"]');
    
    creationMethod.addEventListener('change', function() {
        // Hide all groups first
        fileTypeGroup.style.display = 'none';
        sourceFileGroup.style.display = 'none';
        sourceVersionGroup.style.display = 'none';
        
        // Show relevant group based on selection
        switch(this.value) {
            case 'scratch':
                fileTypeGroup.style.display = 'block';
                break;
            case 'upload':
                sourceFileGroup.style.display = 'block';
                break;
            case 'copy':
                sourceVersionGroup.style.display = 'block';
                break;
        }
    });
});
</script>
@endpush
