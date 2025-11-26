@extends('layouts.app')

@section('title', 'Item Categories')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Manufacturing
                </div>
                <h2 class="page-title">
                    Item Categories
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('options.item-categories.create') }}" class="btn btn-primary">
                        <i class="far fa-plus me-2"></i>&nbsp;
                        <span class="d-none d-sm-inline">New Category</span>
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
                        <h3 class="card-title">All Item Categories</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Category Name</th>
                                    <th>Description</th>
                                    <th>Items Count</th>
                                    <th>Created</th>
                                    <th class="w-1">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                <tr>
                                    <td>
                                        {{ $category->name }}
                                    </td>
                                    <td>
                                        @if($category->description)
                                        <div class="text-muted">{{ Str::limit($category->description, 50) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-blue-lt">{{ $category->items_count }} items</span>
                                    </td>
                                    <td class="text-muted">
                                        {{ $category->created_at->format('M d, Y') }}
                                    </td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <a href="{{ route('options.item-categories.show', $category) }}" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                            <a href="{{ route('options.item-categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary">
                                                Edit
                                            </a>
                                            @if($category->items_count == 0)
                                            <form method="POST" action="{{ route('options.item-categories.destroy', $category) }}" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this category?')">
                                                    Delete
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <div class="empty">
                                            <div class="empty-img"><img src="https://via.placeholder.com/128x128/e9ecef/6c757d?text=No+Data" height="128" alt=""></div>
                                            <p class="empty-title">No categories found</p>
                                            <p class="empty-subtitle text-muted">
                                                Create your first item category to get started.
                                            </p>
                                            <div class="empty-action">
                                                <a href="{{ route('options.item-categories.create') }}" class="btn btn-primary">
                                                    <i class="far fa-plus me-2"></i>&nbsp;
                                                    Create Category
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($categories->hasPages())
                    <div class="card-footer d-flex align-items-center">
                        {{ $categories->links('layouts.pagination') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
