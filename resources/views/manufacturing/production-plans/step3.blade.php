@extends('layouts.app')

@section('title', 'Step 3: Kerupuk Kering Planning')

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
                        @endif
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-vcenter" id="step3-table">
                            <thead>
                                <tr>
                                    <th>Gld Item</th>
                                    <th>Kerupuk Kering Item</th>
                                    <th>GL1 Gel</th>
                                    <th>GL1 Kg</th>
                                    <th>GL2 Gel</th>
                                    <th>GL2 Kg</th>
                                    <th>TA Gel</th>
                                    <th>TA Kg</th>
                                    <th>BL Gel</th>
                                    <th>BL Kg</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody id="step3-tbody">
                                @if(count($calculatedData) > 0)
                                    @foreach($calculatedData as $index => $data)
                                    <tr class="step3-row">
                                        <td>
                                            <select name="step3[{{ $index }}][gelondongan_item_id]" class="form-select" required>
                                                <option value="">Select Gld</option>
                                                @foreach($productionPlan->step2->unique('gelondongan_item_id') as $step2)
                                                <option value="{{ $step2->gelondongan_item_id }}" {{ $data['gelondongan_item_id'] == $step2->gelondongan_item_id ? 'selected' : '' }}>
                                                    {{ $step2->gelondonganItem->name ?? 'N/A' }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="step3[{{ $index }}][kerupuk_kering_item_id]" class="form-select" required>
                                                <option value="">Select Kerupuk Kering</option>
                                                @php
                                                    $kerupukItems = \App\Models\Item::whereHas('itemCategory', function($q) {
                                                        $q->where('name', 'like', '%Finished Products%');
                                                    })->where('is_active', true)->get();
                                                @endphp
                                                @foreach($kerupukItems as $item)
                                                <option value="{{ $item->id }}" {{ $data['kerupuk_kering_item_id'] == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" name="step3[{{ $index }}][qty_gl1_gelondongan]" class="form-control" step="0.001" min="0" value="{{ $data['qty_gl1_gelondongan'] ?? 0 }}" required></td>
                                        <td><input type="number" name="step3[{{ $index }}][qty_gl1_kg]" class="form-control" step="0.001" min="0" value="{{ $data['qty_gl1_kg'] ?? 0 }}" required></td>
                                        <td><input type="number" name="step3[{{ $index }}][qty_gl2_gelondongan]" class="form-control" step="0.001" min="0" value="{{ $data['qty_gl2_gelondongan'] ?? 0 }}" required></td>
                                        <td><input type="number" name="step3[{{ $index }}][qty_gl2_kg]" class="form-control" step="0.001" min="0" value="{{ $data['qty_gl2_kg'] ?? 0 }}" required></td>
                                        <td><input type="number" name="step3[{{ $index }}][qty_ta_gelondongan]" class="form-control" step="0.001" min="0" value="{{ $data['qty_ta_gelondongan'] ?? 0 }}" required></td>
                                        <td><input type="number" name="step3[{{ $index }}][qty_ta_kg]" class="form-control" step="0.001" min="0" value="{{ $data['qty_ta_kg'] ?? 0 }}" required></td>
                                        <td><input type="number" name="step3[{{ $index }}][qty_bl_gelondongan]" class="form-control" step="0.001" min="0" value="{{ $data['qty_bl_gelondongan'] ?? 0 }}" required></td>
                                        <td><input type="number" name="step3[{{ $index }}][qty_bl_kg]" class="form-control" step="0.001" min="0" value="{{ $data['qty_bl_kg'] ?? 0 }}" required></td>
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
                                            <select name="step3[{{ $index }}][gelondongan_item_id]" class="form-select" required>
                                                <option value="">Select Gld</option>
                                                @foreach($productionPlan->step2->unique('gelondongan_item_id') as $step2)
                                                <option value="{{ $step2->gelondongan_item_id }}" {{ $step3->gelondongan_item_id == $step2->gelondongan_item_id ? 'selected' : '' }}>
                                                    {{ $step2->gelondonganItem->name ?? 'N/A' }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="step3[{{ $index }}][kerupuk_kering_item_id]" class="form-select" required>
                                                <option value="">Select Kerupuk Kering</option>
                                                @php
                                                    $kerupukItems = \App\Models\Item::whereHas('itemCategory', function($q) {
                                                        $q->where('name', 'like', '%Finished Products%');
                                                    })->where('is_active', true)->get();
                                                @endphp
                                                @foreach($kerupukItems as $item)
                                                <option value="{{ $item->id }}" {{ $step3->kerupuk_kering_item_id == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" name="step3[{{ $index }}][qty_gl1_gelondongan]" class="form-control" step="0.001" min="0" value="{{ $step3->qty_gl1_gelondongan }}" required></td>
                                        <td><input type="number" name="step3[{{ $index }}][qty_gl1_kg]" class="form-control" step="0.001" min="0" value="{{ $step3->qty_gl1_kg }}" required></td>
                                        <td><input type="number" name="step3[{{ $index }}][qty_gl2_gelondongan]" class="form-control" step="0.001" min="0" value="{{ $step3->qty_gl2_gelondongan }}" required></td>
                                        <td><input type="number" name="step3[{{ $index }}][qty_gl2_kg]" class="form-control" step="0.001" min="0" value="{{ $step3->qty_gl2_kg }}" required></td>
                                        <td><input type="number" name="step3[{{ $index }}][qty_ta_gelondongan]" class="form-control" step="0.001" min="0" value="{{ $step3->qty_ta_gelondongan }}" required></td>
                                        <td><input type="number" name="step3[{{ $index }}][qty_ta_kg]" class="form-control" step="0.001" min="0" value="{{ $step3->qty_ta_kg }}" required></td>
                                        <td><input type="number" name="step3[{{ $index }}][qty_bl_gelondongan]" class="form-control" step="0.001" min="0" value="{{ $step3->qty_bl_gelondongan }}" required></td>
                                        <td><input type="number" name="step3[{{ $index }}][qty_bl_kg]" class="form-control" step="0.001" min="0" value="{{ $step3->qty_bl_kg }}" required></td>
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
<script>
let rowIndex = {{ count($calculatedData) > 0 ? count($calculatedData) : $productionPlan->step3->count() }};

function addStep3Row() {
    const tbody = document.getElementById('step3-tbody');
    const firstRow = tbody.querySelector('.step3-row');
    if (!firstRow || firstRow.querySelector('td').textContent.includes('Click')) {
        tbody.innerHTML = '';
    }
    const row = firstRow ? firstRow.cloneNode(true) : createEmptyRow();
    row.querySelectorAll('[name]').forEach(input => {
        const name = input.getAttribute('name');
        if (name) input.setAttribute('name', name.replace(/\[(\d+)\]/, `[${rowIndex}]`));
    });
    row.querySelectorAll('input[type="number"]').forEach(input => input.value = '0');
    row.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
    tbody.appendChild(row);
    rowIndex++;
}

function createEmptyRow() {
    const row = document.createElement('tr');
    row.className = 'step3-row';
    row.innerHTML = '<td><select name="step3[0][gelondongan_item_id]" class="form-select" required><option value="">Select Gld</option></select></td><td><select name="step3[0][kerupuk_kering_item_id]" class="form-select" required><option value="">Select Kerupuk Kering</option></select></td><td><input type="number" name="step3[0][qty_gl1_gelondongan]" class="form-control" step="0.001" min="0" value="0" required></td><td><input type="number" name="step3[0][qty_gl1_kg]" class="form-control" step="0.001" min="0" value="0" required></td><td><input type="number" name="step3[0][qty_gl2_gelondongan]" class="form-control" step="0.001" min="0" value="0" required></td><td><input type="number" name="step3[0][qty_gl2_kg]" class="form-control" step="0.001" min="0" value="0" required></td><td><input type="number" name="step3[0][qty_ta_gelondongan]" class="form-control" step="0.001" min="0" value="0" required></td><td><input type="number" name="step3[0][qty_ta_kg]" class="form-control" step="0.001" min="0" value="0" required></td><td><input type="number" name="step3[0][qty_bl_gelondongan]" class="form-control" step="0.001" min="0" value="0" required></td><td><input type="number" name="step3[0][qty_bl_kg]" class="form-control" step="0.001" min="0" value="0" required></td><td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="far fa-trash"></i></button></td>';
    return row;
}

function removeRow(button) {
    if (document.getElementById('step3-tbody').querySelectorAll('.step3-row').length > 1) {
        button.closest('.step3-row').remove();
    } else {
        alert('At least one row is required.');
    }
}
</script>
@endpush
@endsection
















