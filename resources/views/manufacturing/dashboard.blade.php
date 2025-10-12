@extends('layouts.app')

@section('title', 'Manufacturing Dashboard')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Overview
                </div>
                <h2 class="page-title">
                    Manufacturing Dashboard
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
                                        <a href="{{ route('manufacturing.items.index', ['category' => $category->id]) }}" class="btn btn-sm btn-outline-primary">View</a>
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
                                    <td class="text-muted">{{ $warehouse->stocked_locations_count }}</td>
                                    <td>
                                        <a href="{{ route('manufacturing.warehouses.show', $warehouse) }}" class="btn btn-sm btn-outline-primary">View</a>
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-filled" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M12 9v2l0 4"/>
                                <path d="M12 17l.01 0"/>
                                <path d="M12 2c5.523 0 10 4.477 10 10s-4.477 10 -10 10s-10 -4.477 -10 -10s4.477 -10 10 -10z"/>
                            </svg>
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
                                @foreach($expiringItems as $location)
                                <tr>
                                    <td>{{ $location->item->name }}</td>
                                    <td>{{ $location->warehouse->name }}@if($location->shelf_area) - {{ $location->shelf_area }}@endif</td>
                                    <td>{{ number_format($location->current_quantity, 2) }} {{ $location->item->unit }}</td>
                                    <td>{{ $location->expiry_date->format('M d, Y') }}</td>
                                    <td>
                                        @php
                                            $daysLeft = $location->expiry_date->diffInDays(now());
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
                                <a href="{{ route('manufacturing.items.index') }}" class="btn btn-outline-primary w-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <polyline points="12 3 20 7.5 20 16.5 12 21 4 16.5 4 7.5 12 3"/>
                                        <line x1="12" y1="12" x2="20" y2="7.5"/>
                                        <line x1="12" y1="12" x2="12" y2="21"/>
                                        <line x1="12" y1="12" x2="4" y2="7.5"/>
                                    </svg>
                                    <br>Manage Items
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('manufacturing.item-categories.index') }}" class="btn btn-outline-primary w-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <rect x="4" y="4" width="6" height="6" rx="1"/>
                                        <rect x="14" y="4" width="6" height="6" rx="1"/>
                                        <rect x="4" y="14" width="6" height="6" rx="1"/>
                                        <rect x="14" y="14" width="6" height="6" rx="1"/>
                                    </svg>
                                    <br>Item Categories
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('manufacturing.warehouses.index') }}" class="btn btn-outline-primary w-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M3 21v-13l9 -4l9 4v13"/>
                                        <path d="M13 13h4v8h-10v-6h6"/>
                                        <path d="M13 21v-9a1 1 0 0 0 -1 -1h-2a1 1 0 0 0 -1 1v3"/>
                                    </svg>
                                    <br>Warehouses
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('manufacturing.warehouses.overview-report') }}" class="btn btn-outline-info w-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M3 3v18h18"/>
                                        <path d="M18 7v10"/>
                                        <path d="M15 10v4"/>
                                        <path d="M12 8v8"/>
                                        <path d="M9 12v4"/>
                                        <path d="M6 10v4"/>
                                    </svg>
                                    <br>Overview Report
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                @can('manufacturing.bom.view')
                                <a href="{{ route('manufacturing.bom.index') }}" class="btn btn-outline-primary w-100">
                                @else
                                <div class="btn btn-outline-secondary w-100 disabled">
                                @endcan
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"/>
                                        <rectangle x="9" y="3" width="6" height="4" rx="2"/>
                                        <path d="M9 12l2 2l4 -4"/>
                                    </svg>
                                    <br>Bill of Materials
                                @can('manufacturing.bom.view')
                                </a>
                                @else
                                </div>
                                @endcan
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="btn btn-outline-secondary w-100 disabled">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M8 9l3 3l-3 3"/>
                                        <line x1="13" y1="15" x2="16" y2="15"/>
                                        <rect x="3" y="4" width="18" height="16" rx="2"/>
                                    </svg>
                                    <br>Production (Coming Soon)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
