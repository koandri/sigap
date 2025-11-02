@extends('layouts.app')

@section('title', 'Edit Document: ' . $version->document->title . ' v' . $version->version_number)

@push('css')
<style>
    /* Set height on card-body so OnlyOffice iframe's 100% height works */
    .onlyoffice-editor-wrapper .card-body {
        height: calc(100vh - 250px);
        min-height: 600px;
        padding: 0;
    }
    
    .onlyoffice-editor-wrapper .card {
        margin-bottom: 0;
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
                        <i class="far fa-arrow-left"></i>&nbsp;
                        Back to Document
                    </a>
                    @if($version->canBeSubmitted())
                    <form action="{{ route('document-versions.submit', $version) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Submit this version for approval?')">
                            <i class="far fa-paper-plane"></i>&nbsp;
                            Submit for Approval
                        </button>
                    </form>
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
                <div class="card onlyoffice-editor-wrapper">
                    <div class="card-header">
                        <h3 class="card-title">Document Editor</h3>
                        <div class="card-actions">
                            <span class="badge badge-outline text-info">
                                {{ strtoupper($version->file_type) }} Document
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="onlyoffice-editor"></div>
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
    var onlyOfficeServerUrl = '{{ config('dms.onlyoffice.server_url', 'https://office.suryagroup.app') }}';
    var apiScriptUrl = onlyOfficeServerUrl + '/web-apps/apps/api/documents/api.js';
    
    // Load OnlyOffice API script dynamically
    var script = document.createElement('script');
    script.src = apiScriptUrl;
    script.onload = function() {
        console.log('OnlyOffice API loaded successfully');
        initializeEditor();
    };
    script.onerror = function() {
        console.error('Failed to load OnlyOffice API from:', apiScriptUrl);
        showError('Failed to load OnlyOffice editor. Please check your connection to: ' + onlyOfficeServerUrl);
    };
    document.head.appendChild(script);
    
    function initializeEditor() {
        try {
            var config = JSON.parse('{!! json_encode($editorConfig) !!}');
            console.log('OnlyOffice config:', config);
            
            if (typeof DocsAPI !== 'undefined') {
                new DocsAPI.DocEditor('onlyoffice-editor', config);
            } else {
                showError('OnlyOffice API is not available');
            }
        } catch (error) {
            console.error('Error initializing OnlyOffice editor:', error);
            showError('Error initializing OnlyOffice editor: ' + error.message);
        }
    }
    
    function showError(message) {
        var errorHtml = '<div class="alert alert-danger">' + 
            '<h4 class="alert-title">OnlyOffice Connection Error</h4>' +
            '<div class="text-secondary">' + message + '</div>' +
            '<hr>' +
            '<div class="text-secondary">' +
            '<strong>Possible solutions:</strong>' +
            '<ul>' +
            '<li>Check if the OnlyOffice server is running and accessible</li>' +
            '<li>Verify CORS settings on the OnlyOffice server</li>' +
            '<li>For local development, consider using a local OnlyOffice instance</li>' +
            '<li>Check your network connection and firewall settings</li>' +
            '</ul>' +
            '</div>' +
            '<div class="mt-3">' +
            '<a href="{{ route("documents.show", $version->document) }}" class="btn btn-primary">Back to Document</a>' +
            '</div>' +
            '</div>';
        document.getElementById('onlyoffice-editor').innerHTML = errorHtml;
    }
});
</script>
@endpush
