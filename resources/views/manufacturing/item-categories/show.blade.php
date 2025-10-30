@extends('layouts.app')

@section('title', 'Item Category: ' . $itemCategory->name)

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.dashboard') }}">Manufacturing</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.item-categories.index') }}">Item Categories</a></li>
                        <li class="breadcrumb-item active">{{ $itemCategory->name }}</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    {{ $itemCategory->name }}
                </h2>
                @if($itemCategory->description)
                <div class="page-subtitle">
                    {{ $itemCategory->description }}
                </div>
                @endif
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('manufacturing.item-categories.edit', $itemCategory) }}" class="btn btn-primary">
                        <i class="far fa-edit me-2"></i>
                        Edit Category
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')
        
        <!-- Category Information -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Category Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <dt>Name:</dt>
                                <dd>{{ $itemCategory->name }}</dd>
                            </div>
                            @if($itemCategory->description)
                            <div class="col-md-3">
                                <dt>Description:</dt>
                                <dd>{{ $itemCategory->description }}</dd>
                            </div>
                            @endif
                            <div class="col-md-2">
                                <dt>Items Count:</dt>
                                <dd><span class="badge bg-primary text-white">{{ $itemCategory->items->count() }} items</span></dd>
                            </div>
                            <div class="col-md-2">
                                <dt>Active Items:</dt>
                                <dd><span class="badge bg-success text-white">{{ $itemCategory->items->where('is_active', true)->count() }} active</span></dd>
                            </div>
                            <div class="col-md-1">
                                <dt>Created:</dt>
                                <dd>{{ $itemCategory->created_at->format('M d, Y') }}</dd>
                            </div>
                            <div class="col-md-2">
                                <dt>Last Updated:</dt>
                                <dd>{{ $itemCategory->updated_at->format('M d, Y H:i') }}</dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4 d-print-none">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <a href="{{ route('manufacturing.items.index') }}?category={{ $itemCategory->id }}" class="btn btn-outline-primary w-100">
                                    <i class="far fa-list"></i>
                                    View All Items
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="{{ route('manufacturing.item-categories.edit', $itemCategory) }}" class="btn btn-outline-secondary w-100">
                                    <i class="far fa-edit"></i>
                                    Edit Category
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="{{ route('manufacturing.item-categories.index') }}" class="btn btn-outline-secondary w-100">
                                    <i class="far fa-arrow-left"></i>
                                    Back to Categories
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items in Category -->
        <div class="row row-deck row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Items in this Category ({{ $itemCategory->items->count() }})</h3>
                    </div>
                    @if($itemCategory->items->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Short Name</th>
                                    <th>Unit</th>
                                    <th>Status</th>
                                    <th class="d-print-none">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($itemCategory->items->sortBy('name') as $item)
                                <tr>
                                    <td>
                                        <div>
                                            <div class="font-weight-medium">{{ $item->name }}</div>
                                            <div class="text-muted small">
                                                ID: {{ $item->accurate_id }}
                                                @if($item->merk) • {{ $item->merk }}@endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($item->shortname)
                                            <span class="badge bg-secondary text-white">{{ $item->shortname }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $item->unit ?? '-' }}
                                        @if($item->qty_kg_per_pack > 1)
                                            <br><small class="text-muted">{{ $item->qty_kg_per_pack }} kg/pack</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->is_active)
                                            <span class="badge bg-success text-white">Active</span>
                                        @else
                                            <span class="badge bg-danger text-white">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="d-print-none">
                                        <div class="btn-list flex-nowrap">
                                            <a href="{{ route('manufacturing.items.show', $item) }}" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                            <a href="{{ route('manufacturing.items.edit', $item) }}" class="btn btn-sm btn-outline-secondary">
                                                Edit
                                            </a>
                                        </div>
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
                                    <i class="far fa-list-alt" style="font-size: 48px; color: #ccc;"></i>
                                </div>
                                <p class="empty-title">No items in this category</p>
                                <p class="empty-subtitle text-muted">Items can only be added via Excel import.</p>
                                <div class="empty-action">
                                    <a href="{{ route('manufacturing.items.import') }}" class="btn btn-primary">
                                        <i class="far fa-file-arrow-up me-2"></i>
                                        Import Items
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Back Navigation -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('manufacturing.item-categories.index') }}" class="btn btn-outline-secondary">
                                <i class="far fa-arrow-left me-2"></i>
                                Back to Categories
                            </a>
                            <div class="btn-list">
                                <a href="{{ route('manufacturing.item-categories.edit', $itemCategory) }}" class="btn btn-primary">
                                    <i class="far fa-edit me-2"></i>
                                    Edit Category
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
