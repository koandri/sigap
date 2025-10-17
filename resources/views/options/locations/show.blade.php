@extends('layouts.app')

@section('title', 'Location Details')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    {{ $location->name }}
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                @can('maintenance.assets.manage')
                <div class="btn-list">
                    <a href="{{ route('options.locations.edit', $location) }}" class="btn btn-primary">
                        <i class="fa-regular fa-pen"></i>
                        Edit Location
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
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Location Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <div class="form-control-plaintext">{{ $location->name }}</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Code</label>
                            <div class="form-control-plaintext">{{ $location->code }}</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-{{ $location->is_active ? 'success' : 'secondary' }} text-white">
                                    {{ $location->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Assets Count</label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-secondary text-white">{{ $location->assets->count() }} assets</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <!-- Assets in this location -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Assets at this Location</h3>
                    </div>
                    <div class="card-body">
                        @if($location->assets->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Department</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($location->assets as $asset)
                                        <tr>
                                            <td>{{ $asset->code }}</td>
                                            <td>
                                                <a href="{{ route('options.assets.show', $asset) }}">
                                                    {{ $asset->name }}
                                                </a>
                                            </td>
                                            <td>{{ $asset->assetCategory?->name ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $asset->status === 'operational' ? 'success' : ($asset->status === 'down' ? 'danger' : 'warning') }} text-white">
                                                    {{ ucfirst($asset->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $asset->department?->name ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty">
                                <div class="empty-icon">
                                    <i class="fa-regular fa-clipboard icon"></i>
                                </div>
                                <p class="empty-title">No assets at this location</p>
                                <p class="empty-subtitle text-muted">
                                    Assets will appear here once they are assigned to this location.
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

