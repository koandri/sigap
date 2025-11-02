@extends('layouts.app')

@section('title', 'Shelf Management: ' . $warehouse->name)

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
                        <li class="breadcrumb-item active">Shelf Management</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    Shelf Management
                    <span class="text-muted">- {{ $warehouse->name }}</span>
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('manufacturing.warehouses.shelf.create', $warehouse) }}" class="btn btn-primary">
                        <i class="far fa-plus me-2"></i>&nbsp;
                        Add Shelf
                    </a>
                    <a href="{{ route('manufacturing.warehouses.show', $warehouse) }}" class="btn btn-outline-secondary">
                        <i class="far fa-arrow-left me-2"></i>&nbsp;
                        Back to Warehouse
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
                        <h3 class="card-title">Warehouse Shelves</h3>
                        <div class="card-actions">
                            <span class="badge bg-blue-lt">{{ $shelves->count() }} shelves</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Shelf Code</th>
                                    <th>Shelf Name</th>
                                    <th>Positions</th>
                                    <th>Capacity</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th class="w-1">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shelves as $shelf)
                                <tr>
                                    <td>
                                        <span class="badge bg-blue-lt">{{ $shelf->shelf_code }}</span>
                                    </td>
                                    <td>
                                        <div class="font-weight-medium">{{ $shelf->shelf_name }}</div>
                                        @if($shelf->description)
                                        <div class="text-muted small">{{ Str::limit($shelf->description, 50) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-green-lt">{{ $shelf->shelf_positions_count }} positions</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $shelf->max_capacity }} max</span>
                                    </td>
                                    <td>
                                        @if($shelf->is_active)
                                            <span class="badge bg-green-lt">Active</span>
                                        @else
                                            <span class="badge bg-red-lt">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $shelf->created_at->format('M j, Y') }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <a href="{{ route('manufacturing.warehouses.shelf-positions', [$warehouse, $shelf]) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="far fa-th-large me-1"></i>&nbsp;
                                                Positions
                                            </a>
                                            <a href="{{ route('manufacturing.warehouses.shelf.edit', [$warehouse, $shelf]) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="far fa-edit me-1"></i>&nbsp;
                                                Edit
                                            </a>
                                            <form action="{{ route('manufacturing.warehouses.shelf.destroy', [$warehouse, $shelf]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this shelf? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="far fa-trash me-1"></i>&nbsp;
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
                                            <div class="empty-img"><img src="https://via.placeholder.com/128x128/e9ecef/6c757d?text=No+Shelves" height="128" alt=""></div>
                                            <p class="empty-title">No shelves found</p>
                                            <p class="empty-subtitle text-muted">
                                                Create your first shelf to organize this warehouse.
                                            </p>
                                            <div class="empty-action">
                                                <a href="{{ route('manufacturing.warehouses.shelf.create', $warehouse) }}" class="btn btn-primary">
                                                    <i class="far fa-plus me-2"></i>&nbsp;
                                                    Add First Shelf
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
