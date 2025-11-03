@extends('layouts.app')

@section('title', 'Step 2: Gelondongan Planning')

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
                        <li class="breadcrumb-item active">Step 2</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    Step 2: Gelondongan Production Planning
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('manufacturing.production-plans.show', $productionPlan) }}" class="btn btn-outline-secondary d-none d-sm-inline-block">
                        <i class="far fa-arrow-left"></i>&nbsp;
                        Back to Plan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')
        
        <form method="POST" action="{{ route('manufacturing.production-plans.step2.store', $productionPlan) }}" id="step2-form">
            @csrf
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Step 2: Gelondongan Planning from Adonan</h3>
                    <div class="card-actions">
                        <button type="button" class="btn btn-sm btn-primary" onclick="addStep2Row()">
                            <i class="far fa-plus"></i>&nbsp;Add Row
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="far fa-info-circle me-2"></i>
                        This step converts Adonan quantities to Gelondongan quantities using yield guidelines. 
                        @if(count($calculatedData) > 0)
                        Auto-calculated values are pre-filled below. You can adjust them as needed.
                        @endif
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-vcenter" id="step2-table">
                            <thead>
                                <tr>
                                    <th>Adonan Item</th>
                                    <th>Gelondongan Item</th>
                                    <th>GL1 Adonan</th>
                                    <th>GL1 Gelondongan</th>
                                    <th>GL2 Adonan</th>
                                    <th>GL2 Gelondongan</th>
                                    <th>TA Adonan</th>
                                    <th>TA Gelondongan</th>
                                    <th>BL Adonan</th>
                                    <th>BL Gelondongan</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody id="step2-tbody">
                                @if(count($calculatedData) > 0)
                                    @foreach($calculatedData as $index => $data)
                                    <tr class="step2-row">
                                        <td>
                                            <select name="step2[{{ $index }}][adonan_item_id]" class="form-select" required>
                                                <option value="">Select Adonan</option>
                                                @foreach($productionPlan->step1 as $step1)
                                                <option value="{{ $step1->dough_item_id }}" {{ $data['adonan_item_id'] == $step1->dough_item_id ? 'selected' : '' }}>
                                                    {{ $step1->doughItem->name ?? 'N/A' }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="step2[{{ $index }}][gelondongan_item_id]" class="form-select" required>
                                                <option value="">Select Gelondongan</option>
                                                @php
                                                    $gelondonganItems = \App\Models\Item::whereHas('itemCategory', function($q) {
                                                        $q->where('name', 'like', '%Gelondongan%');
                                                    })->where('is_active', true)->get();
                                                @endphp
                                                @foreach($gelondonganItems as $item)
                                                <option value="{{ $item->id }}" {{ $data['gelondongan_item_id'] == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_gl1_adonan]" class="form-control" step="0.001" min="0" 
                                                   value="{{ $data['qty_gl1_adonan'] ?? 0 }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_gl1_gelondongan]" class="form-control" step="0.001" min="0" 
                                                   value="{{ $data['qty_gl1_gelondongan'] ?? 0 }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_gl2_adonan]" class="form-control" step="0.001" min="0" 
                                                   value="{{ $data['qty_gl2_adonan'] ?? 0 }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_gl2_gelondongan]" class="form-control" step="0.001" min="0" 
                                                   value="{{ $data['qty_gl2_gelondongan'] ?? 0 }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_ta_adonan]" class="form-control" step="0.001" min="0" 
                                                   value="{{ $data['qty_ta_adonan'] ?? 0 }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_ta_gelondongan]" class="form-control" step="0.001" min="0" 
                                                   value="{{ $data['qty_ta_gelondongan'] ?? 0 }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_bl_adonan]" class="form-control" step="0.001" min="0" 
                                                   value="{{ $data['qty_bl_adonan'] ?? 0 }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_bl_gelondongan]" class="form-control" step="0.001" min="0" 
                                                   value="{{ $data['qty_bl_gelondongan'] ?? 0 }}" required>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                                                <i class="far fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    @foreach($productionPlan->step2 as $index => $step2)
                                    <tr class="step2-row">
                                        <td>
                                            <select name="step2[{{ $index }}][adonan_item_id]" class="form-select" required>
                                                <option value="">Select Adonan</option>
                                                @foreach($productionPlan->step1 as $step1)
                                                <option value="{{ $step1->dough_item_id }}" {{ $step2->adonan_item_id == $step1->dough_item_id ? 'selected' : '' }}>
                                                    {{ $step1->doughItem->name ?? 'N/A' }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="step2[{{ $index }}][gelondongan_item_id]" class="form-select" required>
                                                <option value="">Select Gelondongan</option>
                                                @php
                                                    $gelondonganItems = \App\Models\Item::whereHas('itemCategory', function($q) {
                                                        $q->where('name', 'like', '%Gelondongan%');
                                                    })->where('is_active', true)->get();
                                                @endphp
                                                @foreach($gelondonganItems as $item)
                                                <option value="{{ $item->id }}" {{ $step2->gelondongan_item_id == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_gl1_adonan]" class="form-control" step="0.001" min="0" 
                                                   value="{{ $step2->qty_gl1_adonan }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_gl1_gelondongan]" class="form-control" step="0.001" min="0" 
                                                   value="{{ $step2->qty_gl1_gelondongan }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_gl2_adonan]" class="form-control" step="0.001" min="0" 
                                                   value="{{ $step2->qty_gl2_adonan }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_gl2_gelondongan]" class="form-control" step="0.001" min="0" 
                                                   value="{{ $step2->qty_gl2_gelondongan }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_ta_adonan]" class="form-control" step="0.001" min="0" 
                                                   value="{{ $step2->qty_ta_adonan }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_ta_gelondongan]" class="form-control" step="0.001" min="0" 
                                                   value="{{ $step2->qty_ta_gelondongan }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_bl_adonan]" class="form-control" step="0.001" min="0" 
                                                   value="{{ $step2->qty_bl_adonan }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_bl_gelondongan]" class="form-control" step="0.001" min="0" 
                                                   value="{{ $step2->qty_bl_gelondongan }}" required>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                                                <i class="far fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @if($productionPlan->step2->count() === 0)
                                    <tr class="step2-row">
                                        <td colspan="11" class="text-center text-muted">
                                            Click "Add Row" to add Step 2 data
                                        </td>
                                    </tr>
                                    @endif
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('manufacturing.production-plans.show', $productionPlan) }}" class="btn btn-link">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Step 2</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let rowIndex = {{ count($calculatedData) > 0 ? count($calculatedData) : $productionPlan->step2->count() }};

function addStep2Row() {
    const tbody = document.getElementById('step2-tbody');
    const firstRow = tbody.querySelector('.step2-row');
    
    if (!firstRow || firstRow.querySelector('td').textContent.includes('Click')) {
        tbody.innerHTML = '';
    }
    
    const row = firstRow ? firstRow.cloneNode(true) : createEmptyRow();
    
    // Update indices
    row.querySelectorAll('[name]').forEach(input => {
        const name = input.getAttribute('name');
        if (name) {
            input.setAttribute('name', name.replace(/\[(\d+)\]/, `[${rowIndex}]`));
        }
    });
    
    // Reset values
    row.querySelectorAll('input[type="number"]').forEach(input => input.value = '0');
    row.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
    
    tbody.appendChild(row);
    rowIndex++;
}

function createEmptyRow() {
    const row = document.createElement('tr');
    row.className = 'step2-row';
    row.innerHTML = `
        <td>
            <select name="step2[0][adonan_item_id]" class="form-select" required>
                <option value="">Select Adonan</option>
            </select>
        </td>
        <td>
            <select name="step2[0][gelondongan_item_id]" class="form-select" required>
                <option value="">Select Gelondongan</option>
            </select>
        </td>
        <td><input type="number" name="step2[0][qty_gl1_adonan]" class="form-control" step="0.001" min="0" value="0" required></td>
        <td><input type="number" name="step2[0][qty_gl1_gelondongan]" class="form-control" step="0.001" min="0" value="0" required></td>
        <td><input type="number" name="step2[0][qty_gl2_adonan]" class="form-control" step="0.001" min="0" value="0" required></td>
        <td><input type="number" name="step2[0][qty_gl2_gelondongan]" class="form-control" step="0.001" min="0" value="0" required></td>
        <td><input type="number" name="step2[0][qty_ta_adonan]" class="form-control" step="0.001" min="0" value="0" required></td>
        <td><input type="number" name="step2[0][qty_ta_gelondongan]" class="form-control" step="0.001" min="0" value="0" required></td>
        <td><input type="number" name="step2[0][qty_bl_adonan]" class="form-control" step="0.001" min="0" value="0" required></td>
        <td><input type="number" name="step2[0][qty_bl_gelondongan]" class="form-control" step="0.001" min="0" value="0" required></td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                <i class="far fa-trash"></i>
            </button>
        </td>
    `;
    return row;
}

function removeRow(button) {
    if (document.getElementById('step2-tbody').querySelectorAll('.step2-row').length > 1) {
        button.closest('.step2-row').remove();
    } else {
        alert('At least one row is required.');
    }
}
</script>
@endpush
@endsection

