@extends('layouts.app')

@section('title', 'Create Yield Guideline')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/tabler/libs/tom-select/dist/css/tom-select.bootstrap5.min.css') }}" />
<style>
    /* Ensure select elements have proper width before TomSelect initializes */
    #from_item_id,
    #to_item_id {
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
                <div class="page-pretitle">
                    Manufacturing
                </div>
                <h2 class="page-title">
                    Create Yield Guideline
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('manufacturing.yield-guidelines.index') }}" class="btn btn-outline-secondary d-none d-sm-inline-block">
                        <i class="far fa-arrow-left"></i>&nbsp;
                        Back to Guidelines
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')
        
        <div class="row row-deck row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Yield Guideline Information</h3>
                    </div>
                    <form method="POST" action="{{ route('manufacturing.yield-guidelines.store') }}">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">From Stage</label>
                                        <select name="from_stage" class="form-select @error('from_stage') is-invalid @enderror" required onchange="updateFromItems()">
                                            <option value="">Select From Stage</option>
                                            <option value="adonan" {{ old('from_stage') === 'adonan' ? 'selected' : '' }}>Adonan</option>
                                            <option value="gelondongan" {{ old('from_stage') === 'gelondongan' ? 'selected' : '' }}>Gelondongan</option>
                                            <option value="kerupuk_kg" {{ old('from_stage') === 'kerupuk_kg' ? 'selected' : '' }}>Kerupuk Kg</option>
                                        </select>
                                        @error('from_stage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">To Stage</label>
                                        <select name="to_stage" class="form-select @error('to_stage') is-invalid @enderror" required onchange="updateToItems()">
                                            <option value="">Select To Stage</option>
                                            <option value="gelondongan" {{ old('to_stage') === 'gelondongan' ? 'selected' : '' }}>Gelondongan</option>
                                            <option value="kerupuk_kg" {{ old('to_stage') === 'kerupuk_kg' ? 'selected' : '' }}>Kerupuk Kg</option>
                                            <option value="packing" {{ old('to_stage') === 'packing' ? 'selected' : '' }}>Packing</option>
                                        </select>
                                        @error('to_stage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">From Item</label>
                                        <select name="from_item_id" id="from_item_id" class="form-select @error('from_item_id') is-invalid @enderror" required>
                                            <option value="">Select From Item</option>
                                            @foreach($adonanItems as $item)
                                            <option value="{{ $item->id }}" data-stage="adonan" {{ old('from_item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                            @endforeach
                                            @foreach($gelondonganItems as $item)
                                            <option value="{{ $item->id }}" data-stage="gelondongan" {{ old('from_item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                            @endforeach
                                            @foreach($kerupukKgItems as $item)
                                            <option value="{{ $item->id }}" data-stage="kerupuk_kg" {{ old('from_item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('from_item_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @if(str_contains($message, 'already exists'))
                                            <small class="form-hint text-warning">
                                                <i class="far fa-exclamation-triangle me-1"></i>
                                                A yield guideline already exists for this item combination. Please edit the existing guideline instead.
                                            </small>
                                            @endif
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">To Item</label>
                                        <select name="to_item_id" id="to_item_id" class="form-select @error('to_item_id') is-invalid @enderror" required>
                                            <option value="">Select To Item</option>
                                            @foreach($gelondonganItems as $item)
                                            <option value="{{ $item->id }}" data-stage="gelondongan" {{ old('to_item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                            @endforeach
                                            @foreach($kerupukKgItems as $item)
                                            <option value="{{ $item->id }}" data-stage="kerupuk_kg" {{ old('to_item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                            @endforeach
                                            @foreach($kerupukPackItems as $item)
                                            <option value="{{ $item->id }}" data-stage="packing" {{ old('to_item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('to_item_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 col-lg-4">
                                    <div class="mb-3">
                                        <label class="form-label required">Yield Quantity</label>
                                        <input type="number" class="form-control @error('yield_quantity') is-invalid @enderror" 
                                               name="yield_quantity" value="{{ old('yield_quantity') }}" step="0.001" min="0.001" required>
                                        @error('yield_quantity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">1 from item produces this many units of the to item (e.g., 3.9)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <div class="d-flex">
                                <a href="{{ route('manufacturing.yield-guidelines.index') }}" class="btn btn-link">Cancel</a>
                                <button type="submit" class="btn btn-primary ms-auto">Create Yield Guideline</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/tabler/libs/tom-select/dist/js/tom-select.complete.min.js') }}"></script>
<script>
let fromItemTomSelect = null;
let toItemTomSelect = null;
let allFromItemOptions = [];
let allToItemOptions = [];

function filterOptionsByStage(options, stage) {
    // If no stage is selected, show all options
    if (!stage) return options;
    
    return options.filter(opt => opt.stage === stage);
}

function initializeFromItemSelect() {
    const fromItemSelect = document.getElementById('from_item_id');
    if (!fromItemSelect || typeof TomSelect === 'undefined') return;
    
    // Clean up any partial initialization
    if (fromItemSelect.tomselect) {
        try {
            fromItemSelect.tomselect.destroy();
        } catch(e) {}
    }
    fromItemSelect.classList.remove('tomselected');
    
    // Remove any broken wrappers
    const wrapper = fromItemSelect.closest('.ts-wrapper');
    if (wrapper && (wrapper.offsetWidth === 0 || wrapper.style.display === 'none')) {
        wrapper.remove();
    }
    
    // Filter options based on current stage
    const fromStage = document.querySelector('select[name="from_stage"]').value;
    const filteredFromOptions = filterOptionsByStage(allFromItemOptions, fromStage);
    
    // Clear and rebuild select options
    fromItemSelect.innerHTML = '<option value="">Select From Item</option>';
    filteredFromOptions.forEach(opt => {
        const option = document.createElement('option');
        option.value = opt.value;
        option.textContent = opt.text;
        option.setAttribute('data-stage', opt.stage);
        // Preserve old value if it exists and is still valid
        const oldValue = fromItemSelect.getAttribute('data-old-value');
        if (oldValue && opt.value === oldValue) {
            option.selected = true;
        }
        fromItemSelect.appendChild(option);
    });
    
    // Initialize TomSelect if not already wrapped
    if (!fromItemSelect.closest('.ts-wrapper')) {
        fromItemTomSelect = new TomSelect('#from_item_id', {
            allowEmptyOption: true,
            placeholder: 'Select From Item',
            sortField: {
                field: 'text',
                direction: 'asc'
            }
        });
    }
}

function initializeToItemSelect() {
    const toItemSelect = document.getElementById('to_item_id');
    if (!toItemSelect || typeof TomSelect === 'undefined') return;
    
    // Clean up any partial initialization
    if (toItemSelect.tomselect) {
        try {
            toItemSelect.tomselect.destroy();
        } catch(e) {}
    }
    toItemSelect.classList.remove('tomselected');
    
    // Remove any broken wrappers
    const wrapper = toItemSelect.closest('.ts-wrapper');
    if (wrapper && (wrapper.offsetWidth === 0 || wrapper.style.display === 'none')) {
        wrapper.remove();
    }
    
    // Filter options based on current stage
    const toStage = document.querySelector('select[name="to_stage"]').value;
    const filteredToOptions = filterOptionsByStage(allToItemOptions, toStage);
    
    // Clear and rebuild select options
    toItemSelect.innerHTML = '<option value="">Select To Item</option>';
    filteredToOptions.forEach(opt => {
        const option = document.createElement('option');
        option.value = opt.value;
        option.textContent = opt.text;
        option.setAttribute('data-stage', opt.stage);
        // Preserve old value if it exists and is still valid
        const oldValue = toItemSelect.getAttribute('data-old-value');
        if (oldValue && opt.value === oldValue) {
            option.selected = true;
        }
        toItemSelect.appendChild(option);
    });
    
    // Initialize TomSelect if not already wrapped
    if (!toItemSelect.closest('.ts-wrapper')) {
        toItemTomSelect = new TomSelect('#to_item_id', {
            allowEmptyOption: true,
            placeholder: 'Select To Item',
            sortField: {
                field: 'text',
                direction: 'asc'
            }
        });
    }
}

// Initialize Tom Select - run immediately if DOM is ready, otherwise wait
function initTomSelects() {
    // Collect all from item options
    const fromItemSelect = document.getElementById('from_item_id');
    if (fromItemSelect) {
        Array.from(fromItemSelect.options).forEach(opt => {
            if (opt.value) {
                allFromItemOptions.push({
                    value: opt.value,
                    text: opt.textContent,
                    stage: opt.getAttribute('data-stage')
                });
            }
        });
    }
    
    // Collect all to item options
    const toItemSelect = document.getElementById('to_item_id');
    if (toItemSelect) {
        Array.from(toItemSelect.options).forEach(opt => {
            if (opt.value) {
                allToItemOptions.push({
                    value: opt.value,
                    text: opt.textContent,
                    stage: opt.getAttribute('data-stage')
                });
            }
        });
    }
    
    // Store old values if they exist
    if (fromItemSelect && fromItemSelect.value) {
        fromItemSelect.setAttribute('data-old-value', fromItemSelect.value);
    }
    if (toItemSelect && toItemSelect.value) {
        toItemSelect.setAttribute('data-old-value', toItemSelect.value);
    }
    
    // Initialize TomSelects
    initializeFromItemSelect();
    initializeToItemSelect();
}

// Run immediately if DOM is ready, otherwise wait for DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTomSelects);
} else {
    // DOM is already ready
    initTomSelects();
}

function updateFromItems() {
    const fromItemSelect = document.getElementById('from_item_id');
    const fromStage = document.querySelector('select[name="from_stage"]').value;
    
    if (!fromItemSelect || typeof TomSelect === 'undefined') return;
    
    // Store current value if TomSelect is already initialized
    let currentValue = null;
    if (fromItemTomSelect) {
        currentValue = fromItemTomSelect.getValue();
        
        // Destroy existing instance
        try {
            fromItemTomSelect.destroy();
        } catch(e) {}
        fromItemTomSelect = null;
    } else {
        // Store current value from the select element itself
        currentValue = fromItemSelect.value;
    }
    
    // Remove any leftover wrapper
    const wrapper = fromItemSelect.closest('.ts-wrapper');
    if (wrapper) {
        wrapper.remove();
    }
    
    // Check if current value is still valid for the new stage
    if (currentValue) {
        const filteredOptions = filterOptionsByStage(allFromItemOptions, fromStage);
        const isValid = filteredOptions.some(opt => opt.value === currentValue);
        
        if (isValid) {
            fromItemSelect.setAttribute('data-old-value', currentValue);
        } else {
            // Clear invalid selection
            fromItemSelect.removeAttribute('data-old-value');
        }
    } else {
        fromItemSelect.removeAttribute('data-old-value');
    }
    
    initializeFromItemSelect();
}

function updateToItems() {
    const toItemSelect = document.getElementById('to_item_id');
    const toStage = document.querySelector('select[name="to_stage"]').value;
    
    if (!toItemSelect || typeof TomSelect === 'undefined') return;
    
    // Store current value if TomSelect is already initialized
    let currentValue = null;
    if (toItemTomSelect) {
        currentValue = toItemTomSelect.getValue();
        
        // Destroy existing instance
        try {
            toItemTomSelect.destroy();
        } catch(e) {}
        toItemTomSelect = null;
    } else {
        // Store current value from the select element itself
        currentValue = toItemSelect.value;
    }
    
    // Remove any leftover wrapper
    const wrapper = toItemSelect.closest('.ts-wrapper');
    if (wrapper) {
        wrapper.remove();
    }
    
    // Check if current value is still valid for the new stage
    if (currentValue) {
        const filteredOptions = filterOptionsByStage(allToItemOptions, toStage);
        const isValid = filteredOptions.some(opt => opt.value === currentValue);
        
        if (isValid) {
            toItemSelect.setAttribute('data-old-value', currentValue);
        } else {
            // Clear invalid selection
            toItemSelect.removeAttribute('data-old-value');
        }
    } else {
        toItemSelect.removeAttribute('data-old-value');
    }
    
    initializeToItemSelect();
}
</script>
@endpush
@endsection

