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
                    <a href="{{ route('maintenance.locations.edit', $location) }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/>
                            <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/>
                            <path d="M16 5l3 3"/>
                        </svg>
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
        <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Location Information</h3>
                    </div>
                    <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <div class="form-control-plaintext">{{ $location->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Code</label>
                                <div class="form-control-plaintext">{{ $location->code }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <div class="form-control-plaintext">{{ $location->address ?? 'No address provided' }}</div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">City</label>
                                <div class="form-control-plaintext">{{ $location->city ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Postal Code</label>
                                <div class="form-control-plaintext">{{ $location->postal_code ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <div class="form-control-plaintext">{{ $location->phone ?? '-' }}</div>
                            </div>
                        </div>
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
                                                <a href="{{ route('maintenance.assets.show', $asset) }}">
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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"/>
                                        <rect x="9" y="3" width="6" height="4" rx="2"/>
                                    </svg>
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
@endsection

