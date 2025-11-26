@extends('layouts.app')

@section('title', 'Packing Material Blueprints')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.dashboard') }}">Manufacturing</a></li>
                        <li class="breadcrumb-item active">Packing Material Blueprints</li>
                    </ol>
                </nav>
                <h2 class="page-title">Packing Material Blueprints</h2>
                <p class="text-muted mb-0">Define packing materials required for each Pack SKU</p>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pack Items</h3>
                <div class="col-auto ms-auto">
                    <form method="GET" action="{{ route('manufacturing.packing-material-blueprints.index') }}" class="d-flex gap-2">
                        <input type="search" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="Search pack items...">
                        <button type="submit" class="btn btn-primary">
                            <i class="far fa-search"></i>
                        </button>
                        @if($search)
                        <a href="{{ route('manufacturing.packing-material-blueprints.index') }}" class="btn btn-outline-secondary">
                            <i class="far fa-times"></i>
                        </a>
                        @endif
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter table-striped">
                    <thead>
                        <tr>
                            <th>Pack SKU</th>
                            <th>Category</th>
                            <th class="text-center">Materials</th>
                            <th class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packItems as $packItem)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $packItem->label }}</div>
                            </td>
                            <td>
                                <span class="text-muted">{{ $packItem->itemCategory->name ?? 'N/A' }}</span>
                            </td>
                            <td class="text-center">
                                @if($packItem->packing_material_blueprints_count > 0)
                                    <span class="badge bg-success">
                                        <i class="far fa-check"></i> {{ $packItem->packing_material_blueprints_count }}
                                    </span>
                                @else
                                    <span class="badge bg-warning text-white">
                                        <i class="far fa-exclamation-triangle"></i> Not Set
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                @can('manufacturing.packing-blueprints.view')
                                <a href="{{ route('manufacturing.packing-material-blueprints.manage', $packItem) }}" class="btn btn-sm btn-primary">
                                    <i class="far fa-edit"></i> Manage
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="far fa-box-open fa-3x mb-3 d-block"></i>
                                <p class="mb-0">No pack items found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($packItems->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Showing {{ $packItems->firstItem() }} - {{ $packItems->lastItem() }} of {{ $packItems->total() }}
                </div>
                {{ $packItems->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

