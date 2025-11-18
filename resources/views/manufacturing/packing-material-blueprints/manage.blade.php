@extends('layouts.app')

@section('title', 'Manage Packing Materials')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/tabler/libs/tom-select/dist/css/tom-select.bootstrap5.min.css') }}" />
<style>
    .material-row {
        transition: background-color 0.2s;
    }
    .material-row:hover {
        background-color: #f8f9fa;
    }
    
    /* Ensure select elements have proper width before TomSelect initializes */
    .material-select {
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
    
    /* Ensure the original select is properly hidden when TomSelect is initialized */
    select.tomselected.ts-hidden-accessible {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: 0 !important;
        border: 0 !important;
        clip: rect(0, 0, 0, 0) !important;
        -webkit-clip-path: inset(50%) !important;
        clip-path: inset(50%) !important;
        overflow: hidden !important;
        white-space: nowrap !important;
        opacity: 0 !important;
        pointer-events: none !important;
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
    
    /* Ensure table cells properly contain TomSelect wrappers */
    #materials-table td {
        position: relative;
        vertical-align: middle;
        overflow: visible !important;
    }
    
    #materials-table td .ts-wrapper {
        position: relative;
        width: 100%;
        z-index: 1;
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
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.packing-material-blueprints.index') }}">Packing Material Blueprints</a></li>
                        <li class="breadcrumb-item active">Manage</li>
                    </ol>
                </nav>
                <h2 class="page-title">{{ $item->name }}</h2>
                <p class="text-muted mb-0">
                    Define materials needed to pack one unit of this SKU
                    @if($item->itemCategory)
                    • <span class="badge bg-secondary">{{ $item->itemCategory->name }}</span>
                    @endif
                    @if($item->qty_kg_per_pack)
                    • <span class="badge bg-info">{{ number_format((float) $item->qty_kg_per_pack, 3) }} kg/pack</span>
                    @endif
                </p>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('manufacturing.packing-material-blueprints.index') }}" class="btn btn-outline-secondary">
                    <i class="far fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')

        <form method="POST" action="{{ route('manufacturing.packing-material-blueprints.update', $item) }}">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Packing Materials</h3>
                    <div class="col-auto ms-auto">
                        <button type="button" class="btn btn-sm btn-primary" onclick="addMaterialRow()">
                            <i class="far fa-plus"></i> Add Material
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-vcenter" id="materials-table">
                            <thead>
                                <tr>
                                    <th style="width: 70%;">Material</th>
                                    <th style="width: 20%;" class="text-end">Qty/Pack</th>
                                    <th style="width: 10%;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($item->packingMaterialBlueprints as $index => $blueprint)
                                <tr class="material-row">
                                    <td>
                                        <select name="materials[{{ $index }}][material_item_id]" class="form-select material-select" required>
                                            <option value="">Select material...</option>
                                            @foreach($materialItems as $materialItem)
                                            <option value="{{ $materialItem->id }}" 
                                                {{ $blueprint->material_item_id == $materialItem->id ? 'selected' : '' }}>
                                                {{ $materialItem->name }}
                                                @if($materialItem->itemCategory)
                                                    ({{ $materialItem->itemCategory->name }})
                                                @endif
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" 
                                            name="materials[{{ $index }}][quantity_per_pack]" 
                                            class="form-control text-end" 
                                            value="{{ $blueprint->quantity_per_pack }}" 
                                            step="1" 
                                            min="1" 
                                            required>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeMaterialRow(this)">
                                            <i class="far fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr class="material-row">
                                    <td>
                                        <select name="materials[0][material_item_id]" class="form-select material-select" required>
                                            <option value="">Select material...</option>
                                            @foreach($materialItems as $materialItem)
                                            <option value="{{ $materialItem->id }}">
                                                {{ $materialItem->name }}
                                                @if($materialItem->itemCategory)
                                                    ({{ $materialItem->itemCategory->name }})
                                                @endif
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" 
                                            name="materials[0][quantity_per_pack]" 
                                            class="form-control text-end" 
                                            value="1" 
                                            step="1" 
                                            min="1" 
                                            required>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeMaterialRow(this)">
                                            <i class="far fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($errors->any())
                    <div class="alert alert-danger mt-3">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="far fa-save"></i> Save Blueprint
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/tabler/libs/tom-select/dist/js/tom-select.complete.min.js') }}"></script>
<script>
let materialIndex = {{ $item->packingMaterialBlueprints->count() > 0 ? $item->packingMaterialBlueprints->count() : 1 }};
const materialOptions = {!! json_encode($materialItems->map(function($item) {
    return [
        'id' => $item->id,
        'name' => $item->name,
        'category' => optional($item->itemCategory)->name ?? '',
    ];
})) !!};

document.addEventListener('DOMContentLoaded', function() {
    initializeAllSelects();
});

function initializeAllSelects() {
    document.querySelectorAll('.material-select').forEach(function(select) {
        if (!select.tomselect) {
            initializeTomSelect(select);
        }
    });
}

function initializeTomSelect(selectElement) {
    new TomSelect(selectElement, {
        placeholder: 'Select material...',
        sortField: {
            field: 'text',
            direction: 'asc'
        }
    });
}

function addMaterialRow() {
    const tbody = document.querySelector('#materials-table tbody');
    const newRow = document.createElement('tr');
    newRow.className = 'material-row';
    
    let optionsHtml = '<option value="">Select material...</option>';
    materialOptions.forEach(function(material) {
        optionsHtml += `<option value="${material.id}">
            ${material.name}
            ${material.category ? `(${material.category})` : ''}
        </option>`;
    });
    
    newRow.innerHTML = `
        <td>
            <select name="materials[${materialIndex}][material_item_id]" class="form-select material-select" required>
                ${optionsHtml}
            </select>
        </td>
        <td>
            <input type="number" 
                name="materials[${materialIndex}][quantity_per_pack]" 
                class="form-control text-end" 
                value="1" 
                step="1" 
                min="1" 
                required>
        </td>
        <td class="text-end">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeMaterialRow(this)">
                <i class="far fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(newRow);
    
    const newSelect = newRow.querySelector('.material-select');
    initializeTomSelect(newSelect);
    
    materialIndex++;
}

function removeMaterialRow(button) {
    const tbody = document.querySelector('#materials-table tbody');
    const rows = tbody.querySelectorAll('.material-row');
    
    if (rows.length <= 1) {
        alert('At least one material is required.');
        return;
    }
    
    button.closest('tr').remove();
}
</script>
@endpush
