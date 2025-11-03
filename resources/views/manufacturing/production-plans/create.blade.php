@extends('layouts.app')

@section('title', 'Create Production Plan')

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
                            <h3 class="card-title">Step 1: Dough Production Planning (Adonan)</h3>
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
                                            <th>Dough Item</th>
                                            <th>Recipe</th>
                                            <th>Qty GL1</th>
                                            <th>Qty GL2</th>
                                            <th>Qty TA</th>
                                            <th>Qty BL</th>
                                            <th class="w-1"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="step1-tbody">
                                        <tr class="step1-row">
                                            <td>
                                                <select name="step1[0][dough_item_id]" class="form-select dough-item-select" required onchange="loadRecipes(this, 0)">
                                                    <option value="">Select Dough Item</option>
                                                    @foreach($doughItems as $item)
                                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="step1[0][recipe_id]" class="form-select recipe-select" id="recipe-select-0" onchange="handleRecipeSelection(this, 0)">
                                                    <option value="">Select Recipe</option>
                                                </select>
                                                <div class="mt-2">
                                                    <label class="form-check form-check-inline">
                                                        <input type="checkbox" class="form-check-input" name="step1[0][is_custom_recipe]" onchange="toggleCustomRecipe(this, 0)">
                                                        <span class="form-check-label">Custom Recipe</span>
                                                    </label>
                                                </div>
                                                <div class="custom-recipe-fields mt-2" id="custom-recipe-0" style="display: none;">
                                                    <input type="text" name="step1[0][recipe_name]" class="form-control form-control-sm mb-1" placeholder="Recipe Name">
                                                    <input type="date" name="step1[0][recipe_date]" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" name="step1[0][qty_gl1]" class="form-control" step="0.001" min="0" value="0" required>
                                            </td>
                                            <td>
                                                <input type="number" name="step1[0][qty_gl2]" class="form-control" step="0.001" min="0" value="0" required>
                                            </td>
                                            <td>
                                                <input type="number" name="step1[0][qty_ta]" class="form-control" step="0.001" min="0" value="0" required>
                                            </td>
                                            <td>
                                                <input type="number" name="step1[0][qty_bl]" class="form-control" step="0.001" min="0" value="0" required>
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
<script>
let rowIndex = 1;

function addStep1Row() {
    const tbody = document.getElementById('step1-tbody');
    const row = tbody.querySelector('.step1-row').cloneNode(true);
    
    // Update indices
    row.querySelectorAll('[name]').forEach(input => {
        const name = input.getAttribute('name');
        if (name) {
            input.setAttribute('name', name.replace(/\[0\]/, `[${rowIndex}]`));
        }
    });
    
    // Reset values
    row.querySelectorAll('input[type="number"]').forEach(input => input.value = '0');
    row.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
    row.querySelector('.custom-recipe-fields').style.display = 'none';
    row.querySelector('.recipe-select').id = `recipe-select-${rowIndex}`;
    
    // Update onchange handlers
    const doughSelect = row.querySelector('.dough-item-select');
    doughSelect.setAttribute('onchange', `loadRecipes(this, ${rowIndex})`);
    
    const recipeSelect = row.querySelector('.recipe-select');
    recipeSelect.setAttribute('onchange', `handleRecipeSelection(this, ${rowIndex})`);
    
    const customRecipeCheck = row.querySelector('[name*="[is_custom_recipe]"]');
    customRecipeCheck.setAttribute('onchange', `toggleCustomRecipe(this, ${rowIndex})`);
    
    tbody.appendChild(row);
    rowIndex++;
}

function removeRow(button) {
    if (document.getElementById('step1-tbody').querySelectorAll('.step1-row').length > 1) {
        button.closest('.step1-row').remove();
    } else {
        alert('At least one row is required.');
    }
}

function loadRecipes(select, index) {
    const doughItemId = select.value;
    const recipeSelect = document.getElementById(`recipe-select-${index}`);
    
    if (!doughItemId) {
        recipeSelect.innerHTML = '<option value="">Select Recipe</option>';
        return;
    }
    
    recipeSelect.innerHTML = '<option value="">Loading...</option>';
    
    fetch(`{{ route('manufacturing.production-plans.recipes') }}?dough_item_id=${doughItemId}`)
        .then(response => response.json())
        .then(data => {
            recipeSelect.innerHTML = '<option value="">Select Recipe</option>';
            data.forEach(recipe => {
                const option = document.createElement('option');
                option.value = recipe.id;
                option.textContent = `${recipe.name} (${recipe.recipe_date})`;
                recipeSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading recipes:', error);
            recipeSelect.innerHTML = '<option value="">Error loading recipes</option>';
        });
}

function handleRecipeSelection(select, index) {
    const customRecipeFields = document.getElementById(`custom-recipe-${index}`);
    const customRecipeCheck = document.querySelector(`[name="step1[${index}][is_custom_recipe]"]`);
    
    if (select.value) {
        customRecipeCheck.checked = false;
        customRecipeFields.style.display = 'none';
    }
}

function toggleCustomRecipe(checkbox, index) {
    const customRecipeFields = document.getElementById(`custom-recipe-${index}`);
    const recipeSelect = document.getElementById(`recipe-select-${index}`);
    
    if (checkbox.checked) {
        customRecipeFields.style.display = 'block';
        recipeSelect.value = '';
    } else {
        customRecipeFields.style.display = 'none';
    }
}
</script>
@endpush
@endsection

