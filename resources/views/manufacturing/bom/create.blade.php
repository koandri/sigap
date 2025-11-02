@extends('layouts.app')

@section('title', 'Create BoM Template')

@push('css')
<!-- Tom Select CSS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.min.css" rel="stylesheet">
<style>
    .ts-wrapper.single .ts-control {
        border: 1px solid #d0d7de;
        border-radius: 0.375rem;
        min-height: 2.5rem;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        background-color: #fff;
    }
    
    .ts-wrapper.single .ts-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .ts-wrapper.single .ts-control input {
        font-size: 0.875rem;
    }
    
    .ts-dropdown {
        border: 1px solid #d0d7de;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .ts-dropdown .ts-dropdown-content {
        max-height: 300px;
    }
    
    .ts-dropdown .option {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .ts-dropdown .option.active {
        background-color: #0d6efd;
        color: white;
    }
    
    .ts-dropdown .option:hover {
        background-color: #f8f9fa;
    }
    
    .ts-dropdown .option.active:hover {
        background-color: #0d6efd;
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
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.bom.index') }}">Bill of Materials</a></li>
                        <li class="breadcrumb-item active">Create Template</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    Create BoM Template
                    @if($sourceTemplate)
                        <span class="badge bg-blue ms-2">Copying from: {{ $sourceTemplate->name }}</span>
                    @endif
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('manufacturing.bom.index') }}" class="btn">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')
        
        <form action="{{ route('manufacturing.bom.store') }}" method="POST" id="bomForm">
            @csrf
            
            @if($sourceTemplate)
                <input type="hidden" name="parent_template_id" value="{{ $sourceTemplate->id }}">
            @endif

            <div class="row row-deck row-cards">
                <!-- Basic Information -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Basic Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">BoM Type</label>
                                        <select class="form-select @error('bom_type_id') is-invalid @enderror" name="bom_type_id" required>
                                            <option value="">Select BoM Type</option>
                                            @foreach($bomTypes as $type)
                                                <option value="{{ $type->id }}" 
                                                    {{ old('bom_type_id', $sourceTemplate?->bom_type_id) == $type->id ? 'selected' : '' }}>
                                                    {{ $type->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('bom_type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Choose the type based on your production stage and cost tracking needs.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Template Code</label>
                                        <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                               name="code" value="{{ old('code', $sourceTemplate ? $sourceTemplate->code . '-COPY' : '') }}" 
                                               placeholder="e.g., ADN-001, GLD-002" required maxlength="20">
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Unique identifier for this BoM template.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label required">Template Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               name="name" value="{{ old('name', $sourceTemplate?->name) }}" 
                                               placeholder="e.g., Standard Adonan Recipe" required maxlength="100">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <input type="checkbox" class="form-check-input me-2" name="is_template" value="1" 
                                                   {{ old('is_template', $sourceTemplate?->is_template) ? 'checked' : '' }}>
                                            Mark as Template
                                        </label>
                                        <small class="form-hint d-block">Templates can be used as base for new BoMs.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          name="description" rows="3" placeholder="Describe this BoM template...">{{ old('description', $sourceTemplate?->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Output Product -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Output Product</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Output Item</label>
                                        <select class="form-select @error('output_item_id') is-invalid @enderror" name="output_item_id" required>
                                            <option value="">Select Output Item</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}" 
                                                    data-unit="{{ $item->unit }}"
                                                    {{ old('output_item_id', $sourceTemplate?->output_item_id) == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }} 
                                                    @if($item->itemCategory)
                                                        ({{ $item->itemCategory->name }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('output_item_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label required">Output Quantity</label>
                                        <input type="number" step="0.001" min="0.001" 
                                               class="form-control @error('output_quantity') is-invalid @enderror" 
                                               name="output_quantity" value="{{ old('output_quantity', $sourceTemplate?->output_quantity ?? '1.000') }}" required>
                                        @error('output_quantity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Output Unit</label>
                                        <input type="text" class="form-control @error('output_unit') is-invalid @enderror" 
                                               name="output_unit" value="{{ old('output_unit', $sourceTemplate?->output_unit) }}" 
                                               placeholder="Auto-filled from item" maxlength="15">
                                        @error('output_unit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Leave empty to use item's default unit.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ingredients -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Ingredients</h3>
                            <div class="card-actions">
                                <button type="button" class="btn btn-primary btn-sm" id="addIngredient">
                        <i class="far fa-plus"></i>&nbsp;
                                    Add Ingredient
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="ingredientsList">
                                @if($sourceTemplate && $sourceTemplate->ingredients->count() > 0)
                                    @foreach($sourceTemplate->ingredients as $index => $ingredient)
                                        <div class="ingredient-row border rounded p-3 mb-3">
                                            <div class="row align-items-center">
                                                <div class="col-md-5">
                                                    <label class="form-label required">Ingredient Item</label>
                                                    <select class="form-select" name="ingredients[{{ $index }}][ingredient_item_id]" required>
                                                        <option value="">Select Ingredient</option>
                                                        @foreach($items as $item)
                                                            <option value="{{ $item->id }}" 
                                                                data-unit="{{ $item->unit }}"
                                                                {{ $ingredient->ingredient_item_id == $item->id ? 'selected' : '' }}>
                                                                {{ $item->name }} 
                                                                @if($item->itemCategory)
                                                                    ({{ $item->itemCategory->name }})
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label required">Quantity</label>
                                                    <input type="number" step="0.001" min="0.001" 
                                                           class="form-control" name="ingredients[{{ $index }}][quantity]" 
                                                           value="{{ $ingredient->quantity }}" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Unit</label>
                                                    <input type="text" class="form-control" 
                                                           name="ingredients[{{ $index }}][unit]" 
                                                           value="{{ $ingredient->unit }}" 
                                                           placeholder="Auto-filled" maxlength="15">
                                                </div>
                                                <div class="col-md-1">
                                                    <label class="form-label">&nbsp;</label>
                                                    <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-ingredient">
                        <i class="far fa-xmark"></i>&nbsp;
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-4 text-muted">
                                        <p>No ingredients added yet. Click "Add Ingredient" to start building your recipe.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-footer text-end">
                            <div class="d-flex">
                                <a href="{{ route('manufacturing.bom.index') }}" class="btn btn-outline-secondary me-auto">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                        <i class="far fa-pen"></i>&nbsp;
                                    Create BoM Template
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Ingredient Row Template -->
<template id="ingredientRowTemplate">
    <div class="ingredient-row border rounded p-3 mb-3">
        <div class="row align-items-center">
            <div class="col-md-5">
                <label class="form-label required">Ingredient Item</label>
                <select class="form-select" name="ingredients[INDEX][ingredient_item_id]" required>
                    <option value="">Select Ingredient</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" data-unit="{{ $item->unit }}">
                            {{ $item->name }} 
                            @if($item->itemCategory)
                                ({{ $item->itemCategory->name }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label required">Quantity</label>
                <input type="number" step="0.001" min="0.001" 
                       class="form-control" name="ingredients[INDEX][quantity]" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Unit</label>
                <input type="text" class="form-control" 
                       name="ingredients[INDEX][unit]" 
                       placeholder="Auto-filled" maxlength="15">
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-ingredient">
                        <i class="far fa-xmark"></i>&nbsp;
                </button>
            </div>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<!-- Tom Select JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let ingredientIndex = 0;
    
    // Function to create Tom Select configuration for item dropdowns
    function createTomSelectConfig(isOutputItem = false) {
        return {
            create: false,
            sortField: {
                field: 'text',
                direction: 'asc'
            },
            placeholder: isOutputItem ? 'Search and select output item...' : 'Search and select ingredient...',
            searchField: ['text'],
            maxOptions: null,
            render: {
                option: function(data, escape) {
                    return '<div class="d-flex align-items-center">' +
                        '<div>' +
                            '<div class="fw-bold">' + escape(data.text.split(' (')[0]) + '</div>' +
                            (data.text.includes('(') ? '<div class="text-muted small">' + escape(data.text.split(' (')[1].replace(')', '')) + '</div>' : '') +
                        '</div>' +
                    '</div>';
                },
                item: function(data, escape) {
                    return '<div>' + escape(data.text) + '</div>';
                }
            },
            onChange: function(value) {
                if (value) {
                    const selectElement = this.input;
                    
                    if (isOutputItem) {
                        // Auto-fill output unit when output item is selected
                        const unitInput = document.querySelector('input[name="output_unit"]');
                        const originalSelect = document.querySelector('select[name="output_item_id"]');
                        
                        if (originalSelect && unitInput) {
                            const selectedOption = originalSelect.querySelector(`option[value="${value}"]`);
                            if (selectedOption && selectedOption.dataset.unit) {
                                unitInput.value = selectedOption.dataset.unit;
                            }
                        }
                    } else {
                        // Auto-fill ingredient unit when ingredient item is selected
                        const row = selectElement.closest('.ingredient-row');
                        if (row) {
                            const unitInput = row.querySelector('input[name*="[unit]"]');
                            const originalSelect = row.querySelector('select[name*="ingredient_item_id"]');
                            
                            if (originalSelect && unitInput) {
                                const selectedOption = originalSelect.querySelector(`option[value="${value}"]`);
                                if (selectedOption && selectedOption.dataset.unit) {
                                    unitInput.value = selectedOption.dataset.unit;
                                }
                            }
                        }
                    }
                }
            }
        };
    }
    
    // Initialize Tom Select for Output Item dropdown
    const outputItemSelect = new TomSelect('#bomForm select[name="output_item_id"]', createTomSelectConfig(true));
    
    // Initialize Tom Select for existing ingredient item dropdowns
    function initializeIngredientTomSelects() {
        document.querySelectorAll('select[name*="ingredient_item_id"]:not(.tomselected)').forEach(select => {
            new TomSelect(select, createTomSelectConfig(false));
        });
    }
    
    // Initialize existing ingredient dropdowns
    initializeIngredientTomSelects();
    
    // Add ingredient button
    document.getElementById('addIngredient').addEventListener('click', function() {
        const template = document.getElementById('ingredientRowTemplate');
        const clone = template.content.cloneNode(true);
        
        // Replace INDEX placeholder with actual index
        const html = clone.querySelector('.ingredient-row').outerHTML.replace(/INDEX/g, ingredientIndex);
        
        // If this is the first ingredient, clear the "no ingredients" message
        const ingredientsList = document.getElementById('ingredientsList');
        if (ingredientsList.querySelector('.text-center')) {
            ingredientsList.innerHTML = '';
        }
        
        ingredientsList.insertAdjacentHTML('beforeend', html);
        ingredientIndex++;
        
        // Add remove event listener to the new row
        const newRow = ingredientsList.lastElementChild;
        newRow.querySelector('.remove-ingredient').addEventListener('click', function() {
            newRow.remove();
            checkEmptyIngredients();
        });
        
        // Initialize Tom Select for the new ingredient dropdown
        const newSelect = newRow.querySelector('select[name*="ingredient_item_id"]');
        if (newSelect && !newSelect.classList.contains('tomselected')) {
            new TomSelect(newSelect, createTomSelectConfig(false));
        }
    });
    
    // Remove ingredient functionality for existing rows
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-ingredient') || e.target.closest('.remove-ingredient')) {
            e.preventDefault();
            const row = e.target.closest('.ingredient-row');
            row.remove();
            checkEmptyIngredients();
        }
    });
    
    
    function checkEmptyIngredients() {
        const ingredientsList = document.getElementById('ingredientsList');
        if (ingredientsList.children.length === 0) {
            ingredientsList.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <p>No ingredients added yet. Click "Add Ingredient" to start building your recipe.</p>
                </div>
            `;
        }
    }
});
</script>
@endpush
