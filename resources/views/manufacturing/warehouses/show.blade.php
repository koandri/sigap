@extends('layouts.app')

@section('title', 'Warehouse: ' . $warehouse->name)

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.dashboard') }}">Manufacturing</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.warehouses.index') }}">Warehouses</a></li>
                        <li class="breadcrumb-item active">{{ $warehouse->name }}</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    {{ $warehouse->name }}
                    @if($warehouse->is_active)
                        <span class="badge bg-green text-white ms-2">Active</span>
                    @else
                        <span class="badge bg-red text-white ms-2">Inactive</span>
                    @endif
                    @if($warehouse->is_default)
                        <span class="badge bg-purple text-white ms-2">Default</span>
                    @endif
                </h2>
                @if($warehouse->description)
                <div class="page-subtitle">
                    {{ $warehouse->description }}
                </div>
                @endif
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('manufacturing.warehouses.shelf-inventory', $warehouse) }}" class="btn btn-outline-success">
                        <i class="far fa-th-large me-2"></i>
                        Manage Inventory
                    </a>
                    <a href="{{ route('manufacturing.warehouses.bulk-edit', $warehouse) }}" class="btn btn-outline-warning">
                        <i class="far fa-edit me-2"></i>
                        Bulk Edit
                    </a>
                    <a href="{{ route('manufacturing.warehouses.shelf-management', $warehouse) }}" class="btn btn-outline-info">
                        <i class="far fa-layer-group me-2"></i>
                        Manage Shelves
                    </a>
                    <a href="{{ route('manufacturing.warehouses.edit', $warehouse) }}" class="btn btn-primary">
                        <i class="far fa-edit me-2"></i>
                        Edit Warehouse
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
            <!-- Warehouse Information -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Warehouse Information</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-5">Name:</dt>
                            <dd class="col-7">{{ $warehouse->name }}</dd>
                            
                            @if($warehouse->code)
                            <dt class="col-5">Code:</dt>
                            <dd class="col-7">
                                <span class="badge bg-gray-lt">{{ $warehouse->code }}</span>
                            </dd>
                            @endif
                            
                            @if($warehouse->description)
                            <dt class="col-5">Description:</dt>
                            <dd class="col-7">{{ $warehouse->description }}</dd>
                            @endif
                            
                            <dt class="col-5">Status:</dt>
                            <dd class="col-7">
                                @if($warehouse->is_active)
                                    <span class="badge bg-green text-white">Active</span>
                                @else
                                    <span class="badge bg-red text-white">Inactive</span>
                                @endif
                                @if($warehouse->is_default)
                                    <span class="badge bg-purple text-white ms-1">Default</span>
                                @endif
                            </dd>
                            
                            <dt class="col-5">Items:</dt>
                            <dd class="col-7">
                                <span class="badge bg-blue text-white">{{ $shelfStats['total_positions'] }} positions</span>
                            </dd>
                            
                            <dt class="col-5">Created:</dt>
                            <dd class="col-7">{{ $warehouse->created_at->format('M d, Y H:i') }}</dd>
                            
                            <dt class="col-5">Updated:</dt>
                            <dd class="col-7">{{ $warehouse->updated_at->format('M d, Y H:i') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Inventory Summary -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Inventory Summary</h3>
                        <div class="card-actions">
                            <a href="{{ route('manufacturing.warehouses.shelf-inventory', $warehouse) }}" class="btn btn-primary btn-sm">
                                <i class="far fa-th-large me-2"></i>
                                Manage Inventory
                            </a>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <div class="h1 mb-3">{{ $shelfStats['total_shelves'] }}</div>
                                    <div class="text-muted">Total Shelves</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <div class="h1 mb-3 text-success">{{ $shelfStats['occupied_shelves'] }}</div>
                                    <div class="text-muted">Occupied Shelves</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <div class="h1 mb-3 text-info">{{ $shelfStats['total_positions'] }}</div>
                                    <div class="text-muted">Total Positions</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <div class="h1 mb-3 text-warning">{{ $shelfStats['occupied_positions'] }}</div>
                                    <div class="text-muted">Occupied Positions</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <div class="h1 mb-3 text-primary">{{ $shelfStats['occupancy_rate'] }}%</div>
                                    <div class="text-muted">Occupancy Rate</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <div class="h1 mb-3 text-danger">{{ $shelfStats['expiring_items'] }}</div>
                                    <div class="text-muted">Expiring Items (30 days)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('manufacturing.warehouses.shelf-inventory', $warehouse) }}" class="btn btn-outline-primary w-100">
                                    <i class="far fa-th-large"></i>
                                    Manage Inventory
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('manufacturing.warehouses.bulk-edit', $warehouse) }}" class="btn btn-outline-warning w-100">
                                    <i class="far fa-edit"></i>
                                    Bulk Edit
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('manufacturing.warehouses.edit', $warehouse) }}" class="btn btn-outline-secondary w-100">
                                    <i class="far fa-edit"></i>
                                    Edit Warehouse
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('manufacturing.warehouses.index') }}" class="btn btn-outline-secondary w-100">
                                    <i class="far fa-arrow-left"></i>
                                    Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
