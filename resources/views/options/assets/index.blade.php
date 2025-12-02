@extends('layouts.app')

@section('title', 'Assets')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    Assets
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('options.assets.qr-index') }}" class="btn btn-outline-primary">
                        <i class="far fa-qrcode"></i>&nbsp;
                        <span class="d-none d-sm-inline">View QR Codes</span>
                        <span class="d-sm-none">QR Codes</span>
                    </a>
                    @can('create', App\Models\Asset::class)
                    <a href="{{ route('options.assets.create') }}" class="btn btn-primary">
                        <i class="far fa-plus"></i>&nbsp;
                        <span class="d-none d-sm-inline">Add Asset</span>
                        <span class="d-sm-none">Add</span>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Name, code, or serial number">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="operational" {{ request('status') === 'operational' ? 'selected' : '' }}>Operational</option>
                            <option value="down" {{ request('status') === 'down' ? 'selected' : '' }}>Down</option>
                            <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('options.assets.index') }}" class="btn btn-outline-secondary">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Assets Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assets ({{ $assets->total() }})</h3>
            </div>
            <div class="card-body">
                @if($assets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                    <th>Assigned To</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assets as $asset)
                                <tr>
                                    <td>
                                        <span class="text-muted">{{ $asset->code }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($asset->primaryPhoto())
                                                <span class="avatar avatar-sm me-2" style="background-image: url({{ Storage::disk('s3')->url($asset->primaryPhoto()->photo_path) }})"></span>
                                            @else
                                                <span class="avatar avatar-sm me-2 bg-secondary">
                                                    <i class="far fa-clipboard"></i>&nbsp;
                                                </span>
                                            @endif
                                            <div>
                                                <div class="fw-bold">{{ $asset->name }}</div>
                                                @if($asset->serial_number)
                                                    <div class="text-muted">SN: {{ $asset->serial_number }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary text-white">{{ $asset->assetCategory->name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $asset->status === 'operational' ? 'success' : ($asset->status === 'down' ? 'danger' : ($asset->status === 'disposed' ? 'dark' : 'warning')) }} text-white">
                                            {{ ucfirst($asset->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $asset->location?->name ?? '-' }}</td>
                                    <td>{{ $asset->user?->name ?? 'Unassigned' }}</td>
                                    <td>
                                        <div class="btn-list">
                                            <a href="{{ route('options.assets.show', $asset) }}" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                            @can('update', $asset)
                                            <a href="{{ route('options.assets.edit', $asset) }}" class="btn btn-sm btn-outline-secondary">
                                                Edit
                                            </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $assets->links() }}
                    </div>
                @else
                    <div class="empty">
                        <div class="empty-icon">
                            <i class="far fa-clipboard icon"></i>&nbsp;
                        </div>
                        <p class="empty-title">No assets found</p>
                        <p class="empty-subtitle text-muted">
                            Get started by creating your first asset.
                        </p>
                        @can('create', App\Models\Asset::class)
                        <div class="empty-action">
                            <a href="{{ route('options.assets.create') }}" class="btn btn-primary">
                                <i class="far fa-plus"></i>&nbsp;
                                Add Asset
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




