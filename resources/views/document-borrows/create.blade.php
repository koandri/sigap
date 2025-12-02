@extends('layouts.app')

@section('title', 'Request to Borrow Document')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('document-borrows.index') }}">My Borrows</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Request to Borrow</li>
                        </ol>
                    </nav>
                    <h2 class="page-title">
                        Request to Borrow Document
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
                            <h3 class="card-title">Borrow Request Form</h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('document-borrows.store') }}" method="POST">
                                @csrf
                                
                                <div class="mb-3">
                                    <label for="document_id" class="form-label required">Select Document</label>
                                    <select name="document_id" id="document_id" class="form-select @error('document_id') is-invalid @enderror" required>
                                        <option value="">Select a document to borrow...</option>
                                        @foreach($documents as $document)
                                            <option value="{{ $document->id }}" {{ old('document_id') == $document->id ? 'selected' : '' }}>
                                                {{ $document->document_number }} - {{ $document->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('document_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-hint">
                                        Only documents you have access to and are currently available are shown.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="due_date" class="form-label">Due Date (Optional)</label>
                                    <input type="date" 
                                           name="due_date" 
                                           id="due_date" 
                                           class="form-control @error('due_date') is-invalid @enderror"
                                           value="{{ old('due_date', $defaultDueDate) }}"
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                    @error('due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-hint">
                                        Default is 7 days from today. Leave empty for no due date.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes (Optional)</label>
                                    <textarea name="notes" 
                                              id="notes" 
                                              class="form-control @error('notes') is-invalid @enderror" 
                                              rows="3" 
                                              placeholder="Add any notes about why you need this document...">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-footer">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="far fa-paper-plane"></i>&nbsp;
                                        Submit Borrow Request
                                    </button>
                                    <a href="{{ route('document-borrows.index') }}" class="btn btn-secondary">
                                        <i class="far fa-arrow-left"></i>&nbsp;
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
                            <h3 class="card-title">Borrowing Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="far fa-info-circle"></i>&nbsp;
                                <strong>How it works:</strong>
                            </div>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="far fa-check text-success"></i>&nbsp;
                                    Submit your borrow request
                                </li>
                                <li class="mb-2">
                                    <i class="far fa-clock text-warning"></i>&nbsp;
                                    Wait for approval (if required)
                                </li>
                                <li class="mb-2">
                                    <i class="far fa-book-reader text-primary"></i>&nbsp;
                                    Collect document from Document Control
                                </li>
                                <li class="mb-2">
                                    <i class="far fa-undo text-info"></i>&nbsp;
                                    Return document by due date
                                </li>
                            </ul>

                            @if(auth()->user()->hasRole(['Super Admin', 'Owner']))
                            <div class="alert alert-success mt-3">
                                <i class="far fa-bolt"></i>&nbsp;
                                <strong>Auto-approval:</strong> As a Super Admin/Owner, your requests are automatically approved.
                            </div>
                            @else
                            <div class="alert alert-warning mt-3">
                                <i class="far fa-hourglass-half"></i>&nbsp;
                                <strong>Approval required:</strong> Your request will need to be approved by a Super Admin or Owner.
                            </div>
                            @endif
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
    // Initialize Tom Select for document dropdown if available
    if (typeof TomSelect !== 'undefined') {
        new TomSelect('#document_id', {
            placeholder: 'Search for a document...',
            allowEmptyOption: true,
        });
    }
});
</script>
@endpush

