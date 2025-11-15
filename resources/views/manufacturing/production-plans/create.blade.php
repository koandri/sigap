@extends('layouts.app')

@section('title', 'Create Production Plan')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/tabler/libs/tom-select/dist/css/tom-select.bootstrap5.min.css') }}" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
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
    
    /* Fix form-hint spacing to prevent overlapping */
    .form-hint {
        display: block !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0 !important;
        clear: both !important;
        line-height: 1.5 !important;
    }
    
    /* Ensure proper spacing after form controls and error messages */
    .form-control + .invalid-feedback + .form-hint,
    .form-control + .form-hint {
        margin-top: 0.5rem !important;
    }
    
    /* Ensure card-body has proper padding */
    .card-body .mb-3:last-child {
        margin-bottom: 0 !important;
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
    
    /* Select2 styling for ingredient selects */
    .ingredient-item-select + .select2-container {
        width: 100% !important;
    }
    
    .ingredient-item-select + .select2-container .select2-selection {
        min-height: calc(1.5em + 0.5rem + 2px) !important;
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
        border: 1px solid #dadce0 !important;
        border-radius: 4px !important;
    }
    
    .ingredient-item-select + .select2-container .select2-selection__rendered {
        padding-left: 0 !important;
        padding-right: 0 !important;
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
                    Create Production Plan
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('manufacturing.production-plans.index') }}" class="btn btn-outline-secondary d-none d-sm-inline-block">
                        <i class="far fa-arrow-left"></i>&nbsp;
                        Back to Plans
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')
        
        <form method="POST" action="{{ route('manufacturing.production-plans.store') }}" id="production-plan-form">
            @csrf
            
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
                                       name="plan_date" value="{{ old('plan_date', date('Y-m-d')) }}" required>
                                @error('plan_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">Production start date will be automatically set to plan date + 1 day</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Production Start Date</label>
                                <input type="date" class="form-control @error('production_start_date') is-invalid @enderror"
                                       name="production_start_date"
                                       value="{{ old('production_start_date', date('Y-m-d', strtotime('+1 day'))) }}">
                                @error('production_start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">Defaults to plan date + 1 day when left blank.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ready Date</label>
                                <input type="date" class="form-control @error('ready_date') is-invalid @enderror"
                                       name="ready_date"
                                       value="{{ old('ready_date', date('Y-m-d', strtotime('+3 day'))) }}">
                                @error('ready_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">Defaults to production start date + 2 days when left blank.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          name="notes" rows="3" placeholder="Optional notes about this production plan">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                        <tr class="step1-row">
                                            <td>
                                                <select name="step1[0][dough_item_id]" class="form-select dough-item-select" id="dough-item-select-0" data-row-index="0" required>
                                                    <option value="">Select Dough Item</option>
                                                    @foreach($doughItems as $item)
                                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
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
                            <a href="{{ route('manufacturing.production-plans.index') }}" class="btn btn-link">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Production Plan</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/tabler/libs/tom-select/dist/js/tom-select.complete.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const doughItemsData = @json($doughItems->map(fn($item) => ['value' => (string)$item->id, 'text' => $item->name]));
const ingredientItems = @json($ingredientItems->map(fn($item) => ['id' => $item->id, 'name' => $item->name, 'unit' => $item->unit]));

let rowIndex = 1;
const tomSelectInstances = new Map(); // Store TomSelect instances by row index
let ingredientIndexes = {0: 0}; // Track ingredient indexes per row

function initializeTomSelectsForRow(rowIndex) {
    const doughSelect = document.getElementById(`dough-item-select-${rowIndex}`);
    const recipeSelect = document.getElementById(`recipe-select-${rowIndex}`);
    
    if (!doughSelect || !recipeSelect || typeof TomSelect === 'undefined') {
        return;
    }
    
    // Clean up Dough Item TomSelect - matching Yield Guideline cleanup logic
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
    
    // Clean up Recipe TomSelect - matching Yield Guideline cleanup logic
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
                handleRecipeSelection(value, rowIndex);
            }
        });
        tomSelectInstances.set(`recipe-${rowIndex}`, recipeTomSelect);
    }
}

function addStep1Row() {
    const tbody = document.getElementById('step1-tbody');
    const originalRow = tbody.querySelector('.step1-row');
    
    // Get the original select elements - TomSelect keeps the original select in the DOM
    // We need to find them by traversing through any TomSelect wrappers
    const findOriginalSelect = (row, className) => {
        // First try to find it directly
        let select = row.querySelector(`select.${className}`);
        if (select) return select;
        
        // If wrapped by TomSelect, it should still be in the DOM
        select = row.querySelector(`.${className}`);
        if (select && select.tagName === 'SELECT') return select;
        
        // Last resort: find within any wrapper
        const wrapper = row.querySelector(`.ts-wrapper`);
        if (wrapper) {
            select = wrapper.querySelector(`select.${className}`);
            if (select) return select;
        }
        
        return null;
    };
    
    // Clone the row
    const row = originalRow.cloneNode(true);
    
    // Remove any TomSelect wrappers that were cloned
    row.querySelectorAll('.ts-wrapper').forEach(wrapper => {
        const select = wrapper.querySelector('select');
        if (select && wrapper.parentNode) {
            wrapper.parentNode.insertBefore(select, wrapper);
            wrapper.remove();
        }
    });
    
    // Find the select elements
    const doughSelect = findOriginalSelect(row, 'dough-item-select');
    const recipeSelect = findOriginalSelect(row, 'recipe-select');
    
    if (!doughSelect || !recipeSelect) {
        console.error('Could not find select elements in cloned row');
        return;
    }
    
    // Update indices
    row.querySelectorAll('[name]').forEach(input => {
        const name = input.getAttribute('name');
        if (name) {
            input.setAttribute('name', name.replace(/\[0\]/, `[${rowIndex}]`));
        }
    });
    
    // Update IDs and data attributes
    doughSelect.id = `dough-item-select-${rowIndex}`;
    doughSelect.setAttribute('data-row-index', rowIndex);
    
    recipeSelect.id = `recipe-select-${rowIndex}`;
    recipeSelect.setAttribute('data-row-index', rowIndex);
    
    // Reset values
    row.querySelectorAll('input[type="number"]').forEach(input => {
        if (input.name.includes('qty_')) {
            input.value = '0';
        }
    });
    // Update ingredient table references
    const ingredientsTable = row.querySelector('.ingredients-table');
    if (ingredientsTable) {
        ingredientsTable.id = `ingredients-table-${rowIndex}`;
    }

    const ingredientsTbody = row.querySelector('.ingredients-tbody');
    if (ingredientsTbody) {
        // Destroy any Select2 instances before clearing
        ingredientsTbody.querySelectorAll('.ingredient-item-select').forEach(select => {
            if (typeof jQuery !== 'undefined' && jQuery(select).data('select2')) {
                jQuery(select).select2('destroy');
            }
        });

        ingredientsTbody.id = `ingredients-tbody-${rowIndex}`;
        ingredientsTbody.innerHTML = '';
    }

    const addIngredientBtn = row.querySelector('.add-ingredient-btn');
    if (addIngredientBtn) {
        addIngredientBtn.setAttribute('onclick', `addIngredientRow(${rowIndex})`);
    }
    
    // Update quantity input IDs if needed
    row.querySelectorAll('[name*="[qty_"]').forEach(input => {
        // IDs are not critical for quantity inputs, but we can add them if needed
    });
    
    ingredientIndexes[rowIndex] = 0;
    
    // Clear and rebuild selects
    doughSelect.innerHTML = '<option value="">Select Dough Item</option>';
    doughItemsData.forEach(item => {
        const option = document.createElement('option');
        option.value = item.value;
        option.textContent = item.text;
        doughSelect.appendChild(option);
    });
    
    recipeSelect.innerHTML = '<option value="">Select Recipe</option>';
    
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
        row.querySelectorAll('.ingredient-item-select').forEach(select => {
            if (typeof jQuery !== 'undefined' && jQuery(select).data('select2')) {
                jQuery(select).select2('destroy');
            }
        });
        row.remove();
    } else {
        alert('At least one row is required.');
    }
}

function loadRecipes(doughItemId, index) {
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
        resetIngredients(index);
        return;
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
    
    fetch(`{{ route('manufacturing.production-plans.recipes') }}?dough_item_id=${doughItemId}`, {
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

    ingredientsTbody.querySelectorAll('.ingredient-item-select').forEach(select => {
        if (typeof jQuery !== 'undefined' && jQuery(select).data('select2')) {
            jQuery(select).select2('destroy');
        }
    });

    ingredientsTbody.innerHTML = '';
    ingredientIndexes[rowIndex] = 0;
}

function handleRecipeSelection(value, index) {
    if (value) {
        resetIngredients(index);
        loadRecipeIngredients(value, index);
    } else {
        resetIngredients(index);
    }
}

function loadRecipeIngredients(recipeId, rowIndex) {
    if (!recipeId) {
        return;
    }
    
    fetch(`{{ route('manufacturing.production-plans.recipe-ingredients') }}?recipe_id=${recipeId}`)
        .then(response => response.json())
        .then(ingredients => {
            resetIngredients(rowIndex);
            
            ingredients.forEach(ingredient => {
                addIngredientRow(rowIndex, ingredient);
            });
        })
        .catch(error => {
            console.error('Error loading recipe ingredients:', error);
        });
}

function addIngredientRow(rowIndex, ingredientData = null) {
    if (!ingredientIndexes.hasOwnProperty(rowIndex)) {
        ingredientIndexes[rowIndex] = 0;
    }
    
    const ingIndex = ingredientIndexes[rowIndex]++;
    const tbody = document.getElementById(`ingredients-tbody-${rowIndex}`);
    if (!tbody) return;
    
    const row = document.createElement('tr');
    row.className = 'ingredient-row';
    
    const selectedIngredientId = ingredientData ? ingredientData.ingredient_item_id : '';
    const quantityValue = ingredientData && typeof ingredientData.quantity !== 'undefined' ? ingredientData.quantity : '';
    const unitValue = ingredientData && typeof ingredientData.unit !== 'undefined' ? ingredientData.unit : '';
    
    row.innerHTML = `
        <td>
            <select name="step1[${rowIndex}][ingredients][${ingIndex}][ingredient_item_id]" class="form-select form-select-sm ingredient-item-select" data-ingredient-index="${ingIndex}" data-row-index="${rowIndex}" required>
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
            <input type="text" name="step1[${rowIndex}][ingredients][${ingIndex}][unit]" class="form-control form-control-sm ingredient-unit-input" value="${unitValue}" maxlength="15" placeholder="Unit">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeIngredientRow(this)">
                <i class="far fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    
    const selectElement = row.querySelector('.ingredient-item-select');
    if (selectElement && typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        jQuery(selectElement).select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Select Item',
            allowClear: true
        }).on('change', function() {
            const selectedOption = jQuery(this).find('option:selected');
            const unit = selectedOption.data('unit') || '';
            const unitInput = row.querySelector('.ingredient-unit-input');
            if (unitInput) {
                unitInput.value = unit;
            }
        });
    }
    
    // Prefill unit field from selected option if not provided
    const initialOption = selectElement ? selectElement.options[selectElement.selectedIndex] : null;
    if (selectElement && initialOption && !unitValue) {
        const unitInput = row.querySelector('.ingredient-unit-input');
        if (unitInput) {
            unitInput.value = initialOption.dataset.unit || '';
        }
    }
}

function removeIngredientRow(button) {
    const row = button.closest('.ingredient-row');
    const select = row.querySelector('.ingredient-item-select');
    
    // Destroy Select2 instance before removing
    if (select && typeof jQuery !== 'undefined' && jQuery(select).data('select2')) {
        jQuery(select).select2('destroy');
    }
    
    row.remove();
}

// Initialize TomSelect for the first row when DOM is ready
function initInitialTomSelects() {
    if (typeof TomSelect !== 'undefined') {
        initializeTomSelectsForRow(0);
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

