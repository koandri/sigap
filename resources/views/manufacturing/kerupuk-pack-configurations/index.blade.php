@extends('layouts.app')

@section('title', 'Kerupuk Pack Configurations')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.dashboard') }}">Manufacturing</a></li>
                        <li class="breadcrumb-item active">Kerupuk Pack Configurations</li>
                    </ol>
                </nav>
                <h2 class="page-title">Kerupuk Pack Configurations</h2>
                <p class="text-muted mb-0">Define which Pack SKUs can be used for each Kerupuk Kg and conversion ratios</p>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Kerupuk Kg Items</h3>
                <div class="col-auto ms-auto">
                    <form method="GET" action="{{ route('manufacturing.kerupuk-pack-configurations.index') }}" class="d-flex gap-2">
                        <input type="search" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="Search kerupuk items...">
                        <button type="submit" class="btn btn-primary">
                            <i class="far fa-search"></i>
                        </button>
                        @if($search)
                        <a href="{{ route('manufacturing.kerupuk-pack-configurations.index') }}" class="btn btn-outline-secondary">
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
                            <th>Kerupuk Kg Item</th>
                            <th>Category</th>
                            <th class="text-center">Configured Pack SKUs</th>
                            <th class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kerupukKgItems as $kerupukItem)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $kerupukItem->label }}</div>
                            </td>
                            <td>
                                <span class="text-muted">{{ $kerupukItem->itemCategory->name ?? 'N/A' }}</span>
                            </td>
                            <td class="text-center">
                                @if($kerupukItem->kerupuk_pack_configurations_count > 0)
                                    <span class="badge bg-success">
                                        <i class="far fa-check"></i> {{ $kerupukItem->kerupuk_pack_configurations_count }}
                                    </span>
                                @else
                                    <span class="badge bg-warning text-white">
                                        <i class="far fa-exclamation-triangle"></i> Not Set
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                @can('manufacturing.kerupuk-pack-config.view')
                                <a href="{{ route('manufacturing.kerupuk-pack-configurations.manage', $kerupukItem) }}" class="btn btn-sm btn-primary">
                                    <i class="far fa-edit"></i> Manage
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="far fa-box-open fa-3x mb-3 d-block"></i>
                                <p class="mb-0">No kerupuk kg items found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($kerupukKgItems->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Showing {{ $kerupukKgItems->firstItem() }} - {{ $kerupukKgItems->lastItem() }} of {{ $kerupukKgItems->total() }}
                </div>
                {{ $kerupukKgItems->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

