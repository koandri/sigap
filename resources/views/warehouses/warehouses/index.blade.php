@extends('layouts.app')

@section('title', 'Warehouses')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Manufacturing
                </div>
                <h2 class="page-title">
                    Warehouses
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('warehouses.picklist') }}" class="btn btn-outline-success">
                        <i class="far fa-list-check me-2"></i>&nbsp;
                        <span class="d-none d-sm-inline">Generate Picklist</span>
                        <span class="d-sm-none">Picklist</span>
                    </a>
                    <a href="{{ route('warehouses.overview-report') }}" class="btn btn-outline-info">
                        <i class="far fa-chart-line me-2"></i>&nbsp;
                        <span class="d-none d-sm-inline">Overview Report</span>
                        <span class="d-sm-none">Report</span>
                    </a>
                    <a href="{{ route('warehouses.warehouses.create') }}" class="btn btn-primary">
                        <i class="far fa-plus me-2"></i>&nbsp;
                        <span class="d-none d-sm-inline">New Warehouse</span>
                        <span class="d-sm-none">New</span>
                    </a>
                </div>
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
                        <h3 class="card-title">All Warehouses</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Warehouse</th>
                                    <th>Description</th>
                                    <th>Total Shelves</th>
                                    <th>Occupied Shelves</th>
                                    <th>Status</th>
                                    <th class="w-1">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($warehouses as $warehouse)
                                <tr>
                                    <td>
                                        <div class="flex-fill">
                                            <div class="font-weight-medium">{{ $warehouse->name }}</div>
                                            <div class="text-muted">{{ $warehouse->code }}</div>
                                        </div>
                                    </td>
                                    <td class="text-muted">
                                        {{ Str::limit($warehouse->description, 50) }}
                                    </td>
                                    <td>
                                        <span class="badge bg-blue-lt">{{ $warehouse->total_shelves }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-green-lt">{{ $warehouse->occupied_shelves }}</span>
                                    </td>
                                    <td>
                                        @if($warehouse->is_active)
                                            <span class="badge bg-green-lt">Active</span>
                                        @else
                                            <span class="badge bg-red-lt">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <a href="{{ route('warehouses.warehouses.show', $warehouse) }}" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                            <a href="{{ route('warehouses.warehouses.shelf-inventory', $warehouse) }}" class="btn btn-sm btn-outline-success">
                                                <i class="far fa-th-large me-1"></i>&nbsp;
                                                Inventory
                                            </a>
                                            <a href="{{ route('warehouses.warehouses.bulk-edit', $warehouse) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="far fa-edit me-1"></i>&nbsp;
                                                Bulk Edit
                                            </a>
                                            <a href="{{ route('warehouses.warehouses.shelf-management', $warehouse) }}" class="btn btn-sm btn-outline-info">
                                                <i class="far fa-layer-group me-1"></i>&nbsp;
                                                Shelves
                                            </a>
                                            <a href="{{ route('warehouses.warehouses.edit', $warehouse) }}" class="btn btn-sm btn-outline-secondary">
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <div class="empty">
                                            <div class="empty-img"><img src="https://via.placeholder.com/128x128/e9ecef/6c757d?text=No+Data" height="128" alt=""></div>
                                            <p class="empty-title">No warehouses found</p>
                                            <p class="empty-subtitle text-muted">
                                                Create your first warehouse to get started.
                                            </p>
                                            <div class="empty-action">
                                                <a href="{{ route('warehouses.warehouses.create') }}" class="btn btn-primary">
                                                    <i class="far fa-plus me-2"></i>&nbsp;
                                                    Create Warehouse
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($warehouses->hasPages())
                    <div class="card-footer d-flex align-items-center">
                        {{ $warehouses->links('layouts.pagination') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Summary Cards -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Warehouses</div>
                        </div>
                        <div class="h2 mb-0">{{ $warehouses->total() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Active Warehouses</div>
                        </div>
                        <div class="h2 mb-0">{{ $warehouses->where('is_active', true)->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Locations</div>
                        </div>
                        <div class="h2 mb-0">{{ $warehouses->sum('total_locations') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Stocked Locations</div>
                        </div>
                        <div class="h2 mb-0">{{ $warehouses->sum('stocked_locations') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
