@extends('layouts.app')

@section('title', 'Step 4: Packing Output Planning')

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
                        <li class="breadcrumb-item active">Step 4</li>
                    </ol>
                </nav>
                <h2 class="page-title">Step 4: Packing Output Planning</h2>
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

        <form method="POST" action="{{ route('manufacturing.production-plans.step4.store', $productionPlan) }}" id="step4Form">
            @csrf

            @php
                $rows = [];
                $rowCounter = 0;
                $defaultRow = [
                    'kerupuk_kering_item_id' => null,
                    'kerupuk_packing_item_id' => null,
                    'qty_gl1_packing' => 0.0,
                    'qty_gl2_packing' => 0.0,
                    'qty_ta_packing' => 0.0,
                    'qty_bl_packing' => 0.0,
                ];

                $oldRows = old('step4');
                if (is_array($oldRows) && count($oldRows) > 0) {
                    foreach ($oldRows as $row) {
                        $rows[] = array_merge($defaultRow, [
                            'index' => $rowCounter++,
                            'kerupuk_kering_item_id' => $row['kerupuk_kering_item_id'] ?? null,
                            'kerupuk_packing_item_id' => $row['kerupuk_packing_item_id'] ?? null,
                            'qty_gl1_packing' => (float) ($row['qty_gl1_packing'] ?? 0),
                            'qty_gl2_packing' => (float) ($row['qty_gl2_packing'] ?? 0),
                            'qty_ta_packing' => (float) ($row['qty_ta_packing'] ?? 0),
                            'qty_bl_packing' => (float) ($row['qty_bl_packing'] ?? 0),
                        ]);
                    }
                } elseif (count($calculatedData) > 0) {
                    foreach ($calculatedData as $row) {
                        $rows[] = array_merge($defaultRow, [
                            'index' => $rowCounter++,
                            'kerupuk_kering_item_id' => $row['kerupuk_kering_item_id'] ?? null,
                            'kerupuk_packing_item_id' => $row['kerupuk_packing_item_id'] ?? null,
                            'qty_gl1_packing' => (float) ($row['qty_gl1_packing'] ?? 0),
                            'qty_gl2_packing' => (float) ($row['qty_gl2_packing'] ?? 0),
                            'qty_ta_packing' => (float) ($row['qty_ta_packing'] ?? 0),
                            'qty_bl_packing' => (float) ($row['qty_bl_packing'] ?? 0),
                        ]);
                    }
                } elseif ($productionPlan->step4->count() > 0) {
                    foreach ($productionPlan->step4 as $step4Row) {
                        $rows[] = [
                            'index' => $rowCounter++,
                            'kerupuk_kering_item_id' => $step4Row->kerupuk_kering_item_id,
                            'kerupuk_packing_item_id' => $step4Row->kerupuk_packing_item_id,
                            'qty_gl1_packing' => (float) $step4Row->qty_gl1_packing,
                            'qty_gl2_packing' => (float) $step4Row->qty_gl2_packing,
                            'qty_ta_packing' => (float) $step4Row->qty_ta_packing,
                            'qty_bl_packing' => (float) $step4Row->qty_bl_packing,
                        ];
                    }
                }

                if (count($rows) === 0) {
                    $rows[] = array_merge($defaultRow, ['index' => 0]);
                }

                $rowCount = count($rows);
                $formatPacks = static fn ($value) => number_format((float) $value, 0, '.', '');
                $formatKg = static fn ($value) => number_format((float) $value, 2, '.', '');
                $formatWeight = static fn ($value) => number_format((float) $value, 2, '.', '');
                
                // Group rows by Kerupuk Kg
                $groupedRows = [];
                foreach ($rows as $row) {
                    $kerupukKgId = $row['kerupuk_kering_item_id'] ?? 'unassigned_' . $row['index'];
                    if (!isset($groupedRows[$kerupukKgId])) {
                        $groupedRows[$kerupukKgId] = [
                            'kerupuk_kg_id' => $kerupukKgId,
                            'kerupuk_kg_name' => null,
                            'rows' => []
                        ];
                    }
                    $groupedRows[$kerupukKgId]['rows'][] = $row;
                    
                    // Get Kerupuk Kg name if available
                    if ($kerupukKgId && $kerupukKgId !== 'unassigned_' . $row['index']) {
                        $kerupukOption = $kerupukKeringOptions->firstWhere('id', $kerupukKgId);
                        if ($kerupukOption) {
                            $groupedRows[$kerupukKgId]['kerupuk_kg_name'] = $kerupukOption['name'];
                        }
                    }
                }
            @endphp

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Step 4: Packing Output Planning</h3>
                    <div class="card-actions">
                        <button type="button" class="btn btn-sm btn-primary" onclick="addStep4Row()">
                            <i class="far fa-plus"></i>&nbsp;Add Pack SKU
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="far fa-info-circle me-2"></i>
                        <strong>Important:</strong>
                        <ol class="mb-0 mt-2">
                            <li>User can add or delete Pack SKUs.</li>
                            <li>User can only change the Pack Qty for each location (GL1, GL2, TA, BL) using textboxes.</li>
                            <li>Changing Pack Qty will automatically re-calculate Kg Qty of the same location, and also changes the total Kg and Pack of the Pack SKU.</li>
                            <li>All the totals are plain texts that will change depending on the inputted Pack Qty.</li>
                        </ol>
                        @if(count($calculatedData) > 0 && !old('step4'))
                            <div class="mt-2 text-success"><i class="far fa-check-circle me-1"></i>Auto-calculated suggestions from Step 3 have been pre-filled below.</div>
                        @endif
                    </div>

                    @error('step4')
                        <div class="alert alert-danger">
                            <i class="far fa-triangle-exclamation me-2"></i>{{ $message }}
                        </div>
                    @enderror

                    <div id="validation-warnings" class="alert alert-danger d-none">
                        <i class="far fa-exclamation-triangle me-2"></i>
                        <strong>Validation Error:</strong>
                        <ul id="validation-warnings-list" class="mb-0 mt-2"></ul>
                    </div>

                    <div id="step4-sections">
                        @foreach($groupedRows as $kerupukKgId => $group)
                            <div class="mb-5 kerupuk-kg-section" data-kerupuk-kg-id="{{ $kerupukKgId }}">
                                <div class="mb-3">
                                    <h4 class="mb-2">
                                        <strong>Kerupuk Kg:</strong>
                                        @if($group['kerupuk_kg_name'])
                                            <span class="text-primary">{{ $group['kerupuk_kg_name'] }}</span>
                                        @else
                                            <span class="text-muted">Unassigned Kerupuk Kg</span>
                                        @endif
                                    </h4>
                                </div>
                                
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered table-vcenter table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th rowspan="2" style="width: 60px;">No</th>
                                                <th rowspan="2" style="min-width: 200px;">Pack SKU</th>
                                                <th rowspan="2" style="width: 120px;">Weight/Unit (Kg)</th>
                                                <th colspan="4" class="text-center">Location</th>
                                                <th rowspan="2" style="width: 120px;">Total</th>
                                                <th rowspan="2" style="width: 80px;">Actions</th>
                                            </tr>
                                            <tr>
                                                <th style="width: 150px;">GL1</th>
                                                <th style="width: 150px;">GL2</th>
                                                <th style="width: 150px;">TA</th>
                                                <th style="width: 150px;">BL</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                @foreach($group['rows'] as $rowNum => $row)
                                    @php
                                        $weight = 1.0;
                                        if (!empty($row['kerupuk_kering_item_id']) && !empty($row['kerupuk_packing_item_id'])) {
                                            $configKey = $row['kerupuk_kering_item_id'] . '_' . $row['kerupuk_packing_item_id'];
                                            $weight = $weightConfigurations[$configKey] ?? 1.0;
                                        }
                                        
                                        $gl1Pack = (float) ($row['qty_gl1_packing'] ?? 0);
                                        $gl2Pack = (float) ($row['qty_gl2_packing'] ?? 0);
                                        $taPack = (float) ($row['qty_ta_packing'] ?? 0);
                                        $blPack = (float) ($row['qty_bl_packing'] ?? 0);
                                        $gl1Kg = $gl1Pack * $weight;
                                        $gl2Kg = $gl2Pack * $weight;
                                        $taKg = $taPack * $weight;
                                        $blKg = $blPack * $weight;
                                        $totalKg = $gl1Kg + $gl2Kg + $taKg + $blKg;
                                        $totalPacks = $gl1Pack + $gl2Pack + $taPack + $blPack;
                                        $packSkuId = $row['kerupuk_packing_item_id'] ?? null;
                                        
                                        // Get configured Pack SKUs for this Kerupuk Kg
                                        $allowedPackIds = $packConfigurations[$kerupukKgId] ?? [];
                                    @endphp
                                    
                                    <tr class="kg-row pack-sku-row" data-kerupuk-kg-id="{{ $kerupukKgId }}" data-pack-sku-id="{{ $packSkuId }}" data-row-index="{{ $row['index'] }}">
                                        <td rowspan="2" class="align-middle text-center"><strong>{{ $rowNum + 1 }}</strong></td>
                                        <td rowspan="2" class="align-middle">
                                            <select name="step4[{{ $row['index'] }}][kerupuk_packing_item_id]" 
                                                    class="form-select form-select-sm pack-sku-select" 
                                                    data-row-index="{{ $row['index'] }}" 
                                                    required>
                                                <option value="">Select Pack SKU</option>
                                                @if(count($allowedPackIds) > 0)
                                                    @foreach($allPackingItems ?? $packingItems as $item)
                                                        @if(in_array($item->id, $allowedPackIds))
                                                            <option value="{{ $item->id }}"
                                                                data-default-weight="{{ number_format($item->qty_kg_per_pack > 0 ? $item->qty_kg_per_pack : 1, 2, '.', '') }}"
                                                                {{ (string) ($row['kerupuk_packing_item_id'] ?? '') === (string) $item->id ? 'selected' : '' }}>
                                                                {{ $item->name }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <option value="" disabled>No Pack SKUs configured</option>
                                                @endif
                                            </select>
                                            <!-- Hidden input for kerupuk_kering_item_id -->
                                            <input type="hidden" name="step4[{{ $row['index'] }}][kerupuk_kering_item_id]" value="{{ $row['kerupuk_kering_item_id'] }}">
                                        </td>
                                        <td class="text-end align-middle weight-display">{{ $formatWeight($weight) }} Kg</td>
                                        <td class="text-end align-middle kg-display-gl1">{{ $formatKg($gl1Kg) }} Kg</td>
                                        <td class="text-end align-middle kg-display-gl2">{{ $formatKg($gl2Kg) }} Kg</td>
                                        <td class="text-end align-middle kg-display-ta">{{ $formatKg($taKg) }} Kg</td>
                                        <td class="text-end align-middle kg-display-bl">{{ $formatKg($blKg) }} Kg</td>
                                        <td class="text-end align-middle total-kg-display"><strong>{{ $formatKg($totalKg) }} Kg</strong></td>
                                        <td rowspan="2" class="align-middle text-center">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="removeStep4Row(this)">
                                                <i class="far fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="pack-row">
                                        <td class="text-end align-middle">Per Pack</td>
                                        <td class="text-end align-middle">
                                            <input type="number" 
                                                name="step4[{{ $row['index'] }}][qty_gl1_packing]" 
                                                class="form-control form-control-sm text-end pack-input d-inline-block" 
                                                style="width: 80px;"
                                                data-line="gl1" 
                                                data-row-index="{{ $row['index'] }}"
                                                step="1" 
                                                min="0" 
                                                value="{{ $formatPacks($gl1Pack) }}" 
                                                required>
                                            <span class="ms-1">Pack</span>
                                        </td>
                                        <td class="text-end align-middle">
                                            <input type="number" 
                                                name="step4[{{ $row['index'] }}][qty_gl2_packing]" 
                                                class="form-control form-control-sm text-end pack-input d-inline-block" 
                                                style="width: 80px;"
                                                data-line="gl2" 
                                                data-row-index="{{ $row['index'] }}"
                                                step="1" 
                                                min="0" 
                                                value="{{ $formatPacks($gl2Pack) }}" 
                                                required>
                                            <span class="ms-1">Pack</span>
                                        </td>
                                        <td class="text-end align-middle">
                                            <input type="number" 
                                                name="step4[{{ $row['index'] }}][qty_ta_packing]" 
                                                class="form-control form-control-sm text-end pack-input d-inline-block" 
                                                style="width: 80px;"
                                                data-line="ta" 
                                                data-row-index="{{ $row['index'] }}"
                                                step="1" 
                                                min="0" 
                                                value="{{ $formatPacks($taPack) }}" 
                                                required>
                                            <span class="ms-1">Pack</span>
                                        </td>
                                        <td class="text-end align-middle">
                                            <input type="number" 
                                                name="step4[{{ $row['index'] }}][qty_bl_packing]" 
                                                class="form-control form-control-sm text-end pack-input d-inline-block" 
                                                style="width: 80px;"
                                                data-line="bl" 
                                                data-row-index="{{ $row['index'] }}"
                                                step="1" 
                                                min="0" 
                                                value="{{ $formatPacks($blPack) }}" 
                                                required>
                                            <span class="ms-1">Pack</span>
                                        </td>
                                        <td class="text-end align-middle total-pack-display"><strong>{{ $formatPacks($totalPacks) }} Pack</strong></td>
                                    </tr>
                                @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('manufacturing.production-plans.show', $productionPlan) }}" class="btn btn-link">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="far fa-save me-1"></i>Save &amp; Continue to Step 5
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const packConfigurations = @json($packConfigurations);
const weightConfigurations = @json($weightConfigurations);
const step3Limits = @json($step3Limits);
const packingBlueprints = @json($packingBlueprints);
const allPackingItems = @json(
    ($allPackingItems ?? $packingItems)->map(static fn ($item) => [
        'id' => $item->id,
        'name' => $item->name,
        'weight' => (float) ($item->qty_kg_per_pack > 0 ? $item->qty_kg_per_pack : 1)
    ])
);
let rowIndex = {{ $rowCount }};

document.addEventListener('DOMContentLoaded', () => {
    // Bind events for pack inputs and pack SKU selects
    bindAllEvents();
    // Initial validation
    validateAllLimits();
    // Check if Add Pack SKU button should be visible
    updateAddPackSkuButtonVisibility();
});

function bindAllEvents() {
    document.querySelectorAll('.pack-input').forEach((input) => {
        input.addEventListener('input', () => {
            // Pack inputs are in pack-row, need to get the previous sibling kg-row
            const packRow = input.closest('.pack-row');
            const kgRow = packRow ? packRow.previousElementSibling : null;
            if (kgRow && kgRow.classList.contains('kg-row')) {
                updateRowDisplay(kgRow);
                validateAllLimits();
            }
        });
    });
    
    document.querySelectorAll('.pack-sku-select').forEach((select) => {
        select.addEventListener('change', () => {
            // Pack SKU select is in kg-row
            updateRowDisplay(select.closest('.kg-row.pack-sku-row'));
            validateAllLimits();
            updateAddPackSkuButtonVisibility();
        });
    });
}

function updateRowDisplay(kgRow) {
    if (!kgRow) return;
    
    const rowIndex = kgRow.dataset.rowIndex;
    const kerupukKgId = kgRow.dataset.kerupukKgId;
    const packSkuSelect = kgRow.querySelector('.pack-sku-select');
    const packSkuId = packSkuSelect ? packSkuSelect.value : null;
    
    // Get weight from configuration
    let weight = 1.0;
    if (kerupukKgId && packSkuId) {
        const configKey = kerupukKgId + '_' + packSkuId;
        weight = weightConfigurations[configKey] || 1.0;
    }
    
    // Update weight display
    const weightDisplay = kgRow.querySelector('.weight-display');
    if (weightDisplay) {
        weightDisplay.textContent = weight.toFixed(2) + ' Kg';
    }
    
    // Calculate and update each location
    let totalKg = 0;
    let totalPacks = 0;
    
    // Get the pack row (next sibling)
    const packRow = kgRow.nextElementSibling;
    
    ['gl1', 'gl2', 'ta', 'bl'].forEach((line) => {
        const packInput = packRow ? packRow.querySelector(`.pack-input[data-line="${line}"]`) : null;
        if (packInput) {
            const packs = parseFloat(packInput.value) || 0;
            const kg = packs * weight;
            totalPacks += packs;
            totalKg += kg;
            
            // Update Kg display
            const kgDisplay = kgRow.querySelector(`.kg-display-${line}`);
            if (kgDisplay) {
                kgDisplay.textContent = kg.toFixed(2) + ' Kg';
            }
        }
    });
    
    // Update totals
    const totalKgDisplay = kgRow.querySelector('.total-kg-display');
    if (totalKgDisplay) {
        totalKgDisplay.innerHTML = '<strong>' + totalKg.toFixed(2) + ' Kg</strong>';
    }
    
    const totalPackDisplay = packRow ? packRow.querySelector('.total-pack-display') : null;
    if (totalPackDisplay) {
        totalPackDisplay.innerHTML = '<strong>' + totalPacks.toFixed(0) + ' Pack</strong>';
    }
}

function addStep4Row() {
    // Get all kerupuk kg sections
    const sections = document.querySelectorAll('.kerupuk-kg-section');
    
    if (sections.length === 0) {
        alert('No Kerupuk Kg sections found. Please add at least one Pack SKU first.');
        return;
    }
    
    // If there's only one section, use it. Otherwise, let user choose
    let targetSection;
    if (sections.length === 1) {
        targetSection = sections[0];
    } else {
        // Create a simple selection dialog
        const kerupukKgNames = Array.from(sections).map((section, index) => {
            const nameEl = section.querySelector('h4 span');
            return {
                index: index,
                name: nameEl ? nameEl.textContent : `Section ${index + 1}`
            };
        });
        
        const choices = kerupukKgNames.map((item, idx) => `${idx + 1}. ${item.name}`).join('\n');
        const choice = prompt(`Select Kerupuk Kg section:\n${choices}\n\nEnter number (1-${sections.length}):`);
        
        if (!choice || isNaN(choice) || choice < 1 || choice > sections.length) {
            return;
        }
        
        targetSection = sections[choice - 1];
    }
    
    const kerupukKgId = targetSection.dataset.kerupukKgId;
    
    // Get allowed Pack SKUs for this Kerupuk Kg
    const allowedPackIds = packConfigurations[kerupukKgId] || [];
    
    if (allowedPackIds.length === 0) {
        alert('No Pack SKUs are configured for this Kerupuk Kg. Please configure Pack SKUs in Kerupuk Pack Configuration first.');
        return;
    }
    
    // Get existing Pack SKU IDs in this section
    const existingPackSkuIds = Array.from(targetSection.querySelectorAll('.pack-sku-select'))
        .map(select => select.value)
        .filter(value => value !== '');
    
    // Filter to only Pack SKUs that have packing blueprints and are not already added
    const availablePackItems = allPackingItems.filter(item => {
        return allowedPackIds.includes(item.id) && 
               packingBlueprints[item.id] && 
               packingBlueprints[item.id].length > 0 &&
               !existingPackSkuIds.includes(String(item.id));
    });
    
    if (availablePackItems.length === 0) {
        alert('No more Pack SKUs available to add. All configured Pack SKUs with blueprints have been added.');
        return;
    }
    
    // Get the tbody of the table in this section
    const tbody = targetSection.querySelector('table tbody');
    if (!tbody) return;
    
    // Get current row count for this section
    const currentRows = targetSection.querySelectorAll('.kg-row.pack-sku-row');
    const rowNumber = currentRows.length + 1;
    
    // Create new row index
    rowIndex++;
    
    // Create the kg-row
    const kgRow = document.createElement('tr');
    kgRow.className = 'kg-row pack-sku-row';
    kgRow.dataset.kerupukKgId = kerupukKgId;
    kgRow.dataset.packSkuId = '';
    kgRow.dataset.rowIndex = rowIndex;
    
    // Build Pack SKU options
    let packSkuOptions = '<option value="">Select Pack SKU</option>';
    availablePackItems.forEach(item => {
        const weight = item.weight || 1;
        packSkuOptions += `<option value="${item.id}" data-default-weight="${weight.toFixed(2)}">${item.name}</option>`;
    });
    
    kgRow.innerHTML = `
        <td rowspan="2" class="align-middle text-center"><strong>${rowNumber}</strong></td>
        <td rowspan="2" class="align-middle">
            <select name="step4[${rowIndex}][kerupuk_packing_item_id]" 
                    class="form-select form-select-sm pack-sku-select" 
                    data-row-index="${rowIndex}" 
                    required>
                ${packSkuOptions}
            </select>
            <input type="hidden" name="step4[${rowIndex}][kerupuk_kering_item_id]" value="${kerupukKgId}">
        </td>
        <td class="text-end align-middle weight-display">1.00 Kg</td>
        <td class="text-end align-middle kg-display-gl1">0.00 Kg</td>
        <td class="text-end align-middle kg-display-gl2">0.00 Kg</td>
        <td class="text-end align-middle kg-display-ta">0.00 Kg</td>
        <td class="text-end align-middle kg-display-bl">0.00 Kg</td>
        <td class="text-end align-middle total-kg-display"><strong>0.00 Kg</strong></td>
        <td rowspan="2" class="align-middle text-center">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeStep4Row(this)">
                <i class="far fa-trash"></i>
            </button>
        </td>
    `;
    
    // Create the pack-row
    const packRow = document.createElement('tr');
    packRow.className = 'pack-row';
    
    packRow.innerHTML = `
        <td class="text-end align-middle">Per Pack</td>
        <td class="text-end align-middle">
            <input type="number" 
                name="step4[${rowIndex}][qty_gl1_packing]" 
                class="form-control form-control-sm text-end pack-input d-inline-block" 
                style="width: 80px;"
                data-line="gl1" 
                data-row-index="${rowIndex}"
                step="1" 
                min="0" 
                value="0" 
                required>
            <span class="ms-1">Pack</span>
        </td>
        <td class="text-end align-middle">
            <input type="number" 
                name="step4[${rowIndex}][qty_gl2_packing]" 
                class="form-control form-control-sm text-end pack-input d-inline-block" 
                style="width: 80px;"
                data-line="gl2" 
                data-row-index="${rowIndex}"
                step="1" 
                min="0" 
                value="0" 
                required>
            <span class="ms-1">Pack</span>
        </td>
        <td class="text-end align-middle">
            <input type="number" 
                name="step4[${rowIndex}][qty_ta_packing]" 
                class="form-control form-control-sm text-end pack-input d-inline-block" 
                style="width: 80px;"
                data-line="ta" 
                data-row-index="${rowIndex}"
                step="1" 
                min="0" 
                value="0" 
                required>
            <span class="ms-1">Pack</span>
        </td>
        <td class="text-end align-middle">
            <input type="number" 
                name="step4[${rowIndex}][qty_bl_packing]" 
                class="form-control form-control-sm text-end pack-input d-inline-block" 
                style="width: 80px;"
                data-line="bl" 
                data-row-index="${rowIndex}"
                step="1" 
                min="0" 
                value="0" 
                required>
            <span class="ms-1">Pack</span>
        </td>
        <td class="text-end align-middle total-pack-display"><strong>0 Pack</strong></td>
    `;
    
    // Append rows to tbody
    tbody.appendChild(kgRow);
    tbody.appendChild(packRow);
    
    // Bind events to new elements
    bindEventsForRow(kgRow);
    
    // Validate limits
    validateAllLimits();
    
    // Update Add Pack SKU button visibility
    updateAddPackSkuButtonVisibility();
}

function bindEventsForRow(kgRow) {
    // Bind pack-sku-select change event
    const select = kgRow.querySelector('.pack-sku-select');
    if (select) {
        select.addEventListener('change', () => {
            updateRowDisplay(select.closest('.kg-row.pack-sku-row'));
            validateAllLimits();
            updateAddPackSkuButtonVisibility();
        });
    }
    
    // Bind pack-input events in the next sibling (pack-row)
    const packRow = kgRow.nextElementSibling;
    if (packRow) {
        packRow.querySelectorAll('.pack-input').forEach((input) => {
            input.addEventListener('input', () => {
                const row = input.closest('.pack-row');
                const kg = row ? row.previousElementSibling : null;
                if (kg && kg.classList.contains('kg-row')) {
                    updateRowDisplay(kg);
                    validateAllLimits();
                }
            });
        });
    }
}

function removeStep4Row(button) {
    const kgRow = button.closest('.kg-row');
    if (!kgRow) return;
    
    const section = kgRow.closest('.kerupuk-kg-section');
    if (!section) return;
    
    const rows = section.querySelectorAll('.kg-row.pack-sku-row');
    if (rows.length <= 1) {
        alert('At least one Pack SKU is required per Kerupuk Kg section.');
        return;
    }
    
    if (confirm('Are you sure you want to remove this Pack SKU?')) {
        // Remove both the kg-row and the following pack-row
        const packRow = kgRow.nextElementSibling;
        if (packRow && packRow.classList.contains('pack-row')) {
            packRow.remove();
        }
        kgRow.remove();
        
        // Renumber rows in the section
        renumberRows(section);
        validateAllLimits();
        
        // Update Add Pack SKU button visibility
        updateAddPackSkuButtonVisibility();
    }
}

function renumberRows(section) {
    const rows = section.querySelectorAll('.kg-row.pack-sku-row');
    rows.forEach((row, index) => {
        const noCell = row.querySelector('td:first-child strong');
        if (noCell) {
            noCell.textContent = index + 1;
        }
    });
}

function validateAllLimits() {
    const warnings = [];
    const saveButton = document.querySelector('button[type="submit"]');
    
    // Group all rows by Kerupuk Kg ID
    const sections = document.querySelectorAll('.kerupuk-kg-section');
    
    sections.forEach(section => {
        const kerupukKgId = section.dataset.kerupukKgId;
        
        // Skip unassigned sections
        if (!kerupukKgId || kerupukKgId.startsWith('unassigned_')) {
            return;
        }
        
        // Get limits from Step 3
        const limits = step3Limits[kerupukKgId];
        if (!limits) {
            return;
        }
        
        // Get kerupuk kg name
        const kerupukKgName = section.querySelector('h4 span.text-primary')?.textContent || 'Unknown';
        
        // Calculate totals for this Kerupuk Kg across all Pack SKUs
        const totals = {
            gl1: 0,
            gl2: 0,
            ta: 0,
            bl: 0
        };
        
        const rows = section.querySelectorAll('.kg-row.pack-sku-row');
        rows.forEach(row => {
            const gl1Kg = parseFloat(row.querySelector('.kg-display-gl1')?.textContent) || 0;
            const gl2Kg = parseFloat(row.querySelector('.kg-display-gl2')?.textContent) || 0;
            const taKg = parseFloat(row.querySelector('.kg-display-ta')?.textContent) || 0;
            const blKg = parseFloat(row.querySelector('.kg-display-bl')?.textContent) || 0;
            
            totals.gl1 += gl1Kg;
            totals.gl2 += gl2Kg;
            totals.ta += taKg;
            totals.bl += blKg;
        });
        
        // Check each location
        const locations = [
            { key: 'gl1', name: 'GL1', limit: parseFloat(limits.qty_gl1_kg), total: totals.gl1 },
            { key: 'gl2', name: 'GL2', limit: parseFloat(limits.qty_gl2_kg), total: totals.gl2 },
            { key: 'ta', name: 'TA', limit: parseFloat(limits.qty_ta_kg), total: totals.ta },
            { key: 'bl', name: 'BL', limit: parseFloat(limits.qty_bl_kg), total: totals.bl }
        ];
        
        locations.forEach(loc => {
            if (loc.total > loc.limit) {
                warnings.push(
                    `<strong>${kerupukKgName}</strong> in <strong>${loc.name}</strong>: ` +
                    `Total ${loc.total.toFixed(2)} Kg exceeds Step 3 limit of ${loc.limit.toFixed(2)} Kg ` +
                    `(Excess: ${(loc.total - loc.limit).toFixed(2)} Kg)`
                );
            }
        });
    });
    
    // Display warnings
    const warningsDiv = document.getElementById('validation-warnings');
    const warningsList = document.getElementById('validation-warnings-list');
    
    if (warnings.length > 0) {
        warningsList.innerHTML = warnings.map(w => `<li>${w}</li>`).join('');
        warningsDiv.classList.remove('d-none');
        if (saveButton) {
            saveButton.disabled = true;
            saveButton.classList.add('disabled');
        }
    } else {
        warningsDiv.classList.add('d-none');
        if (saveButton) {
            saveButton.disabled = false;
            saveButton.classList.remove('disabled');
        }
    }
}

function updateAddPackSkuButtonVisibility() {
    const addButton = document.querySelector('button[onclick="addStep4Row()"]');
    if (!addButton) return;
    
    // Get all kerupuk kg sections
    const sections = document.querySelectorAll('.kerupuk-kg-section');
    
    if (sections.length === 0) {
        addButton.style.display = 'none';
        return;
    }
    
    // Check if any section has available Pack SKUs to add
    let hasAvailablePackSkus = false;
    
    sections.forEach(section => {
        const kerupukKgId = section.dataset.kerupukKgId;
        
        // Get allowed Pack SKUs for this Kerupuk Kg
        const allowedPackIds = packConfigurations[kerupukKgId] || [];
        
        if (allowedPackIds.length === 0) {
            return;
        }
        
        // Get existing Pack SKU IDs in this section
        const existingPackSkuIds = Array.from(section.querySelectorAll('.pack-sku-select'))
            .map(select => select.value)
            .filter(value => value !== '');
        
        // Check if there are any Pack SKUs with blueprints that are not already added
        const availableCount = allPackingItems.filter(item => {
            return allowedPackIds.includes(item.id) && 
                   packingBlueprints[item.id] && 
                   packingBlueprints[item.id].length > 0 &&
                   !existingPackSkuIds.includes(String(item.id));
        }).length;
        
        if (availableCount > 0) {
            hasAvailablePackSkus = true;
        }
    });
    
    // Show or hide the button based on availability
    if (hasAvailablePackSkus) {
        addButton.style.display = '';
    } else {
        addButton.style.display = 'none';
    }
}
</script>
@endpush
@endsection
