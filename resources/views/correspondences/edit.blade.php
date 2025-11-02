@extends('layouts.app')

@section('title', 'Edit Correspondence: ' . $instance->instance_number)

@push('css')
<style>
    /* Set height on card-body so OnlyOffice iframe's 100% height works */
    .onlyoffice-editor-wrapper .card-body {
        height: calc((100vh - 250px) * 2);
        min-height: 1200px;
        padding: 0;
    }
    
    .onlyoffice-editor-wrapper .card {
        margin-bottom: 0;
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">Edit Correspondence</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('correspondences.show', $instance) }}" class="btn btn-outline-secondary">
                        <i class="far fa-arrow-left"></i>&nbsp;
                        Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @include('layouts.alerts')

            <div class="row row-deck row-cards">
                <!-- Correspondence Information Card - Full Width -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Correspondence Information</h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('correspondences.update', $instance) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">Subject</label>
                                            <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" 
                                                   value="{{ old('subject', $instance->subject) }}" required>
                                            @error('subject')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Content Summary</label>
                                            <textarea name="content_summary" class="form-control @error('content_summary') is-invalid @enderror" 
                                                      rows="3">{{ old('content_summary', $instance->content_summary) }}</textarea>
                                            @error('content_summary')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-footer">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="far fa-save"></i>&nbsp;
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit Correspondence Document Card - Full Width -->
                <div class="col-12">
                    <div class="card onlyoffice-editor-wrapper">
                        <div class="card-header">
                            <h3 class="card-title">Edit Correspondence Document</h3>
                        </div>
                        <div class="card-body">
                            <div id="onlyoffice-editor"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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
        var errorHtml = '<div class="alert alert-danger m-3">' + 
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
            '</div>';
        document.getElementById('onlyoffice-editor').innerHTML = errorHtml;
    }
});
</script>
@endpush

