@extends('layouts.app')

@section('title', 'Global Picklist Results')

@push('css')
<style>
.results-header {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.results-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.summary-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
    transition: all 0.3s ease;
}

.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.summary-card.success {
    border-left: 4px solid #28a745;
}

.summary-card.warning {
    border-left: 4px solid #ffc107;
}

.summary-card.danger {
    border-left: 4px solid #dc3545;
}

.summary-card.info {
    border-left: 4px solid #17a2b8;
}

.summary-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.summary-card.success .summary-number {
    color: #28a745;
}

.summary-card.warning .summary-number {
    color: #ffc107;
}

.summary-card.danger .summary-number {
    color: #dc3545;
}

.summary-card.info .summary-number {
    color: #17a2b8;
}

.summary-label {
    color: #6c757d;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.item-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.item-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.item-name {
    font-size: 1.25rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.25rem;
}

.item-category {
    color: #6c757d;
    font-size: 0.9rem;
}

.item-stats {
    display: flex;
    gap: 1rem;
    margin-top: 0.5rem;
}

.item-stat {
    background: #f8f9fa;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    text-align: center;
}

.item-stat-label {
    font-size: 0.8rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.item-stat-value {
    font-weight: 600;
    color: #495057;
}

.picklist-positions {
    margin-top: 1rem;
}

.position-row {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 0.5rem;
    border-left: 4px solid #0d6efd;
    transition: all 0.3s ease;
}

.position-row:hover {
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.position-row.expiring {
    border-left-color: #ffc107;
    background: #fff3cd;
}

.position-row.expired {
    border-left-color: #dc3545;
    background: #f8d7da;
}

.position-info {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.position-location {
    font-weight: 600;
    color: #495057;
    font-family: 'Courier New', monospace;
}

.position-quantity {
    background: #0d6efd;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.9rem;
}

.position-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-top: 0.5rem;
}

.position-detail {
    text-align: center;
}

.position-detail-label {
    font-size: 0.8rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.position-detail-value {
    font-weight: 600;
    color: #495057;
    margin-top: 0.25rem;
}

.expiry-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.expiry-badge.expiring {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.expiry-badge.expired {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.expiry-badge.good {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.shortage-alert {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    padding: 1rem;
    border-radius: 8px;
    margin-top: 1rem;
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
    
    .results-header {
        background: #f8f9fa !important;
        color: #000 !important;
    }
    
    .summary-card {
        box-shadow: none;
        border: 1px solid #dee2e6;
    }
}

@media (max-width: 768px) {
    .results-header h1 {
        font-size: 2rem;
    }
    
    .summary-cards {
        grid-template-columns: 1fr;
    }
    
    .item-stats {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .position-details {
        grid-template-columns: 1fr;
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
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.warehouses.picklist') }}">Generate Picklist</a></li>
                        <li class="breadcrumb-item active">Results</li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <button onclick="window.print()" class="btn btn-primary print-button">
                        <i class="far fa-print me-2"></i>
                        Print Picklist
                    </button>
                    <a href="{{ route('manufacturing.warehouses.picklist') }}" class="btn btn-outline-secondary">
                        <i class="far fa-arrow-left me-2"></i>
                        New Picklist
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')
        
        <!-- Results Header -->
        <div class="results-header">
            <div class="row align-items-center">
                <div class="col">
                    <h1>
                        <i class="far fa-list-check me-3"></i>
                        Global Picklist Results
                    </h1>
                    <div class="subtitle">
                        <strong>All Warehouses</strong> • Generated on {{ now()->format('F j, Y \a\t g:i A') }}
                    </div>
                </div>
                <div class="col-auto">
                    <div class="text-end">
                        <div class="h4 mb-0">Multi-Warehouse</div>
                        <small>Global Picklist</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card success">
                <div class="summary-number">{{ $summary['total_items_requested'] }}</div>
                <div class="summary-label">Items Requested</div>
            </div>
            <div class="summary-card info">
                <div class="summary-number">{{ number_format($summary['total_quantity_requested'], 2) }}</div>
                <div class="summary-label">Total Quantity</div>
            </div>
            <div class="summary-card success">
                <div class="summary-number">{{ number_format($summary['total_quantity_pickable'], 2) }}</div>
                <div class="summary-label">Can Pick</div>
            </div>
            @if($summary['total_shortage'] > 0)
            <div class="summary-card danger">
                <div class="summary-number">{{ number_format($summary['total_shortage'], 2) }}</div>
                <div class="summary-label">Shortage</div>
            </div>
            @endif
            @if($summary['expiring_soon_count'] > 0)
            <div class="summary-card warning">
                <div class="summary-number">{{ $summary['expiring_soon_count'] }}</div>
                <div class="summary-label">Expiring Soon</div>
            </div>
            @endif
            @if($summary['expired_count'] > 0)
            <div class="summary-card danger">
                <div class="summary-number">{{ $summary['expired_count'] }}</div>
                <div class="summary-label">Expired Items</div>
            </div>
            @endif
        </div>

        <!-- Picklist Items -->
        @foreach($picklistResults as $itemResult)
        <div class="item-section">
            <div class="item-header">
                <div>
                    <div class="item-name">{{ $itemResult['item']->name }}</div>
                    <div class="item-category">{{ $itemResult['item']->itemCategory->name ?? 'No Category' }}</div>
                    <div class="item-stats">
                        <div class="item-stat">
                            <div class="item-stat-label">Requested</div>
                            <div class="item-stat-value">{{ number_format($itemResult['requested_quantity'], 2) }} {{ $itemResult['item']->unit }}</div>
                        </div>
                        <div class="item-stat">
                            <div class="item-stat-label">Can Pick</div>
                            <div class="item-stat-value">{{ number_format($itemResult['total_pickable'], 2) }} {{ $itemResult['item']->unit }}</div>
                        </div>
                        @if($itemResult['shortage'] > 0)
                        <div class="item-stat">
                            <div class="item-stat-label">Shortage</div>
                            <div class="item-stat-value text-danger">{{ number_format($itemResult['shortage'], 2) }} {{ $itemResult['item']->unit }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($itemResult['shortage'] > 0)
            <div class="shortage-alert">
                <i class="far fa-exclamation-triangle me-2"></i>
                <strong>Shortage Alert:</strong> Only {{ number_format($itemResult['total_pickable'], 2) }} {{ $itemResult['item']->unit }} available, but {{ number_format($itemResult['requested_quantity'], 2) }} {{ $itemResult['item']->unit }} requested.
            </div>
            @endif

            <div class="picklist-positions">
                <h5 class="mb-3">
                    <i class="far fa-map-marker-alt me-2"></i>
                    Pick from these locations (ordered by expiry date):
                </h5>
                
                @foreach($itemResult['picklist_positions'] as $position)
                <div class="position-row 
                    @if($position['days_until_expiry'] !== null && $position['days_until_expiry'] < 0) expired
                    @elseif($position['days_until_expiry'] !== null && $position['days_until_expiry'] <= 7) expiring
                    @endif">
                    <div class="position-info">
                        <div class="position-location">{{ $position['location'] }}</div>
                        <div class="position-quantity">{{ number_format($position['quantity_to_take'], 2) }} {{ $itemResult['item']->unit }}</div>
                    </div>
                    <div class="position-warehouse mb-2">
                        <span class="badge bg-info">{{ $position['warehouse']->name }}</span>
                    </div>
                    
                    <div class="position-details">
                        <div class="position-detail">
                            <div class="position-detail-label">Shelf</div>
                            <div class="position-detail-value">{{ $position['shelf'] }}</div>
                        </div>
                        <div class="position-detail">
                            <div class="position-detail-label">Remaining After Pick</div>
                            <div class="position-detail-value">{{ number_format($position['remaining_after'], 2) }} {{ $itemResult['item']->unit }}</div>
                        </div>
                        <div class="position-detail">
                            <div class="position-detail-label">Expiry Date</div>
                            <div class="position-detail-value">
                                @if($position['expiry_date'])
                                    {{ $position['expiry_date']->format('M j, Y') }}
                                @else
                                    <span class="text-muted">No expiry</span>
                                @endif
                            </div>
                        </div>
                        <div class="position-detail">
                            <div class="position-detail-label">Time Until Expiry</div>
                            <div class="position-detail-value">
                                @if($position['days_until_expiry'] !== null)
                                    @if($position['days_until_expiry'] < 0)
                                        @php
                                            $daysAgo = abs($position['days_until_expiry']);
                                            $monthsAgo = intval($daysAgo / 30);
                                            $remainingDaysAgo = $daysAgo % 30;
                                        @endphp
                                        <span class="expiry-badge expired">
                                            Expired {{ $monthsAgo > 0 ? $monthsAgo . ' month' . ($monthsAgo > 1 ? 's' : '') . ' ' : '' }}{{ $remainingDaysAgo }} day{{ $remainingDaysAgo != 1 ? 's' : '' }} ago
                                        </span>
                                    @elseif($position['days_until_expiry'] <= 7)
                                        @php
                                            $days = $position['days_until_expiry'];
                                            $months = intval($days / 30);
                                            $remainingDays = $days % 30;
                                        @endphp
                                        <span class="expiry-badge expiring">
                                            {{ $months > 0 ? $months . ' month' . ($months > 1 ? 's' : '') . ' ' : '' }}{{ $remainingDays }} day{{ $remainingDays != 1 ? 's' : '' }} to expiry
                                        </span>
                                    @else
                                        @php
                                            $days = $position['days_until_expiry'];
                                            $months = intval($days / 30);
                                            $remainingDays = $days % 30;
                                        @endphp
                                        <span class="expiry-badge good">
                                            {{ $months > 0 ? $months . ' month' . ($months > 1 ? 's' : '') . ' ' : '' }}{{ $remainingDays }} day{{ $remainingDays != 1 ? 's' : '' }} to expiry
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">No expiry</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <!-- Unfulfilled Items -->
        @if($unfulfilledItems->count() > 0)
        <div class="item-section">
            <div class="item-header">
                <h4 class="text-danger">
                    <i class="far fa-exclamation-triangle me-2"></i>
                    Items Not Available in Sufficient Quantity
                </h4>
            </div>
            
            @foreach($unfulfilledItems as $unfulfilled)
            <div class="position-row">
                <div class="position-info">
                    <div>
                        <div class="position-location">{{ $unfulfilled['item']->name }}</div>
                        <div class="text-muted">{{ $unfulfilled['item']->itemCategory->name ?? 'No Category' }}</div>
                    </div>
                    <div class="text-danger">
                        <strong>Shortage: {{ number_format($unfulfilled['shortage'], 3) }} {{ $unfulfilled['item']->unit }}</strong>
                    </div>
                </div>
                <div class="position-details">
                    <div class="position-detail">
                        <div class="position-detail-label">Requested</div>
                        <div class="position-detail-value">{{ number_format($unfulfilled['requested'], 3) }} {{ $unfulfilled['item']->unit }}</div>
                    </div>
                    <div class="position-detail">
                        <div class="position-detail-label">Available</div>
                        <div class="position-detail-value">{{ number_format($unfulfilled['available'], 3) }} {{ $unfulfilled['item']->unit }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Instructions -->
        <div class="item-section">
            <div class="item-header">
                <h4>
                    <i class="far fa-info-circle me-2"></i>
                    Picklist Instructions
                </h4>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h6>Picking Order:</h6>
                    <ol>
                        <li>Items are ordered by expiry date (FIFO - First In, First Out)</li>
                        <li>Pick from locations with closest expiry dates first</li>
                        <li>Items without expiry dates are picked last</li>
                        <li>Within the same expiry date, older items are picked first</li>
                    </ol>
                </div>
                <div class="col-md-6">
                    <h6>Important Notes:</h6>
                    <ul>
                        <li>Check expiry dates before picking</li>
                        <li>Items marked as "Expired" should be removed from inventory</li>
                        <li>Items expiring within 7 days should be prioritized</li>
                        <li>Update inventory quantities after picking</li>
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
    window.printPicklist = function() {
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
    
    // Highlight expiring and expired items
    document.querySelectorAll('.position-row.expiring, .position-row.expired').forEach(row => {
        row.style.animation = 'pulse 2s infinite';
    });
});
</script>
@endpush
