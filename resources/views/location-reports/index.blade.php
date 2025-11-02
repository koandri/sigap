@extends('layouts.app')

@section('title', 'Location Reports')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">Location Reports</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="{{ route('reports.document-management.locations.group-by-location') }}" class="btn btn-info">
                        <i class="far fa-layer-group"></i>&nbsp;
                        Group by Location
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @include('layouts.alerts')

            <!-- Search Form -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Search</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.document-management.locations.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
                                    <option value="all" {{ $filters['type'] === 'all' ? 'selected' : '' }}>All</option>
                                    <option value="documents" {{ $filters['type'] === 'documents' ? 'selected' : '' }}>Documents</option>
                                    <option value="forms" {{ $filters['type'] === 'forms' ? 'selected' : '' }}>Scanned Printed Forms</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Room No</label>
                                <input type="text" name="room_no" class="form-control" placeholder="Room No" value="{{ $filters['room_no'] }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cabinet No</label>
                                <input type="text" name="cabinet_no" class="form-control" placeholder="Cabinet No" value="{{ $filters['cabinet_no'] }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Shelf No</label>
                                <input type="text" name="shelf_no" class="form-control" placeholder="Shelf No" value="{{ $filters['shelf_no'] }}">
                            </div>
                            <div class="col-md-9">
                                <label class="form-label">Find Location by Document/Form Number or Title</label>
                                <input type="text" name="search" class="form-control" placeholder="Search by document number, form number, or title..." value="{{ $filters['search'] }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="far fa-search"></i>&nbsp;
                                    Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Search Results (Finding Locations) -->
            @if($filters['search'] && $searchResults->isNotEmpty())
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Location Search Results</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Number</th>
                                    <th>Title</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($searchResults as $result)
                                <tr>
                                    <td>
                                        <span class="badge {{ $result['type'] === 'document' ? 'bg-primary' : 'bg-info' }}">
                                            {{ $result['type'] === 'document' ? 'Document' : 'Returned Printed Form' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($result['type'] === 'document')
                                            {{ $result['item']->document_number }}
                                        @else
                                            {{ $result['item']->form_number }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($result['type'] === 'document')
                                            {{ $result['item']->title }}
                                        @else
                                            {{ $result['item']->form_name }}
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $result['location_string'] }}</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Documents in Location -->
            @if(($filters['type'] === 'all' || $filters['type'] === 'documents') && !$filters['search'])
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Documents</h3>
                </div>
                <div class="card-body">
                    @if($documents->isEmpty())
                        <div class="empty">
                            <div class="empty-img"><i class="far fa-folder-open" style="font-size: 4rem; color: #ccc;"></i>&nbsp;</div>
                            <p class="empty-title">No documents found</p>
                            <p class="empty-text text-muted">Try adjusting your search filters</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Document Number</th>
                                        <th>Title</th>
                                        <th>Department</th>
                                        <th>Physical Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documents as $document)
                                    <tr>
                                        <td>{{ $document->document_number }}</td>
                                        <td>{{ $document->title }}</td>
                                        <td>{{ $document->department?->name ?? 'N/A' }}</td>
                                        <td><strong>{{ $document->physical_location_string }}</strong></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Printed Forms in Location -->
            @if(($filters['type'] === 'all' || $filters['type'] === 'forms') && !$filters['search'])
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Returned Printed Forms</h3>
                </div>
                <div class="card-body">
                    @if($printedForms->isEmpty())
                        <div class="empty">
                            <div class="empty-img"><i class="far fa-file-alt" style="font-size: 4rem; color: #ccc;"></i>&nbsp;</div>
                            <p class="empty-title">No returned printed forms found</p>
                            <p class="empty-text text-muted">Try adjusting your search filters</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Form Number</th>
                                        <th>Document</th>
                                        <th>Issued To</th>
                                        <th>Physical Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($printedForms as $form)
                                    <tr>
                                        <td>{{ $form->form_number }}</td>
                                        <td>
                                            <div>{{ $form->documentVersion->document->document_number }}</div>
                                            <div class="text-muted small">{{ $form->form_name }}</div>
                                        </td>
                                        <td>{{ $form->issuedTo->name }}</td>
                                        <td><strong>{{ $form->physical_location_string }}</strong></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

