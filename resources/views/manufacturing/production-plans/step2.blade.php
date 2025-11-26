@extends('layouts.app')

@section('title', 'Step 2: Gld Planning')

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
                    Step 2: Gld Production Planning
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('manufacturing.production-plans.show', $productionPlan) }}" class="btn btn-outline-secondary">
                        <i class="far fa-arrow-left"></i>&nbsp;
                        <span class="d-none d-sm-inline">Back to Plan</span>
                        <span class="d-sm-none">Back</span>
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
                    <h3 class="card-title">Step 2: Gld Planning from Adn</h3>
                    <div class="card-actions">
                        <button type="button" class="btn btn-sm btn-primary" onclick="addStep2Row()">
                            <i class="far fa-plus"></i>&nbsp;Add Row
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="far fa-info-circle me-2"></i>
                        <strong>Important:</strong> Each Adn item MUST produce at least one Gld item. This step converts Adn quantities to Gld quantities using yield guidelines. 
                        @if(count($calculatedData) > 0)
                        Auto-calculated values are pre-filled below. You can adjust them as needed.
                        @endif
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-vcenter" id="step2-table">
                            <thead>
                                <tr>
                                    <th>Adn Item</th>
                                    <th>Gld Item</th>
                                    <th width="120">GL1 Adn</th>
                                    <th width="120">GL1 Gld</th>
                                    <th width="120">GL2 Adn</th>
                                    <th width="120">GL2 Gld</th>
                                    <th width="120">TA Adn</th>
                                    <th width="120">TA Gld</th>
                                    <th width="120">BL Adn</th>
                                    <th width="120">BL Gld</th>
                                </tr>
                            </thead>
                            <tbody id="step2-tbody">
                                @if(count($calculatedData) > 0)
                                    @foreach($calculatedData as $index => $data)
                                    <tr class="step2-row">
                                        <td>
                                            <select name="step2[{{ $index }}][adonan_item_id]" class="form-select step2-adonan-select" required>
                                                <option value="">Select Adn</option>
                                                @foreach($productionPlan->step1 as $step1)
                                                <option value="{{ $step1->dough_item_id }}" {{ $data['adonan_item_id'] == $step1->dough_item_id ? 'selected' : '' }}>
                                                    {{ $step1->doughItem?->label ?? $step1->doughItem?->name ?? 'N/A' }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="step2[{{ $index }}][gelondongan_item_id]" class="form-select step2-gelondongan-select" required>
                                                <option value="">Select Gld</option>
                                                @php
                                                    /** @var \Illuminate\Support\Collection<int,string> $gelondonganItems */
                                                    $gelondonganItems = app(\App\Services\ItemDropdownService::class)->forGelondonganItems();
                                                @endphp
                                                @foreach($gelondonganItems as $id => $label)
                                                <option value="{{ $id }}" {{ $data['gelondongan_item_id'] == $id ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_gl1_adonan]" class="form-control" step="1" min="0" 
                                                   value="{{ $data['qty_gl1_adonan'] ?? 0 }}" readonly required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_gl1_gelondongan]" class="form-control" step="1" min="0" 
                                                   value="{{ $data['qty_gl1_gelondongan'] ?? 0 }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_gl2_adonan]" class="form-control" step="1" min="0" 
                                                   value="{{ $data['qty_gl2_adonan'] ?? 0 }}" readonly required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_gl2_gelondongan]" class="form-control" step="1" min="0" 
                                                   value="{{ $data['qty_gl2_gelondongan'] ?? 0 }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_ta_adonan]" class="form-control" step="1" min="0" 
                                                   value="{{ $data['qty_ta_adonan'] ?? 0 }}" readonly required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_ta_gelondongan]" class="form-control" step="1" min="0" 
                                                   value="{{ $data['qty_ta_gelondongan'] ?? 0 }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_bl_adonan]" class="form-control" step="1" min="0" 
                                                   value="{{ $data['qty_bl_adonan'] ?? 0 }}" readonly required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_bl_gelondongan]" class="form-control" step="1" min="0" 
                                                   value="{{ $data['qty_bl_gelondongan'] ?? 0 }}" required>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    @foreach($productionPlan->step2 as $index => $step2)
                                    <tr class="step2-row">
                                        <td>
                                            <select name="step2[{{ $index }}][adonan_item_id]" class="form-select step2-adonan-select" required>
                                                <option value="">Select Adn</option>
                                                @foreach($productionPlan->step1 as $step1)
                                                <option value="{{ $step1->dough_item_id }}" {{ $step2->adonan_item_id == $step1->dough_item_id ? 'selected' : '' }}>
                                                    {{ $step1->doughItem?->label ?? $step1->doughItem?->name ?? 'N/A' }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="step2[{{ $index }}][gelondongan_item_id]" class="form-select step2-gelondongan-select" required>
                                                <option value="">Select Gld</option>
                                                @php
                                                    /** @var \Illuminate\Support\Collection<int,string> $gelondonganItems */
                                                    $gelondonganItems = app(\App\Services\ItemDropdownService::class)->forGelondonganItems();
                                                @endphp
                                                @foreach($gelondonganItems as $id => $label)
                                                <option value="{{ $id }}" {{ $step2->gelondongan_item_id == $id ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_gl1_adonan]" class="form-control" step="1" min="0" 
                                                   value="{{ $step2->qty_gl1_adonan }}" readonly required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_gl1_gelondongan]" class="form-control" step="1" min="0" 
                                                   value="{{ $step2->qty_gl1_gelondongan }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_gl2_adonan]" class="form-control" step="1" min="0" 
                                                   value="{{ $step2->qty_gl2_adonan }}" readonly required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_gl2_gelondongan]" class="form-control" step="1" min="0" 
                                                   value="{{ $step2->qty_gl2_gelondongan }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_ta_adonan]" class="form-control" step="1" min="0" 
                                                   value="{{ $step2->qty_ta_adonan }}" readonly required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_ta_gelondongan]" class="form-control" step="1" min="0" 
                                                   value="{{ $step2->qty_ta_gelondongan }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_bl_adonan]" class="form-control" step="1" min="0" 
                                                   value="{{ $step2->qty_bl_adonan }}" readonly required>
                                        </td>
                                        <td>
                                            <input type="number" name="step2[{{ $index }}][qty_bl_gelondongan]" class="form-control" step="1" min="0" 
                                                   value="{{ $step2->qty_bl_gelondongan }}" required>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @if($productionPlan->step2->count() === 0)
                                    <tr class="step2-row">
                                        <td colspan="10" class="text-center text-muted">
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
<script src="{{ asset('assets/tabler/libs/tom-select/dist/js/tom-select.complete.min.js') }}"></script>
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
    
    // Initialize TomSelect for new row selects
    initializeTomSelectForRow(row);
    rowIndex++;
}

function createEmptyRow() {
    const row = document.createElement('tr');
    row.className = 'step2-row';
    row.innerHTML = `
        <td>
            <select name="step2[0][adonan_item_id]" class="form-select" required>
                <option value="">Select Adn</option>
            </select>
        </td>
        <td>
            <select name="step2[0][gelondongan_item_id]" class="form-select" required>
                <option value="">Select Gld</option>
            </select>
        </td>
        <td><input type="number" name="step2[0][qty_gl1_adonan]" class="form-control" step="1" min="0" value="0" readonly required></td>
        <td><input type="number" name="step2[0][qty_gl1_gelondongan]" class="form-control" step="1" min="0" value="0" required></td>
        <td><input type="number" name="step2[0][qty_gl2_adonan]" class="form-control" step="1" min="0" value="0" readonly required></td>
        <td><input type="number" name="step2[0][qty_gl2_gelondongan]" class="form-control" step="1" min="0" value="0" required></td>
        <td><input type="number" name="step2[0][qty_ta_adonan]" class="form-control" step="1" min="0" value="0" readonly required></td>
        <td><input type="number" name="step2[0][qty_ta_gelondongan]" class="form-control" step="1" min="0" value="0" required></td>
        <td><input type="number" name="step2[0][qty_bl_adonan]" class="form-control" step="1" min="0" value="0" readonly required></td>
        <td><input type="number" name="step2[0][qty_bl_gelondongan]" class="form-control" step="1" min="0" value="0" required></td>
    `;
    return row;
}

document.addEventListener('DOMContentLoaded', function () {
    // Initialize TomSelect for existing rows
    document.querySelectorAll('.step2-adonan-select').forEach(function (el) {
        if (!el.tomselect) {
            new TomSelect(el, {
                allowEmptyOption: true,
                placeholder: 'Select Adn',
                sortField: { field: 'text', direction: 'asc' },
            });
        }
    });

    document.querySelectorAll('.step2-gelondongan-select').forEach(function (el) {
        if (!el.tomselect) {
            new TomSelect(el, {
                allowEmptyOption: true,
                placeholder: 'Select Gld',
                sortField: { field: 'text', direction: 'asc' },
            });
        }
    });
});

function initializeTomSelectForRow(row) {
    const adonanSelect = row.querySelector('.step2-adonan-select');
    if (adonanSelect && !adonanSelect.tomselect) {
        new TomSelect(adonanSelect, {
            allowEmptyOption: true,
            placeholder: 'Select Adn',
            sortField: { field: 'text', direction: 'asc' },
        });
    }

    const gldSelect = row.querySelector('.step2-gelondongan-select');
    if (gldSelect && !gldSelect.tomselect) {
        new TomSelect(gldSelect, {
            allowEmptyOption: true,
            placeholder: 'Select Gld',
            sortField: { field: 'text', direction: 'asc' },
        });
    }
}

</script>
@endpush
@endsection
















