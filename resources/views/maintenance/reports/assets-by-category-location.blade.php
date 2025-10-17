@extends('layouts.app')

@section('title', 'Assets by Category and Location Report')

@push('css')
<link href="{{ asset('assets/tabler/libs/tom-select/dist/css/tom-select.bootstrap5.min.css') }}" rel="stylesheet"/>
<style>
    .ts-dropdown {
        background-color: #ffffff !important;
        border: 1px solid #e0e0e0 !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
    }
    .ts-dropdown .option {
        background-color: #ffffff;
    }
    .ts-dropdown .option:hover,
    .ts-dropdown .option.active {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
    .ts-dropdown .option.selected {
        background-color: #0d6efd !important;
        color: #fff !important;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Reports
                </div>
                <h2 class="page-title">
                    Assets by Category and Location
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('maintenance.dashboard') }}" class="btn">
                        <i class="fa fa-arrow-left me-2"></i>
                        Back to Dashboard
                    </a>
                    @if($selectedCategory && $selectedLocations->isNotEmpty())
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fa fa-print me-2"></i>
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
                    <div class="col-md-6">
                        <label class="form-label">Select Category</label>
                        <select name="category_id" class="form-select" id="category-select" required>
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $selectedCategory && $selectedCategory->id == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Select Locations (Multiple)</label>
                        <select name="location_ids[]" class="form-select" id="location-select" multiple required>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" 
                                    {{ in_array($location->id, request('location_ids', [])) ? 'selected' : '' }}>
                                    {{ $location->name }} ({{ $location->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-search me-2"></i>
                            Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($selectedCategory && $selectedLocations->isNotEmpty())
        <!-- Report Header -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">{{ $selectedCategory->name }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Category:</strong> {{ $selectedCategory->name }}</p>
                        <p class="mb-1"><strong>Description:</strong> {{ $selectedCategory->description ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Selected Locations:</strong></p>
                        <ul class="mb-0">
                            @foreach($selectedLocations as $location)
                                <li>{{ $location->name }} ({{ $location->code }})</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        @php
            $totalAssets = collect($assetsByLocation)->sum('total');
            $totalActive = collect($assetsByLocation)->sum(fn($data) => $data['active']->count());
            $totalInactive = collect($assetsByLocation)->sum(fn($data) => $data['inactive']->count());
        @endphp
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Assets</div>
                            <div class="ms-auto lh-1">
                                <span class="badge bg-primary text-white">{{ $totalAssets }}</span>
                            </div>
                        </div>
                        <div class="h1 mb-0 mt-2">{{ $totalAssets }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Active Assets</div>
                            <div class="ms-auto lh-1">
                                <span class="badge bg-success text-white">{{ $totalActive }}</span>
                            </div>
                        </div>
                        <div class="h1 mb-0 mt-2">{{ $totalActive }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Inactive Assets</div>
                            <div class="ms-auto lh-1">
                                <span class="badge bg-secondary text-white">{{ $totalInactive }}</span>
                            </div>
                        </div>
                        <div class="h1 mb-0 mt-2">{{ $totalInactive }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if(empty($assetsByLocation))
        <div class="card">
            <div class="card-body">
                <div class="empty">
                    <p class="empty-title">No assets found</p>
                    <p class="empty-subtitle text-secondary">
                        There are no assets in the selected category for the chosen locations.
                    </p>
                </div>
            </div>
        </div>
        @else
        <!-- Assets by Location -->
        @foreach($assetsByLocation as $locationName => $data)
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-map-marker-alt me-2"></i>
                    {{ $locationName }}
                    <span class="badge bg-primary text-white ms-2">{{ $data['total'] }} assets</span>
                </h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success text-white me-2">Active</span>
                            <strong>{{ $data['active']->count() }} assets</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-secondary text-white me-2">Inactive</span>
                            <strong>{{ $data['inactive']->count() }} assets</strong>
                        </div>
                    </div>
                </div>

                @if($data['active']->isNotEmpty())
                <h4 class="mt-3 mb-2">Active Assets</h4>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Department</th>
                                <th>Assigned To</th>
                                <th class="d-print-none">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['active'] as $asset)
                            <tr>
                                <td><span class="badge">{{ $asset->code }}</span></td>
                                <td>{{ $asset->name }}</td>
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
                                    <a href="{{ route('maintenance.assets.show', $asset) }}" class="btn btn-sm btn-primary">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                @if($data['inactive']->isNotEmpty())
                <h4 class="mt-3 mb-2">Inactive Assets</h4>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Department</th>
                                <th>Assigned To</th>
                                <th class="d-print-none">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['inactive'] as $asset)
                            <tr>
                                <td><span class="badge text-white">{{ $asset->code }}</span></td>
                                <td>{{ $asset->name }}</td>
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
                                    <a href="{{ route('maintenance.assets.show', $asset) }}" class="btn btn-sm btn-primary">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        @endforeach
        @endif
        @else
        <div class="card">
            <div class="card-body">
                <div class="empty">
                    <p class="empty-title">Select category and locations to view report</p>
                    <p class="empty-subtitle text-secondary">
                        Choose a category and one or more locations from the dropdowns above to see assets.
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/tabler/libs/tom-select/dist/js/tom-select.base.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new TomSelect('#category-select', {
        placeholder: '-- Select Category --',
        allowEmptyOption: true
    });
    
    new TomSelect('#location-select', {
        placeholder: 'Select one or more locations',
        maxItems: null,
        hideSelected: true,
        closeAfterSelect: false
    });
});
</script>
@endpush

