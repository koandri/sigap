@extends('layouts.app')

@section('title', 'Items')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
<style>
/* Fix Tom Select sizing to match Bootstrap form controls */
.ts-control {
    min-height: calc(1.5em + 0.75rem + 2px) !important;
    padding: 0.375rem 0.75rem !important;
    font-size: 0.875rem !important;
    line-height: 1.5 !important;
    border: 1px solid #dadce0 !important;
    border-radius: 4px !important;
    background-color: #fff !important;
    display: flex !important;
    align-items: center !important;
}

.ts-control.single .ts-control-input {
    height: auto !important;
    flex: 1 !important;
}

.ts-control.single .ts-control-input input {
    height: auto !important;
    line-height: 1.5 !important;
    border: none !important;
    background: transparent !important;
    padding: 0 !important;
    margin: 0 !important;
}

/* Fix Tom Select dropdown background and readability */
.ts-dropdown {
    background-color: #ffffff !important;
    border: 1px solid #dadce0 !important;
    border-radius: 4px !important;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    opacity: 1 !important;
}

.ts-dropdown .ts-dropdown-content {
    background-color: #ffffff !important;
}

.ts-dropdown .option {
    background-color: #ffffff !important;
    color: #212529 !important;
    padding: 0.375rem 0.75rem !important;
}

.ts-dropdown .option:hover,
.ts-dropdown .option.selected {
    background-color: #e9ecef !important;
    color: #212529 !important;
}

.ts-dropdown .option.active {
    background-color: #0d6efd !important;
    color: #ffffff !important;
}
</style>
@endpush

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Manufacturing
                </div>
                <h2 class="page-title">
                    Items
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('options.items.import') }}" class="btn btn-primary">
                        <i class="far fa-file-arrow-up me-2"></i>&nbsp;
                        <span class="d-none d-sm-inline">Import from Excel</span>
                        <span class="d-sm-none">Import</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')
        
        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('options.items.index') }}">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Search</label>
                                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                                               placeholder="Search by name, ID, or short name">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Category</label>
                                        <select class="form-select" name="category" id="category-select">
                                            <option value="">All Categories</option>
                                            @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status">
                                            <option value="">All Statuses</option>
                                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="btn-list">
                                            <button type="submit" class="btn btn-primary">Filter</button>
                                            <a href="{{ route('options.items.index') }}" class="btn btn-outline-secondary">Reset</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row row-deck row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Items ({{ $items->total() }})</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Accurate ID</th>
                                    <th>Item</th>
                                    <th>Category</th>
                                    <th>Unit & Pack Size</th>
                                    <th>Status</th>
                                    <th class="w-1">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                <tr>
                                    <td>
                                        {{ $item->accurate_id }}
                                    </td>
                                    <td>
                                        {{ $item->name }}
                                    </td>
                                    <td>
                                        <span class="badge bg-blue-lt">{{ $item->itemCategory->name }}</span>
                                    </td>
                                    <td>
                                        @if($item->unit)
                                            <div>{{ $item->unit }}</div>
                                        @endif
                                        @if($item->qty_kg_per_pack > 1)
                                            <small class="text-muted">{{ $item->qty_kg_per_pack }} kg/pack</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->is_active)
                                            <span class="badge bg-green-lt">Active</span>
                                        @else
                                            <span class="badge bg-red-lt">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <a href="{{ route('options.items.show', $item) }}" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                            <a href="{{ route('options.items.edit', $item) }}" class="btn btn-sm btn-outline-secondary">
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
                                            <p class="empty-title">No items found</p>
                                            <p class="empty-subtitle text-muted">
                                                @if(request()->hasAny(['search', 'category', 'status']))
                                                    Try adjusting your filters or import items from Excel.
                                                @else
                                                    Import your first items to get started.
                                                @endif
                                            </p>
                                            <div class="empty-action">
                                                <a href="{{ route('options.items.import') }}" class="btn btn-primary">
                                                    <i class="far fa-file-arrow-up me-2"></i>&nbsp;
                                                    Import Items
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($items->hasPages())
                    <div class="card-footer d-flex align-items-center">
                        {{ $items->withQueryString()->links('layouts.pagination') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new TomSelect('#category-select', {
        allowEmptyOption: true,
        placeholder: 'All Categories'
    });
});
</script>
@endpush
