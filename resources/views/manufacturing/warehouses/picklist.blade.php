@extends('layouts.app')

@section('title', 'Generate Global Picklist')

@push('css')
<style>
.picklist-form {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.picklist-form h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.picklist-form .subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    font-weight: 300;
}

.item-row {
    background: white;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-left: 4px solid #0d6efd;
    transition: all 0.3s ease;
}

.item-row:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.item-row.removing {
    opacity: 0.5;
    transform: translateX(-20px);
    transition: all 0.3s ease;
}

.item-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.item-details {
    flex: 1;
}

.item-name {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.25rem;
}

.item-category {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.item-available {
    font-size: 0.8rem;
    color: #28a745;
    font-weight: 500;
}

.quantity-input {
    max-width: 120px;
}

.add-item-btn {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.add-item-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
    color: white;
}

.remove-item-btn {
    background: #dc3545;
    border: none;
    border-radius: 6px;
    color: white;
    padding: 0.5rem;
    transition: all 0.3s ease;
}

.remove-item-btn:hover {
    background: #c82333;
    transform: scale(1.05);
}

.generate-btn {
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    border: none;
    border-radius: 12px;
    padding: 1rem 2rem;
    color: white;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
}

.generate-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
    color: white;
}

.item-selector {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 1rem;
}

.available-items {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 0.5rem;
}

.available-item {
    padding: 0.75rem;
    border-radius: 6px;
    margin-bottom: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.available-item:hover {
    background: #f8f9fa;
    border-color: #0d6efd;
}

.available-item.selected {
    background: #e3f2fd;
    border-color: #0d6efd;
}

.summary-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid #28a745;
}

.summary-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.stat-item {
    text-align: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0d6efd;
}

.stat-label {
    font-size: 0.8rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

@media (max-width: 768px) {
    .picklist-form h1 {
        font-size: 2rem;
    }
    
    .item-info {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .quantity-input {
        max-width: 100%;
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
                        <li class="breadcrumb-item active">Generate Picklist</li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('manufacturing.warehouses.index') }}" class="btn btn-outline-secondary">
                        <i class="fa-regular fa-arrow-left me-2"></i>
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
        
        <!-- Header -->
        <div class="picklist-form">
            <div class="row align-items-center">
                <div class="col">
                    <h1>
                        <i class="fa-regular fa-list-check me-3"></i>
                        Generate Global Picklist
                    </h1>
                    <div class="subtitle">
                        <strong>All Warehouses</strong> • FIFO-based item picking by expiry dates across all locations
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

        <form method="POST" action="{{ route('manufacturing.warehouses.picklist.generate') }}" id="picklistForm">
            @csrf
            
            <!-- Item Selector -->
            <div class="item-selector">
                <h3 class="mb-3">
                    <i class="fa-regular fa-plus-circle me-2"></i>
                    Add Items to Picklist
                </h3>
                
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Search Items</label>
                        <input type="text" class="form-control" id="itemSearch" placeholder="Search by item name or category...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Filter by Category</label>
                        <select class="form-select" id="categoryFilter">
                            <option value="">All Categories</option>
                            @foreach($availableItems->pluck('itemCategory.name')->unique()->sort() as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="available-items mt-3" id="availableItems">
                    @foreach($availableItems as $item)
                        @php
                            $totalAvailable = $item->positionItems->where('quantity', '>', 0)->sum('quantity');
                            $warehouses = $item->positionItems->where('quantity', '>', 0)
                                ->pluck('shelfPosition.warehouseShelf.warehouse.name')
                                ->unique()
                                ->implode(', ');
                        @endphp
                        <div class="available-item" 
                             data-item-id="{{ $item->id }}" 
                             data-item-name="{{ $item->name }}"
                             data-category="{{ $item->itemCategory->name ?? 'No Category' }}"
                             data-available="{{ $totalAvailable }}"
                             data-unit="{{ $item->unit }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">{{ $item->name }}</div>
                                    <small class="text-muted">{{ $item->itemCategory->name ?? 'No Category' }}</small>
                                    <br><small class="text-info">Available in: {{ $warehouses }}</small>
                                </div>
                                <div class="text-end">
                                    <div class="text-success fw-bold">{{ number_format($totalAvailable, 2) }} {{ $item->unit }}</div>
                                    <small class="text-muted">Total Available</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Selected Items -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-regular fa-list me-2"></i>
                        Selected Items for Picklist
                    </h3>
                </div>
                <div class="card-body">
                    <div id="selectedItems">
                        <div class="text-center text-muted py-4" id="emptyState">
                            <i class="fa-regular fa-inbox fa-3x mb-3 opacity-50"></i>
                            <p>No items selected yet. Click on items above to add them to your picklist.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary and Generate -->
            <div class="row mt-4">
                <div class="col-md-8">
                    <div class="summary-card">
                        <h4 class="mb-3">
                            <i class="fa-regular fa-chart-pie me-2"></i>
                            Picklist Summary
                        </h4>
                        <div class="summary-stats" id="summaryStats">
                            <div class="stat-item">
                                <div class="stat-number" id="totalItems">0</div>
                                <div class="stat-label">Items</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number" id="totalQuantity">0</div>
                                <div class="stat-label">Total Quantity</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn generate-btn w-100" id="generateBtn" disabled>
                        <i class="fa-regular fa-magic-wand me-2"></i>
                        Generate Picklist
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const availableItems = document.getElementById('availableItems');
    const selectedItems = document.getElementById('selectedItems');
    const emptyState = document.getElementById('emptyState');
    const itemSearch = document.getElementById('itemSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const generateBtn = document.getElementById('generateBtn');
    const form = document.getElementById('picklistForm');
    
    let selectedItemsList = [];
    
    // Search functionality
    itemSearch.addEventListener('input', function() {
        filterItems();
    });
    
    categoryFilter.addEventListener('change', function() {
        filterItems();
    });
    
    function filterItems() {
        const searchTerm = itemSearch.value.toLowerCase();
        const selectedCategory = categoryFilter.value;
        
        Array.from(availableItems.children).forEach(item => {
            const itemName = item.dataset.itemName.toLowerCase();
            const category = item.dataset.category;
            
            const matchesSearch = itemName.includes(searchTerm);
            const matchesCategory = !selectedCategory || category === selectedCategory;
            
            if (matchesSearch && matchesCategory) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }
    
    // Item selection
    availableItems.addEventListener('click', function(e) {
        const item = e.target.closest('.available-item');
        if (!item) return;
        
        const itemId = parseInt(item.dataset.itemId);
        const itemName = item.dataset.itemName;
        const category = item.dataset.category;
        const available = parseFloat(item.dataset.available);
        const unit = item.dataset.unit;
        
        // Check if already selected
        if (selectedItemsList.find(selected => selected.item_id === itemId)) {
            return;
        }
        
        // Add to selected items
        selectedItemsList.push({
            item_id: itemId,
            name: itemName,
            category: category,
            available: available,
            unit: unit,
            quantity: 1
        });
        
        // Update UI
        updateSelectedItems();
        updateSummary();
        item.classList.add('selected');
    });
    
    function updateSelectedItems() {
        if (selectedItemsList.length === 0) {
            emptyState.style.display = 'block';
            return;
        }
        
        emptyState.style.display = 'none';
        
        const html = selectedItemsList.map((item, index) => `
            <div class="item-row" data-index="${index}">
                <div class="item-info">
                    <div class="item-details">
                        <div class="item-name">${item.name}</div>
                        <div class="item-category">${item.category}</div>
                        <div class="item-available">Available: ${item.available} ${item.unit}</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <input type="number" 
                               class="form-control quantity-input" 
                               name="items[${index}][quantity]" 
                               value="${item.quantity}" 
                               min="0.001" 
                               max="${item.available}" 
                               step="0.001"
                               data-index="${index}">
                        <input type="hidden" name="items[${index}][item_id]" value="${item.item_id}">
                        <button type="button" class="btn remove-item-btn" data-index="${index}">
                            <i class="fa-regular fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
        
        selectedItems.innerHTML = html;
        
        // Add event listeners
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('input', function() {
                const index = parseInt(this.dataset.index);
                selectedItemsList[index].quantity = parseFloat(this.value);
                updateSummary();
            });
        });
        
        document.querySelectorAll('.remove-item-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                removeItem(index);
            });
        });
    }
    
    function removeItem(index) {
        const itemId = selectedItemsList[index].item_id;
        
        // Remove from selected items
        selectedItemsList.splice(index, 1);
        
        // Update UI
        updateSelectedItems();
        updateSummary();
        
        // Remove selection from available items
        const availableItem = document.querySelector(`[data-item-id="${itemId}"]`);
        if (availableItem) {
            availableItem.classList.remove('selected');
        }
    }
    
    function updateSummary() {
        const totalItems = selectedItemsList.length;
        const totalQuantity = selectedItemsList.reduce((sum, item) => sum + (item.quantity || 0), 0);
        
        document.getElementById('totalItems').textContent = totalItems;
        document.getElementById('totalQuantity').textContent = totalQuantity.toFixed(2);
        
        generateBtn.disabled = totalItems === 0;
    }
    
    // Form submission
    form.addEventListener('submit', function(e) {
        if (selectedItemsList.length === 0) {
            e.preventDefault();
            alert('Please select at least one item for the picklist.');
            return;
        }
        
        // Validate quantities
        let hasErrors = false;
        selectedItemsList.forEach((item, index) => {
            if (item.quantity <= 0) {
                hasErrors = true;
            }
            if (item.quantity > item.available) {
                hasErrors = true;
                alert(`Quantity for "${item.name}" cannot exceed available amount (${item.available} ${item.unit}).`);
            }
        });
        
        if (hasErrors) {
            e.preventDefault();
            return;
        }
    });
});
</script>
@endpush
