@extends('layouts.app')

@section('title', 'Asset Components')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    <a href="{{ route('options.assets.show', $asset) }}">{{ $asset->name }}</a>
                </div>
                <h2 class="page-title">
                    Components
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    @can('maintenance.assets.manage')
                    <a href="{{ route('assets.components.attach', $asset) }}" class="btn btn-primary">
                        <i class="far fa-plus"></i>&nbsp;
                        Attach Component
                    </a>
                    @endcan
                    <a href="{{ route('options.assets.show', $asset) }}" class="btn btn-outline-secondary">
                        <i class="far fa-arrow-left"></i>&nbsp;
                        Back to Asset
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')

        <!-- Parent Asset Info (if this is a component) -->
        @if($asset->isComponent() && $asset->parentAsset)
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Parent Asset</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Asset:</strong> 
                        <a href="{{ route('options.assets.show', $asset->parentAsset) }}">
                            {{ $asset->parentAsset->name }} ({{ $asset->parentAsset->code }})
                        </a>
                    </div>
                    <div class="col-md-6">
                        <strong>Component Type:</strong>
                        @if($asset->component_type)
                            <span class="badge bg-info">{{ $asset->component_type->label() }}</span>
                        @endif
                    </div>
                </div>
                @if($asset->installed_date)
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Installed Date:</strong> {{ $asset->installed_date->format('Y-m-d') }}
                    </div>
                    @if($asset->installed_usage_value !== null)
                    <div class="col-md-6">
                        <strong>Start Usage:</strong> {{ number_format($asset->installed_usage_value, 2) }}
                        @if($asset->lifetime_unit)
                            {{ $asset->lifetime_unit->label() }}
                        @endif
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Components List -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Child Components ({{ $components->count() }})</h3>
            </div>
            <div class="card-body">
                @if($components->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Installed Date</th>
                                    <th>Start Usage</th>
                                    <th>Status</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($components as $component)
                                <tr>
                                    <td>
                                        <a href="{{ route('options.assets.show', $component) }}">
                                            {{ $component->code }}
                                        </a>
                                    </td>
                                    <td>{{ $component->name }}</td>
                                    <td>
                                        @if($component->component_type)
                                            <span class="badge bg-info">{{ $component->component_type->label() }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $component->installed_date ? $component->installed_date->format('Y-m-d') : '-' }}
                                    </td>
                                    <td>
                                        @if($component->installed_usage_value !== null)
                                            {{ number_format($component->installed_usage_value, 2) }}
                                            @if($component->lifetime_unit)
                                                {{ $component->lifetime_unit->label() }}
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $component->is_active ? 'success' : 'secondary' }}">
                                            {{ $component->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        @can('maintenance.assets.manage')
                                        <a href="{{ route('assets.components.detach', $component) }}" class="btn btn-sm btn-outline-danger">
                                            <i class="far fa-unlink"></i>
                                            Detach
                                        </a>
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
                            <i class="far fa-puzzle-piece"></i>
                        </div>
                        <p class="empty-title">No components attached</p>
                        <p class="empty-subtitle text-muted">
                            This asset has no child components attached.
                        </p>
                        @can('maintenance.assets.manage')
                        <div class="empty-action">
                            <a href="{{ route('assets.components.attach', $asset) }}" class="btn btn-primary">
                                <i class="far fa-plus"></i>
                                Attach Component
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





