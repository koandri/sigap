@extends('layouts.app')

@section('title', 'Recipes')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Manufacturing
                </div>
                <h2 class="page-title">
                    Recipes
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    @can('manufacturing.recipes.create')
                    <a href="{{ route('manufacturing.recipes.create') }}" class="btn btn-primary d-none d-sm-inline-block">
                        <i class="far fa-plus me-2"></i>&nbsp;
                        Create Recipe
                    </a>
                    @endcan
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
                        <form method="GET" action="{{ route('manufacturing.recipes.index') }}">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Search</label>
                                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                                               placeholder="Search by recipe name">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Dough Item</label>
                                        <select class="form-select" name="dough_item">
                                            <option value="">All Dough Items</option>
                                            @foreach($doughItems as $item)
                                            <option value="{{ $item->id }}" {{ request('dough_item') == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}
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
                                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="far fa-filter me-2"></i>&nbsp;
                                                Filter
                                            </button>
                                            <a href="{{ route('manufacturing.recipes.index') }}" class="btn btn-outline-secondary">
                                                <i class="far fa-times"></i>
                                            </a>
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
                        <h3 class="card-title">All Recipes ({{ $recipes->total() }})</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Recipe Name</th>
                                    <th>Dough Item</th>
                                    <th>Recipe Date</th>
                                    <th>Ingredients</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th class="w-1">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recipes as $recipe)
                                <tr>
                                    <td>
                                        <div class="d-flex py-1 align-items-center">
                                            <div class="flex-fill">
                                                <div class="font-weight-medium">{{ $recipe->name }}</div>
                                                @if($recipe->description)
                                                <div class="text-muted small">{{ Str::limit($recipe->description, 60) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-blue-lt">{{ $recipe->doughItem->name }}</span>
                                    </td>
                                    <td>
                                        {{ $recipe->recipe_date->format('d M Y') }}
                                    </td>
                                    <td>
                                        <span class="badge bg-gray-lt">{{ $recipe->ingredients()->count() }} ingredient(s)</span>
                                    </td>
                                    <td>
                                        @if($recipe->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-muted">{{ $recipe->createdBy->name }}</div>
                                        <div class="text-muted small">{{ $recipe->created_at->format('d M Y') }}</div>
                                    </td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <a href="{{ route('manufacturing.recipes.show', $recipe) }}" class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="far fa-eye"></i>
                                            </a>
                                            @can('manufacturing.recipes.edit')
                                            <a href="{{ route('manufacturing.recipes.edit', $recipe) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <div class="empty">
                                            <div class="empty-img"><img src="https://via.placeholder.com/128x128/e9ecef/6c757d?text=No+Data" height="128" alt=""></div>
                                            <p class="empty-title">No recipes found</p>
                                            <p class="empty-subtitle text-muted">
                                                @if(request()->hasAny(['search', 'dough_item', 'status']))
                                                    Try adjusting your filters.
                                                @else
                                                    Create your first recipe to get started.
                                                @endif
                                            </p>
                                            @can('manufacturing.recipes.create')
                                            <div class="empty-action">
                                                <a href="{{ route('manufacturing.recipes.create') }}" class="btn btn-primary">
                                                    <i class="far fa-plus me-2"></i>&nbsp;
                                                    Create Recipe
                                                </a>
                                            </div>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($recipes->hasPages())
                    <div class="card-footer d-flex align-items-center">
                        {{ $recipes->withQueryString()->links('layouts.pagination') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


















