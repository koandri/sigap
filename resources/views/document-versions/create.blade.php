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
                    <i class="ti ti-arrow-left"></i>
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
                                    <option value="upload">Upload existing file</option>
                                    @if($versions->count() > 0)
                                    <option value="copy">Copy from existing version</option>
                                    @endif
                                </select>
                                @error('creation_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3" id="source_file_group" style="display: none;">
                                <label class="form-label required">Source File</label>
                                <input type="file" name="source_file" id="source_file" class="form-control" accept=".docx,.xlsx,.pdf,.jpg,.jpeg,.png">
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
                                    <option value="{{ $version->id }}">v{{ $version->version_number }} - {{ $version->created_at->format('d M Y') }}</option>
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
                            
                            <div class="form-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-plus"></i>
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
    const sourceFileGroup = document.getElementById('source_file_group');
    const sourceVersionGroup = document.getElementById('source_version_group');
    
    creationMethod.addEventListener('change', function() {
        // Hide all groups first
        sourceFileGroup.style.display = 'none';
        sourceVersionGroup.style.display = 'none';
        
        // Show relevant group based on selection
        switch(this.value) {
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
