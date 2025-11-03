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
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Step 4: Packing Planning from Kerupuk Kering</h3>
                    <div class="card-actions">
                        <button type="button" class="btn btn-sm btn-primary" onclick="addStep4Row()">
                            <i class="far fa-plus"></i>&nbsp;Add Row
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="far fa-info-circle me-2"></i>
                        This step converts Kerupuk Kering (Kg) quantities to Packing quantities using weight per unit.
                        @if(count($calculatedData) > 0)
                        Auto-calculated values are pre-filled below. You can adjust them as needed.
                        @endif
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-vcenter" id="step4-table">
                            <thead>
                                <tr>
                                    <th>Kerupuk Kering Item</th>
                                    <th>Packing Item</th>
                                    <th>Weight/Unit (kg)</th>
                                    <th>GL1 Kg</th>
                                    <th>GL1 Pack</th>
                                    <th>GL2 Kg</th>
                                    <th>GL2 Pack</th>
                                    <th>TA Kg</th>
                                    <th>TA Pack</th>
                                    <th>BL Kg</th>
                                    <th>BL Pack</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody id="step4-tbody">
                                @if(count($calculatedData) > 0)
                                    @foreach($calculatedData as $index => $data)
                                    <tr class="step4-row">
                                        <td>
                                            <select name="step4[{{ $index }}][kerupuk_kering_item_id]" class="form-select" required>
                                                <option value="">Select Kerupuk Kering</option>
                                                @foreach($productionPlan->step3->unique('kerupuk_kering_item_id') as $step3)
                                                <option value="{{ $step3->kerupuk_kering_item_id }}" {{ $data['kerupuk_kering_item_id'] == $step3->kerupuk_kering_item_id ? 'selected' : '' }}>
                                                    {{ $step3->kerupukKeringItem->name ?? 'N/A' }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="step4[{{ $index }}][kerupuk_packing_item_id]" class="form-select" required>
                                                <option value="">Select Packing Item</option>
                                                @php
                                                    $packingItems = \App\Models\Item::whereHas('itemCategory', function($q) {
                                                        $q->where('name', 'like', '%Finished Products%');
                                                    })->where('is_active', true)->get();
                                                @endphp
                                                @foreach($packingItems as $item)
                                                <option value="{{ $item->id }}" {{ $data['kerupuk_packing_item_id'] == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" name="step4[{{ $index }}][weight_per_unit]" class="form-control" step="0.001" min="0.001" value="{{ $data['weight_per_unit'] ?? 1 }}" required></td>
                                        <td><input type="number" name="step4[{{ $index }}][qty_gl1_kg]" class="form-control" step="0.001" min="0" value="{{ $data['qty_gl1_kg'] ?? 0 }}" required></td>
                                        <td><input type="number" name="step4[{{ $index }}][qty_gl1_packing]" class="form-control" step="0.001" min="0" value="{{ $data['qty_gl1_packing'] ?? 0 }}" required></td>
                                        <td><input type="number" name="step4[{{ $index }}][qty_gl2_kg]" class="form-control" step="0.001" min="0" value="{{ $data['qty_gl2_kg'] ?? 0 }}" required></td>
                                        <td><input type="number" name="step4[{{ $index }}][qty_gl2_packing]" class="form-control" step="0.001" min="0" value="{{ $data['qty_gl2_packing'] ?? 0 }}" required></td>
                                        <td><input type="number" name="step4[{{ $index }}][qty_ta_kg]" class="form-control" step="0.001" min="0" value="{{ $data['qty_ta_kg'] ?? 0 }}" required></td>
                                        <td><input type="number" name="step4[{{ $index }}][qty_ta_packing]" class="form-control" step="0.001" min="0" value="{{ $data['qty_ta_packing'] ?? 0 }}" required></td>
                                        <td><input type="number" name="step4[{{ $index }}][qty_bl_kg]" class="form-control" step="0.001" min="0" value="{{ $data['qty_bl_kg'] ?? 0 }}" required></td>
                                        <td><input type="number" name="step4[{{ $index }}][qty_bl_packing]" class="form-control" step="0.001" min="0" value="{{ $data['qty_bl_packing'] ?? 0 }}" required></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                                                <i class="far fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    @foreach($productionPlan->step4 as $index => $step4)
                                    <tr class="step4-row">
                                        <td>
                                            <select name="step4[{{ $index }}][kerupuk_kering_item_id]" class="form-select" required>
                                                <option value="">Select Kerupuk Kering</option>
                                                @foreach($productionPlan->step3->unique('kerupuk_kering_item_id') as $step3)
                                                <option value="{{ $step3->kerupuk_kering_item_id }}" {{ $step4->kerupuk_kering_item_id == $step3->kerupuk_kering_item_id ? 'selected' : '' }}>
                                                    {{ $step3->kerupukKeringItem->name ?? 'N/A' }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="step4[{{ $index }}][kerupuk_packing_item_id]" class="form-select" required>
                                                <option value="">Select Packing Item</option>
                                                @php
                                                    $packingItems = \App\Models\Item::whereHas('itemCategory', function($q) {
                                                        $q->where('name', 'like', '%Finished Products%');
                                                    })->where('is_active', true)->get();
                                                @endphp
                                                @foreach($packingItems as $item)
                                                <option value="{{ $item->id }}" {{ $step4->kerupuk_packing_item_id == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" name="step4[{{ $index }}][weight_per_unit]" class="form-control" step="0.001" min="0.001" value="{{ $step4->weight_per_unit }}" required></td>
                                        <td><input type="number" name="step4[{{ $index }}][qty_gl1_kg]" class="form-control" step="0.001" min="0" value="{{ $step4->qty_gl1_kg }}" required></td>
                                        <td><input type="number" name="step4[{{ $index }}][qty_gl1_packing]" class="form-control" step="0.001" min="0" value="{{ $step4->qty_gl1_packing }}" required></td>
                                        <td><input type="number" name="step4[{{ $index }}][qty_gl2_kg]" class="form-control" step="0.001" min="0" value="{{ $step4->qty_gl2_kg }}" required></td>
                                        <td><input type="number" name="step4[{{ $index }}][qty_gl2_packing]" class="form-control" step="0.001" min="0" value="{{ $step4->qty_gl2_packing }}" required></td>
                                        <td><input type="number" name="step4[{{ $index }}][qty_ta_kg]" class="form-control" step="0.001" min="0" value="{{ $step4->qty_ta_kg }}" required></td>
                                        <td><input type="number" name="step4[{{ $index }}][qty_ta_packing]" class="form-control" step="0.001" min="0" value="{{ $step4->qty_ta_packing }}" required></td>
                                        <td><input type="number" name="step4[{{ $index }}][qty_bl_kg]" class="form-control" step="0.001" min="0" value="{{ $step4->qty_bl_kg }}" required></td>
                                        <td><input type="number" name="step4[{{ $index }}][qty_bl_packing]" class="form-control" step="0.001" min="0" value="{{ $step4->qty_bl_packing }}" required></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                                                <i class="far fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @if($productionPlan->step4->count() === 0)
                                    <tr class="step4-row">
                                        <td colspan="12" class="text-center text-muted">Click "Add Row" to add Step 4 data</td>
                                    </tr>
                                    @endif
                                @endif
                            </tbody>
                        </table>
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
let rowIndex = {{ count($calculatedData) > 0 ? count($calculatedData) : $productionPlan->step4->count() }};

function addStep4Row() {
    const tbody = document.getElementById('step4-tbody');
    const firstRow = tbody.querySelector('.step4-row');
    if (!firstRow || firstRow.querySelector('td').textContent.includes('Click')) {
        tbody.innerHTML = '';
    }
    const row = firstRow ? firstRow.cloneNode(true) : createEmptyRow();
    row.querySelectorAll('[name]').forEach(input => {
        const name = input.getAttribute('name');
        if (name) input.setAttribute('name', name.replace(/\[(\d+)\]/, `[${rowIndex}]`));
    });
    row.querySelectorAll('input[type="number"]').forEach(input => {
        if (input.name.includes('weight_per_unit')) {
            input.value = '1';
        } else {
            input.value = '0';
        }
    });
    row.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
    tbody.appendChild(row);
    rowIndex++;
}

function createEmptyRow() {
    const row = document.createElement('tr');
    row.className = 'step4-row';
    row.innerHTML = '<td><select name="step4[0][kerupuk_kering_item_id]" class="form-select" required><option value="">Select Kerupuk Kering</option></select></td><td><select name="step4[0][kerupuk_packing_item_id]" class="form-select" required><option value="">Select Packing Item</option></select></td><td><input type="number" name="step4[0][weight_per_unit]" class="form-control" step="0.001" min="0.001" value="1" required></td><td><input type="number" name="step4[0][qty_gl1_kg]" class="form-control" step="0.001" min="0" value="0" required></td><td><input type="number" name="step4[0][qty_gl1_packing]" class="form-control" step="0.001" min="0" value="0" required></td><td><input type="number" name="step4[0][qty_gl2_kg]" class="form-control" step="0.001" min="0" value="0" required></td><td><input type="number" name="step4[0][qty_gl2_packing]" class="form-control" step="0.001" min="0" value="0" required></td><td><input type="number" name="step4[0][qty_ta_kg]" class="form-control" step="0.001" min="0" value="0" required></td><td><input type="number" name="step4[0][qty_ta_packing]" class="form-control" step="0.001" min="0" value="0" required></td><td><input type="number" name="step4[0][qty_bl_kg]" class="form-control" step="0.001" min="0" value="0" required></td><td><input type="number" name="step4[0][qty_bl_packing]" class="form-control" step="0.001" min="0" value="0" required></td><td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="far fa-trash"></i></button></td>';
    return row;
}

function removeRow(button) {
    if (document.getElementById('step4-tbody').querySelectorAll('.step4-row').length > 1) {
        button.closest('.step4-row').remove();
    } else {
        alert('At least one row is required.');
    }
}
</script>
@endpush
@endsection

