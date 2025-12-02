@extends('layouts.app')

@section('title', 'Warehouse Dashboard')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Overview
                </div>
                <h2 class="page-title">
                    Warehouse Dashboard
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        
        <!-- Statistics Cards -->
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Items</div>
                        </div>
                        <div class="h1 mb-0">{{ $stats['total_items'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Categories</div>
                        </div>
                        <div class="h1 mb-0">{{ $stats['total_categories'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Warehouses</div>
                        </div>
                        <div class="h1 mb-0">{{ $stats['total_warehouses'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Stocked Locations</div>
                        </div>
                        <div class="h1 mb-0">{{ $stats['total_locations'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Items by Category -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Items by Category</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Items Count</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($itemsByCategory as $category)
                                <tr>
                                    <td>{{ $category->name }}</td>
                                    <td class="text-muted">{{ $category->items_count }}</td>
                                    <td>
                                        <a href="{{ route('options.items.index', ['category' => $category->id]) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Warehouses Overview -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Warehouses Overview</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Warehouse</th>
                                    <th>Locations with Stock</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($warehousesWithStock as $warehouse)
                                <tr>
                                    <td>
                                        <div class="d-flex py-1 align-items-center">
                                            <span class="avatar me-2" style="background-image: url(https://via.placeholder.com/32x32/2563eb/ffffff?text={{ substr($warehouse->code, 0, 1) }})"></span>
                                            <div class="flex-fill">
                                                <div class="font-weight-medium">{{ $warehouse->name }}</div>
                                                <div class="text-muted">{{ $warehouse->code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-muted">{{ $warehouse->stocked_shelves_count }}</td>
                                    <td>
                                        <a href="{{ route('warehouses.warehouses.show', $warehouse) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if($expiringItems->count() > 0)
        <!-- Expiring Items Alert -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title text-warning">
                            <i class="far fa-circle-exclamation"></i>&nbsp;
                            Items Expiring Soon (Next 30 Days)
                        </h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Location</th>
                                    <th>Quantity</th>
                                    <th>Expiry Date</th>
                                    <th>Days Left</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expiringItems as $positionItem)
                                <tr>
                                    <td>{{ $positionItem->item->name }}</td>
                                    <td>{{ $positionItem->shelfPosition->warehouseShelf->warehouse->name }}@if($positionItem->shelfPosition->warehouseShelf->shelf_code) - {{ $positionItem->shelfPosition->warehouseShelf->shelf_code }}@endif</td>
                                    <td>{{ number_format($positionItem->quantity, 2) }} {{ $positionItem->item->unit }}</td>
                                    <td>{{ $positionItem->expiry_date->format('M d, Y') }}</td>
                                    <td>
                                        @php
                                            $daysLeft = $positionItem->expiry_date->diffInDays(now());
                                        @endphp
                                        <span class="badge @if($daysLeft <= 7) bg-red @elseif($daysLeft <= 14) bg-orange @else bg-yellow @endif">
                                            {{ $daysLeft }} days
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('options.items.index') }}" class="btn btn-outline-primary w-100">
                                    <i class="far fa-box icon mb-2"></i>&nbsp;
                                    <br>Manage Items
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('options.item-categories.index') }}" class="btn btn-outline-primary w-100">
                                    <i class="far fa-grid-2 icon mb-2"></i>&nbsp;
                                    <br>Item Categories
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('warehouses.warehouses.index') }}" class="btn btn-outline-primary w-100">
                                    <i class="far fa-warehouse icon mb-2"></i>&nbsp;
                                    <br>Warehouses
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('warehouses.overview-report') }}" class="btn btn-outline-info w-100">
                                    <i class="far fa-chart-column icon mb-2"></i>&nbsp;
                                    <br>Overview Report
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






