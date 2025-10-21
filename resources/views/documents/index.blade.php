@extends('layouts.app')

@section('title', 'Documents')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Documents
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    @can('create', App\Models\Document::class)
                        <a href="{{ route('documents.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus"></i>
                            New Document
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('documents.index') }}">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <select name="department" class="form-select">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ $filters['department'] == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="type" class="form-select">
                                    <option value="">All Types</option>
                                    @foreach($documentTypes as $type)
                                        <option value="{{ $type->value }}" {{ $filters['type'] == $type->value ? 'selected' : '' }}>
                                            {{ $type->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Search documents..." value="{{ $filters['search'] }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="ti ti-search"></i>
                                    Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Documents List -->
            <div class="card">
                <div class="card-body">
                    @if($documents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Document Number</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documents as $document)
                                        <tr>
                                            <td>{{ $document->document_number }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="fw-bold">{{ $document->title }}</div>
                                                        @if($document->description)
                                                            <div class="text-muted">{{ Str::limit($document->description, 50) }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-blue-lt">{{ $document->document_type->label() }}</span>
                                            </td>
                                            <td>{{ $document->department->name }}</td>
                                            <td>
                                                @if($document->activeVersion)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-warning">No Active Version</span>
                                                @endif
                                            </td>
                                            <td>{{ $document->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                <div class="btn-list">
                                                    <a href="{{ route('documents.show', $document) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="ti ti-eye"></i>
                                                        View
                                                    </a>
                                                    @can('update', $document)
                                                        <a href="{{ route('documents.edit', $document) }}" class="btn btn-sm btn-outline-secondary">
                                                            <i class="ti ti-edit"></i>
                                                            Edit
                                                        </a>
                                                    @endcan
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
                                <i class="ti ti-file-text"></i>
                            </div>
                            <p class="empty-title">No documents found</p>
                            <p class="empty-subtitle text-muted">
                                Get started by creating a new document.
                            </p>
                            <div class="empty-action">
                                <a href="{{ route('documents.create') }}" class="btn btn-primary">
                                    <i class="ti ti-plus"></i>
                                    Create Document
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
