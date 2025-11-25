@extends('layouts.app')

@section('title', 'Item: ' . $item->name)

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.dashboard') }}">Manufacturing</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('options.items.index') }}">Items</a></li>
                        <li class="breadcrumb-item active">{{ $item->name }}</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    {{ $item->name }}
                    @if($item->is_active)
                        <span class="badge bg-green ms-2">Active</span>
                    @else
                        <span class="badge bg-red ms-2">Inactive</span>
                    @endif
                </h2>
                <div class="page-subtitle">
                    <div class="row">
                        <div class="col-auto">
                            ID: <strong>{{ $item->accurate_id }}</strong>
                        </div>
                        @if($item->shortname)
                        <div class="col-auto">
                            Short Name: <strong>{{ $item->shortname }}</strong>
                        </div>
                        @endif
                        <div class="col-auto">
                            Category: <strong>{{ $item->itemCategory->name }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('options.items.edit', $item) }}" class="btn btn-primary">
                        <i class="far fa-edit me-2"></i>&nbsp;
                        Edit Item
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
            <!-- Item Information -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Item Information</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-5">Accurate ID:</dt>
                            <dd class="col-7">{{ $item->accurate_id }}</dd>
                            
                            <dt class="col-5">Name:</dt>
                            <dd class="col-7">{{ $item->name }}</dd>
                            
                            @if($item->shortname)
                            <dt class="col-5">Short Name:</dt>
                            <dd class="col-7">
                                <span class="badge bg-gray-lt">{{ $item->shortname }}</span>
                            </dd>
                            @endif
                            
                            <dt class="col-5">Category:</dt>
                            <dd class="col-7">
                                <a href="{{ route('options.item-categories.show', $item->itemCategory) }}" class="badge bg-blue-lt">
                                    {{ $item->itemCategory->name }}
                                </a>
                            </dd>
                            
                            <dt class="col-5">Unit:</dt>
                            <dd class="col-7">{{ $item->unit ?? '-' }}</dd>
                            
                            @if($item->merk)
                            <dt class="col-5">Brand/Merk:</dt>
                            <dd class="col-7">{{ $item->merk }}</dd>
                            @endif
                            
                            @if($item->qty_kg_per_pack && $item->qty_kg_per_pack > 1)
                            <dt class="col-5">Pack Size:</dt>
                            <dd class="col-7">{{ $item->qty_kg_per_pack }} kg per pack</dd>
                            @endif
                            
                            <dt class="col-5">Status:</dt>
                            <dd class="col-7">
                                @if($item->is_active)
                                    <span class="badge bg-green">Active</span>
                                @else
                                    <span class="badge bg-red">Inactive</span>
                                @endif
                            </dd>
                            
                            <dt class="col-5">Created:</dt>
                            <dd class="col-7">{{ $item->created_at->format('M d, Y H:i') }}</dd>
                            
                            <dt class="col-5">Updated:</dt>
                            <dd class="col-7">{{ $item->updated_at->format('M d, Y H:i') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Inventory Locations -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Inventory Locations</h3>
                        <div class="card-actions">
                            <span class="text-muted">Total: {{ number_format($item->total_quantity, 2) }} {{ $item->unit }}</span>
                        </div>
                    </div>
                    @if($item->positionItems->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Warehouse</th>
                                    <th>Shelf Position</th>
                                    <th class="text-end">Quantity</th>
                                    <th>Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($item->positionItems as $positionItem)
                                <tr>
                                    <td>
                                        <a href="{{ route('warehouses.warehouses.show', $positionItem->shelfPosition->warehouseShelf->warehouse) }}" class="text-reset">
                                            {{ $positionItem->shelfPosition->warehouseShelf->warehouse->name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-blue-lt">{{ $positionItem->shelfPosition->warehouseShelf->shelf_code }}-{{ $positionItem->shelfPosition->position_code }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="font-weight-medium">{{ number_format($positionItem->quantity, 2) }}</span>
                                        <small class="text-muted">{{ $item->unit }}</small>
                                    </td>
                                    <td>
                                        <span class="text-muted" title="{{ $positionItem->updated_at->format('M d, Y H:i:s') }}">
                                            {{ $positionItem->updated_at->diffForHumans() }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="card-body">
                        <div class="text-center py-4 text-muted">
                            <div class="empty">
                                <div class="empty-icon">
                                    <i class="far fa-building" style="font-size: 48px; color: #ccc;"></i>&nbsp;
                                </div>
                                <p class="empty-title">No inventory positions</p>
                                <p class="empty-subtitle text-muted">This item has not been assigned to any warehouse shelf positions yet.</p>
                            </div>
                        </div>
                    </div>
                    @endif
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
                            <div class="col-md-4 mb-4">
                                <a href="{{ route('options.items.edit', $item) }}" class="btn btn-outline-primary w-100">
                                    <i class="far fa-edit"></i>&nbsp;
                                    Edit Item
                                </a>
                            </div>
                            <div class="col-md-4 mb-4">
                                <a href="{{ route('options.items.index', ['category' => $item->item_category_id]) }}" class="btn btn-outline-info w-100">
                                    <i class="far fa-layer-group"></i>&nbsp;
                                    Similar Items
                                </a>
                            </div>
                            <div class="col-md-4 mb-4">
                                <a href="{{ route('options.items.index') }}" class="btn btn-outline-secondary w-100">
                                    <i class="far fa-arrow-left"></i>&nbsp;
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
