@extends('layouts.app')

@section('title', 'Step 4: Packing Planning')

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
                <h2 class="page-title">Step 4: Packing Planning</h2>
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

        <form method="POST" action="{{ route('manufacturing.production-plans.step4.store', $productionPlan) }}">
            @csrf

            @php
                $rows = [];
                $rowCounter = 0;
                $defaultRow = [
                    'kerupuk_kering_item_id' => null,
                    'kerupuk_packing_item_id' => null,
                    'weight_per_unit' => 1.0,
                    'qty_gl1_packing' => 0.0,
                    'qty_gl2_packing' => 0.0,
                    'qty_ta_packing' => 0.0,
                    'qty_bl_packing' => 0.0,
                    'qty_gl1_kg' => 0.0,
                    'qty_gl2_kg' => 0.0,
                    'qty_ta_kg' => 0.0,
                    'qty_bl_kg' => 0.0,
                ];

                $oldRows = old('step4');
                if (is_array($oldRows) && count($oldRows) > 0) {
                    foreach ($oldRows as $row) {
                        $rows[] = array_merge($defaultRow, [
                            'index' => $rowCounter++,
                            'kerupuk_kering_item_id' => $row['kerupuk_kering_item_id'] ?? null,
                            'kerupuk_packing_item_id' => $row['kerupuk_packing_item_id'] ?? null,
                            'weight_per_unit' => (float) ($row['weight_per_unit'] ?? 1.0),
                            'qty_gl1_packing' => (float) ($row['qty_gl1_packing'] ?? 0),
                            'qty_gl2_packing' => (float) ($row['qty_gl2_packing'] ?? 0),
                            'qty_ta_packing' => (float) ($row['qty_ta_packing'] ?? 0),
                            'qty_bl_packing' => (float) ($row['qty_bl_packing'] ?? 0),
                            'qty_gl1_kg' => (float) ($row['qty_gl1_kg'] ?? 0),
                            'qty_gl2_kg' => (float) ($row['qty_gl2_kg'] ?? 0),
                            'qty_ta_kg' => (float) ($row['qty_ta_kg'] ?? 0),
                            'qty_bl_kg' => (float) ($row['qty_bl_kg'] ?? 0),
                        ]);
                    }
                } elseif (count($calculatedData) > 0) {
                    foreach ($calculatedData as $row) {
                        $rows[] = array_merge($defaultRow, [
                            'index' => $rowCounter++,
                            'kerupuk_kering_item_id' => $row['kerupuk_kering_item_id'] ?? null,
                            'kerupuk_packing_item_id' => $row['kerupuk_packing_item_id'] ?? null,
                            'weight_per_unit' => (float) ($row['weight_per_unit'] ?? 1.0),
                            'qty_gl1_packing' => (float) ($row['qty_gl1_packing'] ?? 0),
                            'qty_gl2_packing' => (float) ($row['qty_gl2_packing'] ?? 0),
                            'qty_ta_packing' => (float) ($row['qty_ta_packing'] ?? 0),
                            'qty_bl_packing' => (float) ($row['qty_bl_packing'] ?? 0),
                            'qty_gl1_kg' => (float) ($row['qty_gl1_kg'] ?? 0),
                            'qty_gl2_kg' => (float) ($row['qty_gl2_kg'] ?? 0),
                            'qty_ta_kg' => (float) ($row['qty_ta_kg'] ?? 0),
                            'qty_bl_kg' => (float) ($row['qty_bl_kg'] ?? 0),
                        ]);
                    }
                } elseif ($productionPlan->step4->count() > 0) {
                    foreach ($productionPlan->step4 as $step4Row) {
                        $rows[] = [
                            'index' => $rowCounter++,
                            'kerupuk_kering_item_id' => $step4Row->kerupuk_kering_item_id,
                            'kerupuk_packing_item_id' => $step4Row->kerupuk_packing_item_id,
                            'weight_per_unit' => (float) $step4Row->weight_per_unit,
                            'qty_gl1_packing' => (float) $step4Row->qty_gl1_packing,
                            'qty_gl2_packing' => (float) $step4Row->qty_gl2_packing,
                            'qty_ta_packing' => (float) $step4Row->qty_ta_packing,
                            'qty_bl_packing' => (float) $step4Row->qty_bl_packing,
                            'qty_gl1_kg' => (float) $step4Row->qty_gl1_kg,
                            'qty_gl2_kg' => (float) $step4Row->qty_gl2_kg,
                            'qty_ta_kg' => (float) $step4Row->qty_ta_kg,
                            'qty_bl_kg' => (float) $step4Row->qty_bl_kg,
                        ];
                    }
                }

                if (count($rows) === 0) {
                    $rows[] = array_merge($defaultRow, ['index' => 0]);
                }

                $rowCount = count($rows);
                $formatQty = static fn ($value) => number_format((float) $value, 3, '.', '');
            @endphp

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Step 4: Packing Output & Material Planning</h3>
                    <div class="card-actions">
                        <button type="button" class="btn btn-sm btn-primary" onclick="addStep4Row()">
                            <i class="far fa-plus"></i>&nbsp;Add Row
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="far fa-info-circle me-2"></i>
                        Enter the number of Kerupuk Packs produced per production line. Kg totals and packing material usage are calculated automatically from the selected SKU's weight per unit and packing blueprint.
                        @if(count($calculatedData) > 0 && !old('step4'))
                            Auto-calculated suggestions from Step 3 have been pre-filled below.
                        @endif
                    </div>

                    @error('step4')
                        <div class="alert alert-danger">
                            <i class="far fa-triangle-exclamation me-2"></i>{{ $message }}
                        </div>
                    @enderror

                    <div class="table-responsive">
                        <table class="table table-vcenter" id="step4-table">
                            <thead>
                                <tr>
                                    <th rowspan="2">Kerupuk Kering Item</th>
                                    <th rowspan="2">Pack SKU</th>
                                    <th rowspan="2" class="text-center">Weight/Unit (kg)</th>
                                    <th colspan="2" class="text-center">GL1</th>
                                    <th colspan="2" class="text-center">GL2</th>
                                    <th colspan="2" class="text-center">TA</th>
                                    <th colspan="2" class="text-center">BL</th>
                                    <th colspan="2" class="text-center">Totals</th>
                                    <th rowspan="2" class="w-1"></th>
                                </tr>
                                <tr>
                                    <th>Kg</th>
                                    <th>Packs</th>
                                    <th>Kg</th>
                                    <th>Packs</th>
                                    <th>Kg</th>
                                    <th>Packs</th>
                                    <th>Kg</th>
                                    <th>Packs</th>
                                    <th>Kg</th>
                                    <th>Packs</th>
                                </tr>
                            </thead>
                            <tbody id="step4-tbody">
                                @foreach($rows as $row)
                                    @php
                                        $weight = (float) $row['weight_per_unit'];
                                        $gl1Pack = (float) $row['qty_gl1_packing'];
                                        $gl2Pack = (float) $row['qty_gl2_packing'];
                                        $taPack = (float) $row['qty_ta_packing'];
                                        $blPack = (float) $row['qty_bl_packing'];
                                        $gl1Kg = $row['qty_gl1_kg'] ?? ($gl1Pack * $weight);
                                        $gl2Kg = $row['qty_gl2_kg'] ?? ($gl2Pack * $weight);
                                        $taKg = $row['qty_ta_kg'] ?? ($taPack * $weight);
                                        $blKg = $row['qty_bl_kg'] ?? ($blPack * $weight);
                                        $totalPacks = $gl1Pack + $gl2Pack + $taPack + $blPack;
                                        $totalKg = $gl1Kg + $gl2Kg + $taKg + $blKg;
                                    @endphp
                                    <tr class="step4-row" data-index="{{ $row['index'] }}">
                                        <td>
                                            <select name="step4[{{ $row['index'] }}][kerupuk_kering_item_id]" class="form-select form-select-sm kerupuk-kg-select" data-row-index="{{ $row['index'] }}" required>
                                                <option value="">Select Kerupuk Kering</option>
                                                @foreach($kerupukKeringOptions as $option)
                                                    <option value="{{ $option['id'] }}" {{ (string) ($row['kerupuk_kering_item_id'] ?? '') === (string) $option['id'] ? 'selected' : '' }}>
                                                        {{ $option['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="step4[{{ $row['index'] }}][kerupuk_packing_item_id]" class="form-select form-select-sm pack-sku-select" data-role="packing-select" data-row-index="{{ $row['index'] }}" required>
                                                <option value="">Select Pack SKU</option>
                                                @foreach($packingItems as $item)
                                                    <option value="{{ $item->id }}"
                                                        data-default-weight="{{ number_format($item->qty_kg_per_pack > 0 ? $item->qty_kg_per_pack : 1, 3, '.', '') }}"
                                                        {{ (string) ($row['kerupuk_packing_item_id'] ?? '') === (string) $item->id ? 'selected' : '' }}>
                                                        {{ $item->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number"
                                                   name="step4[{{ $row['index'] }}][weight_per_unit]"
                                                   class="form-control form-control-sm text-end"
                                                   data-role="weight-input"
                                                   step="0.001"
                                                   min="0.001"
                                                   value="{{ $formatQty($weight) }}"
                                                   required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm text-end bg-light"
                                                data-kg-field="gl1" value="{{ $formatQty($gl1Kg) }}" readonly tabindex="-1">
                                        </td>
                                        <td>
                                            <input type="number" name="step4[{{ $row['index'] }}][qty_gl1_packing]"
                                                class="form-control form-control-sm text-end pack-input"
                                                data-line="gl1" step="1" min="0"
                                                value="{{ $formatQty($gl1Pack) }}" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm text-end bg-light"
                                                data-kg-field="gl2" value="{{ $formatQty($gl2Kg) }}" readonly tabindex="-1">
                                        </td>
                                        <td>
                                            <input type="number" name="step4[{{ $row['index'] }}][qty_gl2_packing]"
                                                class="form-control form-control-sm text-end pack-input"
                                                data-line="gl2" step="1" min="0"
                                                value="{{ $formatQty($gl2Pack) }}" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm text-end bg-light"
                                                data-kg-field="ta" value="{{ $formatQty($taKg) }}" readonly tabindex="-1">
                                        </td>
                                        <td>
                                            <input type="number" name="step4[{{ $row['index'] }}][qty_ta_packing]"
                                                class="form-control form-control-sm text-end pack-input"
                                                data-line="ta" step="1" min="0"
                                                value="{{ $formatQty($taPack) }}" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm text-end bg-light"
                                                data-kg-field="bl" value="{{ $formatQty($blKg) }}" readonly tabindex="-1">
                                        </td>
                                        <td>
                                            <input type="number" name="step4[{{ $row['index'] }}][qty_bl_packing]"
                                                class="form-control form-control-sm text-end pack-input"
                                                data-line="bl" step="1" min="0"
                                                value="{{ $formatQty($blPack) }}" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm text-end bg-light"
                                                data-total-field="kg" value="{{ $formatQty($totalKg) }}" readonly tabindex="-1">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm text-end bg-light"
                                                data-total-field="pack" value="{{ $formatQty($totalPacks) }}" readonly tabindex="-1">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                                                <i class="far fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <h4 class="card-title h5 mb-2">Packing Material Usage Preview</h4>
                        <p class="text-muted small mb-3">
                            The system multiplies each pack SKU's blueprint by the total packs above to estimate required materials. Materials are grouped by Pack SKU.
                        </p>
                        <div id="materials-preview"></div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('manufacturing.production-plans.show', $productionPlan) }}" class="btn btn-link">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Step 4</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const packingBlueprints = @json($packingBlueprints);
const packingDefaultWeights = @json(
    $packingItems->mapWithKeys(
        static fn ($item) => [$item->id => (float) ($item->qty_kg_per_pack > 0 ? $item->qty_kg_per_pack : 1)]
    )
);
const packConfigurations = @json($packConfigurations);
const allPackingItems = @json(
    $packingItems->map(static fn ($item) => [
        'id' => $item->id,
        'name' => $item->name,
        'weight' => (float) ($item->qty_kg_per_pack > 0 ? $item->qty_kg_per_pack : 1)
    ])
);
let rowIndex = {{ $rowCount }};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.step4-row').forEach(bindRowEvents);
    // Initialize pack SKU filters based on current selections
    document.querySelectorAll('.kerupuk-kg-select').forEach(filterPackSKUsForRow);
    refreshMaterialsPreview();
});

function bindRowEvents(row) {
    row.querySelectorAll('.pack-input').forEach((input) => {
        input.addEventListener('input', () => {
            updateRowCalculations(row);
            refreshMaterialsPreview();
        });
    });

    const weightInput = row.querySelector('[data-role="weight-input"]');
    if (weightInput) {
        weightInput.addEventListener('input', () => {
            updateRowCalculations(row);
            refreshMaterialsPreview();
        });
    }

    const kerupukKgSelect = row.querySelector('.kerupuk-kg-select');
    if (kerupukKgSelect) {
        kerupukKgSelect.addEventListener('change', () => {
            filterPackSKUsForRow(kerupukKgSelect);
        });
    }

    const packingSelect = row.querySelector('[data-role="packing-select"]');
    if (packingSelect) {
        packingSelect.addEventListener('change', (event) => {
            const selectedId = event.target.value;
            if (selectedId && packingDefaultWeights[selectedId]) {
                weightInput.value = Number(packingDefaultWeights[selectedId]).toFixed(3);
            }
            updateRowCalculations(row);
            refreshMaterialsPreview();
        });
    }

    updateRowCalculations(row);
}

function filterPackSKUsForRow(kerupukKgSelect) {
    const row = kerupukKgSelect.closest('.step4-row');
    if (!row) return;
    
    const packSkuSelect = row.querySelector('.pack-sku-select');
    if (!packSkuSelect) return;
    
    const kerupukKgId = kerupukKgSelect.value;
    const currentPackSkuId = packSkuSelect.value;
    
    // Clear options except the first "Select Pack SKU"
    packSkuSelect.innerHTML = '<option value="">Select Pack SKU</option>';
    
    if (!kerupukKgId) {
        // If no kerupuk kg selected, show all pack items
        allPackingItems.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            option.dataset.defaultWeight = item.weight.toFixed(3);
            packSkuSelect.appendChild(option);
        });
    } else {
        // Filter by configuration
        const allowedPackIds = packConfigurations[kerupukKgId] || [];
        
        if (allowedPackIds.length === 0) {
            const option = document.createElement('option');
            option.value = "";
            option.textContent = "No pack SKUs configured for this Kerupuk Kg";
            option.disabled = true;
            packSkuSelect.appendChild(option);
        } else {
            allPackingItems.forEach(item => {
                if (allowedPackIds.includes(item.id)) {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    option.dataset.defaultWeight = item.weight.toFixed(3);
                    if (item.id == currentPackSkuId) {
                        option.selected = true;
                    }
                    packSkuSelect.appendChild(option);
                }
            });
        }
    }
    
    // If previously selected pack SKU is not in the filtered list, clear it
    if (currentPackSkuId) {
        const foundOption = Array.from(packSkuSelect.options).find(opt => opt.value == currentPackSkuId);
        if (!foundOption) {
            packSkuSelect.value = "";
            const event = new Event('change');
            packSkuSelect.dispatchEvent(event);
        }
    }
}

function addStep4Row() {
    const tbody = document.getElementById('step4-tbody');
    const firstRow = tbody.querySelector('.step4-row');
    if (!firstRow) {
        return;
    }

    const newRow = firstRow.cloneNode(true);
    newRow.dataset.index = rowIndex;

    newRow.querySelectorAll('[name]').forEach((input) => {
        const oldName = input.getAttribute('name');
        if (oldName) {
            input.setAttribute('name', oldName.replace(/\[\d+]/, `[${rowIndex}]`));
        }

        if (input.matches('select')) {
            input.selectedIndex = 0;
        }

        if (input.tagName === 'INPUT') {
            if (input.classList.contains('pack-input')) {
                input.value = '0.000';
            } else if (input.dataset.role === 'weight-input') {
                input.value = '1.000';
            } else {
                input.value = '0.000';
            }
        }
    });

    tbody.appendChild(newRow);
    bindRowEvents(newRow);
    refreshMaterialsPreview();
    rowIndex++;
}

function removeRow(button) {
    const tbody = document.getElementById('step4-tbody');
    const rows = tbody.querySelectorAll('.step4-row');
    if (rows.length <= 1) {
        alert('At least one row is required.');
        return;
    }
    button.closest('.step4-row').remove();
    refreshMaterialsPreview();
}

function updateRowCalculations(row) {
    const weightInput = row.querySelector('[data-role="weight-input"]');
    const weight = parseFloat(weightInput?.value ?? '0') || 0;

    let totalPacks = 0;
    let totalKg = 0;

    row.querySelectorAll('.pack-input').forEach((input) => {
        const packs = parseFloat(input.value ?? '0') || 0;
        totalPacks += packs;
        const kgValue = packs * weight;
        totalKg += kgValue;
        const line = input.dataset.line;
        const kgField = row.querySelector(`[data-kg-field="${line}"]`);
        if (kgField) {
            kgField.value = kgValue.toFixed(3);
        }
    });

    const totalPackField = row.querySelector('[data-total-field="pack"]');
    const totalKgField = row.querySelector('[data-total-field="kg"]');

    if (totalPackField) {
        totalPackField.value = totalPacks.toFixed(3);
    }
    if (totalKgField) {
        totalKgField.value = totalKg.toFixed(3);
    }
}

function refreshMaterialsPreview() {
    const container = document.getElementById('materials-preview');
    if (!container) {
        return;
    }

    // Group materials by Pack SKU (row index)
    const rowMaterials = [];
    let hasAnyMaterials = false;

    document.querySelectorAll('.step4-row').forEach((row, rowIdx) => {
        const packingSelect = row.querySelector('[data-role="packing-select"]');
        const packingId = packingSelect ? packingSelect.value : null;
        const packingName = packingSelect ? packingSelect.options[packingSelect.selectedIndex]?.text : 'N/A';
        
        if (!packingId || !packingBlueprints[packingId]) {
            rowMaterials.push({
                rowIndex: row.dataset.index || rowIdx,
                packingId: packingId,
                packingName: packingName,
                totalPacks: 0,
                materials: []
            });
            return;
        }

        const totalPackField = row.querySelector('[data-total-field="pack"]');
        const totalPacks = parseFloat(totalPackField?.value ?? '0') || 0;

        const materials = [];
        if (totalPacks > 0 && packingBlueprints[packingId] && packingBlueprints[packingId].length > 0) {
            hasAnyMaterials = true;
            packingBlueprints[packingId].forEach((material) => {
                materials.push({
                    packingMaterialItemId: material.packing_material_item_id,
                    name: material.packing_material_item_name,
                    quantityPerPack: material.quantity_per_pack || 0,
                    quantityTotal: (material.quantity_per_pack || 0) * totalPacks
                });
            });
        }

        rowMaterials.push({
            rowIndex: row.dataset.index || rowIdx,
            packingId: packingId,
            packingName: packingName,
            totalPacks: totalPacks,
            materials: materials
        });
    });

    if (!hasAnyMaterials) {
        container.innerHTML = '<div class="alert alert-warning mb-0"><i class="far fa-exclamation-triangle me-2"></i>No packing materials calculated yet. Select Pack SKUs and enter quantities above.</div>';
        return;
    }

    // Build HTML for each row's materials
    let html = '';
    let materialInputCounter = 0;

    rowMaterials.forEach((rowData) => {
        if (rowData.materials.length === 0) {
            return; // Skip rows with no materials
        }

        html += `
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="far fa-box me-2"></i>${rowData.packingName}
                        <span class="text-muted ms-2">(${rowData.totalPacks.toFixed(3)} packs)</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-vcenter mb-0">
                            <thead>
                                <tr>
                                    <th>Packing Material</th>
                                    <th class="text-end">Per Pack</th>
                                    <th class="text-end" style="width: 250px;">Total Quantity (Editable)</th>
                                </tr>
                            </thead>
                            <tbody>`;

        rowData.materials.forEach((material) => {
            html += `
                <tr>
                    <td class="align-middle">
                        <strong>${material.name}</strong>
                        <input type="hidden" name="materials[${materialInputCounter}][production_plan_step4_row_index]" value="${rowData.rowIndex}">
                        <input type="hidden" name="materials[${materialInputCounter}][packing_material_item_id]" value="${material.packingMaterialItemId}">
                    </td>
                    <td class="text-end align-middle text-muted">
                        ${material.quantityPerPack.toFixed(3)}
                    </td>
                    <td class="text-end align-middle">
                        <div class="input-group input-group-sm" style="max-width: 200px; margin-left: auto;">
                            <input type="number" 
                                name="materials[${materialInputCounter}][quantity_total]" 
                                class="form-control text-end" 
                                value="${Math.round(material.quantityTotal)}" 
                                step="1" 
                                min="0"
                                title="You can adjust this quantity if needed">
                            <span class="input-group-text">units</span>
                        </div>
                        <small class="text-muted">Calculated: ${Math.round(material.quantityTotal)}</small>
                    </td>
                </tr>`;
            materialInputCounter++;
        });

        html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>`;
    });

    html += `
        <div class="alert alert-info mt-2 mb-0">
            <i class="far fa-info-circle me-2"></i>
            <strong>Note:</strong> Quantities are auto-calculated from pack SKU blueprints, but you can adjust them if needed before saving.
        </div>`;

    container.innerHTML = html;
}
</script>
@endpush
@endsection


















