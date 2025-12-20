@extends('layouts.app')

@section('title', $document->title)

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        {{ $document->title }}
                    </h2>
                    <div class="text-muted">
                        Document Number: {{ $document->document_number }}
                    </div>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        @can('update', $document)
                            <a href="{{ route('documents.edit', $document) }}" class="btn btn-outline-secondary">
                                <i class="far fa-edit"></i>&nbsp;
                                Edit
                            </a>
                        @endcan
                        <a href="{{ route('documents.index') }}" class="btn btn-outline-primary">
                            <i class="far fa-arrow-left"></i>&nbsp;
                            Back to Documents
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @include('layouts.alerts')
            <div class="row">
                <div class="col-md-8">
                    <!-- Document Information -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Document Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Document Number</label>
                                        <div class="form-control-plaintext">{{ $document->document_number }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Document Type</label>
                                        <div class="form-control-plaintext">
                                            <span class="badge bg-blue-lt">{{ $document->document_type->label() }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            @if($document->description)
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <div class="form-control-plaintext">{{ $document->description }}</div>
                                </div>
                            @endif
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Department</label>
                                        <div class="form-control-plaintext">{{ $document->department?->name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Created By</label>
                                        <div class="form-control-plaintext">{{ $document->creator->name }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Physical Location</label>
                                <div class="form-control-plaintext">{{ $document->physical_location_string }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Versions -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Document Versions</h3>
                        </div>
                        <div class="card-body">
                            @if($document->versions->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>Version</th>
                                                <th>Status</th>
                                                <th>Created By</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($document->versions as $version)
                                                <tr>
                                                    <td>{{ $version->version_number }}</td>
                                                    <td>
                                                        <span class="badge {{ $version->isActive() ? 'bg-success' : ($version->isDraft() ? 'bg-warning' : 'bg-secondary') }} text-white">
                                                            {{ $version->status->label() }}
                                                        </span>
                                                        @if($document->document_type->value === 'form' && $version->is_ncr_paper)
                                                            <span class="badge bg-info text-white ms-1" title="3-Ply NCR Paper">
                                                                <i class="far fa-copy"></i>&nbsp; NCR
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $version->creator->name }}</td>
                                                    <td>{{ formatDate($version->created_at) }}</td>
                                                    <td>
                                                        <div class="btn-list">
                                                            @if($version->isActive())
                                                                <a href="{{ route('document-versions.view', $version) }}" class="btn btn-sm btn-outline-primary">
                                                                    <i class="far fa-eye"></i>&nbsp;
                                                                    View
                                                                </a>
                                                            @endif
                                                            @if($version->canBeEdited())
                                                                <a href="{{ route('document-versions.editor', $version) }}" class="btn btn-sm btn-outline-secondary">
                                                                    <i class="far fa-edit"></i>&nbsp;
                                                                    Edit
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="empty">
                                    <div class="empty-icon">
                                        <i class="far fa-file-alt"></i>&nbsp;
                                    </div>
                                    <p class="empty-title">No versions found</p>
                                    <p class="empty-subtitle text-muted">
                                        This document doesn't have any versions yet.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Quick Actions -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            @if($document->document_type->canHaveVersions())
                                @can('create', [App\Models\DocumentVersion::class, $document])
                                    <a href="{{ route('documents.versions.create', $document) }}" class="btn btn-primary w-100 mb-2">
                                        <i class="far fa-plus"></i>&nbsp;
                                        Create New Version
                                    </a>
                                @endcan
                            @endif
                            
                            @if($document->document_type->requiresAccessRequest() && !auth()->user()->hasRole(['Super Admin', 'Owner']))
                                <a href="{{ route('documents.request-access', $document) }}" class="btn btn-outline-info w-100 mb-2">
                                    <i class="far fa-eye"></i>&nbsp;
                                    Request Access
                                </a>
                            @endif
                            
                            @if($document->document_type->value === 'form')
                                <a href="{{ route('form-requests.create') }}" class="btn btn-outline-success w-100 mb-2">
                                    <i class="far fa-file-alt"></i>&nbsp;
                                    Request Forms
                                </a>
                            @endif
                            
                            @if($document->document_type->isTemplate() && $document->activeVersion)
                                @can('view', $document)
                                    @can('create', App\Models\DocumentInstance::class)
                                        <a href="{{ route('correspondences.create', ['template_id' => $document->id]) }}" class="btn btn-outline-primary w-100 mb-2">
                                            <i class="far fa-envelope"></i>&nbsp;
                                            Create Correspondence from Template
                                        </a>
                                    @endcan
                                @endcan
                            @endif
                        </div>
                    </div>

                    <!-- Accessible Departments -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Accessible Departments</h3>
                        </div>
                        <div class="card-body">
                            @if($document->accessibleDepartments->count() > 0)
                                <ul class="list-unstyled">
                                    @foreach($document->accessibleDepartments as $department)
                                        <li class="mb-1">
                                            <span class="badge bg-blue-lt">{{ $department->name }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-muted">
                                    <i class="far fa-info-circle"></i>&nbsp;
                                    No additional departments have access to this document.
                                    <br><small>Only the owner department can access this document.</small>
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
