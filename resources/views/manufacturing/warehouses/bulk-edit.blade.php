@extends('layouts.app')

@section('title', 'Bulk Edit Inventory - ' . $warehouse->name)

@push('css')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">

<style>
    option {
        border: 0px !important;
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('manufacturing.dashboard') }}">Manufacturing</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('manufacturing.warehouses.index') }}">Warehouses</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('manufacturing.warehouses.show', $warehouse) }}">{{ $warehouse->name }}</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Bulk Edit</li>
                            </ol>
                        </nav>
                    </div>
                    <h2 class="page-title">
                        <i class="far fa-edit me-2"></i>
                        Bulk Edit Inventory
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ route('manufacturing.warehouses.show', $warehouse) }}" class="btn btn-outline-secondary">
                            <i class="far fa-arrow-left me-2"></i>
                            Back to Warehouse
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="container-xl">
            <div class="alert alert-success alert-dismissible" role="alert">
                <div class="d-flex">
                    <div>
                        <i class="fa fa-check-circle me-2"></i>
                        {{ session('success') }}
                    </div>
                </div>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="container-xl">
            <div class="alert alert-danger alert-dismissible" role="alert">
                <div class="d-flex">
                    <div>
                        <i class="fa fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                    </div>
                </div>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="container-xl">
            <div class="alert alert-danger alert-dismissible" role="alert">
                <div class="d-flex">
                    <div>
                        <i class="fa fa-exclamation-circle me-2"></i>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        </div>
    @endif

    <div class="page-body">
        <div class="container-xl">
            <!-- Warehouse Info -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h3 class="card-title">{{ $warehouse->name }}</h3>
                                    <p class="text-muted">{{ $warehouse->description }}</p>
                                </div>
                                <div class="col-md-6">
                                    <div class="row text-center">
                                        <div class="col">
                                            <div class="h2 text-blue">{{ $warehouse->shelves()->count() }}</div>
                                            <div class="text-muted">Total Shelves</div>
                                        </div>
                                        <div class="col">
                                            <div class="h2 text-green">{{ $warehouse->shelf_inventory_stats['occupied_positions'] }}</div>
                                            <div class="text-muted">Occupied Positions</div>
                                        </div>
                                        <div class="col">
                                            <div class="h2 text-orange">{{ $warehouse->shelf_inventory_stats['total_positions'] }}</div>
                                            <div class="text-muted">Total Positions</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aisle Navigation -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="far fa-layer-group me-2"></i>
                                Aisle Navigation
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                @foreach($availableAisles as $aisle)
                                <div class="col-auto">
                                    <button type="button" class="btn btn-outline-primary aisle-nav-btn {{ $aisles->has($aisle) ? 'active' : '' }}" 
                                            data-aisle="{{ $aisle }}" 
                                            id="aisle-btn-{{ $aisle }}">
                                        <i class="far fa-layer-group me-1"></i>
                                        Aisle {{ $aisle }}
                                        <span class="badge bg-blue-lt ms-1" id="aisle-count-{{ $aisle }}">-</span>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Controls -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Filter by Status</label>
                                    <select class="form-select" id="status-filter">
                                        <option value="">All Status</option>
                                        <option value="empty">Empty Only</option>
                                        <option value="occupied">Occupied Only</option>
                                        <option value="expiring">Expiring Soon</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Search by Shelf</label>
                                    <select class="form-select form-select-sm tom-select" id="search-location">
                                        <option value="">All Shelves</option>
                                        @foreach($aisles as $aisle => $positions)
                                            @php
                                                $shelves = $positions->groupBy(function($position) {
                                                    return substr($position->full_location_code, 0, strrpos($position->full_location_code, '-'));
                                                });
                                            @endphp
                                            @foreach($shelves as $shelfCode => $shelfPositions)
                                            <option value="{{ $shelfCode }}">{{ $shelfCode }}</option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-primary" id="apply-filters">
                                            <i class="far fa-filter me-1"></i>
                                            Filter
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" id="clear-filters">
                                            <i class="far fa-times me-1"></i>
                                            Clear
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bulk Operations -->
            <form method="POST" action="{{ route('manufacturing.warehouses.bulk-update', $warehouse) }}" id="bulk-operations-form">
                @csrf
                <div class="row mb-4" id="bulk-operations" style="display: none;">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label">Bulk Action</label>
                                        <select class="form-select" name="action" id="bulk-action" required>
                                            <option value="">Select Action</option>
                                            <option value="assign">Assign Item to Selected</option>
                                            <option value="update">Update Quantity/Expiry</option>
                                            <option value="clear">Clear Selected Positions</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3" id="bulk-item-field" style="display: none;">
                                        <label class="form-label">Item</label>
                                        <select class="form-select tom-select" name="item_id" id="bulk-item">
                                            <option value="">Select Item</option>
                                            @foreach($items as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2" id="bulk-quantity-field" style="display: none;">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" class="form-control" name="quantity" id="bulk-quantity" min="0" step="0.01">
                                    </div>
                                    <div class="col-md-2" id="bulk-expiry-field" style="display: none;">
                                        <label class="form-label">Expiry Date</label>
                                        <input type="date" class="form-control" name="expiry_date" id="bulk-expiry" value="{{ date('Y-m-d', strtotime('+18 months')) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary" id="execute-bulk-action">
                                            <i class="far fa-play me-1"></i>
                                            Execute
                                        </button>
                                    </div>
                                </div>
                                <!-- Hidden inputs for selected positions will be added by JavaScript -->
                                <div id="selected-positions-inputs"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Aisles Grid -->
            <div class="row" id="aisles-container">
                @foreach($aisles as $aisle => $positions)
                <div class="col-12 mb-4 aisle-section" data-aisle="{{ $aisle }}" id="aisle-section-{{ $aisle }}">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="card-title">
                                        <i class="far fa-layer-group me-2"></i>
                                        Aisle {{ $aisle }}
                                        <span class="badge bg-blue-lt ms-2" id="aisle-count-badge-{{ $aisle }}">{{ $positions->count() }} positions</span>
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="aisle-{{ $aisle }}-content">
                            <div class="table-responsive">
                                <table class="table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" class="form-check-input" id="select-all-{{ $aisle }}">
                                            </th>
                                            <th width="120">Location</th>
                                            <th>Current Item</th>
                                            <th width="120">Quantity</th>
                                            <th width="120">Expiry Date</th>
                                            <th width="200">Updated By</th>
                                            <th width="150">Last Updated</th>
                                            <th width="50">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="aisle-{{ $aisle }}-positions">
                                        @php
                                            $groupedPositions = $positions->groupBy(function($position) {
                                                return substr($position->full_location_code, 0, strrpos($position->full_location_code, '-'));
                                            });
                                        @endphp
                                        @foreach($groupedPositions as $shelfCode => $shelfPositions)
                                            <!-- Shelf Header Row -->
                                            <tr class="shelf-header-row bg-light">
                                                <td colspan="8" class="fw-bold text-primary">
                                                    <i class="far fa-layer-group me-2"></i>
                                                    Shelf {{ $shelfCode }} ({{ $shelfPositions->count() }} positions)
                                                </td>
                                            </tr>
                                            @foreach($shelfPositions as $position)
                                            <tr class="position-row" data-position-id="{{ $position->id }}" data-aisle="{{ $aisle }}" data-shelf="{{ $shelfCode }}">
                                                <td>
                                                    <input type="checkbox" class="form-check-input position-checkbox" value="{{ $position->id }}">
                                                </td>
                                                <td>
                                                    <span class="badge bg-blue-lt">{{ $position->full_location_code }}</span>
                                                </td>
                                                <td>
                                                    <select class="form-select form-select-sm item-select tom-select" data-position-id="{{ $position->id }}">
                                                        <option value="">Select Item</option>
                                                        @foreach($items as $item)
                                                        <option value="{{ $item->id }}" 
                                                            @if($position->current_item && $position->current_item->item_id == $item->id) selected @endif>
                                                            {{ $item->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm quantity-input" 
                                                           data-position-id="{{ $position->id }}" 
                                                           value="{{ $position->current_item ? $position->current_item->quantity : 0 }}" 
                                                           min="0" step="0.01" max="999999.99">
                                                </td>
                                                <td>
                                                    <input type="date" class="form-control form-control-sm expiry-input" 
                                                           data-position-id="{{ $position->id }}" 
                                                           value="{{ $position->current_item && $position->current_item->expiry_date ? $position->current_item->expiry_date->format('Y-m-d') : date('Y-m-d', strtotime('+18 months')) }}">
                                                </td>
                                                <td>
                                                    <span class="text-muted">
                                                        @if($position->current_item && $position->current_item->last_updated_by)
                                                            {{ $position->current_item->updatedBy->name ?? 'Unknown' }}
                                                        @endif
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($position->current_item)
                                                    <span class="text-muted" title="{{ $position->current_item->updated_at->format('M d, Y H:i:s') }}">
                                                        {{ $position->current_item->updated_at->diffForHumans() }}
                                                    </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <form method="POST" action="{{ route('manufacturing.warehouses.bulk-update', $warehouse) }}" 
                                                          style="display: inline;" 
                                                          onsubmit="return confirm('Are you sure you want to clear this position?')">
                                                        @csrf
                                                        <input type="hidden" name="action" value="clear">
                                                        <input type="hidden" name="position_ids[]" value="{{ $position->id }}">
                                                        <button type="submit" class="btn btn-sm btn-outline-warning" 
                                                                title="Clear position data">
                                                            <i class="far fa-broom"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                
                <!-- Loading placeholder for dynamic aisles -->
                <div class="col-12 mb-4" id="aisle-loading-placeholder" style="display: none;">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Loading aisle...</span>
                            </div>
                            <div>Loading aisle data...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        
        <!-- Save All Changes Button -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-success btn-lg" id="save-all-btn" disabled>
                        <i class="far fa-save me-2"></i>
                        Save All Changes
                    </button>
                </div>
            </div>
        </div>
</div>

<!-- Loading Modal removed - using form submissions instead of AJAX -->
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let hasChanges = false;
    let selectedPositions = new Set();
    let loadedAisles = new Set(@json($aisles->keys()->toArray()));
    let allItems = @json($items);

    // Initialize
    initializeEventHandlers();
    updateBulkOperationsVisibility();
    updateAisleNavigation();
    initializeTomSelect();
    
    // Show only the first aisle by default
    $('.aisle-section').hide();
    $('.aisle-section').first().show();
    
    // Initialize aisle counts for loaded aisles
    @foreach($aisles as $aisle => $positions)
        $('#aisle-count-{{ $aisle }}').text('{{ $positions->count() }}');
    @endforeach
    
    // Load counts for all available aisles that aren't already loaded
    @foreach($availableAisles as $aisle)
        @if(!$aisles->has($aisle))
            loadAisleCount('{{ $aisle }}');
        @endif
    @endforeach
    
    // Update navigation to reflect the initial state
    updateAisleNavigation();

    function loadAisleCount(aisle) {
        $.ajax({
            url: '{{ url("manufacturing/warehouses/{$warehouse->id}/aisle-positions") }}/' + aisle,
            method: 'GET',
            success: function(response) {
                if (response.success && response.count !== undefined) {
                    $(`#aisle-count-${aisle}`).text(response.count);
                }
            },
            error: function(xhr) {
                // Silently handle error - keep the "-" placeholder
            }
        });
    }

    function initializeEventHandlers() {
        // Aisle navigation buttons - single selection
        $('.aisle-nav-btn').on('click', function() {
            const aisle = $(this).data('aisle');
            
            // Remove active class from all buttons
            $('.aisle-nav-btn').removeClass('active');
            // Add active class to clicked button
            $(this).addClass('active');
            
            // Always hide all aisle sections first
            $('.aisle-section').hide();
            
            if (!loadedAisles.has(aisle)) {
                loadAisle(aisle);
            } else {
                // Show only the selected aisle
                $(`#aisle-section-${aisle}`).show();
                // Update navigation to reflect the change
                updateAisleNavigation();
            }
        });

        // Position checkboxes
        $(document).on('change', '.position-checkbox', function() {
            const positionId = parseInt($(this).val());
            if ($(this).is(':checked')) {
                selectedPositions.add(positionId);
            } else {
                selectedPositions.delete(positionId);
            }
            updateBulkOperationsVisibility();
        });

        // Select all checkboxes
        $(document).on('change', '[id^="select-all-"]', function() {
            const aisle = $(this).attr('id').replace('select-all-', '');
            const isChecked = $(this).is(':checked');
            
            $(`.position-row[data-aisle="${aisle}"] .position-checkbox`).prop('checked', isChecked);
            
            if (isChecked) {
                $(`.position-row[data-aisle="${aisle}"] .position-checkbox`).each(function() {
                    selectedPositions.add(parseInt($(this).val()));
                });
            } else {
                $(`.position-row[data-aisle="${aisle}"] .position-checkbox`).each(function() {
                    selectedPositions.delete(parseInt($(this).val()));
                });
            }
            updateBulkOperationsVisibility();
        });

        // Removed aisle toggle functionality

        // Removed individual position updates - using bulk operations only

        // Clear position
        // Clear position buttons - now using form submissions

        // Bulk action change
        $('#bulk-action').on('change', function() {
            const action = $(this).val();
            updateBulkActionFields(action);
        });

        // Form submission - add selected positions as hidden inputs
        $("#bulk-operations-form").on("submit", function(e) {
            const positionIds = Array.from(selectedPositions);
            const action = $("#bulk-action").val();
            
            if (positionIds.length === 0) {
                e.preventDefault();
                showToast("warning", "Please select positions first");
                return false;
            }
            
            // Validate based on action type
            if (action === "assign") {
                if (!$("#bulk-item").val()) {
                    e.preventDefault();
                    showToast("warning", "Please select an item for assignment");
                    return false;
                }
                if (!$("#bulk-quantity").val() || parseFloat($("#bulk-quantity").val()) <= 0) {
                    e.preventDefault();
                    showToast("warning", "Please enter a valid quantity for assignment");
                    return false;
                }
            } else if (action === "update") {
                if (!$("#bulk-quantity").val() && !$("#bulk-expiry").val()) {
                    e.preventDefault();
                    showToast("warning", "Please fill at least one field (quantity or expiry date) for update");
                    return false;
                }
            }
            
            // Clear existing hidden inputs
            $("#selected-positions-inputs").empty();
            
            // Add hidden inputs for each selected position
            positionIds.forEach(positionId => {
                $("#selected-positions-inputs").append(
                    `<input type="hidden" name="position_ids[]" value="${positionId}">`
                );
            });
            
            // Show confirmation
            if (!confirm(`Are you sure you want to ${action} ${positionIds.length} positions?`)) {
                e.preventDefault();
                return false;
            }
        });

        // Filters
        $('#apply-filters').on('click', function() {
            applyFilters();
        });

        $('#clear-filters').on('click', function() {
            clearFilters();
        });

        // Save all changes
        $('#save-all-btn').on('click', function() {
            saveAllChanges();
        });

        // Track changes
        $(document).on('change', '.item-select, .quantity-input, .expiry-input', function() {
            hasChanges = true;
            $('#save-all-btn').prop('disabled', false);
        });
    }

    function loadAisle(aisle) {
        const button = $(`#aisle-btn-${aisle}`);
        const originalText = button.html();
        
        // Show loading state
        button.prop('disabled', true).html('<i class="far fa-spinner fa-spin me-1"></i>Loading...');
        $('#aisle-loading-placeholder').show();

        const url = '{{ url("manufacturing/warehouses/{$warehouse->id}/aisle-positions") }}/' + aisle;

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    // Update allItems with filtered items from response
                    if (response.items) {
                        allItems = response.items;
                    }
                    renderAisleData(aisle, response.positions);
                    loadedAisles.add(aisle);
                    updateAisleNavigation();
                    updateAisleFilter();
                    
                    // Initialize TomSelect on newly created dropdowns
                    initializeTomSelect();
                    
                    // Show only the loaded aisle
                    $('.aisle-section').hide();
                    $(`#aisle-section-${aisle}`).show();
                } else {
                    showToast('error', 'Failed to load aisle data');
                }
            },
            error: function(xhr) {
                showToast('error', 'Error loading aisle: ' + (xhr.responseJSON?.message || 'Unknown error'));
            },
            complete: function() {
                button.prop('disabled', false).html(originalText);
                $('#aisle-loading-placeholder').hide();
            }
        });
    }

    // Removed loadAllAisles function - single aisle selection only

    function renderAisleData(aisle, positions) {
        const aisleSection = $(`#aisle-section-${aisle}`);
        
        if (aisleSection.length === 0) {
            // Create new aisle section
            const aisleHtml = createAisleHtml(aisle, positions);
            $('#aisles-container').append(aisleHtml);
        } else {
            // Update existing aisle section
            const tbody = $(`#aisle-${aisle}-positions`);
            tbody.empty();
            
            // Group positions by shelf
            const groupedPositions = groupPositionsByShelf(positions);
            
            Object.keys(groupedPositions).forEach(shelfCode => {
                const shelfPositions = groupedPositions[shelfCode];
                
                // Add shelf header
                const shelfHeaderHtml = `
                    <tr class="shelf-header-row bg-light">
                        <td colspan="8" class="fw-bold text-primary">
                            <i class="far fa-layer-group me-2"></i>
                            Shelf ${shelfCode} (${shelfPositions.length} positions)
                        </td>
                    </tr>
                `;
                tbody.append(shelfHeaderHtml);
                
                // Add position rows
                shelfPositions.forEach(position => {
                    const rowHtml = createPositionRowHtml(position);
                    tbody.append(rowHtml);
                });
            });
            
            // Update count badge
            $(`#aisle-count-badge-${aisle}`).text(`${positions.length} positions`);
        }
        
        // Update navigation button
        $(`#aisle-count-${aisle}`).text(positions.length);
        $(`#aisle-btn-${aisle}`).addClass('active');
        
        // Initialize Tom Select for new elements
        initializeTomSelect();
    }

    function groupPositionsByShelf(positions) {
        const grouped = {};
        positions.forEach(position => {
            const shelfCode = position.full_location.substring(0, position.full_location.lastIndexOf('-'));
            if (!grouped[shelfCode]) {
                grouped[shelfCode] = [];
            }
            grouped[shelfCode].push(position);
        });
        return grouped;
    }

    function createAisleHtml(aisle, positions) {
        let positionsHtml = '';
        positions.forEach(position => {
            positionsHtml += createPositionRowHtml(position);
        });

        return `
            <div class="col-12 mb-4 aisle-section" data-aisle="${aisle}" id="aisle-section-${aisle}">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="card-title">
                                    <i class="far fa-layer-group me-2"></i>
                                    Aisle ${aisle}
                                    <span class="badge bg-blue-lt ms-2" id="aisle-count-badge-${aisle}">${positions.length} positions</span>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" id="aisle-${aisle}-content">
                        <div class="table-responsive">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" class="form-check-input" id="select-all-${aisle}">
                                        </th>
                                        <th width="80">Location</th>
                                        <th>Current Item</th>
                                        <th width="50">Quantity</th>
                                        <th>Expiry Date</th>
                                        <th>Notes</th>
                                        <th>Last Updated</th>
                                        <th width="80">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="aisle-${aisle}-positions">
                                    ${positionsHtml}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function createPositionRowHtml(position) {
        const currentItem = position.current_item;
        const itemOptions = allItems.map(item => 
            `<option value="${item.id}" ${currentItem && currentItem.id === item.id ? 'selected' : ''}>${item.name}</option>`
        ).join('');

        return `
            <tr class="position-row" data-position-id="${position.id}" data-aisle="${position.aisle}" data-shelf="${position.shelf || ''}">
                <td>
                    <input type="checkbox" class="form-check-input position-checkbox" value="${position.id}">
                </td>
                <td>
                    <span class="badge bg-blue-lt">${position.full_location}</span>
                </td>
                <td>
                    <select class="form-select form-select-sm item-select tom-select" data-position-id="${position.id}">
                        <option value="">Select Item</option>
                        ${itemOptions}
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm quantity-input" 
                           data-position-id="${position.id}" 
                           value="${currentItem ? currentItem.quantity : 0}" 
                           min="0" step="0.01" max="999999.99">
                </td>
                <td>
                    <input type="date" class="form-control form-control-sm expiry-input" 
                           data-position-id="${position.id}" 
                           value="${currentItem && currentItem.expiry_date ? currentItem.expiry_date : '{{ date('Y-m-d', strtotime('+18 months')) }}'}">
                </td>
                <td>
                    <span class="text-muted">
                        ${currentItem && currentItem.updated_by_name ? currentItem.updated_by_name : '{{ auth()->user()->name }}'}
                    </span>
                </td>
                <td>
                    ${currentItem ? 
                        `<span class="text-muted" title="${currentItem.updated_at}">${currentItem.updated_at_human}</span>` : 
                        '<span class="text-muted">Never</span>'
                    }
                </td>
                <td>
                    <form method="POST" action="{{ route('manufacturing.warehouses.bulk-update', $warehouse) }}" 
                          style="display: inline;" 
                          onsubmit="return confirm('Are you sure you want to clear this position?')">
                        @csrf
                        <input type="hidden" name="action" value="clear">
                        <input type="hidden" name="position_ids[]" value="${position.id}">
                        <button type="submit" class="btn btn-sm btn-outline-warning" 
                                title="Clear position data">
                            <i class="far fa-broom"></i>
                        </button>
                    </form>
                </td>
            </tr>
        `;
    }

    // Removed toggleAisleView function - no longer needed

    function updateAisleNavigation() {
        $('.aisle-nav-btn').each(function() {
            const aisle = $(this).data('aisle');
            const aisleSection = $(`#aisle-section-${aisle}`);
            
            // Only show active if the aisle is loaded AND currently visible
            if (loadedAisles.has(aisle) && aisleSection.is(':visible')) {
                $(this).addClass('active');
            } else {
                $(this).removeClass('active');
            }
        });
    }

    function updateAisleFilter() {
        const filter = $('#aisle-filter');
        filter.empty().append('<option value="">All Loaded Aisles</option>');
        
        loadedAisles.forEach(aisle => {
            const count = $(`#aisle-${aisle}-positions tr`).length;
            filter.append(`<option value="${aisle}">Aisle ${aisle} (${count} positions)</option>`);
        });
    }

    function initializeTomSelect() {
        // Initialize Tom Select for existing item selects
        $('.item-select.tom-select').each(function() {
            if (!$(this).hasClass('tomselected') && $(this).is(':visible')) {
                try {
                    new TomSelect(this, {
                        placeholder: 'Select Item',
                        allowEmptyOption: true,
                        create: false,
                        sortField: {
                            field: 'text',
                            direction: 'asc'
                        }
                    });
                } catch (error) {
                    // Remove tom-select class to prevent retry
                    $(this).removeClass('tom-select');
                }
            }
        });

        // Initialize TomSelect for search location dropdown
        if ($('#search-location').length && !$('#search-location').hasClass('tomselected')) {
            try {
                new TomSelect('#search-location', {
                placeholder: 'Search by shelf...',
                allowEmptyOption: true,
                create: false,
                sortField: {
                    field: 'text',
                    direction: 'asc'
                }
            });
            } catch (error) {
                // Silently handle error
            }
        }
        
        // Initialize TomSelect for bulk item dropdown
        if ($("#bulk-item").length && !$("#bulk-item").hasClass("tomselected")) {
            try {
                new TomSelect("#bulk-item", {
                    placeholder: "Select Item",
                    allowEmptyOption: true,
                    create: false,
                    sortField: {
                        field: "text",
                        direction: "asc"
                    }
                });
            } catch (error) {
                // Silently handle error
            }
        }
    }

    function updateBulkOperationsVisibility() {
        if (selectedPositions.size > 0) {
            $('#bulk-operations').show();
        } else {
            $('#bulk-operations').hide();
        }
    }

    function updateBulkActionFields(action) {
        $('#bulk-item-field, #bulk-quantity-field, #bulk-expiry-field').hide();
        
        if (action === 'assign') {
            $('#bulk-item-field, #bulk-quantity-field, #bulk-expiry-field').show();
        } else if (action === 'update') {
            $('#bulk-quantity-field, #bulk-expiry-field').show();
        }
    }

    // Individual position update function removed - using bulk operations only

    // Clear function removed - now using form submissions

    // Bulk action function removed - now using form submissions

    function applyFilters() {
        const status = $('#status-filter').val();
        let search = '';
        
        // Get search value from dropdown (TomSelect)
        const searchSelect = document.getElementById('search-location');
        if (searchSelect && searchSelect.tomselect) {
            search = searchSelect.tomselect.getValue();
        } else {
            search = $('#search-location').val();
        }
        

        $('.aisle-section').each(function() {
            // First, show all rows to start fresh
            $(this).find('.shelf-header-row').show();
            $(this).find('.position-row').show();
            
            let showSection = true;

            // Apply both search and status filters together
            $(this).find('.position-row').each(function() {
                let shouldShow = true;
                const location = $(this).find('.badge').text();
                const shelfCode = location.substring(0, location.lastIndexOf('-'));
                
                // Apply search filter (by shelf)
                if (search) {
                    if (shelfCode !== search) {
                        shouldShow = false;
                    }
                }
                
                // Apply status filter (only if search filter passed)
                if (shouldShow && status) {
                    const itemValue = $(this).find('.item-select').val();
                    
                    if (status === 'empty') {
                        shouldShow = itemValue === '';
                    } else if (status === 'occupied') {
                        shouldShow = itemValue !== '';
                    } else if (status === 'expiring') {
                        // Check for items expiring within 30 days
                        const expiryDate = $(this).find('.expiry-input').val();
                        if (expiryDate) {
                            const expiry = new Date(expiryDate);
                            const thirtyDaysFromNow = new Date();
                            thirtyDaysFromNow.setDate(thirtyDaysFromNow.getDate() + 30);
                            shouldShow = expiry <= thirtyDaysFromNow;
                        } else {
                            shouldShow = false;
                        }
                    }
                    
                }
                
                if (shouldShow) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            
            // Hide/show shelf headers based on whether they have visible positions
            $(this).find('.shelf-header-row').each(function() {
                const shelfHeaderText = $(this).text().trim();
                const shelfCode = shelfHeaderText.match(/Shelf (A-\d+)/);
                if (shelfCode) {
                    const headerShelfCode = shelfCode[1];
                    const hasVisiblePositions = $(this).nextUntil('.shelf-header-row').filter('.position-row:visible').length > 0;
                    
                    
                    if (hasVisiblePositions) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                }
            });
            
            // Check if this aisle section has any visible positions
            const visiblePositions = $(this).find('.position-row:visible');
            const hasVisiblePositions = visiblePositions.length > 0;
            
            
            if (!hasVisiblePositions) {
                showSection = false;
            }

            if (showSection) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    function clearFilters() {
        $('#status-filter').val('');
        
        // Clear TomSelect dropdown
        const searchSelect = document.getElementById('search-location');
        if (searchSelect && searchSelect.tomselect) {
            searchSelect.tomselect.clear();
        } else {
            $('#search-location').val('');
        }
        
        // Show all sections and rows
        $('.aisle-section').show();
        $('.shelf-header-row').show();
        $('.position-row').show();
        
    }

    function updateTomSelectDropdown(element, value = '') {
        if (element && element.tomselect) {
            if (value === '') {
                element.tomselect.clear();
            } else {
                element.tomselect.setValue(value);
            }
            element.tomselect.refreshOptions(false);
        }
    }

    function saveAllChanges() {
        // This would implement saving all pending changes
        showToast('info', 'Save all functionality will be implemented in the next phase');
    }

    // Loading functions removed - using form submissions instead of AJAX

    function showToast(type, message) {
        // Simple alert implementation without Bootstrap dependency
        const alertClass = type === 'error' ? 'alert-danger' : 
                          type === 'success' ? 'alert-success' : 
                          type === 'warning' ? 'alert-warning' : 'alert-info';
        
        const alert = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                ${message}
                <button type="button" class="btn-close" onclick="$(this).parent().fadeOut()"></button>
            </div>
        `);
        
        $('body').append(alert);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            alert.fadeOut(() => alert.remove());
        }, 5000);
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.base.min.js"></script>
@endpush
