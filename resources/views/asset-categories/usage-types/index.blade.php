@extends('layouts.app')

@section('title', 'Usage Types')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    <a href="{{ route('options.asset-categories.show', $category) }}">{{ $category->name }}</a>
                </div>
                <h2 class="page-title">
                    Usage Types
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    @can('maintenance.assets.manage')
                    <a href="{{ route('options.asset-categories.usage-types.create', $category) }}" class="btn btn-primary">
                        <i class="far fa-plus"></i>&nbsp;
                        Add Usage Type
                    </a>
                    @endcan
                    <a href="{{ route('options.asset-categories.show', $category) }}" class="btn btn-outline-secondary">
                        <i class="far fa-arrow-left"></i>&nbsp;
                        Back to Category
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Usage Types ({{ $usageTypes->count() }})</h3>
            </div>
            <div class="card-body">
                @if($usageTypes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Lifetime Unit</th>
                                    <th>Expected Average</th>
                                    <th>Status</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usageTypes as $usageType)
                                <tr>
                                    <td>{{ $usageType->name }}</td>
                                    <td>{{ $usageType->description ?? '-' }}</td>
                                    <td>
                                        @if($usageType->lifetime_unit)
                                            <span class="badge bg-info">{{ $usageType->lifetime_unit->label() }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($usageType->expected_average_lifetime)
                                            {{ number_format($usageType->expected_average_lifetime, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $usageType->is_active ? 'success' : 'secondary' }}">
                                            {{ $usageType->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        @can('maintenance.assets.manage')
                                        <div class="btn-list">
                                            <a href="{{ route('options.usage-types.edit', $usageType) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="far fa-pen"></i>
                                            </a>
                                            <form action="{{ route('options.usage-types.recalculate', $usageType) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-info" title="Recalculate Metrics">
                                                    <i class="far fa-calculator"></i>
                                                </button>
                                            </form>
                                        </div>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty">
                        <div class="empty-icon">
                            <i class="far fa-tags"></i>
                        </div>
                        <p class="empty-title">No usage types</p>
                        <p class="empty-subtitle text-muted">
                            Create usage types to differentiate assets within this category.
                        </p>
                        @can('maintenance.assets.manage')
                        <div class="empty-action">
                            <a href="{{ route('options.asset-categories.usage-types.create', $category) }}" class="btn btn-primary">
                                <i class="far fa-plus"></i>
                                Add Usage Type
                            </a>
                        </div>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

