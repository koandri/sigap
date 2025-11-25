@extends('layouts.app')

@section('title', 'Shelf Inventory: ' . $warehouse->name)

@push('css')
<style>
.shelf-grid {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.section-row {
    margin-bottom: 30px;
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.section-row h4 {
    color: #495057;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #dee2e6;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-row h4::before {
    content: '📦';
    font-size: 1.2em;
}

.section-card {
    display: block;
    text-decoration: none;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 12px 8px;
    text-align: center;
    min-height: 90px;
    transition: all 0.3s ease;
    background: #fff;
    margin-bottom: 8px;
    position: relative;
    overflow: hidden;
    cursor: pointer;
}

.section-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: transparent;
    transition: all 0.3s ease;
}

.section-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    text-decoration: none;
    border-width: 3px;
}

.section-empty {
    border-color: #e9ecef;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.section-empty::before {
    background: #6c757d;
}

.section-empty:hover {
    border-color: #adb5bd;
    background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%);
}

.section-occupied {
    border-color: #28a745;
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
}

.section-occupied::before {
    background: #28a745;
}

.section-occupied:hover {
    border-color: #1e7e34;
    background: linear-gradient(135deg, #c3e6cb 0%, #b8dacc 100%);
}

.section-full {
    border-color: #dc3545;
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
}

.section-full::before {
    background: #dc3545;
}

.section-full:hover {
    border-color: #bd2130;
    background: linear-gradient(135deg, #f5c6cb 0%, #f1b0b7 100%);
}

.section-code {
    font-weight: 700;
    font-size: 14px;
    margin-bottom: 6px;
    color: #495057;
    letter-spacing: 0.5px;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.section-status {
    font-size: 16px;
    margin-bottom: 6px;
}

.section-occupancy {
    font-size: 11px;
    color: #6c757d;
    font-weight: 500;
    background: rgba(255,255,255,0.9);
    padding: 3px 8px;
    border-radius: 12px;
    display: inline-block;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stats-card {
    border-left: 4px solid #0d6efd;
    transition: all 0.3s ease;
    border-radius: 8px;
    overflow: hidden;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.stats-card .card-body {
    padding: 1.5rem;
    position: relative;
}

.stats-card .card-body::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(13, 110, 253, 0.05) 100%);
    border-radius: 0 0 0 100%;
}

.stats-number {
    font-size: 2.2rem;
    font-weight: 700;
    color: #0d6efd;
    line-height: 1.2;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.stats-label {
    color: #6c757d;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    font-weight: 600;
}

/* Search and Filter Styles */
.search-filter-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.filter-btn-group .btn {
    border-radius: 20px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.filter-btn-group .btn.active {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Responsive improvements */
@media (max-width: 768px) {
    .section-card {
        min-height: 80px;
        padding: 10px 6px;
    }
    
    .section-code {
        font-size: 12px;
    }
    
    .section-occupancy {
        font-size: 10px;
    }
    
    .stats-number {
        font-size: 1.8rem;
    }
    
    .section-row {
        padding: 15px;
    }
}

/* Loading animation */
.shelf-grid {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Tooltip styles */
.section-card[title] {
    position: relative;
}

/* Empty state styling */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.5;
}

/* 3-Column Layout Styles */
.shelf-grid .row {
    display: flex;
    flex-wrap: nowrap;
    margin: 0;
    padding: 0;
}

.shelf-grid .col-md-4 {
    flex: 0 0 33.333333%;
    max-width: 33.333333%;
    padding: 0 10px;
    display: block;
}

.section-row {
    margin-bottom: 25px;
    background: #ffffff;
    border-radius: 8px;
    padding: 15px;
    border: 1px solid #e9ecef;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    clear: both;
    overflow: hidden;
}

.section-row h6 {
    color: #6c757d;
    font-weight: 600;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e9ecef;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.section-row .row.g-2 {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 0;
    padding: 0;
}

.section-row {
    margin-bottom: 25px;
    background: #ffffff;
    border-radius: 8px;
    padding: 15px;
    border: 1px solid #e9ecef;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.section-row h6 {
    color: #6c757d;
    font-weight: 600;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e9ecef;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* Additional alignment fixes */
.shelf-grid {
    display: block;
    width: 100%;
}

/* Ensure consistent spacing between row groupings */
.col-md-4 .section-row:not(:last-child) {
    margin-bottom: 25px;
}

/* Make shelf cards consistent size */
.section-card {
    min-height: 80px;
    display: block;
    text-align: center;
    margin-bottom: 8px;
    width: 100%;
    box-sizing: border-box;
}

/* Prevent overflow and ensure proper spacing */
.col-md-4 {
    overflow: visible;
    position: relative;
}

.section-row {
    position: relative;
    z-index: 1;
    margin-bottom: 30px; /* Increased spacing between rows */
}

.section-card {
    position: relative;
    z-index: 2;
    margin-bottom: 10px;
}

/* Responsive adjustments for 3-column layout */
@media (max-width: 1200px) {
    .shelf-grid .col-md-4 {
        flex: 0 0 50%;
        max-width: 50%;
    }
}

@media (max-width: 768px) {
    .shelf-grid .row {
        flex-direction: column;
    }
    
    .shelf-grid .col-md-4 {
        flex: 0 0 100%;
        max-width: 100%;
        padding: 0 5px;
    }
    
    .section-card {
        min-height: 70px;
        padding: 8px 6px;
    }
    
    .section-code {
        font-size: 11px;
    }
    
    .section-occupancy {
        font-size: 9px;
    }
    
    .section-row {
        padding: 10px;
        margin-bottom: 20px;
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
                        <li class="breadcrumb-item"><a href="{{ route('warehouses.warehouses.index') }}">Warehouses</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('warehouses.warehouses.show', $warehouse) }}">{{ $warehouse->name }}</a></li>
                        <li class="breadcrumb-item active">Shelf Inventory</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    Shelf Inventory Management
                    <span class="badge bg-blue text-white ms-2">{{ $warehouse->name }}</span>
                </h2>
                <div class="page-subtitle">
                    Visual shelf-based inventory management system
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('warehouses.warehouses.shelf-report', $warehouse) }}" class="btn btn-outline-primary">
                        <i class="far fa-chart-bar me-2"></i>&nbsp;
                        Generate Report
                    </a>
                    <a href="{{ route('warehouses.warehouses.show', $warehouse) }}" class="btn">
                        <i class="far fa-arrow-left me-2"></i>&nbsp;
                        Back to Warehouse
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')
        
        <!-- Statistics Cards -->
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="subheader d-flex align-items-center">
                                    <i class="far fa-warehouse me-2 text-primary"></i>&nbsp;
                                    Total Sections
                                </div>
                                <div class="stats-number">{{ $stats['total_shelves'] }}</div>
                                <div class="stats-label">Sections Available</div>
                            </div>
                            <div class="text-primary">
                                <i class="far fa-warehouse fa-2x opacity-50"></i>&nbsp;
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="subheader d-flex align-items-center">
                                    <i class="fa-solid fa-box me-2 text-success"></i>&nbsp;
                                    Occupied Sections
                                </div>
                                <div class="stats-number text-success">{{ $stats['occupied_shelves'] }}</div>
                                <div class="stats-label">Sections in Use</div>
                            </div>
                            <div class="text-success">
                                <i class="fa-solid fa-box fa-2x opacity-50"></i>&nbsp;
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="subheader d-flex align-items-center">
                                    <i class="far fa-layer-group me-2 text-info"></i>&nbsp;
                                    Total Positions
                                </div>
                                <div class="stats-number text-info">{{ $stats['total_positions'] }}</div>
                                <div class="stats-label">Storage Positions</div>
                            </div>
                            <div class="text-info">
                                <i class="far fa-layer-group fa-2x opacity-50"></i>&nbsp;
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="subheader d-flex align-items-center">
                                    <i class="far fa-chart-pie me-2 text-warning"></i>&nbsp;
                                    Occupancy Rate
                                </div>
                                <div class="stats-number text-warning">{{ $stats['occupancy_rate'] }}%</div>
                                <div class="stats-label">Warehouse Utilization</div>
                            </div>
                            <div class="text-warning">
                                <i class="far fa-chart-pie fa-2x opacity-50"></i>&nbsp;
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="search-filter-section">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="far fa-search"></i>&nbsp;
                        </span>
                        <input type="text" class="form-control" placeholder="Search shelves by code..." id="shelfSearch">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                            <i class="far fa-times"></i>&nbsp;
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="btn-group filter-btn-group w-100" role="group">
                        <button type="button" class="btn btn-outline-secondary active" data-filter="all">
                            <i class="far fa-th me-1"></i>&nbsp; All
                        </button>
                        <button type="button" class="btn btn-outline-success" data-filter="occupied">
                            <i class="fa-solid fa-box me-1"></i>&nbsp; Occupied
                        </button>
                        <button type="button" class="btn btn-outline-warning" data-filter="empty">
                            <i class="far fa-square me-1"></i>&nbsp; Empty
                        </button>
                        <button type="button" class="btn btn-outline-danger" data-filter="full">
                            <i class="fa-solid fa-exclamation-triangle me-1"></i>&nbsp; Full
                        </button>
                    </div>
                </div>
            </div>
            <div class="row mt-3" id="filterSummary" style="display: none;">
                <div class="col-12">
                    <div class="alert alert-info mb-0">
                        <i class="far fa-info-circle me-2"></i>&nbsp;
                        <span id="filterSummaryText">Showing all shelves</span>
                        <button class="btn btn-sm btn-outline-info ms-2" id="resetFilters">
                            <i class="far fa-refresh me-1"></i>&nbsp; Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shelf Grid -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="far fa-warehouse me-2"></i>&nbsp;
                    Warehouse Shelf Layout
                </h3>
                <div class="card-actions">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-success text-white me-2">
                            <i class="fa-solid fa-box me-1"></i>&nbsp; Occupied
                        </span>
                        <span class="badge bg-light text-dark me-2">
                            <i class="far fa-square me-1"></i>&nbsp; Empty
                        </span>
                        <span class="badge bg-danger text-white">
                            <i class="fa-solid fa-exclamation-triangle me-1"></i>&nbsp; Full
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="shelf-grid">
                    @php $shelfColumns = $warehouse->shelf_columns; @endphp
                    <div class="row">
                        <!-- Column 1: Sections 1, 4, 7, 10, etc. -->
                        <div class="col-md-4">
                            @foreach($shelfColumns['column_1'] as $rowSection => $shelves)
                                <div class="section-row">
                                    <h6 class="mb-2 text-muted">{{ $rowSection }}</h6>
                                    <div class="row g-2">
                                        @foreach($shelves as $shelf)
                                            <div class="col-6 col-lg-4">
                                                <a href="{{ route('warehouses.warehouses.shelf-detail', [$warehouse, $shelf]) }}" 
                                                   class="section-card {{ $shelf->is_full ? 'section-full' : ($shelf->occupancy_rate > 0 ? 'section-occupied' : 'section-empty') }}">
                                                    <div class="section-code">{{ $shelf->shelf_code }}</div>
                                                    <div class="section-status">
                                                        @if($shelf->is_full)
                                                            <i class="fa-solid fa-exclamation-triangle text-danger"></i>&nbsp;
                                                        @elseif($shelf->occupancy_rate > 0)
                                                            <i class="fa-solid fa-box text-success"></i>&nbsp;
                                                        @else
                                                            <i class="far fa-square text-muted"></i>&nbsp;
                                                        @endif
                                                    </div>
                                                    <div class="section-occupancy">
                                                        {{ $shelf->occupied_positions }}/{{ $shelf->max_capacity }}
                                                        @if($shelf->occupancy_rate > 0)
                                                            ({{ $shelf->occupancy_rate }}%)
                                                        @endif
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Column 2: Sections 2, 5, 8, 11, etc. -->
                        <div class="col-md-4">
                            @foreach($shelfColumns['column_2'] as $rowSection => $shelves)
                                <div class="section-row">
                                    <h6 class="mb-2 text-muted">{{ $rowSection }}</h6>
                                    <div class="row g-2">
                                        @foreach($shelves as $shelf)
                                            <div class="col-6 col-lg-4">
                                                <a href="{{ route('warehouses.warehouses.shelf-detail', [$warehouse, $shelf]) }}" 
                                                   class="section-card {{ $shelf->is_full ? 'section-full' : ($shelf->occupancy_rate > 0 ? 'section-occupied' : 'section-empty') }}">
                                                    <div class="section-code">{{ $shelf->shelf_code }}</div>
                                                    <div class="section-status">
                                                        @if($shelf->is_full)
                                                            <i class="fa-solid fa-exclamation-triangle text-danger"></i>&nbsp;
                                                        @elseif($shelf->occupancy_rate > 0)
                                                            <i class="fa-solid fa-box text-success"></i>&nbsp;
                                                        @else
                                                            <i class="far fa-square text-muted"></i>&nbsp;
                                                        @endif
                                                    </div>
                                                    <div class="section-occupancy">
                                                        {{ $shelf->occupied_positions }}/{{ $shelf->max_capacity }}
                                                        @if($shelf->occupancy_rate > 0)
                                                            ({{ $shelf->occupancy_rate }}%)
                                                        @endif
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Column 3: Sections 3, 6, 9, 12, etc. -->
                        <div class="col-md-4">
                            @foreach($shelfColumns['column_3'] as $rowSection => $shelves)
                                <div class="section-row">
                                    <h6 class="mb-2 text-muted">{{ $rowSection }}</h6>
                                    <div class="row g-2">
                                        @foreach($shelves as $shelf)
                                            <div class="col-6 col-lg-4">
                                                <a href="{{ route('warehouses.warehouses.shelf-detail', [$warehouse, $shelf]) }}" 
                                                   class="section-card {{ $shelf->is_full ? 'section-full' : ($shelf->occupancy_rate > 0 ? 'section-occupied' : 'section-empty') }}">
                                                    <div class="section-code">{{ $shelf->shelf_code }}</div>
                                                    <div class="section-status">
                                                        @if($shelf->is_full)
                                                            <i class="fa-solid fa-exclamation-triangle text-danger"></i>&nbsp;
                                                        @elseif($shelf->occupancy_rate > 0)
                                                            <i class="fa-solid fa-box text-success"></i>&nbsp;
                                                        @else
                                                            <i class="far fa-square text-muted"></i>&nbsp;
                                                        @endif
                                                    </div>
                                                    <div class="section-occupancy">
                                                        {{ $shelf->occupied_positions }}/{{ $shelf->max_capacity }}
                                                        @if($shelf->occupancy_rate > 0)
                                                            ({{ $shelf->occupancy_rate }}%)
                                                        @endif
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                                <a href="{{ route('options.items.index') }}" class="btn btn-outline-primary w-100">
                                    <i class="far fa-list me-2"></i>&nbsp;
                                    View All Items
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('warehouses.picklist', $warehouse) }}" class="btn btn-outline-success w-100">
                                    <i class="far fa-list-check me-2"></i>&nbsp;
                                    Generate Picklist
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('warehouses.warehouses.shelf-report', $warehouse) }}" class="btn btn-outline-info w-100">
                                    <i class="far fa-chart-bar me-2"></i>&nbsp;
                                    Generate Report
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('warehouses.warehouses.show', $warehouse) }}" class="btn btn-outline-secondary w-100">
                                    <i class="far fa-building me-2"></i>&nbsp;
                                    Warehouse Details
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('warehouses.warehouses.index') }}" class="btn btn-outline-secondary w-100">
                                    <i class="far fa-arrow-left me-2"></i>&nbsp;
                                    All Warehouses
                                </a>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentFilter = 'all';
    let currentSearch = '';
    
    // Search functionality
    const searchInput = document.getElementById('shelfSearch');
    const clearSearchBtn = document.getElementById('clearSearch');
    const filterSummary = document.getElementById('filterSummary');
    const filterSummaryText = document.getElementById('filterSummaryText');
    const resetFiltersBtn = document.getElementById('resetFilters');
    
    function updateFilterSummary() {
        const visibleCards = document.querySelectorAll('.section-card[style*="display: block"], .section-card:not([style*="display: none"])');
        const totalCards = document.querySelectorAll('.section-card').length;
        
        if (currentSearch || currentFilter !== 'all') {
            filterSummary.style.display = 'block';
            let summaryText = `Showing ${visibleCards.length} of ${totalCards} shelves`;
            
            if (currentSearch) {
                summaryText += ` matching "${currentSearch}"`;
            }
            
            if (currentFilter !== 'all') {
                summaryText += ` (${currentFilter} only)`;
            }
            
            filterSummaryText.textContent = summaryText;
        } else {
            filterSummary.style.display = 'none';
        }
    }
    
    function applyFilters() {
        const shelfCards = document.querySelectorAll('.section-card');
        
        shelfCards.forEach(card => {
            const shelfCodeElement = card.querySelector('.section-code');
            if (!shelfCodeElement) return;
            
            const shelfCode = shelfCodeElement.textContent.toLowerCase();
            const matchesSearch = !currentSearch || shelfCode.includes(currentSearch.toLowerCase());
            const matchesFilter = currentFilter === 'all' || card.classList.contains(`section-${currentFilter}`);
            
            if (matchesSearch && matchesFilter) {
                card.style.display = 'block';
                card.style.opacity = '1';
            } else {
                card.style.display = 'none';
                card.style.opacity = '0';
            }
        });
        
        // Update section row visibility
        const sectionRows = document.querySelectorAll('.section-row');
        sectionRows.forEach(row => {
            const visibleCards = row.querySelectorAll('.section-card[style*="display: block"], .section-card:not([style*="display: none"])');
            if (visibleCards.length === 0 && (currentSearch || currentFilter !== 'all')) {
                row.style.display = 'none';
            } else {
                row.style.display = 'block';
            }
        });
        
        // Update column visibility
        const columns = document.querySelectorAll('.col-md-4');
        columns.forEach(column => {
            const visibleRows = column.querySelectorAll('.section-row[style*="display: block"], .section-row:not([style*="display: none"])');
            if (visibleRows.length === 0 && (currentSearch || currentFilter !== 'all')) {
                column.style.display = 'none';
            } else {
                column.style.display = 'block';
            }
        });
        
        updateFilterSummary();
        
        // Re-ensure 3-column layout after filtering
        setTimeout(ensureThreeColumnLayout, 100);
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentSearch = this.value;
            applyFilters();
        });
    }
    
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            if (searchInput) {
                searchInput.value = '';
                currentSearch = '';
                applyFilters();
            }
        });
    }
    
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function() {
            // Reset search
            if (searchInput) {
                searchInput.value = '';
                currentSearch = '';
            }
            
            // Reset filter
            const filterButtons = document.querySelectorAll('[data-filter]');
            filterButtons.forEach(btn => {
                btn.classList.remove('active', 'btn-primary');
                btn.classList.add('btn-outline-secondary', 'btn-outline-success', 'btn-outline-warning', 'btn-outline-danger');
            });
            
            const allBtn = document.querySelector('[data-filter="all"]');
            if (allBtn) {
                allBtn.classList.add('active', 'btn-primary');
                allBtn.classList.remove('btn-outline-secondary');
            }
            
            currentFilter = 'all';
            applyFilters();
        });
    }
    
    // Filter functionality
    const filterButtons = document.querySelectorAll('[data-filter]');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all buttons
            filterButtons.forEach(btn => {
                btn.classList.remove('active', 'btn-primary');
                btn.classList.add('btn-outline-secondary', 'btn-outline-success', 'btn-outline-warning', 'btn-outline-danger');
            });
            
            // Add active class to clicked button
            this.classList.add('active');
            this.classList.remove('btn-outline-secondary', 'btn-outline-success', 'btn-outline-warning', 'btn-outline-danger');
            this.classList.add('btn-primary');
            
            currentFilter = this.dataset.filter;
            applyFilters();
        });
    });
    
    // Add tooltips to shelf cards
    const shelfCards = document.querySelectorAll('.section-card');
    shelfCards.forEach(card => {
        const shelfCode = card.querySelector('.section-code').textContent;
        const occupancy = card.querySelector('.section-occupancy').textContent;
        const status = card.classList.contains('section-empty') ? 'Empty' : 
                     card.classList.contains('section-occupied') ? 'Occupied' : 'Full';
        
        card.setAttribute('title', `${shelfCode} - ${status} (${occupancy})`);
    });
    
    // Add smooth scrolling for better UX
    const shelfGrid = document.querySelector('.shelf-grid');
    if (shelfGrid) {
        shelfGrid.style.scrollBehavior = 'smooth';
    }
    
    // Add loading animation
    const cards = document.querySelectorAll('.section-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.05}s`;
        card.style.animation = 'fadeInUp 0.6s ease-out forwards';
    });
    
    // Initialize filter summary
    updateFilterSummary();
    
    // Ensure proper 3-column layout
    function ensureThreeColumnLayout() {
        const shelfGrid = document.querySelector('.shelf-grid');
        if (shelfGrid) {
            const row = shelfGrid.querySelector('.row');
            if (row) {
                row.style.display = 'flex';
                row.style.flexWrap = 'nowrap';
            }
            
            const columns = shelfGrid.querySelectorAll('.col-md-4');
            columns.forEach(column => {
                column.style.flex = '0 0 33.333333%';
                column.style.maxWidth = '33.333333%';
                column.style.display = 'block';
            });
        }
    }
    
    // Run layout fix on page load
    ensureThreeColumnLayout();
});
</script>
@endpush
