@section('title', 'Home')

@extends('layouts.app')

@section('content')            
            <!-- BEGIN PAGE HEADER -->
            <div class="page-header d-print-none" aria-label="Page header">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <h2 class="page-title">@yield('title')</h2>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE HEADER -->
            <!-- BEGIN PAGE BODY -->
            <div class="page-body">
                <div class="container-xl">
                    <div class="row">
                        @include('layouts.alerts')
                    </div>
                    
                    @canany(['role:QC', 'role:Production', 'role:IT', 'role:Super Admin', 'role:Owner'])
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
                    @endcanany
                    
                    <div class="row row-deck row-cards">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Home</h3>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    @if (session('status'))
                                    <div>{{ session('status') }}</div>
                                    @endif
                                    <div>You are logged in!</div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection

@canany(['role:QC', 'role:Production', 'role:IT', 'role:Super Admin', 'role:Owner'])
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

    // Check if elements exist (they might not if user doesn't have permission)
    if (!timeRangeInput || !intervalSelect || !reloadBtn) {
        return; // Exit if elements don't exist
    }

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

            const response = await fetch(`{{ route('api.temperature-data') }}?${params.toString()}`, {
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
                        borderColor: 'rgb(13, 110, 253)',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 2,
                        pointHoverRadius: 4,
                        spanGaps: false
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
                                color: 'rgba(0, 0, 0, 0.1)',
                                borderDash: [5, 5],
                                drawBorder: false
                            },
                            title: {
                                display: true,
                                text: `Temperature (${chartData.unit})`
                            }
                        },
                        x: {
                            grid: {
                                display: false
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
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }
        if (temperatureChart) {
            temperatureChart.destroy();
        }
    });
})();
</script>
@endpush
@endcanany