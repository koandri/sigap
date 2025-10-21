@extends('layouts.app')

@section('title', 'Create Form Request')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Create Form Request
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="{{ route('form-requests.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left"></i>
                        Back to Requests
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form method="POST" action="{{ route('form-requests.store') }}">
                @csrf
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Select Forms</h3>
                    </div>
                    <div class="card-body">
                        @if($formDocuments->count() > 0)
                            <div class="row">
                                @foreach($formDocuments as $document)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-fill">
                                                        <div class="fw-bold">{{ $document->title }}</div>
                                                        <div class="text-muted">{{ $document->document_number }}</div>
                                                        <div class="text-muted">{{ $document->department->name }}</div>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <label class="form-label">Quantity</label>
                                                    <input type="number" name="forms[{{ $loop->index }}][quantity]" 
                                                           class="form-control" min="1" max="100" value="1"
                                                           onchange="toggleFormSelection(this, {{ $document->id }})">
                                                    <input type="hidden" name="forms[{{ $loop->index }}][document_version_id]" 
                                                           value="{{ $document->activeVersion->id }}" id="form_version_{{ $document->id }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty">
                                <div class="empty-icon">
                                    <i class="ti ti-file-text"></i>
                                </div>
                                <p class="empty-title">No form documents available</p>
                                <p class="empty-subtitle text-muted">
                                    There are no form documents available for request.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                @if($formDocuments->count() > 0)
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-device-floppy"></i>
                                    Submit Request
                                </button>
                                <a href="{{ route('form-requests.index') }}" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

<script>
function toggleFormSelection(input, documentId) {
    const quantity = parseInt(input.value);
    const versionInput = document.getElementById('form_version_' + documentId);
    
    if (quantity > 0) {
        versionInput.disabled = false;
    } else {
        versionInput.disabled = true;
    }
}

// Initialize form selection
document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('input[type="number"]');
    quantityInputs.forEach(input => {
        const documentId = input.name.match(/forms\[(\d+)\]/)[1];
        toggleFormSelection(input, documentId);
    });
});
</script>
@endsection
