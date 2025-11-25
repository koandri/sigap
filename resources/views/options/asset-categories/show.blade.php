@extends('layouts.app')

@section('title', 'Asset Category Details')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    {{ $assetCategory->name }}
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                @can('maintenance.assets.manage')
                <div class="btn-list">
                    <a href="{{ route('options.asset-categories.edit', $assetCategory) }}" class="btn btn-primary">
                        <i class="far fa-pen"></i>&nbsp;
                        Edit Category
                    </a>
                </div>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row">
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Category Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <div class="form-control-plaintext">{{ $assetCategory->name }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Code</label>
                                    <div class="form-control-plaintext">{{ $assetCategory->code }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <div class="form-control-plaintext">{{ $assetCategory->description ?? 'No description provided' }}</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-{{ $assetCategory->is_active ? 'success' : 'secondary' }} text-white">
                                    {{ $assetCategory->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Assets Count</label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-secondary text-white">{{ $assetCategory->assets->count() }} assets</span>
                            </div>
                        </div>


                </div>
            </div>

            <div class="col-12">
                <!-- Assets in this category -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Assets in this Category</h3>
                    </div>
                    <div class="card-body">
                        @if($assetCategory->assets->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>Location</th>
                                            <th>Department</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($assetCategory->assets as $asset)
                                        <tr>
                                            <td>{{ $asset->code }}</td>
                                            <td>
                                                <a href="{{ route('options.assets.show', $asset) }}">
                                                    {{ $asset->name }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $asset->status === 'operational' ? 'success' : ($asset->status === 'down' ? 'danger' : 'warning') }} text-white">
                                                    {{ ucfirst($asset->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $asset->location?->name ?? '-' }}</td>
                                            <td>{{ $asset->department?->name ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty">
                                <div class="empty-icon">
                                    <i class="far fa-clipboard icon"></i>&nbsp;
                                </div>
                                <p class="empty-title">No assets in this category</p>
                                <p class="empty-subtitle text-muted">
                                    Assets will appear here once they are assigned to this category.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection