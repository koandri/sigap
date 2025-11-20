@extends('layouts.app')

@section('title', 'Copy Recipe: ' . $recipe->name)

@push('css')
<link rel="stylesheet" href="{{ asset('assets/tabler/libs/tom-select/dist/css/tom-select.bootstrap5.min.css') }}" />
<style>
    /* Ensure select elements have proper width before TomSelect initializes */
    .ingredient-item-select,
    #dough-item-select {
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
    
    /* Ensure table cells properly contain TomSelect wrappers */
    #ingredients-table td {
        position: relative;
        vertical-align: middle;
        overflow: visible !important;
    }
    
    #ingredients-table td .ts-wrapper {
        position: relative;
        width: 100%;
        z-index: 1;
    }
    
    /* Ensure table-responsive doesn't clip dropdown */
    #ingredients-table {
        position: relative;
    }
    
    /* When dropdown is appended to body, ensure proper positioning */
    body > .ts-dropdown {
        z-index: 99999 !important;
        position: absolute !important;
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
        z-index: 99999 !important;
        position: absolute !important;
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
                <div class="page-pretitle">
                    Manufacturing
                </div>
                <h2 class="page-title">
                    Copy Recipe: {{ $recipe->name }}
                </h2>
                <div class="text-muted">
                    Copying from: <strong>{{ $recipe->doughItem->name }}</strong> - {{ $recipe->recipe_date->format('d M Y') }}
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('manufacturing.recipes.show', $recipe) }}" class="btn btn-outline-secondary d-none d-sm-inline-block">
                        <i class="far fa-arrow-left"></i>&nbsp;
                        Back to Recipe
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')
        
        <form method="POST" action="{{ route('manufacturing.recipes.duplicate.store', $recipe) }}" id="recipe-form">
            @csrf
            
            <div class="row row-deck row-cards mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recipe Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Dough Item</label>
                                        <select class="form-select @error('dough_item_id') is-invalid @enderror" 
                                                name="dough_item_id" id="dough-item-select" required>
                                            <option value="">Select Dough Item</option>
                                            @foreach($doughItems as $item)
                                            <option value="{{ $item->id }}" {{ old('dough_item_id', $recipe->dough_item_id) == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('dough_item_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Recipe Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               name="name" value="{{ old('name', $recipe->name . ' (Copy)') }}" placeholder="Enter recipe name" maxlength="100" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Recipe Date</label>
                                        <input type="date" class="form-control @error('recipe_date') is-invalid @enderror" 
                                               name="recipe_date" value="{{ old('recipe_date', date('Y-m-d')) }}" required>
                                        @error('recipe_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Date when this recipe becomes effective</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" name="is_active" value="1" 
                                                   {{ old('is_active', true) ? 'checked' : '' }}>
                                            <label class="form-check-label">Active</label>
                                        </div>
                                        <small class="form-hint">Active recipes appear in production plan dropdowns</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          name="description" rows="3" placeholder="Optional description for this recipe">{{ old('description', $recipe->description) }}</textarea>
                                @error('description')
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
                            <h3 class="card-title">Ingredients</h3>
                            <div class="card-actions">
                                <button type="button" class="btn btn-sm btn-primary" onclick="addIngredientRow()">
                                    <i class="far fa-plus"></i>&nbsp;Add Ingredient
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive" style="overflow: visible !important;">
                                <table class="table table-vcenter" id="ingredients-table">
                                    <thead>
                                        <tr>
                                            <th>Ingredient Item</th>
                                            <th>Quantity</th>
                                            <th class="w-1"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="ingredients-tbody">
                                        @foreach($recipe->ingredients->sortBy('sort_order') as $index => $ingredient)
                                        <tr class="ingredient-row">
                                            <td>
                                                <select name="ingredients[{{ $index }}][ingredient_item_id]" class="form-select ingredient-item-select" id="ingredient-item-select-{{ $index }}" required>
                                                    <option value="">Select Ingredient</option>
                                                    @forelse($ingredientItems ?? [] as $item)
                                                    <option value="{{ $item->id }}" {{ $ingredient->ingredient_item_id == $item->id ? 'selected' : '' }}>
                                                        {{ $item->name }}
                                                    </option>
                                                    @empty
                                                    <option value="">No ingredients available</option>
                                                    @endforelse
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="ingredients[{{ $index }}][quantity]" class="form-control" 
                                                       step="0.001" min="0" placeholder="0.000" value="{{ old('ingredients.' . $index . '.quantity', $ingredient->quantity) }}" required>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeIngredientRow(this)">
                                                    <i class="far fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @if($recipe->ingredients->count() === 0)
                                        <tr class="ingredient-row">
                                            <td>
                                                <select name="ingredients[0][ingredient_item_id]" class="form-select ingredient-item-select" id="ingredient-item-select-0" required>
                                                    <option value="">Select Ingredient</option>
                                                    @forelse($ingredientItems ?? [] as $item)
                                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                    @empty
                                                    <option value="">No ingredients available</option>
                                                    @endforelse
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="ingredients[0][quantity]" class="form-control" 
                                                       step="0.001" min="0" placeholder="0.000" required>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeIngredientRow(this)">
                                                    <i class="far fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <small class="form-hint">Ingredients from the source recipe are pre-filled. You can modify them as needed.</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-footer text-end">
                            <a href="{{ route('manufacturing.recipes.show', $recipe) }}" class="btn btn-link">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Recipe</button>
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
let ingredientIndex = {{ $recipe->ingredients->count() > 0 ? $recipe->ingredients->count() : 1 }};
let tomSelectInstances = {};

// Initialize Tom Select - run immediately if DOM is ready, otherwise wait
function initTomSelects() {
    // Dough item select
    const doughSelect = document.getElementById('dough-item-select');
    if (doughSelect) {
        // Clean up any partial initialization
        if (doughSelect.tomselect) {
            try {
                doughSelect.tomselect.destroy();
            } catch(e) {}
        }
        doughSelect.classList.remove('tomselected');
        
        // Remove any broken wrappers
        const wrapper = doughSelect.closest('.ts-wrapper');
        if (wrapper && wrapper.offsetWidth === 0) {
            wrapper.remove();
        }
        
        if (!doughSelect.closest('.ts-wrapper')) {
            new TomSelect('#dough-item-select', {
                allowEmptyOption: true,
                placeholder: 'Select Dough Item',
                sortField: {
                    field: 'text',
                    direction: 'asc'
                },
                dropdownParent: 'body'
            });
        }
    }

    // Initialize all ingredient selects
    const ingredientRows = document.querySelectorAll('.ingredient-row');
    ingredientRows.forEach((row, index) => {
        const selectId = `ingredient-item-select-${index}`;
        initializeIngredientSelect(index);
    });
}

// Run immediately if DOM is ready, otherwise wait for DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTomSelects);
} else {
    // DOM is already ready
    initTomSelects();
}

function initializeIngredientSelect(index) {
    const selectId = `ingredient-item-select-${index}`;
    const selectElement = document.getElementById(selectId);
    
    if (!selectElement) return;
    
    // Clean up any partial initialization
    if (selectElement.tomselect) {
        try {
            selectElement.tomselect.destroy();
        } catch(e) {}
    }
    selectElement.classList.remove('tomselected');
    
    // Remove any broken wrappers
    const wrapper = selectElement.closest('.ts-wrapper');
    if (wrapper && (wrapper.offsetWidth === 0 || wrapper.style.display === 'none')) {
        wrapper.remove();
    }
    
    if (!selectElement.closest('.ts-wrapper')) {
        const instance = new TomSelect(selectElement, {
            allowEmptyOption: true,
            placeholder: 'Select Ingredient',
            maxOptions: null,
            sortField: {
                field: 'text',
                direction: 'asc'
            },
            dropdownParent: 'body'
        });
        
        // Ensure dropdown is positioned correctly when opened
        instance.on('dropdown_open', function() {
            const self = this;
            // Use requestAnimationFrame to ensure DOM is updated
            requestAnimationFrame(() => {
                if (self.dropdown) {
                    self.positionDropdown();
                    // Force visibility
                    self.dropdown.style.display = 'block';
                    self.dropdown.style.visibility = 'visible';
                    self.dropdown.style.zIndex = '99999';
                    self.dropdown.style.position = 'absolute';
                }
            });
        });
        
        // Update position on scroll and resize
        const updatePosition = () => {
            if (instance.isOpen) {
                instance.positionDropdown();
            }
        };
        
        window.addEventListener('scroll', updatePosition, true);
        window.addEventListener('resize', updatePosition);
        
        tomSelectInstances[index] = instance;
    }
}

function addIngredientRow() {
    const tbody = document.getElementById('ingredients-tbody');
    const originalRow = tbody.querySelector('.ingredient-row');
    
    // Clone the row deeply
    const row = originalRow.cloneNode(true);
    
    // Clean up any TomSelect wrappers that might have been cloned
    const clonedWrappers = row.querySelectorAll('.ts-wrapper');
    clonedWrappers.forEach(wrapper => wrapper.remove());
    
    // Update indices
    row.querySelectorAll('[name]').forEach(input => {
        const name = input.getAttribute('name');
        if (name) {
            const match = name.match(/\[(\d+)\]/);
            if (match) {
                input.setAttribute('name', name.replace(/\[(\d+)\]/, `[${ingredientIndex}]`));
            }
        }
    });
    
    // Update IDs
    const selectElement = row.querySelector('.ingredient-item-select');
    if (selectElement) {
        const newSelectId = `ingredient-item-select-${ingredientIndex}`;
        selectElement.id = newSelectId;
        
        // Ensure select is clean - remove any TomSelect classes
        selectElement.classList.remove('tomselected');
        
        // Reset values
        row.querySelectorAll('input[type="number"]').forEach(input => input.value = '');
        selectElement.selectedIndex = 0;
    }
    
    tbody.appendChild(row);
    
    // Initialize Tom Select for the new row after it's appended
    setTimeout(() => {
        initializeIngredientSelect(ingredientIndex);
    }, 0);
    
    ingredientIndex++;
}

function removeIngredientRow(button) {
    const tbody = document.getElementById('ingredients-tbody');
    const row = button.closest('.ingredient-row');
    
    if (tbody.querySelectorAll('.ingredient-row').length > 1) {
        // Destroy Tom Select instance if exists
        const selectElement = row.querySelector('.ingredient-item-select');
        if (selectElement && selectElement.id) {
            const match = selectElement.id.match(/(\d+)$/);
            if (match) {
                const index = parseInt(match[1]);
                if (tomSelectInstances[index]) {
                    tomSelectInstances[index].destroy();
                    delete tomSelectInstances[index];
                }
            }
        }
        
        row.remove();
    } else {
        alert('At least one ingredient is required.');
    }
}
</script>
@endpush
@endsection


