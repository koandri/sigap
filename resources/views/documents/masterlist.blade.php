@extends('layouts.app')

@section('title', 'Documents Masterlist')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Documents Masterlist
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                        <i class="ti ti-printer"></i>
                        Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Masterlist by Department and Type -->
            <div class="card">
                <div class="card-body">
                    @if($masterlist->count() > 0)
                        @foreach($masterlist as $departmentName => $departmentDocuments)
                            <div class="mb-4">
                                <h3 class="card-title">{{ $departmentName }}</h3>
                                
                                @foreach($departmentDocuments as $documentType => $documents)
                                    <div class="mb-3">
                                        <h4 class="text-muted">{{ $documentType }}</h4>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-vcenter">
                                                <thead>
                                                    <tr>
                                                        <th>Document Number</th>
                                                        <th>Title</th>
                                                        <th>Status</th>
                                                        <th>Created By</th>
                                                        <th>Created At</th>
                                                        <th>Physical Location</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($documents as $document)
                                                        <tr>
                                                            <td>{{ $document->document_number }}</td>
                                                            <td>
                                                                <div class="fw-bold">{{ $document->title }}</div>
                                                                @if($document->description)
                                                                    <div class="text-muted">{{ Str::limit($document->description, 50) }}</div>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($document->activeVersion)
                                                                    <span class="badge bg-success">Active</span>
                                                                @else
                                                                    <span class="badge bg-warning">No Active Version</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $document->creator->name }}</td>
                                                            <td>{{ $document->created_at->format('Y-m-d') }}</td>
                                                            <td>{{ $document->physical_location_string }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @else
                        <div class="empty">
                            <div class="empty-icon">
                                <i class="ti ti-list"></i>
                            </div>
                            <p class="empty-title">No documents found</p>
                            <p class="empty-subtitle text-muted">
                                There are no documents in the system yet.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .page-header,
    .btn,
    .card-footer {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .table {
        font-size: 12px;
    }
}
</style>
@endsection
