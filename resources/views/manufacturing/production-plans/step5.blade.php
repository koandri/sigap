@extends('layouts.app')

@section('title', 'Step 5: Packing Materials Planning')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/tabler/libs/tom-select/dist/css/tom-select.bootstrap5.min.css') }}" />
@endpush

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.dashboard') }}">Manufacturing</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.production-plans.index') }}">Production Plans</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.production-plans.show', $productionPlan) }}">Plan Details</a></li>
                        <li class="breadcrumb-item active">Step 5</li>
                    </ol>
                </nav>
                <h2 class="page-title">Step 5: Packing Materials Planning</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('manufacturing.production-plans.show', $productionPlan) }}" class="btn btn-outline-secondary">
                    <i class="far fa-arrow-left"></i>&nbsp;Back to Plan
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')

        <form method="POST" action="{{ route('manufacturing.production-plans.step5.store', $productionPlan) }}" id="step5Form">
            @csrf

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Step 5: Packing Materials Planning</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="far fa-info-circle me-2"></i>
                        <strong>Important:</strong>
                        <ol class="mb-0 mt-2">
                            <li>The Pack SKU Qty are from Step 4, and <strong>cannot be changed</strong>.</li>
                            <li>User can add/delete Packing Materials for each Pack SKU.</li>
                            <li>User can modify the Packing Materials Qty using textboxes.</li>
                        </ol>
                    </div>

                    @error('materials')
                        <div class="alert alert-danger">
                            <i class="far fa-triangle-exclamation me-2"></i>{{ $message }}
                        </div>
                    @enderror

                    @foreach($packSkus as $packSkuIndex => $packSku)
                        <div class="mb-5 pack-sku-section" data-pack-sku-id="{{ $packSku['pack_sku_id'] }}">
                            <div class="mb-3">
                                <h4 class="mb-2">
                                    <strong>Pack SKU {{ $packSkuIndex + 1 }}:</strong>
                                    <span class="text-primary">{{ $packSku['pack_sku_name'] }}</span>
                                    <span class="badge bg-secondary ms-2 text-white">Total Qty: {{ number_format($packSku['total_qty'], 0) }} Pack</span>
                                </h4>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-vcenter table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th style="min-width: 250px;">Packing Material</th>
                                            <th style="width: 150px;" class="text-end">Per Pack</th>
                                            <th style="width: 200px;" class="text-end">Total Qty Pack</th>
                                            <th style="width: 200px;" class="text-end">Total Qty</th>
                                            <th style="width: 100px;" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="materials-tbody" data-pack-sku-id="{{ $packSku['pack_sku_id'] }}">
                                        @php
                                            $materials = $packSku['materials'] ?? collect();
                                            // Blueprints use string keys
                                            $packSkuIdKey = (string) $packSku['pack_sku_id'];
                                            $blueprints = $packingBlueprints[$packSkuIdKey] ?? collect();
                                            
                                            // If no materials exist yet, use blueprints to auto-calculate
                                            if ($materials->isEmpty() && $blueprints->isNotEmpty()) {
                                                $materials = $blueprints->map(function ($blueprint) use ($packSku) {
                                                    return (object) [
                                                        'packing_material_item_id' => $blueprint['packing_material_item_id'],
                                                        'packing_material_item_name' => $blueprint['packing_material_item_name'],
                                                        'quantity_per_pack' => $blueprint['quantity_per_pack'],
                                                        'quantity_total' => (int) round($blueprint['quantity_per_pack'] * $packSku['total_qty']),
                                                        'unit' => $blueprint['unit'] ?? 'pcs',
                                                    ];
                                                });
                                            } else {
                                                $materials = $materials->map(function ($material) use ($packSku, $blueprints) {
                                                    // Get quantity per pack from blueprint
                                                    $blueprint = $blueprints->firstWhere('packing_material_item_id', $material->packing_material_item_id);
                                                    $qtyPerPack = $blueprint ? $blueprint['quantity_per_pack'] : 0;
                                                    
                                                    return (object) [
                                                        'packing_material_item_id' => $material->packing_material_item_id,
                                                        'packing_material_item_name' => $material->packingMaterialItem->name ?? 'N/A',
                                                        'quantity_per_pack' => $qtyPerPack,
                                                        'quantity_total' => $material->quantity_total,
                                                        'unit' => $material->packingMaterialItem->unit ?? 'pcs',
                                                    ];
                                                });
                                            }
                                            
                                            if ($materials->isEmpty()) {
                                                $materials = collect([
                                                    (object) [
                                                        'packing_material_item_id' => null,
                                                        'packing_material_item_name' => null,
                                                        'quantity_per_pack' => 0,
                                                        'quantity_total' => 0,
                                                        'unit' => 'pcs',
                                                    ]
                                                ]);
                                            }
                                        @endphp

                                        @foreach($materials as $materialIndex => $material)
                                            <tr class="material-row" data-material-index="{{ $materialIndex }}">
                                                <td class="align-middle text-center">{{ $materialIndex + 1 }}</td>
                                                <td class="align-middle">
                                                    <select name="materials[{{ $packSkuIndex }}_{{ $materialIndex }}][packing_material_item_id]" 
                                                            class="form-select form-select-sm material-select" 
                                                            data-pack-sku-id="{{ $packSku['pack_sku_id'] }}"
                                                            data-material-index="{{ $materialIndex }}"
                                                            required>
                                                        <option value="">Select Packing Material</option>
                                                        @foreach($packingMaterialItems as $item)
                                                            <option value="{{ $item->id }}"
                                                                data-unit="{{ $item->unit ?? 'pcs' }}"
                                                                {{ $material->packing_material_item_id == $item->id ? 'selected' : '' }}>
                                                                {{ $item->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <input type="hidden" name="materials[{{ $packSkuIndex }}_{{ $materialIndex }}][pack_sku_id]" value="{{ $packSku['pack_sku_id'] }}">
                                                </td>
                                                <td class="align-middle text-end qty-per-pack-display">
                                                    {{ number_format($material->quantity_per_pack, 1) }}
                                                    <span class="unit-display">{{ $material->unit }}</span>
                                                </td>
                                                <td class="align-middle text-end">
                                                    <span class="badge bg-secondary text-white">{{ number_format($packSku['total_qty'], 0) }} Pack</span>
                                                </td>
                                                <td class="align-middle">
                                                    <input type="number" 
                                                        name="materials[{{ $packSkuIndex }}_{{ $materialIndex }}][quantity_total]" 
                                                        class="form-control form-control-sm text-end material-qty-input" 
                                                        value="{{ $material->quantity_total }}" 
                                                        step="1" 
                                                        min="0"
                                                        required>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <button type="button" class="btn btn-sm btn-danger remove-material-btn" onclick="removeMaterial(this)">
                                                        <i class="far fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6" class="text-start">
                                                <button type="button" class="btn btn-sm btn-primary add-material-btn" 
                                                        onclick="addMaterial({{ $packSkuIndex }}, {{ $packSku['pack_sku_id'] }}, {{ $packSku['total_qty'] }})">
                                                    <i class="far fa-plus"></i>&nbsp;Add Packing Material
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('manufacturing.production-plans.show', $productionPlan) }}" class="btn btn-link">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="far fa-save me-1"></i>Save Step 5
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/tabler/libs/tom-select/dist/js/tom-select.complete.min.js') }}"></script>
<script>
const packingBlueprints = @json($packingBlueprints);
const packingMaterialItems = @json($packingMaterialItems->map(fn($item) => [
    'id' => $item->id,
    'name' => $item->name,
    'unit' => $item->unit ?? 'pcs'
]));

let materialCounter = 1000; // Start with high number to avoid conflicts

function initializeAllMaterialSelects() {
    document.querySelectorAll('.material-select').forEach(select => {
        if (!select.tomselect) {
            new TomSelect(select, {
                placeholder: 'Select Packing Material',
                sortField: {
                    field: 'text',
                    direction: 'asc'
                },
                dropdownParent: 'body'
            });
        }
    });
}

function addMaterial(packSkuIndex, packSkuId, totalQty) {
    const tbody = document.querySelector(`.materials-tbody[data-pack-sku-id="${packSkuId}"]`);
    if (!tbody) return;
    
    const rowCount = tbody.querySelectorAll('.material-row').length;
    const newIndex = materialCounter++;
    
    const newRow = document.createElement('tr');
    newRow.className = 'material-row';
    newRow.dataset.materialIndex = newIndex;
    
    newRow.innerHTML = `
        <td class="align-middle text-center">${rowCount + 1}</td>
        <td class="align-middle">
            <select name="materials[${packSkuIndex}_${newIndex}][packing_material_item_id]" 
                    class="form-select form-select-sm material-select" 
                    data-pack-sku-id="${packSkuId}"
                    data-material-index="${newIndex}"
                    required>
                <option value="">Select Packing Material</option>
                ${packingMaterialItems.map(item => 
                    `<option value="${item.id}" data-unit="${item.unit}">${item.name}</option>`
                ).join('')}
            </select>
            <input type="hidden" name="materials[${packSkuIndex}_${newIndex}][pack_sku_id]" value="${packSkuId}">
        </td>
        <td class="align-middle text-end qty-per-pack-display">
            0.0
            <span class="unit-display">pcs</span>
        </td>
        <td class="align-middle text-end">
            <span class="badge bg-secondary text-white">${parseInt(totalQty).toLocaleString()} Pack</span>
        </td>
        <td class="align-middle">
            <input type="number" 
                name="materials[${packSkuIndex}_${newIndex}][quantity_total]" 
                class="form-control form-control-sm text-end material-qty-input" 
                value="0" 
                step="1" 
                min="0"
                required>
        </td>
        <td class="align-middle text-center">
            <button type="button" class="btn btn-sm btn-danger remove-material-btn" onclick="removeMaterial(this)">
                <i class="far fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(newRow);
    
    // Initialize Tom Select and bind events for new select
    const newSelect = newRow.querySelector('.material-select');
    if (newSelect) {
        if (!newSelect.tomselect) {
            new TomSelect(newSelect, {
                placeholder: 'Select Packing Material',
                sortField: {
                    field: 'text',
                    direction: 'asc'
                },
                dropdownParent: 'body'
            });
        }
        newSelect.addEventListener('change', function() {
            updateQtyPerPack(this, totalQty);
        });
    }
    
    // Renumber all rows
    renumberRows(tbody);
}

function removeMaterial(button) {
    const row = button.closest('.material-row');
    if (!row) return;
    
    const tbody = row.closest('.materials-tbody');
    const rows = tbody.querySelectorAll('.material-row');
    
    if (rows.length <= 1) {
        alert('At least one packing material is required per Pack SKU.');
        return;
    }
    
    if (confirm('Are you sure you want to remove this packing material?')) {
        row.remove();
        renumberRows(tbody);
    }
}

function renumberRows(tbody) {
    const rows = tbody.querySelectorAll('.material-row');
    rows.forEach((row, index) => {
        const noCell = row.querySelector('td:first-child');
        if (noCell) {
            noCell.textContent = index + 1;
        }
    });
}

function updateQtyPerPack(selectElement, totalQty) {
    const row = selectElement.closest('.material-row');
    if (!row) return;
    
    const packSkuId = selectElement.dataset.packSkuId;
    const materialId = selectElement.value;
    
    if (!materialId || !packSkuId) {
        return;
    }
    
    // Get blueprint for this material - blueprints use string keys
    const blueprints = packingBlueprints[String(packSkuId)] || [];
    const blueprint = blueprints.find(bp => bp.packing_material_item_id == materialId);
    
    const qtyPerPackDisplay = row.querySelector('.qty-per-pack-display');
    const qtyTotalInput = row.querySelector('.material-qty-input');
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const unit = selectedOption ? selectedOption.dataset.unit : 'pcs';
    
    if (blueprint) {
        const qtyPerPack = parseFloat(blueprint.quantity_per_pack);
        qtyPerPackDisplay.innerHTML = `${qtyPerPack.toFixed(1)} <span class="unit-display">${unit}</span>`;
        
        // Auto-calculate total quantity if totalQty is provided
        if (totalQty && qtyTotalInput && qtyTotalInput.value == 0) {
            const calculatedTotal = Math.round(qtyPerPack * totalQty);
            qtyTotalInput.value = calculatedTotal;
        }
    } else {
        qtyPerPackDisplay.innerHTML = `0.0 <span class="unit-display">${unit}</span>`;
    }
}

// Bind Tom Select + change events on page load and auto-update for pre-selected items
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Tom Select for all existing material selects
    initializeAllMaterialSelects();

    document.querySelectorAll('.material-select').forEach(select => {
        // Get total qty from the badge in the same row
        const row = select.closest('.material-row');
        const totalQtyBadge = row ? row.querySelector('.badge.bg-secondary') : null;
        const totalQty = totalQtyBadge ? parseInt(totalQtyBadge.textContent.replace(/[^\d]/g, '')) : null;
        
        // Update for pre-selected items
        if (select.value) {
            updateQtyPerPack(select, totalQty);
        }
        
        // Bind change event
        select.addEventListener('change', function() {
            updateQtyPerPack(this, totalQty);
        });
    });
});
</script>
@endpush
@endsection

