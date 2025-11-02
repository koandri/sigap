@extends('layouts.app')

@section('title', 'Documents & Forms Grouped by Location')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">Documents & Forms Grouped by Location</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="{{ route('reports.document-management.locations.index') }}" class="btn btn-outline-secondary">
                        <i class="far fa-search"></i>&nbsp;
                        Search Locations
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @include('layouts.alerts')

            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.document-management.locations.group-by-location') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
                                    <option value="all" {{ $filters['type'] === 'all' ? 'selected' : '' }}>All</option>
                                    <option value="documents" {{ $filters['type'] === 'documents' ? 'selected' : '' }}>Documents</option>
                                    <option value="forms" {{ $filters['type'] === 'forms' ? 'selected' : '' }}>Returned Printed Forms</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="far fa-filter"></i>&nbsp;
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Documents Grouped by Location -->
            @if(($filters['type'] === 'all' || $filters['type'] === 'documents') && $documentsByLocation->isNotEmpty())
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Documents by Location</h3>
                </div>
                <div class="card-body">
                    @foreach($documentsByLocation as $location)
                        <div class="card mb-3">
                            <div class="card-header bg-primary-lt">
                                <h4 class="card-title m-0">
                                    <i class="far fa-map-marker-alt"></i>&nbsp;
                                    Room: <strong>{{ $location['room_no'] }}</strong> | 
                                    Cabinet: <strong>{{ $location['cabinet_no'] }}</strong> | 
                                    Shelf: <strong>{{ $location['shelf_no'] }}</strong>
                                    <span class="badge bg-primary text-white ms-2">{{ $location['count'] }} document(s)</span>
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Document Number</th>
                                                <th>Title</th>
                                                <th>Department</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($location['items'] as $document)
                                            <tr>
                                                <td>{{ $document->document_number }}</td>
                                                <td>{{ $document->title }}</td>
                                                <td>{{ $document->department?->name ?? 'N/A' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Printed Forms Grouped by Location -->
            @if(($filters['type'] === 'all' || $filters['type'] === 'forms') && $formsByLocation->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Returned Printed Forms by Location</h3>
                </div>
                <div class="card-body">
                    @foreach($formsByLocation as $location)
                        <div class="card mb-3">
                            <div class="card-header bg-info-lt">
                                <h4 class="card-title m-0">
                                    <i class="far fa-map-marker-alt"></i>&nbsp;
                                    Room: <strong>{{ $location['room_no'] }}</strong> | 
                                    Cabinet: <strong>{{ $location['cabinet_no'] }}</strong> | 
                                    Shelf: <strong>{{ $location['shelf_no'] }}</strong>
                                    <span class="badge bg-info ms-2">{{ $location['count'] }} form(s)</span>
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Form Number</th>
                                                <th>Document</th>
                                                <th>Issued To</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($location['items'] as $form)
                                            <tr>
                                                <td>{{ $form->form_number }}</td>
                                                <td>
                                                    <div>{{ $form->documentVersion->document->document_number }}</div>
                                                    <div class="text-muted small">{{ $form->form_name }}</div>
                                                </td>
                                                <td>{{ $form->issuedTo->name }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Empty State -->
            @if(($filters['type'] === 'all' || $filters['type'] === 'documents') && $documentsByLocation->isEmpty() && 
                ($filters['type'] === 'all' || $filters['type'] === 'forms') && $formsByLocation->isEmpty())
            <div class="card">
                <div class="card-body">
                    <div class="empty">
                        <div class="empty-img"><i class="far fa-inbox" style="font-size: 4rem; color: #ccc;"></i>&nbsp;</div>
                        <p class="empty-title">No items found</p>
                        <p class="empty-text text-muted">No documents or forms have been assigned to physical locations yet.</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

