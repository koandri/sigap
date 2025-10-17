@extends('layouts.app')

@section('title', 'Bill of Materials')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Manufacturing
                </div>
                <h2 class="page-title">
                    Bill of Materials
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    @can('manufacturing.bom.create')
                    <a href="{{ route('manufacturing.bom.create') }}" class="btn btn-primary d-none d-sm-inline-block">
                        <i class="fa-regular fa-plus"></i>
                        Create BoM Template
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
        <div class="row row-deck row-cards mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Filters</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('manufacturing.bom.index') }}">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">BoM Type</label>
                                        <select class="form-select" name="type">
                                            <option value="">All Types</option>
                                            @foreach($bomTypes as $type)
                                                <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                                                    {{ $type->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Search</label>
                                        <input type="text" class="form-control" name="search" placeholder="Search by name or code..." value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-outline-primary">Filter</button>
                                            <a href="{{ route('manufacturing.bom.index') }}" class="btn btn-outline-secondary">Clear</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- BoM Templates List -->
        <div class="row row-deck row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">BoM Templates ({{ $bomTemplates->total() }})</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Template Name</th>
                                    <th>Type</th>
                                    <th>Output Product</th>
                                    <th>Quantity</th>
                                    <th>Created By</th>
                                    <th>Created</th>
                                    <th class="w-1">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bomTemplates as $template)
                                <tr>
                                    <td>
                                        <span class="text-muted">{{ $template->code }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex py-1 align-items-center">
                                            <div class="flex-fill">
                                                <div class="font-weight-medium">{{ $template->name }}</div>
                                                @if($template->description)
                                                <div class="text-muted text-truncate" style="max-width: 200px;">{{ $template->description }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-outline text-blue">{{ $template->bomType->full_name }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-fill">
                                                <div class="font-weight-medium">{{ $template->outputItem->name }}</div>
                                                <div class="text-muted">{{ $template->outputItem->itemCategory->name ?? 'No Category' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ number_format($template->output_quantity, 3) }} {{ $template->output_unit ?: $template->outputItem->unit }}</span>
                                    </td>
                                    <td>
                                        <div class="text-muted">{{ $template->createdBy->name }}</div>
                                    </td>
                                    <td>
                                        <div class="text-muted">{{ $template->created_at->format('M d, Y') }}</div>
                                    </td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            @can('manufacturing.bom.view')
                                            <a href="{{ route('manufacturing.bom.show', $template) }}" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                            @endcan
                                            
                                            @can('manufacturing.bom.edit')
                                            <a href="{{ route('manufacturing.bom.edit', $template) }}" class="btn btn-sm btn-outline-secondary">
                                                Edit
                                            </a>
                                            @endcan
                                            
                                            @can('manufacturing.bom.create')
                                            <a href="{{ route('manufacturing.bom.copy', $template) }}" class="btn btn-sm btn-outline-info" title="Copy Template">
                                                <i class="fa-regular fa-copy"></i>
                                            </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <div class="empty">
                                            <div class="empty-icon">
                                                <i class="fa-regular fa-clipboard icon"></i>
                                            </div>
                                            <p class="empty-title">No BoM templates found</p>
                                            <p class="empty-subtitle text-muted">
                                                @if(request()->hasAny(['type', 'status', 'search']))
                                                    Try adjusting your search criteria or <a href="{{ route('manufacturing.bom.index') }}">clear filters</a>.
                                                @else
                                                    Get started by creating your first Bill of Materials template.
                                                @endif
                                            </p>
                                            @can('manufacturing.bom.create')
                                                @if(!request()->hasAny(['type', 'status', 'search']))
                                                <div class="empty-action">
                                                    <a href="{{ route('manufacturing.bom.create') }}" class="btn btn-primary">
                                                        Create First BoM Template
                                                    </a>
                                                </div>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($bomTemplates->hasPages())
                    <div class="card-footer">
                        {{ $bomTemplates->withQueryString()->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
