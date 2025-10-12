@extends('layouts.app')

@section('title', 'BoM Template: ' . $bom->name)

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.dashboard') }}">Manufacturing</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.bom.index') }}">Bill of Materials</a></li>
                        <li class="breadcrumb-item active">{{ $bom->code }}</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    {{ $bom->name }}
                    @if($bom->bomType)
                    <span class="badge bg-{{ $bom->bomType->category === 'job_costing' ? 'orange' : 'blue' }} text-white ms-2">
                        {{ $bom->bomType->full_name }}
                    </span>
                    @else
                    <span class="badge bg-secondary text-white ms-2">
                        No Type
                    </span>
                    @endif
                </h2>
                <div class="page-subtitle">
                    <div class="row">
                        <div class="col-auto">
                            Code: <strong>{{ $bom->code }}</strong>
                        </div>
                        <div class="col-auto">
                            Version: <strong>{{ $bom->version }}</strong>
                        </div>
                        <div class="col-auto">
                            Created: <strong>{{ $bom->created_at ? $bom->created_at->format('M d, Y') : 'N/A' }}</strong>
                        </div>
                        <div class="col-auto">
                            By: <strong>{{ $bom->createdBy ? $bom->createdBy->name : 'N/A' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    @can('manufacturing.bom.create')
                    @if($bom && $bom->id)
                    <a href="{{ route('manufacturing.bom.copy', $bom) }}" class="btn btn-outline-info">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <rect x="8" y="8" width="12" height="12" rx="2"/>
                            <path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2"/>
                        </svg>
                        Copy Template
                    </a>
                    @else
                    <span class="btn btn-outline-info disabled">Copy Template</span>
                    @endif
                    @endcan
                    
                    @can('manufacturing.bom.edit')
                    @if($bom && $bom->id)
                    <a href="{{ route('manufacturing.bom.edit', $bom) }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/>
                            <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/>
                            <path d="M16 5l3 3"/>
                        </svg>
                        Edit Template
                    </a>
                    @else
                    <span class="btn btn-primary disabled">Edit Template</span>
                    @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')
        
        <div class="row row-deck row-cards">
            <!-- Basic Information -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Template Information</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-5">Code:</dt>
                            <dd class="col-7">{{ $bom->code }}</dd>
                            
                            <dt class="col-5">Name:</dt>
                            <dd class="col-7">{{ $bom->name }}</dd>
                            
                            <dt class="col-5">Type:</dt>
                            <dd class="col-7">
                                @if($bom->bomType)
                                <span class="badge bg-{{ $bom->bomType->category === 'job_costing' ? 'orange' : 'blue' }} text-white">
                                    {{ $bom->bomType->full_name }}
                                </span>
                                @else
                                <span class="badge bg-secondary text-white">No Type</span>
                                @endif
                            </dd>
                            
                            <dt class="col-5">Version:</dt>
                            <dd class="col-7">{{ $bom->version }}</dd>
                            
                            <dt class="col-5">Active:</dt>
                            <dd class="col-7">
                                @if($bom->is_active)
                                    <span class="badge bg-green text-white">Active</span>
                                @else
                                    <span class="badge bg-secondary text-white">Inactive</span>
                                @endif
                            </dd>
                            
                            <dt class="col-5">Template:</dt>
                            <dd class="col-7">
                                @if($bom->is_template)
                                    <span class="badge bg-purple text-white">Template</span>
                                @else
                                    <span class="badge bg-secondary text-white">Regular</span>
                                @endif
                            </dd>
                        </dl>
                        
                        @if($bom->description)
                        <div class="mt-3">
                            <strong>Description:</strong>
                            <p class="text-muted">{{ $bom->description }}</p>
                        </div>
                        @endif
                        
                        @if($bom->parentTemplate)
                        <div class="mt-3">
                            <strong>Copied from:</strong>
                            <a href="{{ route('manufacturing.bom.show', $bom->parentTemplate) }}" class="text-blue">
                                {{ $bom->parentTemplate->name }} ({{ $bom->parentTemplate->code }})
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                
                @if($bom->childTemplates->count() > 0)
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Copied Templates</h3>
                    </div>
                    <div class="card-body">
                        @foreach($bom->childTemplates as $child)
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-fill">
                                <a href="{{ route('manufacturing.bom.show', $child) }}" class="text-blue">
                                    {{ $child->name }}
                                </a>
                                <div class="text-muted small">{{ $child->code }}</div>
                            </div>
                            <div class="text-muted small">{{ $child->created_at ? $child->created_at->format('M d') : 'N/A' }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Output Product -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Output Product</h3>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-3 bg-blue text-white">
                                        {{ substr($bom->outputItem->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-weight-medium">{{ $bom->outputItem->name }}</div>
                                        <div class="text-muted">{{ $bom->outputItem->itemCategory->name ?? 'No Category' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="h2 mb-0">{{ number_format($bom->output_quantity, 3) }}</div>
                                <div class="text-muted">{{ $bom->output_unit ?: $bom->outputItem->unit }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Ingredients -->
        <div class="row row-deck row-cards mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ingredients ({{ $bom->ingredients->count() }})</h3>
                    </div>
                    @if($bom->ingredients->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Ingredient</th>
                                    <th>Category</th>
                                    <th class="text-end">Quantity</th>
                                    <th>Unit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bom->ingredients as $index => $ingredient)
                                <tr>
                                    <td>{{ $ingredient->sort_order }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-xs me-2 bg-blue text-white">
                                                {{ substr($ingredient->ingredientItem->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-weight-medium">{{ $ingredient->ingredientItem->name }}</div>
                                                @if($ingredient->ingredientItem->code)
                                                <div class="text-muted small">{{ $ingredient->ingredientItem->code }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($ingredient->ingredientItem->itemCategory)
                                        <span class="badge badge-outline text-white">{{ $ingredient->ingredientItem->itemCategory->name }}</span>
                                        @else
                                        <span class="text-muted">No Category</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="h4 mb-0">{{ number_format($ingredient->quantity, 3) }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $ingredient->display_unit }}</span>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <circle cx="12" cy="12" r="3"/>
                                        <path d="M12 1v6m0 6v6"/>
                                        <path d="M21 12h-6m-6 0h-6"/>
                                    </svg>
                                </div>
                                <p class="empty-title">No ingredients defined</p>
                                <p class="empty-subtitle text-muted">This BoM template doesn't have any ingredients yet.</p>
                                @can('manufacturing.bom.edit')
                                <div class="empty-action">
                                    <a href="{{ route('manufacturing.bom.edit', $bom) }}" class="btn btn-primary">
                                        Add Ingredients
                                    </a>
                                </div>
                                @endcan
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
                            @can('manufacturing.bom.create')
                            <div class="col-md-4 mb-4">
                                @if($bom && $bom->id)
                                <a href="{{ route('manufacturing.bom.copy', $bom) }}" class="btn btn-outline-info w-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <rect x="8" y="8" width="12" height="12" rx="2"/>
                                        <path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2"/>
                                    </svg>
                                    <br>Copy Template
                                </a>
                                @else
                                <span class="btn btn-outline-info w-100 disabled">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <rect x="8" y="8" width="12" height="12" rx="2"/>
                                        <path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2"/>
                                    </svg>
                                    <br>Copy Template
                                </span>
                                @endif
                            </div>
                            @endcan
                            
                            @can('manufacturing.bom.edit')
                            <div class="col-md-4 mb-4">
                                @if($bom && $bom->id)
                                <a href="{{ route('manufacturing.bom.edit', $bom) }}" class="btn btn-outline-primary w-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/>
                                        <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/>
                                        <path d="M16 5l3 3"/>
                                    </svg>
                                    <br>Edit Template
                                </a>
                                @else
                                <span class="btn btn-outline-primary w-100 disabled">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/>
                                        <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/>
                                        <path d="M16 5l3 3"/>
                                    </svg>
                                    <br>Edit Template
                                </span>
                                @endif
                            </div>
                            @endcan
                            
                            
                            <div class="col-md-4 mb-4">
                                <a href="{{ route('manufacturing.bom.index') }}" class="btn btn-outline-secondary w-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <line x1="9" y1="14" x2="20" y2="3"/>
                                        <path d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5"/>
                                    </svg>
                                    <br>Back to List
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

@section('styles')
<style>
@media print {
    .page-header,
    .card:last-child {
        display: none !important;
    }
    
    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
}
</style>
@endsection
