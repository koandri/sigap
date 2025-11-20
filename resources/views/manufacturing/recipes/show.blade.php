@extends('layouts.app')

@section('title', 'Recipe: ' . $recipe->name)

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.dashboard') }}">Manufacturing</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.recipes.index') }}">Recipes</a></li>
                        <li class="breadcrumb-item active">{{ $recipe->name }}</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    {{ $recipe->name }}
                    @if($recipe->is_active)
                        <span class="badge bg-green ms-2">Active</span>
                    @else
                        <span class="badge bg-red ms-2">Inactive</span>
                    @endif
                </h2>
                <div class="page-subtitle">
                    <div class="row">
                        <div class="col-auto">
                            Dough Item: <strong>{{ $recipe->doughItem->name }}</strong>
                        </div>
                        <div class="col-auto">
                            Recipe Date: <strong>{{ $recipe->recipe_date->format('d M Y') }}</strong>
                        </div>
                        <div class="col-auto">
                            Created By: <strong>{{ $recipe->createdBy->name }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    @can('manufacturing.recipes.create')
                    <a href="{{ route('manufacturing.recipes.duplicate', $recipe) }}" class="btn btn-success">
                        <i class="far fa-copy me-2"></i>&nbsp;
                        Copy Recipe
                    </a>
                    @endcan
                    @can('manufacturing.recipes.edit')
                    <a href="{{ route('manufacturing.recipes.edit', $recipe) }}" class="btn btn-primary">
                        <i class="far fa-edit me-2"></i>&nbsp;
                        Edit Recipe
                    </a>
                    @endcan
                    <a href="{{ route('manufacturing.recipes.index') }}" class="btn btn-outline-secondary">
                        <i class="far fa-arrow-left me-2"></i>&nbsp;
                        Back to Recipes
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
            <!-- Recipe Information -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recipe Information</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-5">Dough Item:</dt>
                            <dd class="col-7">
                                <span class="badge bg-blue-lt">{{ $recipe->doughItem->name }}</span>
                            </dd>
                            
                            <dt class="col-5">Recipe Name:</dt>
                            <dd class="col-7">{{ $recipe->name }}</dd>
                            
                            <dt class="col-5">Recipe Date:</dt>
                            <dd class="col-7">{{ $recipe->recipe_date->format('d M Y') }}</dd>
                            
                            <dt class="col-5">Status:</dt>
                            <dd class="col-7">
                                @if($recipe->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </dd>
                            
                            @if($recipe->description)
                            <dt class="col-5">Description:</dt>
                            <dd class="col-7">{{ $recipe->description }}</dd>
                            @endif
                            
                            <dt class="col-5">Created By:</dt>
                            <dd class="col-7">{{ $recipe->createdBy->name }}</dd>
                            
                            <dt class="col-5">Created At:</dt>
                            <dd class="col-7">{{ $recipe->created_at->format('d M Y H:i') }}</dd>
                            
                            <dt class="col-5">Updated At:</dt>
                            <dd class="col-7">{{ $recipe->updated_at->format('d M Y H:i') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            
            <!-- Ingredients -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ingredients ({{ $recipe->ingredients->count() }})</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Ingredient</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recipe->ingredients->sortBy('sort_order') as $ingredient)
                                <tr>
                                    <td>
                                        <div class="font-weight-medium">{{ $ingredient->ingredientItem->name }}</div>
                                        @if($ingredient->ingredientItem->accurate_id)
                                        <div class="text-muted small">ID: {{ $ingredient->ingredientItem->accurate_id }}</div>
                                        @endif
                                    </td>
                                    <td>{{ number_format($ingredient->quantity, 3) }}</td>
                                    <td>{{ $ingredient->ingredientItem->unit ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        No ingredients added yet.
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

