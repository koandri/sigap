@extends('layouts.app')

@section('title', 'Assets by Department Report')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Reports
                </div>
                <h2 class="page-title">
                    Assets by Department
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('maintenance.dashboard') }}" class="btn">
                        <i class="fa fa-arrow-left me-2"></i>
                        Back to Dashboard
                    </a>
                    @if($selectedDepartment)
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
                    <div class="col-md-10">
                        <label class="form-label">Select Department</label>
                        <select name="department_id" class="form-select" id="department-select" required>
                            <option value="">-- Select Department --</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ $selectedDepartment && $selectedDepartment->id == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
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

        @if($selectedDepartment)
        <!-- Report Header -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">{{ $selectedDepartment->name }}</h3>
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
                                <span class="badge bg-success text-white">{{ $activeAssets->count() }}</span>
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
                            <th>Location</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th class="d-print-none">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeAssets as $asset)
                        <tr>
                            <td><span class="badge">{{ $asset->code }}</span></td>
                            <td>{{ $asset->name }}</td>
                            <td>{{ $asset->assetCategory->name }}</td>
                            <td>{{ $asset->location->name ?? '-' }}</td>
                            <td>
                                @if($asset->status === 'operational')
                                    <span class="badge bg-success text-white">Operational</span>
                                @elseif($asset->status === 'maintenance')
                                    <span class="badge bg-warning text-white">Maintenance</span>
                                @else
                                    <span class="badge bg-danger text-white">Down</span>
                                @endif
                            </td>
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
                            <th>Location</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th class="d-print-none">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inactiveAssets as $asset)
                        <tr>
                            <td><span class="badge">{{ $asset->code }}</span></td>
                            <td>{{ $asset->name }}</td>
                            <td>{{ $asset->assetCategory->name }}</td>
                            <td>{{ $asset->location->name ?? '-' }}</td>
                            <td>
                                @if($asset->status === 'operational')
                                    <span class="badge bg-success">Operational</span>
                                @elseif($asset->status === 'maintenance')
                                    <span class="badge bg-warning">Maintenance</span>
                                @else
                                    <span class="badge bg-danger">Down</span>
                                @endif
                            </td>
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
        @else
        <div class="card">
            <div class="card-body">
                <div class="empty">
                    <p class="empty-title">Select a department to view report</p>
                    <p class="empty-subtitle text-secondary">
                        Choose a department from the dropdown above to see all assets in that department.
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
    new TomSelect('#department-select', {
        placeholder: '-- Select Department --',
        allowEmptyOption: true
    });
});
</script>
@endsection

