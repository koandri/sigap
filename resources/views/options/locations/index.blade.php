@extends('layouts.app')

@section('title', 'Locations')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    Locations
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                @can('maintenance.assets.manage')
                <div class="btn-list">
                    <a href="{{ route('options.locations.create') }}" class="btn btn-primary d-none d-sm-inline-block">
                        <i class="far fa-plus"></i>
                        Add Location
                    </a>
                </div>
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
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Name or code">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('options.locations.index') }}" class="btn btn-outline-secondary">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Locations Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Locations ({{ $locations->total() }})</h3>
            </div>
            <div class="card-body">
                @if($locations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th width="100">Code</th>
                                    <th>Name</th>
                                    <th width="150">Assets Count</th>
                                    <th width="120">Status</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($locations as $location)
                                <tr>
                                    <td>
                                        <span class="text-muted">{{ $location->code }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $location->name }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary text-white">{{ $location->assets_count ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $location->is_active ? 'success' : 'secondary' }} text-white">
                                            {{ $location->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-list">
                                            <a href="{{ route('options.locations.show', $location) }}" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                            @can('maintenance.assets.manage')
                                            <a href="{{ route('options.locations.edit', $location) }}" class="btn btn-sm btn-outline-secondary">
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

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $locations->links() }}
                    </div>
                @else
                    <div class="empty">
                        <div class="empty-icon">
                            <i class="far fa-location-dot icon"></i>
                        </div>
                        <p class="empty-title">No locations found</p>
                        <p class="empty-subtitle text-muted">
                            Get started by creating your first location.
                        </p>
                        @can('maintenance.assets.manage')
                        <div class="empty-action">
                            <a href="{{ route('options.locations.create') }}" class="btn btn-primary">
                                <i class="far fa-plus"></i>
                                Add Location
                            </a>
                        </div>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

