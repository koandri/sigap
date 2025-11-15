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
                    <button type="button" class="btn btn-outline-primary" onclick="printMasterlist()">
                        <i class="far fa-print"></i>&nbsp;
                        Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Filters -->
            <div class="card mb-3 d-print-none">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.document-management.masterlist') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Department</label>
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
                                <label class="form-label">Document Type</label>
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
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Document number or title..." value="{{ $filters['search'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="far fa-filter"></i>&nbsp;
                                        Filter
                                    </button>
                                    <a href="{{ route('reports.document-management.masterlist') }}" class="btn btn-outline-secondary">
                                        <i class="far fa-times"></i>&nbsp;
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Masterlist by Department and Type -->
            <div class="card">
                <div class="card-body">
                    @if($masterlist->count() > 0)
                        @foreach($masterlist as $departmentName => $departmentDocuments)
                            <div class="mb-4">
                                <h3 class="card-title">{{ $departmentName }}</h3>
                                
                                @foreach($departmentDocuments as $documentType => $documents)
                                    <div class="mb-3">
                                        <h4 class="text-muted">
                                            @php
                                                try {
                                                    $typeLabel = \App\Enums\DocumentType::from($documentType)->label();
                                                } catch (\ValueError $e) {
                                                    $typeLabel = 'Unknown Type (' . $documentType . ')';
                                                }
                                            @endphp
                                            {{ $typeLabel }}
                                        </h4>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-vcenter" style="table-layout: fixed; width: 100%;">
                                                <colgroup>
                                                    <col style="width: 12%;">
                                                    <col style="width: 30%;">
                                                    <col style="width: 10%;">
                                                    <col style="width: 18%;">
                                                    <col style="width: 12%;">
                                                    <col style="width: 18%;">
                                                </colgroup>
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
                                                            <td class="text-nowrap">{{ $document->document_number }}</td>
                                                            <td>
                                                                <div class="fw-bold">{{ $document->title }}</div>
                                                                @if($document->description)
                                                                    <div class="text-muted small">{{ Str::limit($document->description, 50) }}</div>
                                                                @endif
                                                            </td>
                                                            <td class="text-nowrap">
                                                                @if($document->activeVersion)
                                                                    <span class="badge bg-success text-white">Active</span>
                                                                @else
                                                                    <span class="badge bg-warning text-white">No Active Version</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-nowrap">{{ $document->creator->name }}</td>
                                                            <td class="text-nowrap">{{ $document->created_at->format('Y-m-d') }}</td>
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
                                <i class="far fa-list"></i>&nbsp;
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

<script>
function printMasterlist() {
    // Get current filter values
    const params = new URLSearchParams();
    const departmentSelect = document.querySelector('select[name="department"]');
    const typeSelect = document.querySelector('select[name="type"]');
    const searchInput = document.querySelector('input[name="search"]');
    
    if (departmentSelect && departmentSelect.value) {
        params.append('department', departmentSelect.value);
    }
    if (typeSelect && typeSelect.value) {
        params.append('type', typeSelect.value);
    }
    if (searchInput && searchInput.value) {
        params.append('search', searchInput.value);
    }
    
    // Open print page in new tab
    const url = '{{ route("reports.document-management.masterlist.print") }}' + (params.toString() ? '?' + params.toString() : '');
    window.open(url, '_blank');
}
</script>
@endsection
