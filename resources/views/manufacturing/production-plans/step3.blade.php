@extends('layouts.app')

@section('title', 'Step 3: Kerupuk Kering Planning')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/tabler/libs/tom-select/dist/css/tom-select.bootstrap5.min.css') }}" />
<style>
    /* Ensure select elements have proper width before TomSelect initializes */
    .gelondongan-select {
        width: 100% !important;
        min-width: 200px !important;
    }

    .kerupuk-kering-select {
        width: 100% !important;
        min-width: 200px !important;
    }
    
    /* Fix Tom Select sizing to match Bootstrap form controls */
    .ts-control {
        min-height: calc(1.5em + 0.75rem + 2px) !important;
        padding: 0.375rem 0.75rem !important;
        font-size: 0.875rem !important;
        line-height: 1.5 !important;
        border: 1px solid #dadce0 !important;
        border-radius: 4px !important;
        background-color: #fff !important;
        display: flex !important;
        align-items: center !important;
        width: 100% !important;
    }
    
    .ts-control.single .ts-control-input {
        height: auto !important;
        flex: 1 !important;
        display: flex !important;
        align-items: center !important;
    }
    
    .ts-control.single .ts-control-input input {
        height: auto !important;
        line-height: 1.5 !important;
        border: none !important;
        background: transparent !important;
        padding: 0 !important;
        margin: 0 !important;
        font-size: 0.875rem !important;
    }
    
    .ts-wrapper {
        width: 100% !important;
        display: block !important;
    }
    
    /* Fix Tom Select dropdown background and readability */
    .ts-dropdown {
        background-color: #ffffff !important;
        border: 1px solid #dadce0 !important;
        border-radius: 4px !important;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        opacity: 1 !important;
        max-height: 300px !important;
        overflow-y: auto !important;
    }
    
    .ts-dropdown .ts-dropdown-content {
        background-color: #ffffff !important;
        max-height: 300px !important;
        overflow-y: auto !important;
    }
    
    .ts-dropdown .option {
        background-color: #ffffff !important;
        color: #212529 !important;
        padding: 0.375rem 0.75rem !important;
        cursor: pointer !important;
    }
    
    .ts-dropdown .option:hover,
    .ts-dropdown .option.selected {
        background-color: #e9ecef !important;
        color: #212529 !important;
    }
    
    .ts-dropdown .option.active {
        background-color: #0d6efd !important;
        color: #ffffff !important;
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
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.production-plans.index') }}">Production Plans</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.production-plans.show', $productionPlan) }}">Plan Details</a></li>
                        <li class="breadcrumb-item active">Step 3</li>
                    </ol>
                </nav>
                <h2 class="page-title">Step 3: Kerupuk Kering Production Planning</h2>
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
        
        <form method="POST" action="{{ route('manufacturing.production-plans.step3.store', $productionPlan) }}">
            @csrf
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Step 3: Kerupuk Kering Planning from Gld</h3>
                    <div class="card-actions">
                        <button type="button" class="btn btn-sm btn-primary" onclick="addStep3Row()">
                            <i class="far fa-plus"></i>&nbsp;Add Row
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="far fa-info-circle me-2"></i>
                        This step converts Gld quantities to Kerupuk Kering (Kg) quantities using yield guidelines.
                        @if(count($calculatedData) > 0)
                        Auto-calculated values are pre-filled below. You can adjust them as needed.
                        @elseif($productionPlan->step3->count() === 0 && $productionPlan->step2->count() > 0)
                        <strong>Note:</strong> Auto-calculation could not be performed. This may be because:
                        <ul class="mb-0 mt-2">
                            <li>No matching Kerupuk Kering items (from "Kerupuk Kg" category) were found for the Gelondongan items in Step 2</li>
                            <li>Yield guidelines are missing for the Gelondongan → Kerupuk Kering conversion</li>
                        </ul>
                        Please add rows manually and ensure yield guidelines are configured.
                        @elseif($productionPlan->step2->count() === 0)
                        <strong>Warning:</strong> Step 2 data is missing. Please complete Step 2 first.
                        @endif
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table" id="step3-table">
                            <thead>
                                <tr>
                                    <th>Gld Item</th>
                                    <th>Kerupuk Kering Item</th>
                                    <th width="150">GL1</th>
                                    <th width="150">GL2</th>
                                    <th width="150">TA</th>
                                    <th width="150">BL</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody id="step3-tbody">
                                @if(count($calculatedData) > 0)
                                    @foreach($calculatedData as $index => $data)
                                    <tr class="step3-row">
                                        <td>
                                            <select name="step3[{{ $index }}][gelondongan_item_id]" class="form-select gelondongan-select" id="gelondongan-select-{{ $index }}" data-row-index="{{ $index }}" required>
                                                <option value="">Select Gld</option>
                                                @foreach($productionPlan->step2->unique('gelondongan_item_id') as $step2)
                                                <option value="{{ $step2->gelondongan_item_id }}" {{ $data['gelondongan_item_id'] == $step2->gelondongan_item_id ? 'selected' : '' }}>
                                                    {{ $step2->gelondonganItem->name ?? 'N/A' }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="step3[{{ $index }}][kerupuk_kering_item_id]" class="form-select kerupuk-kering-select" id="kerupuk-kering-select-{{ $index }}" data-row-index="{{ $index }}" required>
                                                <option value="">Select Kerupuk Kering</option>
                                                @php
                                                    $kerupukItems = \App\Models\Item::whereHas('itemCategory', function($q) {
                                                        $q->where('name', 'like', '%Kerupuk Kg%');
                                                    })->where('is_active', true)->get();
                                                @endphp
                                                @foreach($kerupukItems as $item)
                                                <option value="{{ $item->id }}" {{ $data['kerupuk_kering_item_id'] == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            Gld: <input type="number" name="step3[{{ $index }}][qty_gl1_gelondongan]" class="form-control" step="0.001" min="0" value="{{ $data['qty_gl1_gelondongan'] ?? 0 }}" readonly required><br />
                                            Kg: <input type="number" name="step3[{{ $index }}][qty_gl1_kg]" class="form-control" step="0.001" min="0" value="{{ $data['qty_gl1_kg'] ?? 0 }}" required>
                                        </td>
                                        <td>
                                            Gld: <input type="number" name="step3[{{ $index }}][qty_gl2_gelondongan]" class="form-control" step="0.001" min="0" value="{{ $data['qty_gl2_gelondongan'] ?? 0 }}" readonly required><br />
                                            Kg: <input type="number" name="step3[{{ $index }}][qty_gl2_kg]" class="form-control" step="0.001" min="0" value="{{ $data['qty_gl2_kg'] ?? 0 }}" required>
                                        </td>
                                        <td>
                                            Gld: <input type="number" name="step3[{{ $index }}][qty_ta_gelondongan]" class="form-control" step="0.001" min="0" value="{{ $data['qty_ta_gelondongan'] ?? 0 }}" readonly required><br />
                                            Kg: <input type="number" name="step3[{{ $index }}][qty_ta_kg]" class="form-control" step="0.001" min="0" value="{{ $data['qty_ta_kg'] ?? 0 }}" required>
                                        </td>
                                        <td>
                                            Gld: <input type="number" name="step3[{{ $index }}][qty_bl_gelondongan]" class="form-control" step="0.001" min="0" value="{{ $data['qty_bl_gelondongan'] ?? 0 }}" readonly required><br />
                                            Kg: <input type="number" name="step3[{{ $index }}][qty_bl_kg]" class="form-control" step="0.001" min="0" value="{{ $data['qty_bl_kg'] ?? 0 }}" required>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                                                <i class="far fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    @foreach($productionPlan->step3 as $index => $step3)
                                    <tr class="step3-row">
                                        <td>
                                            <select name="step3[{{ $index }}][gelondongan_item_id]" class="form-select gelondongan-select" id="gelondongan-select-{{ $index }}" data-row-index="{{ $index }}" required>
                                                <option value="">Select Gld</option>
                                                @foreach($productionPlan->step2->unique('gelondongan_item_id') as $step2)
                                                <option value="{{ $step2->gelondongan_item_id }}" {{ $step3->gelondongan_item_id == $step2->gelondongan_item_id ? 'selected' : '' }}>
                                                    {{ $step2->gelondonganItem->name ?? 'N/A' }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="step3[{{ $index }}][kerupuk_kering_item_id]" class="form-select kerupuk-kering-select" id="kerupuk-kering-select-{{ $index }}" data-row-index="{{ $index }}" required>
                                                <option value="">Select Kerupuk Kering</option>
                                                @php
                                                    $kerupukItems = \App\Models\Item::whereHas('itemCategory', function($q) {
                                                        $q->where('name', 'like', '%Kerupuk Kg%');
                                                    })->where('is_active', true)->get();
                                                @endphp
                                                @foreach($kerupukItems as $item)
                                                <option value="{{ $item->id }}" {{ $step3->kerupuk_kering_item_id == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            Gld: <input type="number" name="step3[{{ $index }}][qty_gl1_gelondongan]" class="form-control" step="0.001" min="0" value="{{ $step3->qty_gl1_gelondongan }}" readonly required><br />
                                            Kg: <input type="number" name="step3[{{ $index }}][qty_gl1_kg]" class="form-control" step="0.001" min="0" value="{{ $step3->qty_gl1_kg }}" required>
                                        </td>
                                        <td>
                                            Gld: <input type="number" name="step3[{{ $index }}][qty_gl2_gelondongan]" class="form-control" step="0.001" min="0" value="{{ $step3->qty_gl2_gelondongan }}" readonly required><br />
                                            Kg: <input type="number" name="step3[{{ $index }}][qty_gl2_kg]" class="form-control" step="0.001" min="0" value="{{ $step3->qty_gl2_kg }}" required>
                                        </td>
                                        <td>
                                            Gld: <input type="number" name="step3[{{ $index }}][qty_ta_gelondongan]" class="form-control" step="0.001" min="0" value="{{ $step3->qty_ta_gelondongan }}" readonly required><br />
                                            Kg: <input type="number" name="step3[{{ $index }}][qty_ta_kg]" class="form-control" step="0.001" min="0" value="{{ $step3->qty_ta_kg }}" required>
                                        </td>
                                        <td>
                                            Gld: <input type="number" name="step3[{{ $index }}][qty_bl_gelondongan]" class="form-control" step="0.001" min="0" value="{{ $step3->qty_bl_gelondongan }}" readonly required><br />
                                            Kg: <input type="number" name="step3[{{ $index }}][qty_bl_kg]" class="form-control" step="0.001" min="0" value="{{ $step3->qty_bl_kg }}" required>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                                                <i class="far fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @if($productionPlan->step3->count() === 0)
                                    <tr class="step3-row">
                                        <td colspan="11" class="text-center text-muted">Click "Add Row" to add Step 3 data</td>
                                    </tr>
                                    @endif
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('manufacturing.production-plans.show', $productionPlan) }}" class="btn btn-link">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Step 3</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/tabler/libs/tom-select/dist/js/tom-select.complete.min.js') }}"></script>
<script>
let rowIndex = {{ count($calculatedData) > 0 ? count($calculatedData) : $productionPlan->step3->count() }};
const tomSelectInstances = new Map(); // Store TomSelect instances by row index

// Store dropdown options for creating new rows
const gelondonganOptions = {!! json_encode($productionPlan->step2->unique('gelondongan_item_id')->map(function($step2) {
    return [
        'id' => $step2->gelondongan_item_id,
        'name' => $step2->gelondonganItem->name ?? 'N/A'
    ];
})->values()->toArray()) !!};

@php
$kerupukItems = \App\Models\Item::whereHas('itemCategory', function($q) {
    $q->where('name', 'like', '%Kerupuk Kg%');
})->where('is_active', true)->get();
@endphp
const kerupukKeringOptions = {!! json_encode($kerupukItems->map(function($item) {
    return [
        'id' => $item->id,
        'name' => $item->name
    ];
})->values()->toArray()) !!};

function initializeTomSelectsForRow(rowIndex) {
    const gelondonganSelect = document.getElementById(`gelondongan-select-${rowIndex}`);
    const kerupukSelect = document.getElementById(`kerupuk-kering-select-${rowIndex}`);
    
    if (!gelondonganSelect || !kerupukSelect || typeof TomSelect === 'undefined') {
        return;
    }
    
    // Clean up Gelondongan TomSelect
    if (gelondonganSelect.tomselect) {
        try {
            gelondonganSelect.tomselect.destroy();
        } catch(e) {}
    }
    gelondonganSelect.classList.remove('tomselected');
    
    // Remove any broken wrappers for gelondongan select
    const gelondonganWrapper = gelondonganSelect.closest('.ts-wrapper');
    if (gelondonganWrapper && (gelondonganWrapper.offsetWidth === 0 || gelondonganWrapper.style.display === 'none')) {
        gelondonganWrapper.remove();
    }
    
    // Clean up Kerupuk Kering TomSelect
    if (kerupukSelect.tomselect) {
        try {
            kerupukSelect.tomselect.destroy();
        } catch(e) {}
    }
    kerupukSelect.classList.remove('tomselected');
    
    // Remove any broken wrappers for kerupuk select
    const kerupukWrapper = kerupukSelect.closest('.ts-wrapper');
    if (kerupukWrapper && (kerupukWrapper.offsetWidth === 0 || kerupukWrapper.style.display === 'none')) {
        kerupukWrapper.remove();
    }
    
    // Clean up existing instances from our Map
    if (tomSelectInstances.has(`gelondongan-${rowIndex}`)) {
        try {
            tomSelectInstances.get(`gelondongan-${rowIndex}`).destroy();
        } catch(e) {}
        tomSelectInstances.delete(`gelondongan-${rowIndex}`);
    }
    if (tomSelectInstances.has(`kerupuk-${rowIndex}`)) {
        try {
            tomSelectInstances.get(`kerupuk-${rowIndex}`).destroy();
        } catch(e) {}
        tomSelectInstances.delete(`kerupuk-${rowIndex}`);
    }
    
    // Initialize Gelondongan TomSelect if not already wrapped
    if (!gelondonganSelect.closest('.ts-wrapper')) {
        const gelondonganTomSelect = new TomSelect(`#gelondongan-select-${rowIndex}`, {
            allowEmptyOption: true,
            placeholder: 'Select Gld',
            sortField: {
                field: 'text',
                direction: 'asc'
            },
            dropdownParent: 'body'
        });
        tomSelectInstances.set(`gelondongan-${rowIndex}`, gelondonganTomSelect);
    }
    
    // Initialize Kerupuk Kering TomSelect if not already wrapped
    if (!kerupukSelect.closest('.ts-wrapper')) {
        const kerupukTomSelect = new TomSelect(`#kerupuk-kering-select-${rowIndex}`, {
            allowEmptyOption: true,
            placeholder: 'Select Kerupuk Kering',
            sortField: {
                field: 'text',
                direction: 'asc'
            },
            dropdownParent: 'body'
        });
        tomSelectInstances.set(`kerupuk-${rowIndex}`, kerupukTomSelect);
    }
}

function addStep3Row() {
    const tbody = document.getElementById('step3-tbody');
    const firstRow = tbody.querySelector('.step3-row');
    
    if (!firstRow || (firstRow.querySelector('td') && firstRow.querySelector('td').textContent.includes('Click'))) {
        tbody.innerHTML = '';
    }
    
    // Get the original select elements - TomSelect keeps the original select in the DOM
    const findOriginalSelect = (row, className) => {
        let select = row.querySelector(`select.${className}`);
        if (select) return select;
        
        select = row.querySelector(`.${className}`);
        if (select && select.tagName === 'SELECT') return select;
        
        const wrapper = row.querySelector(`.ts-wrapper`);
        if (wrapper) {
            select = wrapper.querySelector(`select.${className}`);
            if (select) return select;
        }
        
        return null;
    };
    
    // Clone the row
    const row = firstRow ? firstRow.cloneNode(true) : createEmptyRow();
    
    // Remove any TomSelect wrappers that were cloned
    row.querySelectorAll('.ts-wrapper').forEach(wrapper => {
        const select = wrapper.querySelector('select');
        if (select && wrapper.parentNode) {
            wrapper.parentNode.insertBefore(select, wrapper);
            wrapper.remove();
        }
    });
    
    // Find the select elements
    const gelondonganSelect = findOriginalSelect(row, 'gelondongan-select');
    const kerupukSelect = findOriginalSelect(row, 'kerupuk-kering-select');
    
    if (!gelondonganSelect || !kerupukSelect) {
        console.error('Could not find select elements in cloned row');
        return;
    }
    
    // Update indices
    row.querySelectorAll('[name]').forEach(input => {
        const name = input.getAttribute('name');
        if (name) {
            input.setAttribute('name', name.replace(/\[(\d+)\]/, `[${rowIndex}]`));
        }
    });
    
    // Update IDs and data attributes
    gelondonganSelect.id = `gelondongan-select-${rowIndex}`;
    gelondonganSelect.setAttribute('data-row-index', rowIndex);
    
    kerupukSelect.id = `kerupuk-kering-select-${rowIndex}`;
    kerupukSelect.setAttribute('data-row-index', rowIndex);
    
    // Reset values
    row.querySelectorAll('input[type="number"]').forEach(input => input.value = '0');
    row.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
    
    tbody.appendChild(row);
    
    // Initialize TomSelect for the new row
    initializeTomSelectsForRow(rowIndex);
    
    rowIndex++;
}

function createEmptyRow() {
    const row = document.createElement('tr');
    row.className = 'step3-row';
    
    // Create gelondongan dropdown
    let gelondonganSelect = `<select name="step3[0][gelondongan_item_id]" class="form-select gelondongan-select" id="gelondongan-select-0" data-row-index="0" required><option value="">Select Gld</option>`;
    if (gelondonganOptions && gelondonganOptions.length > 0) {
        gelondonganOptions.forEach(option => {
            gelondonganSelect += `<option value="${option.id}">${option.name}</option>`;
        });
    }
    gelondonganSelect += '</select>';
    
    // Create kerupuk kering dropdown
    let kerupukSelect = `<select name="step3[0][kerupuk_kering_item_id]" class="form-select kerupuk-kering-select" id="kerupuk-kering-select-0" data-row-index="0" required><option value="">Select Kerupuk Kering</option>`;
    if (kerupukKeringOptions && kerupukKeringOptions.length > 0) {
        kerupukKeringOptions.forEach(option => {
            kerupukSelect += `<option value="${option.id}">${option.name}</option>`;
        });
    }
    kerupukSelect += '</select>';
    
    row.innerHTML = `
        <td>${gelondonganSelect}</td>
        <td>${kerupukSelect}</td>
        <td>
            Gld: <input type="number" name="step3[0][qty_gl1_gelondongan]" class="form-control" step="0.001" min="0" value="0" readonly required><br />
            Kg: <input type="number" name="step3[0][qty_gl1_kg]" class="form-control" step="0.001" min="0" value="0" required>
        </td>
        <td>
            Gld: <input type="number" name="step3[0][qty_gl2_gelondongan]" class="form-control" step="0.001" min="0" value="0" readonly required><br />
            Kg: <input type="number" name="step3[0][qty_gl2_kg]" class="form-control" step="0.001" min="0" value="0" required>
        </td>
        <td>
            Gld: <input type="number" name="step3[0][qty_ta_gelondongan]" class="form-control" step="0.001" min="0" value="0" readonly required><br />
            Kg: <input type="number" name="step3[0][qty_ta_kg]" class="form-control" step="0.001" min="0" value="0" required>
        </td>
        <td>
            Gld: <input type="number" name="step3[0][qty_bl_gelondongan]" class="form-control" step="0.001" min="0" value="0" readonly required><br />
            Kg: <input type="number" name="step3[0][qty_bl_kg]" class="form-control" step="0.001" min="0" value="0" required>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                <i class="far fa-trash"></i>
            </button>
        </td>
    `;
    return row;
}

function removeRow(button) {
    const row = button.closest('.step3-row');
    const gelondonganSelect = row.querySelector('select.gelondongan-select');
    const kerupukSelect = row.querySelector('select.kerupuk-kering-select');
    const rowIndexAttr = gelondonganSelect ? gelondonganSelect.getAttribute('data-row-index') : null;
    
    if (rowIndexAttr) {
        // Clean up TomSelect instances
        if (tomSelectInstances.has(`gelondongan-${rowIndexAttr}`)) {
            try {
                tomSelectInstances.get(`gelondongan-${rowIndexAttr}`).destroy();
            } catch(e) {}
            tomSelectInstances.delete(`gelondongan-${rowIndexAttr}`);
        }
        if (tomSelectInstances.has(`kerupuk-${rowIndexAttr}`)) {
            try {
                tomSelectInstances.get(`kerupuk-${rowIndexAttr}`).destroy();
            } catch(e) {}
            tomSelectInstances.delete(`kerupuk-${rowIndexAttr}`);
        }
    }
    
    if (document.getElementById('step3-tbody').querySelectorAll('.step3-row').length > 1) {
        row.remove();
    } else {
        alert('At least one row is required.');
    }
}

// Initialize TomSelect for the first row when DOM is ready
function initInitialTomSelects() {
    if (typeof TomSelect !== 'undefined') {
        // Initialize TomSelect for all existing rows
        document.querySelectorAll('.step3-row').forEach((row, index) => {
            const gelondonganSelect = row.querySelector('select.gelondongan-select');
            const kerupukSelect = row.querySelector('select.kerupuk-kering-select');
            if (gelondonganSelect && kerupukSelect) {
                const rowIndex = gelondonganSelect.getAttribute('data-row-index') || index;
                initializeTomSelectsForRow(rowIndex);
            }
        });
    }
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initInitialTomSelects);
} else {
    initInitialTomSelects();
}
</script>
@endpush
@endsection
















