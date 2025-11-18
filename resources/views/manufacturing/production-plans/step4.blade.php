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
                $formatKg = static fn ($value) => number_format((float) $value, 2, '.', '');
                $formatPacks = static fn ($value) => number_format((float) $value, 0, '.', '');
                $formatWeight = static fn ($value) => number_format((float) $value, 2, '.', '');
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

                    @php
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

                    <div id="step4-sections">
                        @foreach($groupedRows as $kerupukKgId => $group)
                            @php
                                $firstRow = $group['rows'][0];
                                $packSkuId = $firstRow['kerupuk_packing_item_id'] ?? null;
                                $weight = 1.0;
                                if (!empty($firstRow['kerupuk_kering_item_id']) && !empty($firstRow['kerupuk_packing_item_id'])) {
                                    $configKey = $firstRow['kerupuk_kering_item_id'] . '_' . $firstRow['kerupuk_packing_item_id'];
                                    $weight = $weightConfigurations[$configKey] ?? 1.0;
                                }
                                
                                // Aggregate totals for this Kerupuk Kg
                                $totalGl1Kg = 0;
                                $totalGl1Pack = 0;
                                $totalGl2Kg = 0;
                                $totalGl2Pack = 0;
                                $totalTaKg = 0;
                                $totalTaPack = 0;
                                $totalBlKg = 0;
                                $totalBlPack = 0;
                                
                                foreach ($group['rows'] as $row) {
                                    $rowWeight = 1.0;
                                    if (!empty($row['kerupuk_kering_item_id']) && !empty($row['kerupuk_packing_item_id'])) {
                                        $configKey = $row['kerupuk_kering_item_id'] . '_' . $row['kerupuk_packing_item_id'];
                                        $rowWeight = $weightConfigurations[$configKey] ?? 1.0;
                                    }
                                    $gl1Pack = (float) ($row['qty_gl1_packing'] ?? 0);
                                    $gl2Pack = (float) ($row['qty_gl2_packing'] ?? 0);
                                    $taPack = (float) ($row['qty_ta_packing'] ?? 0);
                                    $blPack = (float) ($row['qty_bl_packing'] ?? 0);
                                    $gl1Kg = $row['qty_gl1_kg'] ?? ($gl1Pack * $rowWeight);
                                    $gl2Kg = $row['qty_gl2_kg'] ?? ($gl2Pack * $rowWeight);
                                    $taKg = $row['qty_ta_kg'] ?? ($taPack * $rowWeight);
                                    $blKg = $row['qty_bl_kg'] ?? ($blPack * $rowWeight);
                                    
                                    $totalGl1Kg += $gl1Kg;
                                    $totalGl1Pack += $gl1Pack;
                                    $totalGl2Kg += $gl2Kg;
                                    $totalGl2Pack += $gl2Pack;
                                    $totalTaKg += $taKg;
                                    $totalTaPack += $taPack;
                                    $totalBlKg += $blKg;
                                    $totalBlPack += $blPack;
                                }
                                
                                $totalKg = $totalGl1Kg + $totalGl2Kg + $totalTaKg + $totalBlKg;
                                $totalPacks = $totalGl1Pack + $totalGl2Pack + $totalTaPack + $totalBlPack;
                            @endphp
                            
                            <div class="mb-5 pack-sku-section" data-kerupuk-kg-id="{{ $kerupukKgId }}" data-pack-sku-id="{{ $packSkuId }}">
                                <div class="mb-3">
                                    <h4 class="h5 mb-2">
                                        @if($group['kerupuk_kg_name'])
                                            {{ $group['kerupuk_kg_name'] }}
                                        @else
                                            <span class="text-muted">Unassigned Kerupuk Kg</span>
                                        @endif
                                    </h4>
                                </div>
                                
                                <!-- Packing Output Table -->
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>Pack SKU</th>
                                                <th>Weight/Unit (Kg)</th>
                                                <th>GL1</th>
                                                <th>GL2</th>
                                                <th>TA</th>
                                                <th>BL</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td rowspan="2" class="align-top">
                                                    @if(count($group['rows']) === 1)
                                                        @php 
                                                            $firstRow = $group['rows'][0];
                                                            // Get configured Pack SKUs for this Kerupuk Kg
                                                            $allowedPackIds = $packConfigurations[$kerupukKgId] ?? [];
                                                        @endphp
                                                        <select name="step4[{{ $firstRow['index'] }}][kerupuk_packing_item_id]" 
                                                                class="form-select form-select-sm pack-sku-select" 
                                                                data-role="packing-select" 
                                                                data-row-index="{{ $firstRow['index'] }}" 
                                                                required>
                                                            <option value="">Select Pack SKU</option>
                                                            @if(count($allowedPackIds) > 0)
                                                                @foreach($allPackingItems ?? $packingItems as $item)
                                                                    @if(in_array($item->id, $allowedPackIds))
                                                                        <option value="{{ $item->id }}"
                                                                            data-default-weight="{{ number_format($item->qty_kg_per_pack > 0 ? $item->qty_kg_per_pack : 1, 2, '.', '') }}"
                                                                            {{ (string) ($firstRow['kerupuk_packing_item_id'] ?? '') === (string) $item->id ? 'selected' : '' }}>
                                                                            {{ $item->name }}
                                                                        </option>
                                                                    @endif
                                                                @endforeach
                                                            @else
                                                                <option value="" disabled>No Pack SKUs configured for this Kerupuk Kg</option>
                                                            @endif
                                                        </select>
                                                    @else
                                                        <span class="text-muted">Multiple Pack SKUs</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">{{ $formatWeight($weight) }} Kg</td>
                                                <td class="text-end">{{ $formatKg($totalGl1Kg) }} Kg</td>
                                                <td class="text-end">{{ $formatKg($totalGl2Kg) }} Kg</td>
                                                <td class="text-end">{{ $formatKg($totalTaKg) }} Kg</td>
                                                <td class="text-end">{{ $formatKg($totalBlKg) }} Kg</td>
                                                <td class="text-end"><strong>{{ $formatKg($totalKg) }} Kg</strong></td>
                                            </tr>
                                            <tr>
                                                <td class="text-end">{{ $formatWeight($weight) }} Kg</td>
                                                <td class="text-end">
                                                    @if(count($group['rows']) === 1)
                                                        @php $firstRow = $group['rows'][0]; @endphp
                                                        <input type="number" 
                                                            name="step4[{{ $firstRow['index'] }}][qty_gl1_packing]" 
                                                            class="form-control form-control-sm text-end pack-input d-inline-block" 
                                                            style="width: 80px;"
                                                            data-line="gl1" 
                                                            data-row-index="{{ $firstRow['index'] }}"
                                                            step="1" 
                                                            min="0" 
                                                            value="{{ $formatPacks($totalGl1Pack) }}" 
                                                            required>
                                                        <span> Pack</span>
                                                    @else
                                                        {{ $formatPacks($totalGl1Pack) }} Pack
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @if(count($group['rows']) === 1)
                                                        <input type="number" 
                                                            name="step4[{{ $firstRow['index'] }}][qty_gl2_packing]" 
                                                            class="form-control form-control-sm text-end pack-input d-inline-block" 
                                                            style="width: 80px;"
                                                            data-line="gl2" 
                                                            data-row-index="{{ $firstRow['index'] }}"
                                                            step="1" 
                                                            min="0" 
                                                            value="{{ $formatPacks($totalGl2Pack) }}" 
                                                            required>
                                                        <span> Pack</span>
                                                    @else
                                                        {{ $formatPacks($totalGl2Pack) }} Pack
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @if(count($group['rows']) === 1)
                                                        <input type="number" 
                                                            name="step4[{{ $firstRow['index'] }}][qty_ta_packing]" 
                                                            class="form-control form-control-sm text-end pack-input d-inline-block" 
                                                            style="width: 80px;"
                                                            data-line="ta" 
                                                            data-row-index="{{ $firstRow['index'] }}"
                                                            step="1" 
                                                            min="0" 
                                                            value="{{ $formatPacks($totalTaPack) }}" 
                                                            required>
                                                        <span> Pack</span>
                                                    @else
                                                        {{ $formatPacks($totalTaPack) }} Pack
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @if(count($group['rows']) === 1)
                                                        <input type="number" 
                                                            name="step4[{{ $firstRow['index'] }}][qty_bl_packing]" 
                                                            class="form-control form-control-sm text-end pack-input d-inline-block" 
                                                            style="width: 80px;"
                                                            data-line="bl" 
                                                            data-row-index="{{ $firstRow['index'] }}"
                                                            step="1" 
                                                            min="0" 
                                                            value="{{ $formatPacks($totalBlPack) }}" 
                                                            required>
                                                        <span> Pack</span>
                                                    @else
                                                        {{ $formatPacks($totalBlPack) }} Pack
                                                    @endif
                                                </td>
                                                <td class="text-end"><strong>{{ $formatPacks($totalPacks) }}</strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Hidden input rows for form submission -->
                                <div class="step4-rows-container d-none" data-kerupuk-kg-id="{{ $kerupukKgId }}" data-pack-sku-id="{{ $packSkuId }}">
                                    @foreach($group['rows'] as $row)
                                        @php
                                            $rowWeight = 1.0;
                                            if (!empty($row['kerupuk_kering_item_id']) && !empty($row['kerupuk_packing_item_id'])) {
                                                $configKey = $row['kerupuk_kering_item_id'] . '_' . $row['kerupuk_packing_item_id'];
                                                $rowWeight = $weightConfigurations[$configKey] ?? 1.0;
                                            }
                                            $gl1Pack = (float) ($row['qty_gl1_packing'] ?? 0);
                                            $gl2Pack = (float) ($row['qty_gl2_packing'] ?? 0);
                                            $taPack = (float) ($row['qty_ta_packing'] ?? 0);
                                            $blPack = (float) ($row['qty_bl_packing'] ?? 0);
                                            $gl1Kg = $row['qty_gl1_kg'] ?? ($gl1Pack * $rowWeight);
                                            $gl2Kg = $row['qty_gl2_kg'] ?? ($gl2Pack * $rowWeight);
                                            $taKg = $row['qty_ta_kg'] ?? ($taPack * $rowWeight);
                                            $blKg = $row['qty_bl_kg'] ?? ($blPack * $rowWeight);
                                            $totalPacksRow = $gl1Pack + $gl2Pack + $taPack + $blPack;
                                            $totalKgRow = $gl1Kg + $gl2Kg + $taKg + $blKg;
                                        @endphp
                                        <div class="step4-row" data-index="{{ $row['index'] }}">
                                            <select name="step4[{{ $row['index'] }}][kerupuk_kering_item_id]" class="form-select form-select-sm kerupuk-kg-select" data-row-index="{{ $row['index'] }}" required>
                                                <option value="">Select Kerupuk Kering</option>
                                                @foreach($kerupukKeringOptions as $option)
                                                    <option value="{{ $option['id'] }}" {{ (string) ($row['kerupuk_kering_item_id'] ?? '') === (string) $option['id'] ? 'selected' : '' }}>
                                                        {{ $option['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if(count($group['rows']) > 1)
                                                <select name="step4[{{ $row['index'] }}][kerupuk_packing_item_id]" class="form-select form-select-sm pack-sku-select" data-role="packing-select" data-row-index="{{ $row['index'] }}" required>
                                                    <option value="">Select Pack SKU</option>
                                                    @foreach($allPackingItems ?? $packingItems as $item)
                                                        <option value="{{ $item->id }}"
                                                            data-default-weight="{{ number_format($item->qty_kg_per_pack > 0 ? $item->qty_kg_per_pack : 1, 2, '.', '') }}"
                                                            {{ (string) ($row['kerupuk_packing_item_id'] ?? '') === (string) $item->id ? 'selected' : '' }}>
                                                            {{ $item->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @endif
                                            <input type="text" class="form-control form-control-sm text-end bg-light" data-role="weight-display" value="{{ $formatWeight($rowWeight) }}" readonly tabindex="-1">
                                            <input type="text" class="form-control form-control-sm text-end bg-light" data-kg-field="gl1" value="{{ $formatKg($gl1Kg) }}" readonly tabindex="-1">
                                            @if(count($group['rows']) > 1)
                                                <input type="number" name="step4[{{ $row['index'] }}][qty_gl1_packing]" class="form-control form-control-sm text-end pack-input" data-line="gl1" step="1" min="0" value="{{ $formatPacks($gl1Pack) }}" required>
                                            @endif
                                            <input type="text" class="form-control form-control-sm text-end bg-light" data-kg-field="gl2" value="{{ $formatKg($gl2Kg) }}" readonly tabindex="-1">
                                            @if(count($group['rows']) > 1)
                                                <input type="number" name="step4[{{ $row['index'] }}][qty_gl2_packing]" class="form-control form-control-sm text-end pack-input" data-line="gl2" step="1" min="0" value="{{ $formatPacks($gl2Pack) }}" required>
                                            @endif
                                            <input type="text" class="form-control form-control-sm text-end bg-light" data-kg-field="ta" value="{{ $formatKg($taKg) }}" readonly tabindex="-1">
                                            @if(count($group['rows']) > 1)
                                                <input type="number" name="step4[{{ $row['index'] }}][qty_ta_packing]" class="form-control form-control-sm text-end pack-input" data-line="ta" step="1" min="0" value="{{ $formatPacks($taPack) }}" required>
                                            @endif
                                            <input type="text" class="form-control form-control-sm text-end bg-light" data-kg-field="bl" value="{{ $formatKg($blKg) }}" readonly tabindex="-1">
                                            @if(count($group['rows']) > 1)
                                                <input type="number" name="step4[{{ $row['index'] }}][qty_bl_packing]" class="form-control form-control-sm text-end pack-input" data-line="bl" step="1" min="0" value="{{ $formatPacks($blPack) }}" required>
                                            @endif
                                            <input type="text" class="form-control form-control-sm text-end bg-light" data-total-field="kg" value="{{ $formatKg($totalKgRow) }}" readonly tabindex="-1">
                                            <input type="text" class="form-control form-control-sm text-end bg-light" data-total-field="pack" value="{{ $formatPacks($totalPacksRow) }}" readonly tabindex="-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                                                <i class="far fa-trash"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <!-- Packing Materials Usage Table -->
                                <div class="packing-materials-section" data-kerupuk-kg-id="{{ $kerupukKgId }}" data-pack-sku-id="{{ $packSkuId }}">
                                    <h5 class="h6 mb-2">Packing Materials Usage</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Packing Material</th>
                                                    <th class="text-end">Per Pack</th>
                                                    <th class="text-end">Total Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody class="packing-materials-tbody" data-kerupuk-kg-id="{{ $kerupukKgId }}" data-pack-sku-id="{{ $packSkuId }}">
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">
                                                        <small>Select Pack SKU and enter quantities to see materials</small>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
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
const packConfigurations = @json($packConfigurations);
const weightConfigurations = @json($weightConfigurations);
const allPackingItems = @json(
    ($allPackingItems ?? $packingItems)->map(static fn ($item) => [
        'id' => $item->id,
        'name' => $item->name,
        'weight' => (float) ($item->qty_kg_per_pack > 0 ? $item->qty_kg_per_pack : 1)
    ])
);
let rowIndex = {{ $rowCount }};
let globalMaterialCounter = 0;

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.step4-row').forEach(bindRowEvents);
    // Initialize pack SKU filters based on current selections
    document.querySelectorAll('.kerupuk-kg-select').forEach(filterPackSKUsForRow);
    // Bind display table inputs
    document.querySelectorAll('.pack-sku-section').forEach(bindDisplayTableInputs);
    // Refresh all sections and populate materials
    refreshAllSections();
});

function bindRowEvents(row) {
    row.querySelectorAll('.pack-input').forEach((input) => {
        input.addEventListener('input', () => {
            updateRowCalculations(row);
            syncDisplayTableInputs(row);
            refreshAllSections();
        });
    });

    const kerupukKgSelect = row.querySelector('.kerupuk-kg-select');
    if (kerupukKgSelect) {
        kerupukKgSelect.addEventListener('change', () => {
            filterPackSKUsForRow(kerupukKgSelect);
            updateRowCalculations(row);
            refreshAllSections();
        });
    }

    const packingSelect = row.querySelector('[data-role="packing-select"]');
    if (packingSelect) {
        packingSelect.addEventListener('change', () => {
            updateRowCalculations(row);
            handlePackSkuChange(row);
            refreshAllSections();
        });
    }

    updateRowCalculations(row);
}

// Bind events for display table inputs and Pack SKU select
function bindDisplayTableInputs(section) {
    const displayTable = section.querySelector('table tbody');
    if (!displayTable) return;
    
    // Bind Pack SKU select dropdown
    const packSkuSelect = displayTable.querySelector('.pack-sku-select');
    if (packSkuSelect) {
        packSkuSelect.addEventListener('change', () => {
            const rowIndex = packSkuSelect.dataset.rowIndex;
            const section = packSkuSelect.closest('.pack-sku-section');
            const rowsContainer = section.querySelector('.step4-rows-container');
            const row = rowsContainer.querySelector(`[data-index="${rowIndex}"]`);
            
            if (row) {
                // Update hidden select
                const hiddenSelect = row.querySelector(`[name*="[kerupuk_packing_item_id]"]`);
                if (hiddenSelect) {
                    hiddenSelect.value = packSkuSelect.value;
                    // Trigger change event on hidden select
                    const event = new Event('change');
                    hiddenSelect.dispatchEvent(event);
                }
                
                // Update section's pack SKU ID
                const newPackSkuId = packSkuSelect.value;
                section.setAttribute('data-pack-sku-id', newPackSkuId);
                rowsContainer.setAttribute('data-pack-sku-id', newPackSkuId);
                const materialsSection = section.querySelector('.packing-materials-section');
                const materialsTbody = section.querySelector('.packing-materials-tbody');
                if (materialsSection) materialsSection.setAttribute('data-pack-sku-id', newPackSkuId);
                if (materialsTbody) materialsTbody.setAttribute('data-pack-sku-id', newPackSkuId);
                
                updateRowCalculations(row);
                refreshAllSections();
            }
        });
    }
    
    // Bind Pack quantity inputs
    displayTable.querySelectorAll('.pack-input').forEach((input) => {
        input.addEventListener('input', () => {
            const rowIndex = input.dataset.rowIndex;
            const line = input.dataset.line;
            const section = input.closest('.pack-sku-section');
            const rowsContainer = section.querySelector('.step4-rows-container');
            const row = rowsContainer.querySelector(`[data-index="${rowIndex}"]`);
            
            if (row) {
                // Update hidden input
                const hiddenInput = row.querySelector(`[name*="[qty_${line}_packing]"]`);
                if (hiddenInput) {
                    hiddenInput.value = input.value;
                }
                
                updateRowCalculations(row);
                refreshAllSections();
            }
        });
    });
}

function syncDisplayTableInputs(row) {
    const rowIndex = row.dataset.index;
    const section = row.closest('.pack-sku-section');
    if (!section) return;
    
    const displayTable = section.querySelector('table tbody');
    if (!displayTable) return;
    
    ['gl1', 'gl2', 'ta', 'bl'].forEach((line) => {
        const hiddenInput = row.querySelector(`[name*="[qty_${line}_packing]"]`);
        const displayInput = displayTable.querySelector(`.pack-input[data-line="${line}"][data-row-index="${rowIndex}"]`);
        
        if (hiddenInput && displayInput) {
            displayInput.value = hiddenInput.value;
        }
    });
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
            option.dataset.defaultWeight = item.weight.toFixed(2);
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
                    option.dataset.defaultWeight = item.weight.toFixed(2);
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
    const sectionsContainer = document.getElementById('step4-sections');
    if (!sectionsContainer) return;
    
    // Find the first unassigned section or create a new one
    let targetSection = sectionsContainer.querySelector('[data-pack-sku-id^="unassigned_"]');
    if (!targetSection) {
        // Create a new unassigned section
        const firstSection = sectionsContainer.querySelector('.pack-sku-section');
        if (!firstSection) return;
        
        targetSection = firstSection.cloneNode(true);
        const newPackSkuId = 'unassigned_' + rowIndex;
        targetSection.setAttribute('data-pack-sku-id', newPackSkuId);
        targetSection.querySelector('.packing-materials-section').setAttribute('data-pack-sku-id', newPackSkuId);
        targetSection.querySelector('.packing-materials-tbody').setAttribute('data-pack-sku-id', newPackSkuId);
        targetSection.querySelector('.step4-rows-container').setAttribute('data-pack-sku-id', newPackSkuId);
        targetSection.querySelector('h4').innerHTML = '<span class="text-muted">Unassigned Pack SKU</span>';
        sectionsContainer.appendChild(targetSection);
    }
    
    const rowsContainer = targetSection.querySelector('.step4-rows-container');
    const firstRow = rowsContainer.querySelector('.step4-row');
    if (!firstRow) return;
    
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
                input.value = '0';
            } else if (input.dataset.role === 'weight-display') {
                input.value = '1.00';
            } else if (input.dataset.kgField || input.dataset.totalField === 'kg') {
                input.value = '0.00';
            } else if (input.dataset.totalField === 'pack') {
                input.value = '0';
            }
        }
    });
    
    rowsContainer.appendChild(newRow);
    bindRowEvents(newRow);
    refreshAllSections();
    rowIndex++;
}

function removeRow(button) {
    const row = button.closest('.step4-row');
    if (!row) return;
    
    const rowsContainer = row.closest('.step4-rows-container');
    const rows = rowsContainer.querySelectorAll('.step4-row');
    
    if (rows.length <= 1) {
        alert('At least one row is required per Pack SKU section.');
        return;
    }
    
    row.remove();
    refreshAllSections();
}

function updateRowCalculations(row) {
    // Get weight from configuration
    const kerupukKgSelect = row.querySelector('.kerupuk-kg-select');
    const packingSelect = row.querySelector('[data-role="packing-select"]');
    const kerupukKgId = kerupukKgSelect?.value;
    const packingId = packingSelect?.value;
    
    let weight = 1.0;
    if (kerupukKgId && packingId) {
        const configKey = kerupukKgId + '_' + packingId;
        weight = weightConfigurations[configKey] || 1.0;
    }
    
    // Update weight display
    const weightDisplay = row.querySelector('[data-role="weight-display"]');
    if (weightDisplay) {
        weightDisplay.value = weight.toFixed(2);
    }

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
            kgField.value = kgValue.toFixed(2);
        }
    });

    const totalPackField = row.querySelector('[data-total-field="pack"]');
    const totalKgField = row.querySelector('[data-total-field="kg"]');

    if (totalPackField) {
        totalPackField.value = totalPacks.toFixed(0);
    }
    if (totalKgField) {
        totalKgField.value = totalKg.toFixed(2);
    }
}

function updatePackSkuSection(section) {
    if (!section) return;
    
    const rowsContainer = section.querySelector('.step4-rows-container');
    if (!rowsContainer) return;
    
    const packSkuId = section.getAttribute('data-pack-sku-id');
    const rows = rowsContainer.querySelectorAll('.step4-row');
    
    // Aggregate totals from all rows in this section
    let totalGl1Kg = 0, totalGl1Pack = 0;
    let totalGl2Kg = 0, totalGl2Pack = 0;
    let totalTaKg = 0, totalTaPack = 0;
    let totalBlKg = 0, totalBlPack = 0;
    let weight = 1.0;
    let packSkuName = null;
    
    rows.forEach((r) => {
        const packInput = r.querySelector('[data-line="gl1"]');
        const gl1Pack = packInput ? parseFloat(packInput.value || '0') || 0 : 0;
        const packInput2 = r.querySelector('[data-line="gl2"]');
        const gl2Pack = packInput2 ? parseFloat(packInput2.value || '0') || 0 : 0;
        const packInput3 = r.querySelector('[data-line="ta"]');
        const taPack = packInput3 ? parseFloat(packInput3.value || '0') || 0 : 0;
        const packInput4 = r.querySelector('[data-line="bl"]');
        const blPack = packInput4 ? parseFloat(packInput4.value || '0') || 0 : 0;
        
        const gl1Kg = parseFloat(r.querySelector('[data-kg-field="gl1"]')?.value || '0') || 0;
        const gl2Kg = parseFloat(r.querySelector('[data-kg-field="gl2"]')?.value || '0') || 0;
        const taKg = parseFloat(r.querySelector('[data-kg-field="ta"]')?.value || '0') || 0;
        const blKg = parseFloat(r.querySelector('[data-kg-field="bl"]')?.value || '0') || 0;
        
        totalGl1Kg += gl1Kg;
        totalGl1Pack += gl1Pack;
        totalGl2Kg += gl2Kg;
        totalGl2Pack += gl2Pack;
        totalTaKg += taKg;
        totalTaPack += taPack;
        totalBlKg += blKg;
        totalBlPack += blPack;
        
        // Get weight and pack SKU name from first row
        if (!packSkuName) {
            const weightDisplay = r.querySelector('[data-role="weight-display"]');
            if (weightDisplay) {
                weight = parseFloat(weightDisplay.value || '1') || 1.0;
            }
            
            const packingSelect = r.querySelector('[data-role="packing-select"]');
            if (packingSelect && packingSelect.value) {
                const selectedOption = packingSelect.options[packingSelect.selectedIndex];
                packSkuName = selectedOption ? selectedOption.text : null;
            }
        }
    });
    
    const totalKg = totalGl1Kg + totalGl2Kg + totalTaKg + totalBlKg;
    const totalPacks = totalGl1Pack + totalGl2Pack + totalTaPack + totalBlPack;
    
    // Update the display table
    const displayTable = section.querySelector('table tbody');
    if (displayTable) {
        const tableRows = displayTable.querySelectorAll('tr');
        if (tableRows.length >= 2) {
            // Update Pack SKU select value if exists
            const packSkuSelect = displayTable.querySelector('.pack-sku-select');
            if (packSkuSelect && rows.length === 1) {
                const firstRow = rows[0];
                const hiddenSelect = firstRow.querySelector('[data-role="packing-select"]');
                if (hiddenSelect && hiddenSelect.value) {
                    packSkuSelect.value = hiddenSelect.value;
                }
            }
            
            // Update Kg row
            const kgRow = tableRows[0];
            const kgCells = kgRow.querySelectorAll('td');
            if (kgCells.length >= 7) {
                kgCells[1].textContent = weight.toFixed(2) + ' Kg';
                kgCells[2].textContent = totalGl1Kg.toFixed(2) + ' Kg';
                kgCells[3].textContent = totalGl2Kg.toFixed(2) + ' Kg';
                kgCells[4].textContent = totalTaKg.toFixed(2) + ' Kg';
                kgCells[5].textContent = totalBlKg.toFixed(2) + ' Kg';
                kgCells[6].innerHTML = '<strong>' + totalKg.toFixed(2) + ' Kg</strong>';
            }
            
            // Update Pack row - keep inputs if single row, update values
            const packRow = tableRows[1];
            const packCells = packRow.querySelectorAll('td');
            if (packCells.length >= 7) {
                packCells[1].textContent = weight.toFixed(2) + ' Kg';
                
                // Update Pack inputs or text
                if (rows.length === 1) {
                    const firstRow = rows[0];
                    const rowIndex = firstRow.dataset.index;
                    ['gl1', 'gl2', 'ta', 'bl'].forEach((line, idx) => {
                        const cell = packCells[idx + 2];
                        const packValue = line === 'gl1' ? totalGl1Pack : line === 'gl2' ? totalGl2Pack : line === 'ta' ? totalTaPack : totalBlPack;
                        const existingInput = cell.querySelector('.pack-input');
                        if (existingInput) {
                            existingInput.value = packValue.toFixed(0);
                        } else {
                            cell.innerHTML = `<input type="number" name="step4[${rowIndex}][qty_${line}_packing]" class="form-control form-control-sm text-end pack-input d-inline-block" style="width: 80px;" data-line="${line}" data-row-index="${rowIndex}" step="1" min="0" value="${packValue.toFixed(0)}" required><span> Pack</span>`;
                            bindDisplayTableInputs(section);
                        }
                    });
                } else {
                    packCells[2].textContent = totalGl1Pack.toFixed(0) + ' Pack';
                    packCells[3].textContent = totalGl2Pack.toFixed(0) + ' Pack';
                    packCells[4].textContent = totalTaPack.toFixed(0) + ' Pack';
                    packCells[5].textContent = totalBlPack.toFixed(0) + ' Pack';
                }
                packCells[6].innerHTML = '<strong>' + totalPacks.toFixed(0) + '</strong>';
            }
        }
    }
    
    // Update materials preview for this section
    refreshMaterialsForSection(section);
}

function handlePackSkuChange(row) {
    updateRowCalculations(row);
    
    const rowsContainer = row.closest('.step4-rows-container');
    if (!rowsContainer) return;
    
    const section = rowsContainer.closest('.pack-sku-section');
    if (!section) return;
    
    const packingSelect = row.querySelector('[data-role="packing-select"]');
    const newPackSkuId = packingSelect ? packingSelect.value : null;
    
    if (newPackSkuId) {
        // Update section's pack SKU ID
        section.setAttribute('data-pack-sku-id', newPackSkuId);
        rowsContainer.setAttribute('data-pack-sku-id', newPackSkuId);
        const materialsSection = section.querySelector('.packing-materials-section');
        const materialsTbody = section.querySelector('.packing-materials-tbody');
        if (materialsSection) materialsSection.setAttribute('data-pack-sku-id', newPackSkuId);
        if (materialsTbody) materialsTbody.setAttribute('data-pack-sku-id', newPackSkuId);
    }
}

function refreshMaterialsForSection(section) {
    const packSkuId = section.getAttribute('data-pack-sku-id');
    const materialsTbody = section.querySelector('.packing-materials-tbody');
    if (!materialsTbody) return;
    
    const rowsContainer = section.querySelector('.step4-rows-container');
    const rows = rowsContainer.querySelectorAll('.step4-row');
    
    // Calculate total packs for this Pack SKU
    let totalPacks = 0;
    let firstRowIndex = null;
    
    rows.forEach((row) => {
        const totalPackField = row.querySelector('[data-total-field="pack"]');
        const packs = parseFloat(totalPackField?.value ?? '0') || 0;
        totalPacks += packs;
        if (!firstRowIndex) {
            firstRowIndex = row.dataset.index;
        }
    });
    
    if (!packSkuId || packSkuId.startsWith('unassigned_') || totalPacks <= 0) {
        materialsTbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted"><small>Select Pack SKU and enter quantities to see materials</small></td></tr>';
        return;
    }
    
    // Get blueprints for this Pack SKU
    const packingIdKey = String(packSkuId);
    const blueprints = packingBlueprints[packingIdKey] || packingBlueprints[Number(packSkuId)] || null;
    
    if (!blueprints || blueprints.length === 0) {
        materialsTbody.innerHTML = '<tr><td colspan="3" class="text-center text-warning"><small>No packing material blueprints configured for this Pack SKU</small></td></tr>';
        return;
    }
    
    // Build materials table
    let html = '';
    
    blueprints.forEach((material) => {
        const quantityPerPack = material.quantity_per_pack || 0;
        const quantityTotal = quantityPerPack * totalPacks;
        const currentCounter = globalMaterialCounter++;
        
        html += `
            <tr>
                <td>
                    <strong>${material.packing_material_item_name}</strong>
                    <input type="hidden" name="materials[${currentCounter}][production_plan_step4_row_index]" value="${firstRowIndex}">
                    <input type="hidden" name="materials[${currentCounter}][packing_material_item_id]" value="${material.packing_material_item_id}">
                    <input type="hidden" name="materials[${currentCounter}][pack_sku_id]" value="${packSkuId}">
                </td>
                <td class="text-end">${quantityPerPack.toFixed(1)}</td>
                <td class="text-end">
                    <input type="number" 
                        name="materials[${currentCounter}][quantity_total]" 
                        class="form-control form-control-sm text-end" 
                        value="${Math.round(quantityTotal)}" 
                        step="1" 
                        min="0"
                        style="max-width: 120px; margin-left: auto;">
                </td>
            </tr>`;
    });
    
    materialsTbody.innerHTML = html;
}

function refreshAllSections() {
    globalMaterialCounter = 0; // Reset counter
    document.querySelectorAll('.pack-sku-section').forEach((section) => {
        updatePackSkuSection(section);
        bindDisplayTableInputs(section);
    });
}
</script>
@endpush
@endsection


















