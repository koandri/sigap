@extends('layouts.app')

@section('title', 'Maintenance Reports')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    Maintenance Reports
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <!-- Report Filters -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Report Parameters</h3>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Asset</label>
                        <select name="asset_id" class="form-select">
                            <option value="">All Assets</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}" {{ $assetId == $asset->id ? 'selected' : '' }}>
                                    {{ $asset->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Summary -->
        <div class="row row-deck row-cards mb-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Work Orders</div>
                        </div>
                        <div class="h1 mb-3">{{ $totalWorkOrders }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Cost</div>
                        </div>
                        <div class="h1 mb-3">${{ number_format($totalCost, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Hours</div>
                        </div>
                        <div class="h1 mb-3">{{ number_format($totalHours, 1) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Avg Hours/Order</div>
                        </div>
                        <div class="h1 mb-3">{{ number_format($avgHours, 1) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row row-deck row-cards">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Maintenance Type Breakdown</h3>
                    </div>
                    <div class="card-body">
                        <div id="maintenanceTypeChart"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Asset Breakdown</h3>
                    </div>
                    <div class="card-body">
                        <div id="assetChart"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Trend -->
        <div class="row row-deck row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Monthly Trend</h3>
                    </div>
                    <div class="card-body">
                        <div id="monthlyTrendChart"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Orders Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Work Orders ({{ $workOrders->count() }})</h3>
            </div>
            <div class="card-body">
                @if($workOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>WO Number</th>
                                    <th>Asset</th>
                                    <th>Type</th>
                                    <th>Completed Date</th>
                                    <th>Hours</th>
                                    <th>Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($workOrders as $workOrder)
                                <tr>
                                    <td>
                                        <a href="{{ route('maintenance.work-orders.show', $workOrder) }}">
                                            {{ $workOrder->wo_number }}
                                        </a>
                                    </td>
                                    <td>{{ $workOrder->asset->name }}</td>
                                    <td>{{ $workOrder->maintenanceType->name }}</td>
                                    <td>{{ $workOrder->completed_date?->format('M d, Y') ?? '-' }}</td>
                                    <td>{{ $workOrder->actual_hours ?? '-' }}</td>
                                    <td>${{ number_format($workOrder->parts->sum(function($part) { return $part->quantity_used * ($part->item->price ?? 0); }), 2) }}</td>
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
                        <p class="empty-title">No work orders found</p>
                        <p class="empty-subtitle text-muted">
                            No work orders match the selected criteria.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/tabler/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Maintenance Type Chart
    const maintenanceTypeData = @json($maintenanceTypeBreakdown);
    const maintenanceTypeLabels = Object.keys(maintenanceTypeData);
    const maintenanceTypeValues = Object.values(maintenanceTypeData).map(item => item.count);

    new ApexCharts(document.querySelector("#maintenanceTypeChart"), {
        series: maintenanceTypeValues,
        chart: {
            type: 'donut',
            height: 300
        },
        labels: maintenanceTypeLabels,
        legend: {
            position: 'bottom'
        }
    }).render();

    // Asset Chart
    const assetData = @json($assetBreakdown);
    const assetLabels = Object.keys(assetData);
    const assetValues = Object.values(assetData).map(item => item.count);

    new ApexCharts(document.querySelector("#assetChart"), {
        series: assetValues,
        chart: {
            type: 'pie',
            height: 300
        },
        labels: assetLabels,
        legend: {
            position: 'bottom'
        }
    }).render();

    // Monthly Trend Chart
    const monthlyTrendData = @json($monthlyTrend);
    const monthlyLabels = Object.keys(monthlyTrendData);
    const monthlyCounts = Object.values(monthlyTrendData).map(item => item.count);
    const monthlyCosts = Object.values(monthlyTrendData).map(item => item.cost);

    new ApexCharts(document.querySelector("#monthlyTrendChart"), {
        series: [{
            name: 'Work Orders',
            type: 'column',
            data: monthlyCounts
        }, {
            name: 'Cost ($)',
            type: 'line',
            data: monthlyCosts
        }],
        chart: {
            height: 350,
            type: 'line'
        },
        stroke: {
            width: [0, 4]
        },
        xaxis: {
            categories: monthlyLabels
        },
        yaxis: [{
            title: {
                text: 'Count'
            }
        }, {
            opposite: true,
            title: {
                text: 'Cost ($)'
            }
        }]
    }).render();
});
</script>
@endpush
