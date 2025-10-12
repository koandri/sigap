@extends('layouts.app')

@section('title', 'Shelf Positions: ' . $shelf->shelf_code)

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.dashboard') }}">Manufacturing</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.warehouses.index') }}">Warehouses</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.warehouses.show', $warehouse) }}">{{ $warehouse->name }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.warehouses.shelf-management', $warehouse) }}">Shelf Management</a></li>
                        <li class="breadcrumb-item active">{{ $shelf->shelf_code }} Positions</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    Shelf Positions
                    <span class="text-muted">- {{ $shelf->shelf_code }}</span>
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('manufacturing.warehouses.position.create', [$warehouse, $shelf]) }}" class="btn btn-primary">
                        <i class="fa-regular fa-plus me-2"></i>
                        Add Position
                    </a>
                    <a href="{{ route('manufacturing.warehouses.shelf-management', $warehouse) }}" class="btn btn-outline-secondary">
                        <i class="fa-regular fa-arrow-left me-2"></i>
                        Back to Shelves
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
                        <h3 class="card-title">Shelf: {{ $shelf->shelf_name }}</h3>
                        <div class="card-actions">
                            <span class="badge bg-blue-lt">{{ $positions->count() }} positions</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Position Code</th>
                                    <th>Position Name</th>
                                    <th>Full Location</th>
                                    <th>Capacity</th>
                                    <th>Status</th>
                                    <th>Items</th>
                                    <th class="w-1">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($positions as $position)
                                <tr>
                                    <td>
                                        <span class="badge bg-green-lt">{{ $position->position_code }}</span>
                                    </td>
                                    <td>
                                        <div class="font-weight-medium">{{ $position->position_name }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-blue-lt">{{ $position->full_location_code }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $position->max_capacity }} max</span>
                                    </td>
                                    <td>
                                        @if($position->is_active)
                                            <span class="badge bg-green-lt">Active</span>
                                        @else
                                            <span class="badge bg-red-lt">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($position->positionItems->count() > 0)
                                            <span class="badge bg-orange-lt">{{ $position->positionItems->count() }} items</span>
                                        @else
                                            <span class="badge bg-gray-lt">Empty</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <a href="{{ route('manufacturing.warehouses.position.edit', [$warehouse, $shelf, $position]) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="fa-regular fa-edit me-1"></i>
                                                Edit
                                            </a>
                                            <form action="{{ route('manufacturing.warehouses.position.destroy', [$warehouse, $shelf, $position]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this position? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fa-regular fa-trash me-1"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <div class="empty">
                                            <div class="empty-img"><img src="https://via.placeholder.com/128x128/e9ecef/6c757d?text=No+Positions" height="128" alt=""></div>
                                            <p class="empty-title">No positions found</p>
                                            <p class="empty-subtitle text-muted">
                                                Create positions for this shelf to organize items.
                                            </p>
                                            <div class="empty-action">
                                                <a href="{{ route('manufacturing.warehouses.position.create', [$warehouse, $shelf]) }}" class="btn btn-primary">
                                                    <i class="fa-regular fa-plus me-2"></i>
                                                    Add First Position
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
