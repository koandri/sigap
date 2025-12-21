@extends('layouts.app')

@section('title', 'Create Correspondence')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">Create Correspondence</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('correspondences.index') }}" class="btn btn-outline-secondary">
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
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Select Template</h3>
                        </div>
                        <div class="card-body">
                            @if($templates->isEmpty() && !request()->has('search') && !request()->has('type'))
                                <div class="alert alert-info">
                                    <i class="far fa-info-circle me-2"></i>&nbsp;
                                    No templates available. Create an Internal Memo or Outgoing Letter document with an active version to use as a template.
                                </div>
                            @else
                                <!-- Search and Filter -->
                                <form method="GET" action="{{ route('correspondences.create') }}" class="mb-4">
                                    <input type="hidden" name="template_id" value="{{ $selectedTemplateId }}">
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <input type="text" name="search" class="form-control" 
                                                   value="{{ request('search') }}" 
                                                   placeholder="Search templates by title, number, or department...">
                                        </div>
                                        <div class="col-md-4">
                                            <select name="type" class="form-select">
                                                <option value="">All Types</option>
                                                <option value="internal_memo" {{ request('type') == 'internal_memo' ? 'selected' : '' }}>Internal Memo</option>
                                                <option value="outgoing_letter" {{ request('type') == 'outgoing_letter' ? 'selected' : '' }}>Outgoing Letter</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="far fa-search"></i>&nbsp; Search
                                            </button>
                                        </div>
                                    </div>
                                    @if(request()->has('search') || request()->has('type'))
                                        <div class="mt-2">
                                            <a href="{{ route('correspondences.create', $selectedTemplateId ? ['template_id' => $selectedTemplateId] : []) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="far fa-times"></i>&nbsp; Clear Filters
                                            </a>
                                        </div>
                                    @endif
                                </form>

                                @if($templates->isEmpty())
                                    <div class="alert alert-warning">
                                        <i class="far fa-exclamation-triangle me-2"></i>&nbsp;
                                        No templates found matching your criteria.
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
                                                @foreach($templates as $template)
                                                    <tr>
                                                        <td>{{ $template->document_number }}</td>
                                                        <td>
                                                            <strong>{{ $template->title }}</strong>
                                                            @if($selectedTemplateId == $template->id)
                                                                <span class="badge bg-primary ms-2">Selected</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-blue-lt">{{ $template->document_type->label() }}</span>
                                                        </td>
                                                        <td>{{ $template->department?->name ?? 'N/A' }}</td>
                                                        <td>
                                                            <span class="badge bg-success text-white">v{{ $template->activeVersion->version_number }}</span>
                                                        </td>
                                                        <td>{{ $template->creator->name }}</td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createCorrespondenceModal{{ $template->id }}">
                                                                <i class="far fa-envelope"></i>&nbsp;
                                                                Use Template
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Modals for creating correspondence -->
                                    @foreach($templates as $template)
                                    <div class="modal modal-blur fade" id="createCorrespondenceModal{{ $template->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Create Correspondence from Template</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('correspondences.store', $template) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Template</label>
                                                            <div class="form-control-plaintext">
                                                                <strong>{{ $template->title }}</strong>
                                                                <small class="text-muted">({{ $template->document_number }})</small>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label required">Subject</label>
                                                            <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" 
                                                                   value="{{ old('subject') }}" required placeholder="Enter subject...">
                                                            @error('subject')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Content Summary</label>
                                                            <textarea name="content_summary" class="form-control @error('content_summary') is-invalid @enderror" 
                                                                      rows="3" placeholder="Optional summary...">{{ old('content_summary') }}</textarea>
                                                            @error('content_summary')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="far fa-plus"></i>&nbsp;
                                                            Create Correspondence
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

