@extends('layouts.app')

@section('title', 'Manufacturing Dashboard')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Overview
                </div>
                <h2 class="page-title">
                    Manufacturing Dashboard
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        
        <!-- Statistics Cards -->
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Items</div>
                        </div>
                        <div class="h1 mb-0">{{ $stats['total_items'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Categories</div>
                        </div>
                        <div class="h1 mb-0">{{ $stats['total_categories'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Warehouses</div>
                        </div>
                        <div class="h1 mb-0">{{ $stats['total_warehouses'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Stocked Locations</div>
                        </div>
                        <div class="h1 mb-0">{{ $stats['total_locations'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Temperature Sensor Widget -->
        <div class="row mt-4 mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">
                                <i class="far fa-temperature-half"></i>&nbsp;
                                Temperature Sensor&nbsp;
                                <button type="button" id="reload-temperature-btn" class="btn btn-sm btn-outline-primary">
                                    <i class="far fa-rotate"></i>&nbsp;Reload
                                </button>
                            </h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Time Range Controls -->
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label class="form-label">Time Period</label>
                                <input type="text" id="temperature-time-range" class="form-control" placeholder="Select date and time range" readonly />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Interval</label>
                                <select id="temperature-interval" class="form-select">
                                    <option value="5">5 minutes</option>
                                    <option value="15">15 minutes</option>
                                    <option value="30" selected>30 minutes</option>
                                    <option value="60">60 minutes</option>
                                </select>
                            </div>
                        </div>

                        <!-- Loading Indicator -->
                        <div id="temperature-loading" class="text-center py-4" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading temperature data...</p>
                        </div>

                        <!-- Error Message -->
                        <div id="temperature-error" class="alert alert-danger" style="display: none;">
                            <i class="far fa-circle-exclamation"></i>&nbsp;
                            <span id="temperature-error-message"></span>
                        </div>

                        <!-- Chart Container -->
                        <div id="temperature-chart-container">
                            <canvas id="temperature-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Items by Category -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Items by Category</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Items Count</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($itemsByCategory as $category)
                                <tr>
                                    <td>{{ $category->name }}</td>
                                    <td class="text-muted">{{ $category->items_count }}</td>
                                    <td>
                                        <a href="{{ route('manufacturing.items.index', ['category' => $category->id]) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Warehouses Overview -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Warehouses Overview</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Warehouse</th>
                                    <th>Locations with Stock</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($warehousesWithStock as $warehouse)
                                <tr>
                                    <td>
                                        <div class="d-flex py-1 align-items-center">
                                            <span class="avatar me-2" style="background-image: url(https://via.placeholder.com/32x32/2563eb/ffffff?text={{ substr($warehouse->code, 0, 1) }})"></span>
                                            <div class="flex-fill">
                                                <div class="font-weight-medium">{{ $warehouse->name }}</div>
                                                <div class="text-muted">{{ $warehouse->code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-muted">{{ $warehouse->stocked_locations_count }}</td>
                                    <td>
                                        <a href="{{ route('manufacturing.warehouses.show', $warehouse) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if($expiringItems->count() > 0)
        <!-- Expiring Items Alert -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title text-warning">
                            <i class="far fa-circle-exclamation"></i>&nbsp;
                            Items Expiring Soon (Next 30 Days)
                        </h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Location</th>
                                    <th>Quantity</th>
                                    <th>Expiry Date</th>
                                    <th>Days Left</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expiringItems as $location)
                                <tr>
                                    <td>{{ $location->item->name }}</td>
                                    <td>{{ $location->warehouse->name }}@if($location->shelf_area) - {{ $location->shelf_area }}@endif</td>
                                    <td>{{ number_format($location->current_quantity, 2) }} {{ $location->item->unit }}</td>
                                    <td>{{ $location->expiry_date->format('M d, Y') }}</td>
                                    <td>
                                        @php
                                            $daysLeft = $location->expiry_date->diffInDays(now());
                                        @endphp
                                        <span class="badge @if($daysLeft <= 7) bg-red @elseif($daysLeft <= 14) bg-orange @else bg-yellow @endif">
                                            {{ $daysLeft }} days
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('manufacturing.items.index') }}" class="btn btn-outline-primary w-100">
                                    <i class="far fa-box icon mb-2"></i>&nbsp;
                                    <br>Manage Items
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('manufacturing.item-categories.index') }}" class="btn btn-outline-primary w-100">
                                    <i class="far fa-grid-2 icon mb-2"></i>&nbsp;
                                    <br>Item Categories
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('manufacturing.warehouses.index') }}" class="btn btn-outline-primary w-100">
                                    <i class="far fa-warehouse icon mb-2"></i>&nbsp;
                                    <br>Warehouses
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('manufacturing.warehouses.overview-report') }}" class="btn btn-outline-info w-100">
                                    <i class="far fa-chart-column icon mb-2"></i>&nbsp;
                                    <br>Overview Report
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="btn btn-outline-secondary w-100 disabled">
                                    <i class="far fa-terminal icon mb-2"></i>&nbsp;
                                    <br>Production (Coming Soon)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>

<script>
(function() {
    'use strict';

    // Chart instance
    let temperatureChart = null;
    let autoRefreshInterval = null;
    let timeRangePicker = null;

    // DOM elements
    const timeRangeInput = document.getElementById('temperature-time-range');
    const intervalSelect = document.getElementById('temperature-interval');
    const reloadBtn = document.getElementById('reload-temperature-btn');
    const loadingDiv = document.getElementById('temperature-loading');
    const errorDiv = document.getElementById('temperature-error');
    const errorMessage = document.getElementById('temperature-error-message');
    const chartContainer = document.getElementById('temperature-chart-container');
    const chartCanvas = document.getElementById('temperature-chart');

    // Initialize Litepicker for datetime range
    function initializeTimeRangePicker() {
        if (!timeRangeInput || typeof Litepicker === 'undefined') {
            console.error('Time range input or Litepicker not available');
            return;
        }

        const now = new Date();
        const eightHoursAgo = new Date(now.getTime() - 8 * 60 * 60 * 1000);

        // Format date for Litepicker (YYYY-MM-DD HH:mm)
        const formatDateTime = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${year}-${month}-${day} ${hours}:${minutes}`;
        };

        const startValue = formatDateTime(eightHoursAgo);
        const endValue = formatDateTime(now);

        // Initialize Litepicker with range mode and time picker
        timeRangePicker = new Litepicker({
            element: timeRangeInput,
            format: 'YYYY-MM-DD HH:mm',
            singleMode: false,
            numberOfMonths: 2,
            numberOfColumns: 2,
            timePicker: true,
            timePickerOptions: {
                format: 'HH:mm',
                step: 1
            },
            startDate: startValue,
            endDate: endValue,
            onSelect: function(startDate, endDate) {
                if (startDate && endDate) {
                    fetchTemperatureData();
                }
            }
        });

        // Set initial display value
        timeRangeInput.value = `${startValue} - ${endValue}`;
    }

    // Format date to ISO 8601 for API
    function formatDateTimeForAPI(date) {
        if (!date) return null;
        // Convert Date object or date string to ISO 8601 format
        if (date instanceof Date) {
            return date.toISOString();
        }
        // If it's a string in format "YYYY-MM-DD HH:mm", parse it
        if (typeof date === 'string') {
            const [datePart, timePart] = date.split(' ');
            if (datePart && timePart) {
                const [year, month, day] = datePart.split('-').map(Number);
                const [hours, minutes] = timePart.split(':').map(Number);
                const dateObj = new Date(year, month - 1, day, hours, minutes);
                return dateObj.toISOString();
            }
        }
        return null;
    }

    // Fetch temperature data from API
    async function fetchTemperatureData() {
        if (!timeRangePicker) {
            console.error('Time range picker not initialized');
            return;
        }

        const startDate = timeRangePicker.getStartDate();
        const endDate = timeRangePicker.getEndDate();
        const intervalMinutes = parseInt(intervalSelect.value) || 30;

        if (!startDate || !endDate) {
            console.error('Start or end date not selected');
            return;
        }

        const startTime = formatDateTimeForAPI(startDate);
        const endTime = formatDateTimeForAPI(endDate);

        // Show loading, hide error
        loadingDiv.style.display = 'block';
        errorDiv.style.display = 'none';
        chartContainer.style.display = 'none';

        try {
            const params = new URLSearchParams();
            if (startTime) params.append('start_time', startTime);
            if (endTime) params.append('end_time', endTime);
            params.append('interval', intervalMinutes);

            const response = await fetch(`{{ route('api.manufacturing.temperature-data') }}?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Failed to fetch temperature data');
            }

            // Update chart
            updateChart(data.data);
            
            // Show chart, hide loading
            loadingDiv.style.display = 'none';
            chartContainer.style.display = 'block';

        } catch (error) {
            console.error('Error fetching temperature data:', error);
            loadingDiv.style.display = 'none';
            errorDiv.style.display = 'block';
            errorMessage.textContent = error.message || 'An error occurred while fetching temperature data';
            chartContainer.style.display = 'none';
        }
    }

    // Initialize or update Chart.js
    function updateChart(chartData) {
        const ctx = chartCanvas.getContext('2d');

        if (temperatureChart) {
            // Update existing chart
            temperatureChart.data.labels = chartData.labels;
            temperatureChart.data.datasets[0].data = chartData.temperatures;
            temperatureChart.data.datasets[0].label = `Temperature (${chartData.unit})`;
            temperatureChart.update();
        } else {
            // Create new chart
            temperatureChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: `Temperature (${chartData.unit})`,
                        data: chartData.temperatures,
                        borderColor: 'rgb(13, 110, 253)', // Dark blue matching site's primary color
                        backgroundColor: 'rgba(13, 110, 253, 0.1)', // Light blue fill
                        tension: 0.4,
                        fill: true,
                        pointRadius: 2,
                        pointHoverRadius: 4,
                        spanGaps: false // Show gaps for null values
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)', // Light grey for grid lines
                                borderDash: [5, 5], // Dashed lines
                                drawBorder: false
                            },
                            title: {
                                display: true,
                                text: `Temperature (${chartData.unit})`
                            }
                        },
                        x: {
                            grid: {
                                display: false // No vertical grid lines
                            },
                            title: {
                                display: true,
                                text: 'Time'
                            },
                            ticks: {
                                maxRotation: 90,
                                minRotation: 90,
                                callback: function(value, index, values) {
                                    const label = this.getLabelForValue(value);
                                    if (label) {
                                        const date = new Date(label);
                                        if (!isNaN(date.getTime())) {
                                            const day = String(date.getDate()).padStart(2, '0');
                                            const month = date.toLocaleDateString('en-US', { month: 'short' });
                                            const year = String(date.getFullYear()).slice(-2);
                                            const hours = String(date.getHours()).padStart(2, '0');
                                            const minutes = String(date.getMinutes()).padStart(2, '0');
                                            return `${day} ${month} ${year} ${hours}:${minutes}`;
                                        }
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        }
    }

    // Start auto-refresh
    function startAutoRefresh() {
        // Clear existing interval if any
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }

        // Refresh every 30 seconds
        autoRefreshInterval = setInterval(() => {
            fetchTemperatureData();
        }, 30000);
    }

    // Stop auto-refresh
    function stopAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;
        }
    }

    // Event listeners
    reloadBtn.addEventListener('click', () => {
        fetchTemperatureData();
    });

    // Interval change handler
    if (intervalSelect) {
        intervalSelect.addEventListener('change', function() {
            fetchTemperatureData();
        });
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure inputs are available before initializing
        if (timeRangeInput && intervalSelect) {
            initializeTimeRangePicker();
            // Wait a bit for Litepicker to initialize, then fetch data
            setTimeout(function() {
                fetchTemperatureData();
            }, 200);
        } else {
            // Retry after a short delay if inputs aren't ready
            setTimeout(function() {
                if (timeRangeInput && intervalSelect) {
                    initializeTimeRangePicker();
                    setTimeout(function() {
                        fetchTemperatureData();
                    }, 200);
                } else {
                    console.error('Failed to initialize temperature widget: inputs not found');
                }
            }, 100);
        }
        // Auto-refresh disabled - chart updates only when user changes datetime or clicks reload
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        stopAutoRefresh();
        if (temperatureChart) {
            temperatureChart.destroy();
        }
    });
})();
</script>
@endpush
