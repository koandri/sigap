@extends('layouts.app')

@section('title', 'Assets by Location Report')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Reports
                </div>
                <h2 class="page-title">
                    Assets by Location
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('maintenance.dashboard') }}" class="btn">
                        <i class="fa fa-arrow-left me-2"></i>&nbsp;
                        Back to Dashboard
                    </a>
                    @if($selectedLocation)
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fa fa-print me-2"></i>&nbsp;
                        Print Report
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <!-- Filter -->
        <div class="card mb-3 d-print-none">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-10">
                        <label class="form-label">Select Location</label>
                        <select name="location_id" class="form-select" id="location-select" required>
                            <option value="">-- Select Location --</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" {{ $selectedLocation && $selectedLocation->id == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }} ({{ $location->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-search me-2"></i>&nbsp;
                            Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($selectedLocation)
        <!-- Report Header -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">{{ $selectedLocation->name }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Code:</strong> {{ $selectedLocation->code }}</p>
                        <p class="mb-1"><strong>Address:</strong> {{ $selectedLocation->address ?? '-' }}</p>
                        <p class="mb-1"><strong>City:</strong> {{ $selectedLocation->city ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Phone:</strong> {{ $selectedLocation->phone ?? '-' }}</p>
                        <p class="mb-1"><strong>Postal Code:</strong> {{ $selectedLocation->postal_code ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Assets</div>
                            <div class="ms-auto lh-1">
                                <span class="badge bg-primary text-white">{{ $activeAssets->count() + $inactiveAssets->count() }}</span>
                            </div>
                        </div>
                        <div class="h1 mb-0 mt-2">{{ $activeAssets->count() + $inactiveAssets->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Active Assets</div>
                            <div class="ms-auto lh-1">
                                <span class="badge bg-success">{{ $activeAssets->count() }}</span>
                            </div>
                        </div>
                        <div class="h1 mb-0 mt-2">{{ $activeAssets->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Inactive Assets</div>
                            <div class="ms-auto lh-1">
                                <span class="badge bg-secondary text-white">{{ $inactiveAssets->count() }}</span>
                            </div>
                        </div>
                        <div class="h1 mb-0 mt-2">{{ $inactiveAssets->count() }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Assets -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="badge bg-success me-2 text-white">Active</span>
                    Active Assets ({{ $activeAssets->count() }})
                </h3>
            </div>
            @if($activeAssets->isEmpty())
            <div class="card-body">
                <div class="empty">
                    <p class="empty-title">No active assets found</p>
                </div>
            </div>
            @else
            <div class="table-responsive">
                <table class="table card-table table-vcenter">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Department</th>
                            <th>Assigned To</th>
                            <th class="d-print-none">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeAssets as $asset)
                        <tr>
                            <td><span class="badge text-white">{{ $asset->code }}</span></td>
                            <td>{{ $asset->name }}</td>
                            <td>{{ $asset->assetCategory->name }}</td>
                            <td>
                                @if($asset->status === 'operational')
                                    <span class="badge bg-success text-white">Operational</span>
                                @elseif($asset->status === 'maintenance')
                                    <span class="badge bg-warning text-white">Maintenance</span>
                                @else
                                    <span class="badge bg-danger text-white">Down</span>
                                @endif
                            </td>
                            <td>{{ $asset->department->name ?? '-' }}</td>
                            <td>{{ $asset->user->name ?? '-' }}</td>
                            <td class="d-print-none">
                                <a href="{{ route('options.assets.show', $asset) }}" class="btn btn-sm btn-primary">
                                    <i class="fa fa-eye"></i>&nbsp;
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <!-- Inactive Assets -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="badge bg-secondary text-white me-2">Inactive</span>
                    Inactive Assets ({{ $inactiveAssets->count() }})
                </h3>
            </div>
            @if($inactiveAssets->isEmpty())
            <div class="card-body">
                <div class="empty">
                    <p class="empty-title">No inactive assets found</p>
                </div>
            </div>
            @else
            <div class="table-responsive">
                <table class="table card-table table-vcenter">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Department</th>
                            <th>Assigned To</th>
                            <th class="d-print-none">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inactiveAssets as $asset)
                        <tr>
                            <td><span class="badge text-white">{{ $asset->code }}</span></td>
                            <td>{{ $asset->name }}</td>
                            <td>{{ $asset->assetCategory->name }}</td>
                            <td>
                                @if($asset->status === 'operational')
                                    <span class="badge bg-success text-white">Operational</span>
                                @elseif($asset->status === 'maintenance')
                                    <span class="badge bg-warning text-white">Maintenance</span>
                                @else
                                    <span class="badge bg-danger text-white">Down</span>
                                @endif
                            </td>
                            <td>{{ $asset->department->name ?? '-' }}</td>
                            <td>{{ $asset->user->name ?? '-' }}</td>
                            <td class="d-print-none">
                                <a href="{{ route('options.assets.show', $asset) }}" class="btn btn-sm btn-primary">
                                    <i class="fa fa-eye"></i>&nbsp;
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @else
        <div class="card">
            <div class="card-body">
                <div class="empty">
                    <p class="empty-title">Select a location to view report</p>
                    <p class="empty-subtitle text-secondary">
                        Choose a location from the dropdown above to see all assets in that location.
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<link href="{{ asset('assets/tabler/dist/libs/tom-select/dist/css/tom-select.bootstrap5.css') }}" rel="stylesheet"/>
<script src="{{ asset('assets/tabler/dist/libs/tom-select/dist/js/tom-select.base.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new TomSelect('#location-select', {
        placeholder: '-- Select Location --',
        allowEmptyOption: true
    });
});
</script>
@endsection

