@extends('layouts.app')

@section('title', 'Warehouse Overview Report')

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

.filter-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
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

.badge-no-expiry {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
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
    
    .filter-section {
        display: none;
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
                        <li class="breadcrumb-item active">Overview Report</li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('manufacturing.warehouses.overview-report-print', request()->query()) }}" target="_blank" class="btn btn-primary print-button">
                        <i class="far fa-print me-2"></i>&nbsp;
                        Print Report
                    </a>
                    <a href="{{ route('manufacturing.warehouses.index') }}" class="btn btn-outline-secondary">
                        <i class="far fa-arrow-left me-2"></i>&nbsp;
                        Back to Warehouses
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
                        <i class="far fa-chart-line me-3"></i>&nbsp;
                        Warehouse Overview Report
                    </h1>
                    <div class="subtitle">
                        Complete inventory overview across all warehouses • Generated on {{ now()->setTimezone('Asia/Jakarta')->format('F j, Y \a\t g:i A') }}
                    </div>
                </div>
                <div class="col-auto">
                    <div class="text-end">
                        <div class="h4 mb-0">{{ $warehouses->count() }}</div>
                        <small>Active Warehouses</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ number_format($summary['total_items']) }}</div>
                <div class="stat-label">Total Items</div>
            </div>
            <div class="stat-card success">
                <div class="stat-number">{{ number_format($summary['total_quantity']) }}</div>
                <div class="stat-label">Total Quantity</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-number">{{ number_format($summary['expiring_soon_count']) }}</div>
                <div class="stat-label">Expiring Soon (7 days)</div>
            </div>
            <div class="stat-card danger">
                <div class="stat-number">{{ number_format($summary['expired_count']) }}</div>
                <div class="stat-label">Expired Items</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <h3>
                <i class="far fa-filter me-2"></i>&nbsp;
                Filters
            </h3>
            <form method="GET" action="{{ route('manufacturing.warehouses.overview-report') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="warehouse" class="form-label">Warehouse</label>
                    <select name="warehouse" id="warehouse" class="form-select">
                        <option value="">All Warehouses</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="expiry_filter" class="form-label">Expiry Filter</label>
                    <select name="expiry_filter" id="expiry_filter" class="form-select">
                        <option value="">All Items</option>
                        <option value="expired" {{ request('expiry_filter') == 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="expiring_7" {{ request('expiry_filter') == 'expiring_7' ? 'selected' : '' }}>Expiring in 7 days</option>
                        <option value="expiring_30" {{ request('expiry_filter') == 'expiring_30' ? 'selected' : '' }}>Expiring in 30 days</option>
                        <option value="no_expiry" {{ request('expiry_filter') == 'no_expiry' ? 'selected' : '' }}>No Expiry Date</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="item_name" class="form-label">Item Name</label>
                    <input type="text" name="item_name" id="item_name" class="form-control" 
                           value="{{ request('item_name') }}" placeholder="Search item name...">
                </div>
                <div class="col-md-3">
                    <label for="sort_by" class="form-label">Sort By</label>
                    <select name="sort_by" id="sort_by" class="form-select">
                        <option value="item_name" {{ request('sort_by') == 'item_name' ? 'selected' : '' }}>Item Name</option>
                        <option value="warehouse" {{ request('sort_by') == 'warehouse' ? 'selected' : '' }}>Warehouse</option>
                        <option value="expiry_date" {{ request('sort_by') == 'expiry_date' ? 'selected' : '' }}>Expiry Date</option>
                        <option value="quantity" {{ request('sort_by') == 'quantity' ? 'selected' : '' }}>Quantity</option>
                        <option value="location" {{ request('sort_by') == 'location' ? 'selected' : '' }}>Location</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sort_direction" class="form-label">Sort Direction</label>
                    <select name="sort_direction" id="sort_direction" class="form-select">
                        <option value="asc" {{ request('sort_direction') == 'asc' ? 'selected' : '' }}>Ascending</option>
                        <option value="desc" {{ request('sort_direction') == 'desc' ? 'selected' : '' }}>Descending</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="far fa-search me-2"></i>&nbsp;
                            Apply Filters
                        </button>
                        <a href="{{ route('manufacturing.warehouses.overview-report') }}" class="btn btn-outline-secondary">
                            <i class="far fa-times me-2"></i>&nbsp;
                            Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Items Table -->
        <div class="report-section">
            <h3>
                <i class="far fa-list me-2"></i>&nbsp;
                Inventory Items
                <span class="badge bg-primary text-white ms-2">{{ $items->total() }} items</span>
            </h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Warehouse</th>
                            <th>Location</th>
                            <th>Quantity</th>
                            <th>Expiry Date</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>
                                <div class="flex-fill">
                                    <div class="font-weight-medium">{{ $item->item->name }}</div>
                                    <div class="text-muted">{{ $item->item->accurate_id }}</div>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $item->shelfPosition->warehouseShelf->warehouse->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $item->shelfPosition->warehouseShelf->warehouse->code }}</small>
                            </td>
                            <td>
                                <code>{{ $item->shelfPosition->full_location_code }}</code>
                            </td>
                            <td>
                                <strong>{{ number_format($item->quantity, 2) }}</strong>
                                <br>
                                <small class="text-muted">{{ $item->item->unit }}</small>
                            </td>
                            <td>
                                @if($item->expiry_date)
                                    @php
                                        $daysRemaining = now()->diffInDays($item->expiry_date, false);
                                        $months = floor($daysRemaining / 30);
                                        $days = $daysRemaining % 30;
                                        
                                        if ($daysRemaining < 0) {
                                            $overdueDays = abs($daysRemaining);
                                            $overdueMonths = floor($overdueDays / 30);
                                            $overdueDaysRemainder = $overdueDays % 30;
                                        }
                                    @endphp
                                    <div class="font-weight-medium">{{ $item->expiry_date->format('M j, Y') }}</div>
                                    @if($daysRemaining < 0)
                                        @if($overdueMonths > 0)
                                            <span class="badge badge-expired">{{ $overdueMonths }} months {{ $overdueDaysRemainder }} days overdue</span>
                                        @else
                                            <span class="badge badge-expired">{{ $overdueDays }} days overdue</span>
                                        @endif
                                    @elseif($daysRemaining <= 7)
                                        @if($months > 0)
                                            <span class="badge badge-expiring">{{ $months }} months {{ $days }} days remaining</span>
                                        @else
                                            <span class="badge badge-expiring">{{ $days }} days remaining</span>
                                        @endif
                                    @elseif($daysRemaining <= 30)
                                        @if($months > 0)
                                            <span class="badge badge-warning">{{ $months }} months {{ $days }} days remaining</span>
                                        @else
                                            <span class="badge badge-warning">{{ $days }} days remaining</span>
                                        @endif
                                    @else
                                        @if($months > 0)
                                            <span class="badge badge-success">{{ $months }} months {{ $days }} days remaining</span>
                                        @else
                                            <span class="badge badge-success">{{ $days }} days remaining</span>
                                        @endif
                                    @endif
                                @else
                                    <span class="badge badge-no-expiry">No Expiry</span>
                                @endif
                            </td>
                            <td>
                                <div class="font-weight-medium">{{ $item->last_updated_at->format('M j, Y') }}</div>
                                <small class="text-muted">{{ $item->last_updated_at->format('g:i A') }}</small>
                                @if($item->lastUpdatedBy)
                                    <br><small class="text-muted">by {{ $item->lastUpdatedBy->name }}</small>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <div class="empty">
                                    <div class="empty-img"><img src="https://via.placeholder.com/128x128/e9ecef/6c757d?text=No+Data" height="128" alt=""></div>
                                    <p class="empty-title">No items found</p>
                                    <p class="empty-subtitle text-muted">
                                        Try adjusting your filters to see more results.
                                    </p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($items->hasPages())
            <div class="card-footer d-flex align-items-center">
                {{ $items->links('layouts.pagination') }}
            </div>
            @endif
        </div>

        <!-- Report Footer -->
        <div class="report-section">
            <div class="row">
                <div class="col-md-6">
                    <h5>Report Information</h5>
                    <ul class="list-unstyled">
                        <li><strong>Generated:</strong> {{ now()->setTimezone('Asia/Jakarta')->format('F j, Y \a\t g:i A') }}</li>
                        <li><strong>Generated by:</strong> {{ Auth::user()->name }}</li>
                        <li><strong>Total Warehouses:</strong> {{ $warehouses->count() }}</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5>Summary Statistics</h5>
                    <ul class="list-unstyled">
                        <li><strong>Total Items:</strong> {{ number_format($summary['total_items']) }}</li>
                        <li><strong>Total Quantity:</strong> {{ number_format($summary['total_quantity']) }}</li>
                        <li><strong>Expiring Soon (7 days):</strong> {{ number_format($summary['expiring_soon_count']) }}</li>
                        <li><strong>Expiring in 30 days:</strong> {{ number_format($summary['expiring_30_days_count']) }}</li>
                        <li><strong>Expired Items:</strong> {{ number_format($summary['expired_count']) }}</li>
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
