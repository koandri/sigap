@extends('layouts.app')

@section('title', 'Shelf Inventory Report: ' . $warehouse->name)

@push('css')
<style>
.report-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.report-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.report-header .subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    font-weight: 300;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border-left: 4px solid #0d6efd;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}

.stat-card.success {
    border-left-color: #28a745;
}

.stat-card.warning {
    border-left-color: #ffc107;
}

.stat-card.danger {
    border-left-color: #dc3545;
}

.stat-card.info {
    border-left-color: #17a2b8;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: #0d6efd;
    line-height: 1;
    margin-bottom: 0.5rem;
}

.stat-card.success .stat-number {
    color: #28a745;
}

.stat-card.warning .stat-number {
    color: #ffc107;
}

.stat-card.danger .stat-number {
    color: #dc3545;
}

.stat-card.info .stat-number {
    color: #17a2b8;
}

.stat-label {
    color: #6c757d;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.report-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.report-section h3 {
    color: #495057;
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.table-responsive {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.table {
    margin-bottom: 0;
}

.table th {
    background: #f8f9fa;
    border: none;
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
    padding: 1rem;
}

.table td {
    border: none;
    padding: 1rem;
    vertical-align: middle;
}

.table tbody tr {
    border-bottom: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background: #f8f9fa;
}

.badge {
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
}

.badge-expiring {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.badge-expired {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.badge-occupied {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.badge-empty {
    background: #e2e3e5;
    color: #383d41;
    border: 1px solid #d6d8db;
}

.badge-full {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.print-button {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 1000;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

@media print {
    .print-button {
        display: none;
    }
    
    .report-header {
        background: #f8f9fa !important;
        color: #000 !important;
    }
    
    .stat-card {
        box-shadow: none;
        border: 1px solid #dee2e6;
    }
}

@media (max-width: 768px) {
    .report-header h1 {
        font-size: 2rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-number {
        font-size: 2rem;
    }
}
</style>
@endpush

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.dashboard') }}">Manufacturing</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.warehouses.index') }}">Warehouses</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.warehouses.show', $warehouse) }}">{{ $warehouse->name }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.warehouses.shelf-inventory', $warehouse) }}">Shelf Inventory</a></li>
                        <li class="breadcrumb-item active">Report</li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <button onclick="window.print()" class="btn btn-primary print-button">
                        <i class="far fa-print me-2"></i>&nbsp;
                        Print Report
                    </button>
                    <a href="{{ route('manufacturing.warehouses.shelf-inventory', $warehouse) }}" class="btn btn-outline-secondary">
                        <i class="far fa-arrow-left me-2"></i>&nbsp;
                        Back to Inventory
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')
        
        <!-- Report Header -->
        <div class="report-header">
            <div class="row align-items-center">
                <div class="col">
                    <h1>
                        <i class="far fa-chart-bar me-3"></i>&nbsp;
                        Shelf Inventory Report
                    </h1>
                    <div class="subtitle">
                        <strong>{{ $warehouse->name }}</strong> • Generated on {{ now()->format('F j, Y \a\t g:i A') }}
                    </div>
                </div>
                <div class="col-auto">
                    <div class="text-end">
                        <div class="h4 mb-0">{{ $warehouse->code }}</div>
                        <small>Warehouse Code</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_shelves'] }}</div>
                <div class="stat-label">Total Sections</div>
            </div>
            <div class="stat-card success">
                <div class="stat-number">{{ $stats['occupied_shelves'] }}</div>
                <div class="stat-label">Occupied Sections</div>
            </div>
            <div class="stat-card info">
                <div class="stat-number">{{ $stats['total_positions'] }}</div>
                <div class="stat-label">Total Positions</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-number">{{ $stats['occupancy_rate'] }}%</div>
                <div class="stat-label">Occupancy Rate</div>
            </div>
        </div>

        <!-- Shelf Status Summary -->
        <div class="report-section">
            <h3>
                <i class="far fa-warehouse me-2"></i>&nbsp;
                Shelf Status Summary
            </h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Shelf Code</th>
                            <th>Status</th>
                            <th>Occupancy</th>
                            <th>Total Positions</th>
                            <th>Occupied Positions</th>
                            <th>Items Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warehouse->shelves as $shelf)
                        <tr>
                            <td>
                                <strong>{{ $shelf->shelf_code }}</strong>
                            </td>
                            <td>
                                @if($shelf->is_full)
                                    <span class="badge badge-full">Full</span>
                                @elseif($shelf->occupancy_rate > 0)
                                    <span class="badge badge-occupied">Occupied</span>
                                @else
                                    <span class="badge badge-empty">Empty</span>
                                @endif
                            </td>
                            <td>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar 
                                        @if($shelf->is_full) bg-danger
                                        @elseif($shelf->occupancy_rate > 0) bg-success
                                        @else bg-secondary
                                        @endif" 
                                        style="width: {{ $shelf->occupancy_rate }}%">
                                    </div>
                                </div>
                                <small class="text-muted">{{ $shelf->occupancy_rate }}%</small>
                            </td>
                            <td>{{ $shelf->max_capacity }}</td>
                            <td>{{ $shelf->occupied_positions }}</td>
                            <td>{{ $shelf->shelfPositions->where('is_occupied', true)->count() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Expiring Items -->
        @if($expiringItems->count() > 0)
        <div class="report-section">
            <h3>
                <i class="far fa-clock me-2"></i>&nbsp;
                Items Expiring Soon (Next 30 Days)
            </h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Location</th>
                            <th>Quantity</th>
                            <th>Expiry Date</th>
                            <th>Days Remaining</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expiringItems as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->item->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $item->item->itemCategory->name ?? 'No Category' }}</small>
                            </td>
                            <td>
                                <code>{{ $item->shelfPosition->full_location_code }}</code>
                            </td>
                            <td>{{ $item->quantity }} {{ $item->item->unit }}</td>
                            <td>{{ $item->expiry_date ? $item->expiry_date->format('M j, Y') : 'N/A' }}</td>
                            <td>
                                @if($item->expiry_date)
                                    @php
                                        $daysRemaining = now()->diffInDays($item->expiry_date, false);
                                    @endphp
                                    <span class="badge badge-expiring">
                                        {{ $daysRemaining }} days
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Expired Items -->
        @if($expiredItems->count() > 0)
        <div class="report-section">
            <h3>
                <i class="far fa-exclamation-triangle me-2"></i>&nbsp;
                Expired Items
            </h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Location</th>
                            <th>Quantity</th>
                            <th>Expiry Date</th>
                            <th>Days Overdue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expiredItems as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->item->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $item->item->itemCategory->name ?? 'No Category' }}</small>
                            </td>
                            <td>
                                <code>{{ $item->shelfPosition->full_location_code }}</code>
                            </td>
                            <td>{{ $item->quantity }} {{ $item->item->unit }}</td>
                            <td>{{ $item->expiry_date ? $item->expiry_date->format('M j, Y') : 'N/A' }}</td>
                            <td>
                                @if($item->expiry_date)
                                    @php
                                        $daysOverdue = $item->expiry_date->diffInDays(now());
                                    @endphp
                                    <span class="badge badge-expired">
                                        {{ $daysOverdue }} days
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Detailed Inventory -->
        <div class="report-section">
            <h3>
                <i class="far fa-list me-2"></i>&nbsp;
                Detailed Inventory
            </h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Location</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Expiry Date</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warehouse->shelves as $shelf)
                            @foreach($shelf->shelfPositions as $position)
                                @foreach($position->positionItems as $positionItem)
                                <tr>
                                    <td>
                                        <strong>{{ $positionItem->item->name }}</strong>
                                    </td>
                                    <td>{{ $positionItem->item->itemCategory->name ?? 'No Category' }}</td>
                                    <td>
                                        <code>{{ $position->full_location_code }}</code>
                                    </td>
                                    <td>{{ $positionItem->quantity }}</td>
                                    <td>{{ $positionItem->item->unit }}</td>
                                    <td>
                                        @if($positionItem->expiry_date)
                                            {{ $positionItem->expiry_date->format('M j, Y') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $positionItem->last_updated_at->format('M j, Y g:i A') }}
                                        @if($positionItem->lastUpdatedBy)
                                            <br><small class="text-muted">by {{ $positionItem->lastUpdatedBy->name }}</small>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Report Footer -->
        <div class="report-section">
            <div class="row">
                <div class="col-md-6">
                    <h5>Report Information</h5>
                    <ul class="list-unstyled">
                        <li><strong>Generated:</strong> {{ now()->format('F j, Y \a\t g:i A') }}</li>
                        <li><strong>Generated by:</strong> {{ Auth::user()->name }}</li>
                        <li><strong>Warehouse:</strong> {{ $warehouse->name }} ({{ $warehouse->code }})</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5>Summary</h5>
                    <ul class="list-unstyled">
                        <li><strong>Total Sections:</strong> {{ $stats['total_shelves'] }}</li>
                        <li><strong>Occupied Sections:</strong> {{ $stats['occupied_shelves'] }}</li>
                        <li><strong>Occupancy Rate:</strong> {{ $stats['occupancy_rate'] }}%</li>
                        <li><strong>Expiring Items:</strong> {{ $expiringItems->count() }}</li>
                        <li><strong>Expired Items:</strong> {{ $expiredItems->count() }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add print functionality
    window.printReport = function() {
        window.print();
    };
    
    // Add smooth scrolling for better UX
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
});
</script>
@endpush
