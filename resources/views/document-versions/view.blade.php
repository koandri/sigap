@extends('layouts.app')

@section('title', 'View Document: ' . $version->document->title . ' v' . $version->version_number)

@push('css')
<style>
    .pdf-viewer {
        height: 100vh;
        width: 100%;
        border: none;
    }
    .viewer-container {
        height: calc(100vh - 200px);
        min-height: 600px;
    }
</style>
@endpush

@section('content')
<!-- BEGIN PAGE HEADER -->
<div class="page-header d-print-none" aria-label="Page header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">@yield('title')</h2>
            </div>
            <div class="col-auto">
                <div class="btn-list">
                    <a href="{{ route('documents.show', $version->document) }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left"></i>
                        Back to Document
                    </a>
                    @if($version->canBeEdited())
                    <a href="{{ route('document-versions.editor', $version) }}" class="btn btn-primary">
                        <i class="ti ti-edit"></i>
                        Edit Document
                    </a>
                    @endif
                </div>
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
                        <h3 class="card-title">Document Viewer</h3>
                        <div class="card-actions">
                            <span class="badge badge-outline text-info">
                                {{ strtoupper($version->file_type) }} Document
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="viewer-container">
                            @if($version->file_type === 'pdf')
                                <iframe src="{{ route('document-versions.view', $version) }}" class="pdf-viewer"></iframe>
                            @else
                                <div class="alert alert-info">
                                    <h4>Document Preview</h4>
                                    <p>This document type ({{ strtoupper($version->file_type) }}) cannot be previewed in the browser.</p>
                                    <a href="{{ route('document-versions.view', $version) }}" class="btn btn-primary" download>
                                        <i class="ti ti-download"></i>
                                        Download Document
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE BODY -->
@endsection
