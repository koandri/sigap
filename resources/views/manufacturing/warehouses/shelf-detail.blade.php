@extends('layouts.app')

@section('title', 'Shelf Detail: ' . $shelf->shelf_code)

@push('css')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
<style>
.position-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.position-card {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    background: #fff;
    transition: all 0.2s;
}

.position-card.occupied {
    border-color: #28a745;
    background: #d4edda;
}

.position-card.empty {
    border-color: #e9ecef;
    background: #f8f9fa;
}

.position-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e9ecef;
}

.position-code {
    font-weight: bold;
    font-size: 18px;
    color: #0d6efd;
}

.position-name {
    color: #6c757d;
    font-size: 14px;
}

.item-details {
    margin-bottom: 15px;
}

.item-name {
    font-weight: bold;
    font-size: 16px;
    margin-bottom: 5px;
}

.item-quantity {
    color: #28a745;
    font-weight: bold;
    margin-bottom: 5px;
}

.item-expiry {
    color: #dc3545;
    font-size: 14px;
    margin-bottom: 5px;
}

.item-notes {
    color: #6c757d;
    font-size: 12px;
    font-style: italic;
}

.empty-position {
    text-align: center;
    padding: 20px;
}

.add-item-form {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-top: 15px;
}

/* Fix Tom Select styling */
.ts-control {
    min-height: calc(1.5em + 0.75rem + 2px) !important;
    padding: 0.375rem 0.75rem !important;
    font-size: 0.875rem !important;
    line-height: 1.5 !important;
    border: 1px solid #dadce0 !important;
    border-radius: 4px !important;
    background-color: #fff !important;
}

.ts-dropdown {
    background-color: #ffffff !important;
    border: 1px solid #dadce0 !important;
    border-radius: 4px !important;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
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
                        <li class="breadcrumb-item active">{{ $shelf->shelf_code }}</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    Shelf Detail
                    <span class="badge bg-blue text-white ms-2">{{ $shelf->shelf_code }}</span>
                </h2>
                <div class="page-subtitle">
                    Manage items in all positions of this shelf
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('manufacturing.warehouses.shelf-inventory', $warehouse) }}" class="btn">
                        <i class="far fa-arrow-left me-2"></i>&nbsp;
                        Back to Shelf Inventory
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')
        
        <!-- Section Statistics -->
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Positions</div>
                        </div>
                        <div class="h1 mb-3">{{ $shelf->shelfPositions->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Occupied Positions</div>
                        </div>
                        <div class="h1 mb-3">{{ $shelf->occupied_positions }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Available Positions</div>
                        </div>
                        <div class="h1 mb-3">{{ $shelf->available_positions }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Occupancy Rate</div>
                        </div>
                        <div class="h1 mb-3">{{ $shelf->occupancy_rate }}%</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Positions Grid -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Shelf Positions</h3>
                <div class="card-actions">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        <i class="far fa-plus me-2"></i>&nbsp;
                        Add Item to Position
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="position-grid">
                    @foreach($shelf->shelfPositions as $position)
                        <div class="position-card {{ $position->is_occupied ? 'occupied' : 'empty' }}">
                            <div class="position-header">
                                <div>
                                    <div class="position-code">{{ $position->full_location_code }}</div>
                                    <div class="position-name">{{ $position->position_name }}</div>
                                </div>
                                <div>
                                    @if($position->is_occupied)
                                        <span class="badge bg-success text-white">Occupied</span>
                                    @else
                                        <span class="badge bg-light text-dark">Empty</span>
                                    @endif
                                </div>
                            </div>
                            
                            @if($position->is_occupied)
                                @php $item = $position->current_item; @endphp
                                <div class="item-details">
                                    <div class="item-name">{{ $item->item->name }}</div>
                                    <div class="item-quantity">Quantity: {{ number_format($item->quantity, 2) }} {{ $item->item->unit }}</div>
                                    @if($item->expiry_date)
                                        <div class="item-expiry">
                                            Expiry: {{ $item->expiry_date->format('M d, Y') }}
                                            @if($item->is_expiring_soon)
                                                <span class="badge bg-warning ms-2">Expiring Soon</span>
                                            @endif
                                        </div>
                                    @endif
                                    @if($item->notes)
                                        <div class="item-notes">{{ $item->notes }}</div>
                                    @endif
                                    <div class="text-muted small">
                                        Last updated: {{ $item->last_updated_at->diffForHumans() }}
                                        by {{ $item->lastUpdatedBy->name }}
                                    </div>
                                </div>
                                
                                <div class="item-actions">
                                    <button class="btn btn-sm btn-outline-primary edit-item-btn" 
                                            data-item-id="{{ $item->id }}"
                                            data-quantity="{{ $item->quantity }}"
                                            data-expiry="{{ $item->expiry_date ? $item->expiry_date->format('Y-m-d') : '' }}"
                                            data-notes="{{ $item->notes }}">
                                        <i class="far fa-edit me-1"></i>&nbsp;
                                        Edit
                                    </button>
                                    <button class="btn btn-sm btn-outline-info move-item-btn" 
                                            data-item-id="{{ $item->id }}"
                                            data-position-id="{{ $position->id }}"
                                            data-quantity="{{ $item->quantity }}">
                                        <i class="far fa-arrows-up-down-left-right me-1"></i>&nbsp;
                                        Move
                                    </button>
                                    <form action="{{ route('manufacturing.warehouses.position-item.remove', [$warehouse, $item]) }}" 
                                          method="POST" class="d-inline" 
                                          onsubmit="return confirm('Remove this item from position?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="far fa-trash me-1"></i>&nbsp;
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="empty-position">
                                    <i class="far fa-square text-muted" style="font-size: 48px;"></i>&nbsp;
                                    <div class="mt-2">
                                        <button class="btn btn-outline-success add-item-to-position-btn" 
                                                data-position-id="{{ $position->id }}">
                                            <i class="far fa-plus me-2"></i>&nbsp;
                                            Add Item
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Item to Position</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addItemForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Position</label>
                        <select class="form-select" name="position_id" id="positionSelect" required>
                            <option value="">Select Position</option>
                            @foreach($shelf->shelfPositions as $position)
                                @if(!$position->is_occupied)
                                    <option value="{{ $position->id }}" 
                                            data-position-code="{{ $position->position_code }}"
                                            data-position-name="{{ $position->position_name }}"
                                            data-max-capacity="{{ $position->max_capacity }}">
                                        {{ $position->full_location_code }} - {{ $position->position_name }}
                                        @if($position->max_capacity > 1)
                                            (Max: {{ $position->max_capacity }})
                                        @endif
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <div class="form-text">Select an empty position to add the item</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Item</label>
                        <select class="form-select" name="item_id" id="itemSelect" required>
                            <option value="">Select Item</option>
                            @foreach($availableItems as $item)
                                <option value="{{ $item->id }}" 
                                        data-unit="{{ $item->unit }}"
                                        data-category="{{ $item->itemCategory ? $item->itemCategory->name : '' }}"
                                        data-item-name="{{ $item->name }}">
                                    {{ $item->name }} 
                                    @if($item->itemCategory)
                                        ({{ $item->itemCategory->name }})
                                    @endif
                                    @if($item->unit)
                                        - {{ $item->unit }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Choose the item to add to the selected position</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Quantity</label>
                        <div class="input-group">
                            <input type="number" step="0.001" min="0.001" max="999999.999"
                                   class="form-control" name="quantity" id="quantityInput" required>
                            <span class="input-group-text" id="quantityUnit">unit</span>
                        </div>
                        <div class="form-text">Enter the quantity to add (minimum 0.001)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" class="form-control" name="expiry_date" id="expiryDateInput" 
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                        <div class="form-text">Optional: Set expiry date for perishable items</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="far fa-plus me-2"></i>&nbsp;
                        Add Item to Position
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editItemForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Quantity</label>
                        <input type="number" step="0.001" min="0" 
                               class="form-control" name="quantity" id="editQuantity" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" class="form-control" name="expiry_date" id="editExpiryDate">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Move Item Modal -->
<div class="modal fade" id="moveItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Move Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="moveItemForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Target Position</label>
                        <select class="form-select" name="target_position_id" id="targetPositionSelect" required>
                            <option value="">Select Target Position</option>
                        </select>
                        <div class="form-text">Choose the destination position for this item</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Quantity to Move</label>
                        <div class="input-group">
                            <input type="number" step="0.001" min="0.001" max="999999.999"
                                   class="form-control" name="quantity" id="moveQuantity" required>
                            <span class="input-group-text" id="moveQuantityUnit">unit</span>
                        </div>
                        <div class="form-text">Enter the quantity to move (minimum 0.001)</div>
                    </div>
                    <div class="alert alert-info">
                        <i class="far fa-info-circle me-2"></i>&nbsp;
                        <strong>Note:</strong> The remaining quantity will stay in the current position.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="far fa-arrows-up-down-left-right me-2"></i>&nbsp;
                        Move Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.base.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemTomSelect;
    let positionTomSelect;
    let targetPositionTomSelect;
    
    // Initialize Tom Select for item dropdown
    itemTomSelect = new TomSelect('#itemSelect', {
        placeholder: 'Select Item',
        searchField: ['text'],
        maxOptions: 100,
        render: {
            option: function(data, escape) {
                // Get the original option element to access data attributes
                const option = document.querySelector(`#itemSelect option[value="${data.value}"]`);
                const itemName = option ? option.dataset.itemName : data.text;
                return `<div class="fw-medium">${escape(itemName)}</div>`;
            },
            item: function(data, escape) {
                // Get the original option element to access data attributes
                const option = document.querySelector(`#itemSelect option[value="${data.value}"]`);
                const itemName = option ? option.dataset.itemName : data.text;
                return `<div>${escape(itemName)}</div>`;
            }
        }
    });
    
    // Initialize Tom Select for position dropdown
    positionTomSelect = new TomSelect('#positionSelect', {
        placeholder: 'Select Position',
        searchField: ['text'],
        maxOptions: 50,
        render: {
            option: function(data, escape) {
                const maxCapacity = data.dataset && data.dataset.maxCapacity ? data.dataset.maxCapacity : '';
                return `<div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-medium">${escape(data.text)}</div>
                        ${maxCapacity && maxCapacity > 1 ? `<div class="text-muted small">Max Capacity: ${maxCapacity}</div>` : ''}
                    </div>
                </div>`;
            },
            item: function(data, escape) {
                return `<div>${escape(data.text)}</div>`;
            }
        }
    });
    
    // Initialize Tom Select for target position dropdown
    targetPositionTomSelect = new TomSelect('#targetPositionSelect', {
        placeholder: 'Select Target Position',
        searchField: ['text'],
        maxOptions: 50,
        render: {
            option: function(data, escape) {
                return `<div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-medium">${escape(data.text)}</div>
                    </div>
                </div>`;
            },
            item: function(data, escape) {
                return `<div>${escape(data.text)}</div>`;
            }
        }
    });
    
    // Update quantity unit when item is selected
    itemTomSelect.on('change', function(value) {
        const selectedOption = document.querySelector(`#itemSelect option[value="${value}"]`);
        if (selectedOption) {
            const unit = selectedOption.dataset.unit || 'unit';
            document.getElementById('quantityUnit').textContent = unit;
        }
    });
    
    // Add item to specific position
    document.querySelectorAll('.add-item-to-position-btn').forEach(button => {
        button.addEventListener('click', function() {
            const positionId = this.dataset.positionId;
            
            // Set the position value directly in the select element
            document.getElementById('positionSelect').value = positionId;
            document.getElementById('positionSelect').disabled = true;
            
            // Update TomSelect if it exists
            if (positionTomSelect) {
                positionTomSelect.setValue(positionId);
                positionTomSelect.disable();
            }
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('addItemModal'));
            modal.show();
        });
    });
    
    // Edit item
    document.querySelectorAll('.edit-item-btn').forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const quantity = this.dataset.quantity;
            const expiry = this.dataset.expiry;
            
            document.getElementById('editItemForm').action = `{{ route('manufacturing.warehouses.position-item.update', [$warehouse, 'ITEM_ID']) }}`.replace('ITEM_ID', itemId);
            document.getElementById('editQuantity').value = quantity;
            document.getElementById('editExpiryDate').value = expiry;
            
            new bootstrap.Modal(document.getElementById('editItemModal')).show();
        });
    });
    
    // Move item
    document.querySelectorAll('.move-item-btn').forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const positionId = this.dataset.positionId;
            const quantity = this.dataset.quantity;
            
            // Set the form action
            document.getElementById('moveItemForm').action = `{{ route('manufacturing.warehouses.position-item.move', [$warehouse, 'ITEM_ID']) }}`.replace('ITEM_ID', itemId);
            
            // Set max quantity and default value
            document.getElementById('moveQuantity').max = quantity;
            document.getElementById('moveQuantity').value = quantity;
            
            // Load available positions
            loadAvailablePositions(itemId, positionId);
            
            new bootstrap.Modal(document.getElementById('moveItemModal')).show();
        });
    });
    
    // Function to load available positions
    function loadAvailablePositions(itemId, currentPositionId) {
        // Clear and disable TomSelect while loading
        targetPositionTomSelect.clear();
        targetPositionTomSelect.disable();
        
        fetch(`{{ route('manufacturing.warehouses.position-item.available-positions', [$warehouse, 'ITEM_ID']) }}`.replace('ITEM_ID', itemId))
            .then(response => response.json())
            .then(positions => {
                // Clear existing options
                targetPositionTomSelect.clearOptions();
                
                // Add default option
                targetPositionTomSelect.addOption({
                    value: '',
                    text: 'Select Target Position'
                });
                
                // Add available positions
                positions.forEach(position => {
                    // Skip the current position
                    if (position.id != currentPositionId) {
                        targetPositionTomSelect.addOption({
                            value: position.id,
                            text: `${position.code} - ${position.name}`
                        });
                    }
                });
                
                // Re-enable TomSelect
                targetPositionTomSelect.enable();
            })
            .catch(error => {
                console.error('Error loading positions:', error);
                targetPositionTomSelect.clearOptions();
                targetPositionTomSelect.addOption({
                    value: '',
                    text: 'Error loading positions'
                });
                targetPositionTomSelect.enable();
            });
    }
    
    // Handle form submission
    document.getElementById('addItemForm').addEventListener('submit', function(e) {
        const positionId = positionTomSelect.getValue();
        const itemId = itemTomSelect.getValue();
        const quantity = document.getElementById('quantityInput').value;
        
        if (!positionId || !itemId || !quantity) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }
        
        // Set the form action dynamically based on selected position
        const selectedPosition = document.querySelector(`#positionSelect option[value="${positionId}"]`);
        if (selectedPosition) {
            this.action = `{{ route('manufacturing.warehouses.position.add-item', [$warehouse, 'POSITION_ID']) }}`.replace('POSITION_ID', positionId);
        }
    });
    
    // Reset form when modal is hidden
    document.getElementById('addItemModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('addItemForm').reset();
        positionTomSelect.clear();
        itemTomSelect.clear();
        positionTomSelect.enable();
        document.getElementById('quantityUnit').textContent = 'unit';
    });
    
    // Reset move modal when hidden
    document.getElementById('moveItemModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('moveItemForm').reset();
        targetPositionTomSelect.clear();
        targetPositionTomSelect.clearOptions();
        targetPositionTomSelect.addOption({
            value: '',
            text: 'Select Target Position'
        });
    });
    
    // Set default expiry date to 18 months from now
    const defaultExpiryDate = new Date();
    defaultExpiryDate.setMonth(defaultExpiryDate.getMonth() + 18);
    document.getElementById('expiryDateInput').value = defaultExpiryDate.toISOString().split('T')[0];
});
</script>
@endpush
