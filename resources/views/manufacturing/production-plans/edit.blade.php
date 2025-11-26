@extends('layouts.app')

@section('title', 'Edit Production Plan')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/tabler/libs/tom-select/dist/css/tom-select.bootstrap5.min.css') }}" />
<style>
    /* Ensure select elements have proper width before TomSelect initializes */
    .dough-item-select {
        width: 100% !important;
        min-width: 200px !important;
    }

    .recipe-select {
        width: 100% !important;
        min-width: 150px !important;
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
    
    .form-label-sm {
        font-size: 0.75rem;
        margin-bottom: 0.25rem;
    }
    
    /* Make Dough/Recipe/Ingredient columns responsive and top-aligned */
    #step1-table th:nth-child(1),
    #step1-table td:nth-child(1) {
        width: 25% !important;
        vertical-align: top !important;
    }

    #step1-table th:nth-child(2),
    #step1-table td:nth-child(2) {
        width: 25% !important;
        vertical-align: top !important;
    }

    #step1-table th:nth-child(3),
    #step1-table td:nth-child(3) {
        width: 45% !important;
        vertical-align: top !important;
    }

    .ingredient-helper-text {
        font-size: 0.75rem;
        color: #6c757d;
    }

    .ingredient-table-wrapper {
        max-height: 320px;
        overflow-y: auto;
    }
</style>
@endpush

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Manufacturing
                </div>
                <h2 class="page-title">
                    Edit Production Plan
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
        
        <form method="POST" action="{{ route('manufacturing.production-plans.update', $productionPlan) }}" id="production-plan-form">
            @csrf
            @method('PUT')
            
            <div class="row row-deck row-cards mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Plan Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label required">Plan Date</label>
                                <input type="date" class="form-control @error('plan_date') is-invalid @enderror" 
                                       name="plan_date" value="{{ old('plan_date', $productionPlan->plan_date->format('Y-m-d')) }}" required>
                                @error('plan_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">Production start date will be automatically set to plan date + 1 day</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Production Start Date</label>
                                <input type="date" class="form-control @error('production_start_date') is-invalid @enderror" 
                                       name="production_start_date" value="{{ old('production_start_date', $productionPlan->production_start_date?->format('Y-m-d')) }}">
                                @error('production_start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ready Date</label>
                                <input type="date" class="form-control @error('ready_date') is-invalid @enderror" 
                                       name="ready_date" value="{{ old('ready_date', $productionPlan->ready_date?->format('Y-m-d')) }}">
                                @error('ready_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          name="notes" rows="3" placeholder="Optional notes about this production plan">{{ old('notes', $productionPlan->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control" value="{{ ucfirst($productionPlan->status) }}" disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row row-deck row-cards mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Step 1: Dough Production Planning (Adn)</h3>
                            <div class="card-actions">
                                <button type="button" class="btn btn-sm btn-primary" onclick="addStep1Row()">
                                    <i class="far fa-plus"></i>&nbsp;Add Row
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-vcenter" id="step1-table">
                                    <thead>
                                        <tr>
                                            <th>Dough Item &amp; Qty</th>
                                            <th>Recipe</th>
                                            <th>Ingredients</th>
                                            <th class="w-1"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="step1-tbody">
                                        @forelse($productionPlan->step1 as $index => $step1)
                                        <tr class="step1-row">
                                            <td>
                                                <select name="step1[{{ $index }}][dough_item_id]" class="form-select dough-item-select" id="dough-item-select-{{ $index }}" required data-row-index="{{ $index }}">
                                                    <option value="">Select Dough Item</option>
                                                    @foreach($doughItems as $id => $label)
                                                    <option value="{{ $id }}" {{ $step1->dough_item_id == $id ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="row g-2 mt-2">
                                                    <div class="col-sm-6">
                                                        <label class="form-label form-label-sm">Qty GL1</label>
                                                        <input type="number" name="step1[{{ $index }}][qty_gl1]" class="form-control form-control-sm" step="1" min="0" inputmode="numeric" value="{{ $step1->qty_gl1 }}" required>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <label class="form-label form-label-sm">Qty GL2</label>
                                                        <input type="number" name="step1[{{ $index }}][qty_gl2]" class="form-control form-control-sm" step="1" min="0" inputmode="numeric" value="{{ $step1->qty_gl2 }}" required>
                                                    </div>
                                                </div>
                                                <div class="row g-2 mt-2">
                                                    <div class="col-sm-6">
                                                        <label class="form-label form-label-sm">Qty TA</label>
                                                        <input type="number" name="step1[{{ $index }}][qty_ta]" class="form-control form-control-sm" step="1" min="0" inputmode="numeric" value="{{ $step1->qty_ta }}" required>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <label class="form-label form-label-sm">Qty BL</label>
                                                        <input type="number" name="step1[{{ $index }}][qty_bl]" class="form-control form-control-sm" step="1" min="0" inputmode="numeric" value="{{ $step1->qty_bl }}" required>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <select name="step1[{{ $index }}][recipe_id]" class="form-select recipe-select" id="recipe-select-{{ $index }}" data-row-index="{{ $index }}" required>
                                                    <option value="">Select Recipe</option>
                                                </select>
                                                <small class="text-muted d-block mt-2">Select a recipe to load its ingredients.</small>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                                    <span class="ingredient-helper-text">Ingredient changes are saved only in this plan.</span>
                                                    <button type="button" class="btn btn-sm btn-outline-primary add-ingredient-btn" onclick="addIngredientRow({{ $index }})">
                                                        <i class="far fa-plus"></i> Add Ingredient
                                                    </button>
                                                </div>
                                                <div class="ingredient-table-wrapper">
                                                    <table class="table table-sm table-bordered mb-0 ingredients-table" id="ingredients-table-{{ $index }}">
                                                        <thead>
                                                            <tr>
                                                                <th>Ingredient</th>
                                                                <th>Quantity</th>
                                                                <th>Unit</th>
                                                                <th class="w-1"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="ingredients-tbody" id="ingredients-tbody-{{ $index }}">
                                                            @foreach($step1->recipeIngredients as $ingIndex => $ingredient)
                                                            <tr class="ingredient-row">
                                                                <td>
                                                                    <select name="step1[{{ $index }}][ingredients][{{ $ingIndex }}][ingredient_item_id]" class="form-select form-select-sm ingredient-item-select" required>
                                                                        <option value="">Select Item</option>
                                                                        @foreach($ingredientItems as $item)
                                                                        <option value="{{ $item->id }}" {{ $ingredient->ingredient_item_id == $item->id ? 'selected' : '' }}>{{ $item->label }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <input type="number" name="step1[{{ $index }}][ingredients][{{ $ingIndex }}][quantity]" class="form-control form-control-sm" step="0.001" min="0.001" value="{{ $ingredient->quantity }}" required>
                                                                </td>
                                                                <td>
                                                                    <input type="text" name="step1[{{ $index }}][ingredients][{{ $ingIndex }}][unit]" class="form-control form-control-sm ingredient-unit-input" value="{{ $ingredient->unit }}" maxlength="15">
                                                                </td>
                                                                <td>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeIngredientRow(this)">
                                                                        <i class="far fa-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                                                    <i class="far fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr class="step1-row">
                                            <td>
                                                <select name="step1[0][dough_item_id]" class="form-select dough-item-select" id="dough-item-select-0" required data-row-index="0">
                                                    <option value="">Select Dough Item</option>
                                                    @foreach($doughItems as $id => $label)
                                                    <option value="{{ $id }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="row g-2 mt-2">
                                                    <div class="col-sm-6">
                                                        <label class="form-label form-label-sm">Qty GL1</label>
                                                        <input type="number" name="step1[0][qty_gl1]" class="form-control form-control-sm" step="1" min="0" inputmode="numeric" value="0" required>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <label class="form-label form-label-sm">Qty GL2</label>
                                                        <input type="number" name="step1[0][qty_gl2]" class="form-control form-control-sm" step="1" min="0" inputmode="numeric" value="0" required>
                                                    </div>
                                                </div>
                                                <div class="row g-2 mt-2">
                                                    <div class="col-sm-6">
                                                        <label class="form-label form-label-sm">Qty TA</label>
                                                        <input type="number" name="step1[0][qty_ta]" class="form-control form-control-sm" step="1" min="0" inputmode="numeric" value="0" required>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <label class="form-label form-label-sm">Qty BL</label>
                                                        <input type="number" name="step1[0][qty_bl]" class="form-control form-control-sm" step="1" min="0" inputmode="numeric" value="0" required>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <select name="step1[0][recipe_id]" class="form-select recipe-select" id="recipe-select-0" data-row-index="0" required>
                                                    <option value="">Select Recipe</option>
                                                </select>
                                                <small class="text-muted d-block mt-2">Select a recipe to load its ingredients.</small>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                                    <span class="ingredient-helper-text">Ingredient changes are saved only in this plan.</span>
                                                    <button type="button" class="btn btn-sm btn-outline-primary add-ingredient-btn" onclick="addIngredientRow(0)">
                                                        <i class="far fa-plus"></i> Add Ingredient
                                                    </button>
                                                </div>
                                                <div class="ingredient-table-wrapper">
                                                    <table class="table table-sm table-bordered mb-0 ingredients-table" id="ingredients-table-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Ingredient</th>
                                                                <th>Quantity</th>
                                                                <th>Unit</th>
                                                                <th class="w-1"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="ingredients-tbody" id="ingredients-tbody-0"></tbody>
                                                    </table>
                                                </div>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                                                    <i class="far fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-footer text-end">
                            <a href="{{ route('manufacturing.production-plans.show', $productionPlan) }}" class="btn btn-link">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Production Plan</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/tabler/libs/tom-select/dist/js/tom-select.complete.min.js') }}"></script>
<script>
const doughItemsData = @json($doughItems->map(fn($label, $id) => ['value' => (string)$id, 'text' => $label])->values());
const ingredientItems = @json($ingredientItems->map(fn($item) => ['id' => $item->id, 'name' => $item->label, 'unit' => $item->unit]));

// Step 1 data from server
@php
$step1DataArray = $productionPlan->step1->map(function($step) {
    return [
        'recipe_id' => $step->recipe_id,
        'recipe_name' => $step->recipe_name,
        'recipe_date' => optional($step->recipe_date)->format('Y-m-d'),
        'ingredients' => $step->recipeIngredients->map(function($ing) {
            return [
                'ingredient_item_id' => $ing->ingredient_item_id,
                'quantity' => $ing->quantity,
                'unit' => $ing->unit,
            ];
        })->toArray(),
    ];
})->toArray();
@endphp
const step1Data = @json($step1DataArray);

let rowIndex = {{ $productionPlan->step1->count() }};
const tomSelectInstances = new Map(); // Store TomSelect instances by row index
let ingredientIndexes = {};
const previousRecipeValues = {}; // Track previous recipe values for each row
const isInitializing = {}; // Track which rows are being initialized
@foreach($productionPlan->step1 as $index => $step1)
ingredientIndexes[{{ $index }}] = {{ $step1->recipeIngredients->count() }};
isInitializing[{{ $index }}] = true; // Mark as initializing
@endforeach
if (Object.keys(ingredientIndexes).length === 0) {
    ingredientIndexes[0] = 0;
}

function initializeTomSelectsForRow(rowIndex) {
    const doughSelect = document.getElementById(`dough-item-select-${rowIndex}`);
    const recipeSelect = document.getElementById(`recipe-select-${rowIndex}`);
    
    if (!doughSelect || !recipeSelect || typeof TomSelect === 'undefined') {
        return;
    }
    
    // Clean up Dough Item TomSelect
    if (doughSelect.tomselect) {
        try {
            doughSelect.tomselect.destroy();
        } catch(e) {}
    }
    doughSelect.classList.remove('tomselected');
    
    // Remove any broken wrappers for dough select
    const doughWrapper = doughSelect.closest('.ts-wrapper');
    if (doughWrapper && (doughWrapper.offsetWidth === 0 || doughWrapper.style.display === 'none')) {
        doughWrapper.remove();
    }
    
    // Clean up Recipe TomSelect
    if (recipeSelect.tomselect) {
        try {
            recipeSelect.tomselect.destroy();
        } catch(e) {}
    }
    recipeSelect.classList.remove('tomselected');
    
    // Remove any broken wrappers for recipe select
    const recipeWrapper = recipeSelect.closest('.ts-wrapper');
    if (recipeWrapper && (recipeWrapper.offsetWidth === 0 || recipeWrapper.style.display === 'none')) {
        recipeWrapper.remove();
    }
    
    // Clean up existing instances from our Map
    if (tomSelectInstances.has(`dough-${rowIndex}`)) {
        try {
            tomSelectInstances.get(`dough-${rowIndex}`).destroy();
        } catch(e) {}
        tomSelectInstances.delete(`dough-${rowIndex}`);
    }
    if (tomSelectInstances.has(`recipe-${rowIndex}`)) {
        try {
            tomSelectInstances.get(`recipe-${rowIndex}`).destroy();
        } catch(e) {}
        tomSelectInstances.delete(`recipe-${rowIndex}`);
    }
    
    // Initialize Dough Item TomSelect if not already wrapped
    if (!doughSelect.closest('.ts-wrapper')) {
        const doughTomSelect = new TomSelect(`#dough-item-select-${rowIndex}`, {
            allowEmptyOption: true,
            placeholder: 'Select Dough Item',
            sortField: {
                field: 'text',
                direction: 'asc'
            },
            dropdownParent: 'body',
            onChange: function(value) {
                loadRecipes(value, rowIndex);
            }
        });
        tomSelectInstances.set(`dough-${rowIndex}`, doughTomSelect);
    }
    
    // Initialize Recipe TomSelect if not already wrapped
    if (!recipeSelect.closest('.ts-wrapper')) {
        const recipeTomSelect = new TomSelect(`#recipe-select-${rowIndex}`, {
            allowEmptyOption: true,
            placeholder: 'Select Recipe',
            sortField: {
                field: 'text',
                direction: 'asc'
            },
            dropdownParent: 'body',
            onChange: function(value) {
                // Get previous value before it changes
                const previousValue = previousRecipeValues[rowIndex] || null;
                // Store current value as previous for next change
                previousRecipeValues[rowIndex] = value || null;
                handleRecipeSelection(value, rowIndex, previousValue);
            }
        });
        tomSelectInstances.set(`recipe-${rowIndex}`, recipeTomSelect);
        // Store initial value
        if (recipeSelect.value) {
            previousRecipeValues[rowIndex] = recipeSelect.value;
        }
    }
}

function addStep1Row() {
    const tbody = document.getElementById('step1-tbody');
    const row = tbody.querySelector('.step1-row').cloneNode(true);
    
    // Update indices
    row.querySelectorAll('[name]').forEach(input => {
        const name = input.getAttribute('name');
        if (name) {
            input.setAttribute('name', name.replace(/\[(\d+)\]/, (match, num) => {
                return `[${rowIndex}]`;
            }));
        }
    });
    
    // Update IDs and data attributes
    row.querySelectorAll('[id]').forEach(el => {
        const id = el.getAttribute('id');
        if (id && id.includes('-0')) {
            el.setAttribute('id', id.replace('-0', `-${rowIndex}`));
        }
    });
    
    row.querySelectorAll('[data-row-index]').forEach(el => {
        el.setAttribute('data-row-index', rowIndex);
    });
    
    // Reset values
    row.querySelectorAll('input[type="number"]').forEach(input => {
        if (input.name.includes('qty_')) {
            input.value = '0';
        }
    });
    row.querySelectorAll('select').forEach(select => {
        if (!select.classList.contains('dough-item-select')) {
            select.selectedIndex = 0;
        }
    });
    row.querySelector('.recipe-select').id = `recipe-select-${rowIndex}`;
    
    // Update IDs for dough and recipe selects
    const doughSelect = row.querySelector('.dough-item-select');
    if (doughSelect) {
        doughSelect.id = `dough-item-select-${rowIndex}`;
        doughSelect.setAttribute('data-row-index', rowIndex);
    }
    
    const recipeSelect = row.querySelector('.recipe-select');
    if (recipeSelect) {
        recipeSelect.id = `recipe-select-${rowIndex}`;
        recipeSelect.setAttribute('data-row-index', rowIndex);
    }
    
    const ingredientsTable = row.querySelector('.ingredients-table');
    if (ingredientsTable) {
        ingredientsTable.id = `ingredients-table-${rowIndex}`;
    }
    
    const ingredientsTbody = row.querySelector('.ingredients-tbody');
    if (ingredientsTbody) {
        ingredientsTbody.id = `ingredients-tbody-${rowIndex}`;
        ingredientsTbody.innerHTML = '';
    }
    
    const addIngredientBtn = row.querySelector('.add-ingredient-btn');
    if (addIngredientBtn) {
        addIngredientBtn.setAttribute('onclick', `addIngredientRow(${rowIndex})`);
    }
    
    ingredientIndexes[rowIndex] = 0;
    previousRecipeValues[rowIndex] = null;
    
    tbody.appendChild(row);
    
    // Initialize TomSelect for the new row
    initializeTomSelectsForRow(rowIndex);
    
    rowIndex++;
}

function removeRow(button) {
    const row = button.closest('.step1-row');
    
    // Find the dough select element (could be wrapped by TomSelect)
    const doughSelect = row.querySelector('select.dough-item-select') || row.querySelector('.dough-item-select');
    const rowIndexAttr = doughSelect ? doughSelect.getAttribute('data-row-index') : null;
    
    if (rowIndexAttr) {
        // Clean up TomSelect instances
        if (tomSelectInstances.has(`dough-${rowIndexAttr}`)) {
            try {
                tomSelectInstances.get(`dough-${rowIndexAttr}`).destroy();
            } catch(e) {}
            tomSelectInstances.delete(`dough-${rowIndexAttr}`);
        }
        if (tomSelectInstances.has(`recipe-${rowIndexAttr}`)) {
            try {
                tomSelectInstances.get(`recipe-${rowIndexAttr}`).destroy();
            } catch(e) {}
            tomSelectInstances.delete(`recipe-${rowIndexAttr}`);
        }
    }
    
    if (document.getElementById('step1-tbody').querySelectorAll('.step1-row').length > 1) {
        row.remove();
    } else {
        alert('At least one row is required.');
    }
}

function loadRecipes(doughItemId, index, skipIngredientHydration = false) {
    const recipeSelect = document.getElementById(`recipe-select-${index}`);
    const recipeTomSelect = tomSelectInstances.get(`recipe-${index}`);
    
    if (!doughItemId) {
        if (recipeTomSelect) {
            recipeTomSelect.clear();
            recipeTomSelect.clearOptions();
            recipeTomSelect.addOption({ value: '', text: 'Select Recipe' });
            recipeTomSelect.refreshOptions(false);
        } else {
            recipeSelect.innerHTML = '<option value="">Select Recipe</option>';
        }
        if (!skipIngredientHydration) {
            resetIngredients(index);
        }
        return Promise.resolve();
    }
    
    // Show loading state
    if (recipeTomSelect) {
        recipeTomSelect.clear();
        recipeTomSelect.clearOptions();
        recipeTomSelect.addOption({ value: '', text: 'Loading...' });
        recipeTomSelect.setValue('');
        recipeTomSelect.refreshOptions(false);
    } else {
        recipeSelect.innerHTML = '<option value="">Loading...</option>';
    }
    
    return fetch(`{{ route('manufacturing.production-plans.recipes') }}?dough_item_id=${doughItemId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (recipeTomSelect) {
                // Clear existing options
                recipeTomSelect.clear();
                recipeTomSelect.clearOptions();
                
                // Add default option
                recipeTomSelect.addOption({ value: '', text: 'Select Recipe' });
                
                // Add recipe options using TomSelect API
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(recipe => {
                        recipeTomSelect.addOption({
                            value: String(recipe.id),
                            text: `${recipe.name} (${recipe.recipe_date})`
                        });
                    });
                }
                
                // Refresh options to display them
                recipeTomSelect.refreshOptions(false);
                
                // Restore existing recipe if any (from step1Data)
                if (step1Data[index] && step1Data[index].recipe_id) {
                    const savedRecipeId = String(step1Data[index].recipe_id);
                    // Use silent=true to prevent onChange from firing during initialization
                    recipeTomSelect.setValue(savedRecipeId, true);
                    // Store initial recipe value
                    previousRecipeValues[index] = savedRecipeId;
                    
                    // Check if ingredients already exist in DOM (from Blade template)
                    const existingIngredients = document.querySelectorAll(`#ingredients-tbody-${index} .ingredient-row`);
                    if (skipIngredientHydration) {
                        // Skip hydration - ingredients are already in DOM from Blade template
                        // Just ensure ingredientIndexes is set correctly
                        if (existingIngredients.length > 0) {
                            ingredientIndexes[index] = existingIngredients.length;
                        }
                        // Mark initialization as complete
                        if (isInitializing[index]) {
                            isInitializing[index] = false;
                        }
                    } else if (existingIngredients.length > 0) {
                        // Ingredients already exist, don't overwrite them
                        // Just ensure ingredientIndexes is set correctly
                        ingredientIndexes[index] = existingIngredients.length;
                        // Mark initialization as complete
                        if (isInitializing[index]) {
                            isInitializing[index] = false;
                        }
                    } else {
                        // No existing ingredients, hydrate from saved data
                        const hydrated = hydrateIngredientsFromSavedData(index);
                        if (!hydrated) {
                            loadRecipeIngredients(savedRecipeId, index, skipIngredientHydration);
                        } else {
                            // Mark initialization as complete if hydrated
                            if (isInitializing[index]) {
                                isInitializing[index] = false;
                            }
                        }
                    }
                    delete step1Data[index];
                }
            } else {
                // Fallback for non-TomSelect selects
                recipeSelect.innerHTML = '<option value="">Select Recipe</option>';
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(recipe => {
                        const option = document.createElement('option');
                        option.value = recipe.id;
                        option.textContent = `${recipe.name} (${recipe.recipe_date})`;
                        recipeSelect.appendChild(option);
                    });
                }
                
                // Restore existing recipe if any (from step1Data)
                if (step1Data[index] && step1Data[index].recipe_id) {
                    const savedRecipeId = String(step1Data[index].recipe_id);
                    recipeSelect.value = savedRecipeId;
                    // Store initial recipe value
                    previousRecipeValues[index] = savedRecipeId;
                    
                    // Check if ingredients already exist in DOM (from Blade template)
                    const existingIngredients = document.querySelectorAll(`#ingredients-tbody-${index} .ingredient-row`);
                    if (skipIngredientHydration) {
                        // Skip hydration - ingredients are already in DOM from Blade template
                        // Just ensure ingredientIndexes is set correctly
                        if (existingIngredients.length > 0) {
                            ingredientIndexes[index] = existingIngredients.length;
                        }
                        // Mark initialization as complete
                        if (isInitializing[index]) {
                            isInitializing[index] = false;
                        }
                    } else if (existingIngredients.length > 0) {
                        // Ingredients already exist, don't overwrite them
                        // Just ensure ingredientIndexes is set correctly
                        ingredientIndexes[index] = existingIngredients.length;
                        // Mark initialization as complete
                        if (isInitializing[index]) {
                            isInitializing[index] = false;
                        }
                    } else {
                        // No existing ingredients, hydrate from saved data
                        const hydrated = hydrateIngredientsFromSavedData(index);
                        if (!hydrated) {
                            loadRecipeIngredients(savedRecipeId, index, skipIngredientHydration);
                        } else {
                            // Mark initialization as complete if hydrated
                            if (isInitializing[index]) {
                                isInitializing[index] = false;
                            }
                        }
                    }
                    delete step1Data[index];
                }
            }
        })
        .catch(error => {
            console.error('Error loading recipes:', error);
            if (recipeTomSelect) {
                recipeTomSelect.clear();
                recipeTomSelect.clearOptions();
                recipeTomSelect.addOption({ value: '', text: 'Error loading recipes' });
                recipeTomSelect.refreshOptions(false);
            } else {
                recipeSelect.innerHTML = '<option value="">Error loading recipes</option>';
            }
        });
}

function resetIngredients(rowIndex) {
    const ingredientsTbody = document.getElementById(`ingredients-tbody-${rowIndex}`);
    if (!ingredientsTbody) {
        return;
    }

    ingredientsTbody.innerHTML = '';
    ingredientIndexes[rowIndex] = 0;
}

function loadRecipeIngredients(recipeId, rowIndex, skipIfExists = false) {
    if (!recipeId) {
        return;
    }
    
    // If we're initializing and ingredients already exist, don't load recipe ingredients
    if (skipIfExists || isInitializing[rowIndex]) {
        const existingIngredients = document.querySelectorAll(`#ingredients-tbody-${rowIndex} .ingredient-row`);
        if (existingIngredients.length > 0) {
            // Ingredients already exist, don't overwrite them
            isInitializing[rowIndex] = false; // Mark initialization as complete
            return;
        }
    }
    
    fetch(`{{ route('manufacturing.production-plans.recipe-ingredients') }}?recipe_id=${recipeId}`)
        .then(response => response.json())
        .then(ingredients => {
            resetIngredients(rowIndex);
            
            ingredients.forEach(ingredient => {
                addIngredientRow(rowIndex, ingredient);
            });
            
            if (isInitializing[rowIndex]) {
                isInitializing[rowIndex] = false; // Mark initialization as complete
            }
        })
        .catch(error => {
            console.error('Error loading recipe ingredients:', error);
            if (isInitializing[rowIndex]) {
                isInitializing[rowIndex] = false; // Mark initialization as complete even on error
            }
        });
}

function hydrateIngredientsFromSavedData(index) {
    if (!step1Data[index] || !Array.isArray(step1Data[index].ingredients) || step1Data[index].ingredients.length === 0) {
        return false;
    }

    resetIngredients(index);
    step1Data[index].ingredients.forEach(ingredient => addIngredientRow(index, ingredient));

    return true;
}

function handleRecipeSelection(value, index, previousValue = null) {
    // Don't handle recipe selection during initialization
    if (isInitializing[index]) {
        return;
    }
    
    if (value) {
        // When user manually changes recipe, ask for confirmation if ingredients exist
        const existingIngredients = document.querySelectorAll(`#ingredients-tbody-${index} .ingredient-row`);
        if (existingIngredients.length > 0) {
            // Ask user if they want to replace existing ingredients
            if (!confirm('Changing the recipe will replace the current ingredients with the recipe\'s default ingredients. Do you want to continue?')) {
                // User cancelled - restore previous recipe selection
                const recipeTomSelect = tomSelectInstances.get(`recipe-${index}`);
                if (recipeTomSelect) {
                    if (previousValue) {
                        recipeTomSelect.setValue(String(previousValue), true);
                        // Restore previous value in tracking
                        previousRecipeValues[index] = previousValue;
                    } else {
                        recipeTomSelect.clear();
                        previousRecipeValues[index] = null;
                    }
                }
                return;
            }
        }
        // Load recipe ingredients (either no existing ingredients, or user confirmed)
        resetIngredients(index);
        loadRecipeIngredients(value, index, false);
    } else {
        resetIngredients(index);
        previousRecipeValues[index] = null;
    }
}


function addIngredientRow(rowIndex, ingredientData = null) {
    if (!ingredientIndexes.hasOwnProperty(rowIndex)) {
        ingredientIndexes[rowIndex] = 0;
    }
    
    const ingIndex = ingredientIndexes[rowIndex]++;
    const tbody = document.getElementById(`ingredients-tbody-${rowIndex}`);
    if (!tbody) {
        return;
    }
    
    const row = document.createElement('tr');
    row.className = 'ingredient-row';
    const selectedIngredientId = ingredientData ? ingredientData.ingredient_item_id : '';
    const quantityValue = ingredientData && typeof ingredientData.quantity !== 'undefined' ? ingredientData.quantity : '';
    const unitValue = ingredientData && typeof ingredientData.unit !== 'undefined' ? ingredientData.unit : '';
    row.innerHTML = `
        <td>
            <select name="step1[${rowIndex}][ingredients][${ingIndex}][ingredient_item_id]" class="form-select form-select-sm ingredient-item-select" required>
                <option value="">Select Item</option>
                ${ingredientItems.map(item => 
                    `<option value="${item.id}" data-unit="${item.unit || ''}" ${selectedIngredientId == item.id ? 'selected' : ''}>${item.name}</option>`
                ).join('')}
            </select>
        </td>
        <td>
            <input type="number" name="step1[${rowIndex}][ingredients][${ingIndex}][quantity]" class="form-control form-control-sm" step="0.001" min="0.001" value="${quantityValue}" required>
        </td>
        <td>
            <input type="text" name="step1[${rowIndex}][ingredients][${ingIndex}][unit]" class="form-control form-control-sm ingredient-unit-input" value="${unitValue}" maxlength="15">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeIngredientRow(this)">
                <i class="far fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);

    const selectElement = row.querySelector('.ingredient-item-select');
    if (selectElement) {
        selectElement.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const unitInput = row.querySelector('.ingredient-unit-input');
            if (unitInput && selectedOption) {
                unitInput.value = selectedOption.dataset.unit || '';
            }
        });

        if (!unitValue && selectElement.selectedIndex > -1) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const unitInput = row.querySelector('.ingredient-unit-input');
            if (unitInput && selectedOption) {
                unitInput.value = selectedOption.dataset.unit || '';
            }
        }
    }
}

function removeIngredientRow(button) {
    button.closest('.ingredient-row').remove();
}

// Initialize TomSelect for the first row when DOM is ready
function initInitialTomSelects() {
    if (typeof TomSelect !== 'undefined') {
        // Initialize TomSelect for all existing rows
        document.querySelectorAll('.dough-item-select').forEach((select) => {
            const index = parseInt(select.getAttribute('data-row-index'));
            if (!isNaN(index)) {
                initializeTomSelectsForRow(index);
                
                // Load recipes if dough item is already selected
                // Skip ingredient hydration since ingredients are already rendered from Blade template
                const doughItemId = select.value;
                if (doughItemId) {
                    loadRecipes(doughItemId, index, true).catch(error => {
                        console.error('Error initializing row:', error);
                    });
                }
            }
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initInitialTomSelects);
} else {
    initInitialTomSelects();
}
</script>
@endpush
@endsection

