@extends('layouts.app')

@section('title', 'Production Execution: ' . $productionPlan->plan_date->format('M d, Y'))

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
                        <li class="breadcrumb-item active">Production Execution</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    Production Execution: {{ $productionPlan->plan_date->format('M d, Y') }}
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    @can('manufacturing.production-plans.view-actuals')
                    <a href="{{ route('manufacturing.production-plans.actuals', $productionPlan) }}" class="btn btn-info">
                        <i class="far fa-chart-bar me-2"></i>&nbsp;
                        View Comparison
                    </a>
                    @endcan
                    <a href="{{ route('manufacturing.production-plans.show', $productionPlan) }}" class="btn btn-outline-secondary">
                        <i class="far fa-arrow-left me-2"></i>&nbsp;
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
        
        <!-- Progress Indicator -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Production Progress</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="progress progress-lg mb-3">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $progress['completion_percentage'] }}%" aria-valuenow="{{ $progress['completion_percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ number_format($progress['completion_percentage'], 1) }}%
                                    </div>
                                </div>
                                <div class="d-flex gap-3">
                                    @for($i = 1; $i <= 5; $i++)
                                        @php
                                            $isComplete = in_array($i, $progress['steps_complete']);
                                            $isIncomplete = in_array($i, $progress['steps_incomplete']);
                                        @endphp
                                        <div class="text-center">
                                            <div class="mb-1">
                                                @if($isComplete)
                                                    <i class="far fa-check-circle text-success" style="font-size: 1.5rem;"></i>
                                                @elseif($isIncomplete && $productionPlan->{"step{$i}"}()->exists())
                                                    <i class="far fa-clock text-warning" style="font-size: 1.5rem;"></i>
                                                @else
                                                    <i class="far fa-circle text-muted" style="font-size: 1.5rem;"></i>
                                                @endif
                                            </div>
                                            <small class="text-muted">Step {{ $i }}</small>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-end">
                                    <div class="h3 mb-0">{{ number_format($progress['completion_percentage'], 1) }}%</div>
                                    <small class="text-muted">Complete</small>
                                    <div class="mt-3">
                                        <span class="badge bg-{{ $progress['overall_status'] === 'complete' ? 'success' : 'warning' }}">
                                            {{ ucfirst(str_replace('_', ' ', $progress['overall_status'])) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Production Execution Forms -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            @for($i = 1; $i <= 5; $i++)
                                @php
                                    $stepExists = $productionPlan->{"step{$i}"}()->exists();
                                    $stepComplete = in_array($i, $progress['steps_complete']);
                                    $hasAccess = $stepAccess[$i] ?? false;
                                @endphp
                                <li class="nav-item">
                                    <a class="nav-link {{ $i === 1 ? 'active' : '' }} {{ !$hasAccess ? 'disabled' : '' }}" 
                                       href="#step{{ $i }}" 
                                       data-bs-toggle="tab" 
                                       {{ !$hasAccess ? 'onclick="return false;" title="You do not have access to this step or previous step must be completed first"' : '' }}>
                                        Step {{ $i }}
                                        @if($stepComplete)
                                            <i class="far fa-check-circle text-success ms-1"></i>
                                        @elseif($stepExists)
                                            <i class="far fa-clock text-warning ms-1"></i>
                                        @endif
                                        @if(!$hasAccess)
                                            <i class="far fa-lock text-muted ms-1"></i>
                                        @endif
                                    </a>
                                </li>
                            @endfor
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Step 1 Tab -->
                            <div class="tab-pane active" id="step1">
                                @if($stepAccess[1] ?? false)
                                    <form method="POST" action="{{ route('manufacturing.production-plans.actuals.step1', $productionPlan) }}" id="step1-form">
                                        @csrf
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h4 class="mb-0">Step 1: Dough Production - Actual Quantities</h4>
                                            @if($stepAccess[1])
                                                <button type="button" class="btn btn-sm btn-primary" onclick="addStep1Row()">
                                                    <i class="far fa-plus"></i>&nbsp;Add Row
                                                </button>
                                            @endif
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped" id="step1-table">
                                                <thead>
                                                    <tr>
                                                        <th>Dough Item</th>
                                                        <th>Recipe</th>
                                                        <th>Ingredients</th>
                                                        <th class="text-end">Planned GL1</th>
                                                        <th class="text-end">Actual GL1</th>
                                                        <th class="text-end">Planned GL2</th>
                                                        <th class="text-end">Actual GL2</th>
                                                        <th class="text-end">Planned TA</th>
                                                        <th class="text-end">Actual TA</th>
                                                        <th class="text-end">Planned BL</th>
                                                        <th class="text-end">Actual BL</th>
                                                        <th class="w-1">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="step1-tbody">
                                                    @forelse($productionPlan->step1 as $index => $step1)
                                                    @php
                                                        $actualStep1 = $step1->actualStep1;
                                                    @endphp
                                                    <tr class="step1-row" data-row-index="{{ $index }}">
                                                        <td>
                                                            <select name="step1[{{ $index }}][dough_item_id]" 
                                                                    class="form-select form-select-sm dough-item-select" 
                                                                    id="dough-item-select-{{ $index }}" 
                                                                    data-row-index="{{ $index }}" 
                                                                    {{ $actualStep1 ? 'disabled' : '' }}>
                                                                <option value="">Select Dough Item</option>
                                                                @foreach($doughItems as $item)
                                                                <option value="{{ $item->id }}" {{ $step1->dough_item_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @if($actualStep1)
                                                                <input type="hidden" name="step1[{{ $index }}][dough_item_id]" value="{{ $step1->dough_item_id }}">
                                                            @endif
                                                            <input type="hidden" name="step1[{{ $index }}][production_plan_step1_id]" value="{{ $step1->id }}">
                                                        </td>
                                                        <td>
                                                            <select name="step1[{{ $index }}][recipe_id]" 
                                                                    class="form-select form-select-sm recipe-select" 
                                                                    id="recipe-select-{{ $index }}" 
                                                                    data-row-index="{{ $index }}"
                                                                    {{ $actualStep1 ? 'disabled' : '' }}>
                                                                <option value="">Select Recipe</option>
                                                            </select>
                                                            @if($actualStep1)
                                                                <input type="hidden" name="step1[{{ $index }}][recipe_id]" value="{{ $step1->recipe_id }}">
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="ingredient-table-wrapper" style="max-height: 150px; overflow-y: auto;">
                                                                <table class="table table-sm table-bordered mb-0 ingredients-table" id="ingredients-table-{{ $index }}">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Item</th>
                                                                            <th>Qty</th>
                                                                            <th>Unit</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody class="ingredients-tbody" id="ingredients-tbody-{{ $index }}">
                                                                        @foreach($step1->recipeIngredients as $ingIndex => $ingredient)
                                                                        <tr class="ingredient-row">
                                                                            <td>{{ $ingredient->ingredientItem->name ?? 'N/A' }}</td>
                                                                            <td>{{ number_format($ingredient->quantity, 3) }}</td>
                                                                            <td>{{ $ingredient->unit }}</td>
                                                                        </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            @if(!$actualStep1)
                                                                <button type="button" class="btn btn-sm btn-outline-primary mt-1 add-ingredient-btn" onclick="addIngredientRow({{ $index }})">
                                                                    <i class="far fa-plus"></i> Add Ingredient
                                                                </button>
                                                            @endif
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step1->qty_gl1, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step1[{{ $index }}][actual_qty_gl1]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep1->actual_qty_gl1 ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step1->qty_gl2, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step1[{{ $index }}][actual_qty_gl2]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep1->actual_qty_gl2 ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step1->qty_ta, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step1[{{ $index }}][actual_qty_ta]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep1->actual_qty_ta ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step1->qty_bl, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step1[{{ $index }}][actual_qty_bl]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep1->actual_qty_bl ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td>
                                                            @if($actualStep1)
                                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteStep1Record({{ $actualStep1->id }}, this)">
                                                                    <i class="far fa-trash"></i>
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStep1Row(this)">
                                                                    <i class="far fa-trash"></i>
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr id="step1-empty-row">
                                                        <td colspan="12" class="text-center text-muted">No Step 1 data available. Click "Add Row" to add items.</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="far fa-save me-2"></i>Save Step 1
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="far fa-lock me-2"></i>You do not have access to Step 1. Please contact your administrator.
                                    </div>
                                @endif
                            </div>

                            <!-- Step 2 Tab -->
                            <div class="tab-pane" id="step2">
                                @if($stepAccess[2] ?? false)
                                    <form method="POST" action="{{ route('manufacturing.production-plans.actuals.step2', $productionPlan) }}" id="step2-form">
                                        @csrf
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h4 class="mb-0">Step 2: Gelondongan Production - Actual Quantities</h4>
                                            @if($stepAccess[2])
                                                <button type="button" class="btn btn-sm btn-primary" onclick="addStep2Row()">
                                                    <i class="far fa-plus"></i>&nbsp;Add Row
                                                </button>
                                            @endif
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped" id="step2-table">
                                                <thead>
                                                    <tr>
                                                        <th>Adonan Item</th>
                                                        <th>Gelondongan Item</th>
                                                        <th class="text-end">Planned GL1 Adn</th>
                                                        <th class="text-end">Actual GL1 Adn</th>
                                                        <th class="text-end">Planned GL1 Gld</th>
                                                        <th class="text-end">Actual GL1 Gld</th>
                                                        <th class="text-end">Planned GL2 Adn</th>
                                                        <th class="text-end">Actual GL2 Adn</th>
                                                        <th class="text-end">Planned GL2 Gld</th>
                                                        <th class="text-end">Actual GL2 Gld</th>
                                                        <th class="text-end">Planned TA Adn</th>
                                                        <th class="text-end">Actual TA Adn</th>
                                                        <th class="text-end">Planned TA Gld</th>
                                                        <th class="text-end">Actual TA Gld</th>
                                                        <th class="text-end">Planned BL Adn</th>
                                                        <th class="text-end">Actual BL Adn</th>
                                                        <th class="text-end">Planned BL Gld</th>
                                                        <th class="text-end">Actual BL Gld</th>
                                                        <th class="w-1">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="step2-tbody">
                                                    @forelse($productionPlan->step2 as $index => $step2)
                                                    @php
                                                        $actualStep2 = $step2->actualStep2;
                                                    @endphp
                                                    <tr class="step2-row" data-row-index="{{ $index }}">
                                                        <td>
                                                            <select name="step2[{{ $index }}][adonan_item_id]" 
                                                                    class="form-select form-select-sm adonan-item-select" 
                                                                    id="adonan-item-select-{{ $index }}" 
                                                                    data-row-index="{{ $index }}"
                                                                    {{ $actualStep2 ? 'disabled' : '' }}>
                                                                <option value="">Select Adonan Item</option>
                                                                @foreach($adonanItems as $item)
                                                                <option value="{{ $item->id }}" {{ $step2->adonan_item_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @if($actualStep2)
                                                                <input type="hidden" name="step2[{{ $index }}][adonan_item_id]" value="{{ $step2->adonan_item_id }}">
                                                            @endif
                                                            <input type="hidden" name="step2[{{ $index }}][production_plan_step2_id]" value="{{ $step2->id }}">
                                                        </td>
                                                        <td>
                                                            <select name="step2[{{ $index }}][gelondongan_item_id]" 
                                                                    class="form-select form-select-sm gelondongan-item-select" 
                                                                    id="gelondongan-item-select-{{ $index }}" 
                                                                    data-row-index="{{ $index }}"
                                                                    {{ $actualStep2 ? 'disabled' : '' }}>
                                                                <option value="">Select Gelondongan Item</option>
                                                                @foreach($gelondonganItems as $item)
                                                                <option value="{{ $item->id }}" {{ $step2->gelondongan_item_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @if($actualStep2)
                                                                <input type="hidden" name="step2[{{ $index }}][gelondongan_item_id]" value="{{ $step2->gelondongan_item_id }}">
                                                            @endif
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step2->qty_gl1_adonan, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step2[{{ $index }}][actual_qty_gl1_adonan]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep2->actual_qty_gl1_adonan ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step2->qty_gl1_gelondongan, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step2[{{ $index }}][actual_qty_gl1_gelondongan]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep2->actual_qty_gl1_gelondongan ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step2->qty_gl2_adonan, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step2[{{ $index }}][actual_qty_gl2_adonan]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep2->actual_qty_gl2_adonan ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step2->qty_gl2_gelondongan, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step2[{{ $index }}][actual_qty_gl2_gelondongan]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep2->actual_qty_gl2_gelondongan ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step2->qty_ta_adonan, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step2[{{ $index }}][actual_qty_ta_adonan]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep2->actual_qty_ta_adonan ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step2->qty_ta_gelondongan, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step2[{{ $index }}][actual_qty_ta_gelondongan]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep2->actual_qty_ta_gelondongan ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step2->qty_bl_adonan, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step2[{{ $index }}][actual_qty_bl_adonan]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep2->actual_qty_bl_adonan ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step2->qty_bl_gelondongan, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step2[{{ $index }}][actual_qty_bl_gelondongan]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep2->actual_qty_bl_gelondongan ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td>
                                                            @if($actualStep2)
                                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteStep2Record({{ $actualStep2->id }}, this)">
                                                                    <i class="far fa-trash"></i>
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStep2Row(this)">
                                                                    <i class="far fa-trash"></i>
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr id="step2-empty-row">
                                                        <td colspan="19" class="text-center text-muted">No Step 2 data available. Click "Add Row" to add items.</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="far fa-save me-2"></i>Save Step 2
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="far fa-lock me-2"></i>You do not have access to Step 2. Please complete Step 1 first or contact your administrator.
                                    </div>
                                @endif
                            </div>

                            <!-- Step 3 Tab -->
                            <div class="tab-pane" id="step3">
                                @if($stepAccess[3] ?? false)
                                    <form method="POST" action="{{ route('manufacturing.production-plans.actuals.step3', $productionPlan) }}" id="step3-form">
                                        @csrf
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h4 class="mb-0">Step 3: Kerupuk Kering Production - Actual Quantities</h4>
                                            @if($stepAccess[3])
                                                <button type="button" class="btn btn-sm btn-primary" onclick="addStep3Row()">
                                                    <i class="far fa-plus"></i>&nbsp;Add Row
                                                </button>
                                            @endif
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped" id="step3-table">
                                                <thead>
                                                    <tr>
                                                        <th>Gelondongan Item</th>
                                                        <th>Kerupuk Kering Item</th>
                                                        <th class="text-end">Planned GL1 Gld</th>
                                                        <th class="text-end">Actual GL1 Gld</th>
                                                        <th class="text-end">Planned GL1 Kg</th>
                                                        <th class="text-end">Actual GL1 Kg</th>
                                                        <th class="text-end">Planned GL2 Gld</th>
                                                        <th class="text-end">Actual GL2 Gld</th>
                                                        <th class="text-end">Planned GL2 Kg</th>
                                                        <th class="text-end">Actual GL2 Kg</th>
                                                        <th class="text-end">Planned TA Gld</th>
                                                        <th class="text-end">Actual TA Gld</th>
                                                        <th class="text-end">Planned TA Kg</th>
                                                        <th class="text-end">Actual TA Kg</th>
                                                        <th class="text-end">Planned BL Gld</th>
                                                        <th class="text-end">Actual BL Gld</th>
                                                        <th class="text-end">Planned BL Kg</th>
                                                        <th class="text-end">Actual BL Kg</th>
                                                        <th class="w-1">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="step3-tbody">
                                                    @forelse($productionPlan->step3 as $index => $step3)
                                                    @php
                                                        $actualStep3 = $step3->actualStep3;
                                                    @endphp
                                                    <tr class="step3-row" data-row-index="{{ $index }}">
                                                        <td>
                                                            <select name="step3[{{ $index }}][gelondongan_item_id]" 
                                                                    class="form-select form-select-sm gelondongan-item-select" 
                                                                    id="gelondongan-item-select-step3-{{ $index }}" 
                                                                    data-row-index="{{ $index }}"
                                                                    {{ $actualStep3 ? 'disabled' : '' }}>
                                                                <option value="">Select Gelondongan Item</option>
                                                                @foreach($gelondonganItems as $item)
                                                                <option value="{{ $item->id }}" {{ $step3->gelondongan_item_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @if($actualStep3)
                                                                <input type="hidden" name="step3[{{ $index }}][gelondongan_item_id]" value="{{ $step3->gelondongan_item_id }}">
                                                            @endif
                                                            <input type="hidden" name="step3[{{ $index }}][production_plan_step3_id]" value="{{ $step3->id }}">
                                                        </td>
                                                        <td>
                                                            <select name="step3[{{ $index }}][kerupuk_kering_item_id]" 
                                                                    class="form-select form-select-sm kerupuk-kering-item-select" 
                                                                    id="kerupuk-kering-item-select-{{ $index }}" 
                                                                    data-row-index="{{ $index }}"
                                                                    {{ $actualStep3 ? 'disabled' : '' }}>
                                                                <option value="">Select Kerupuk Kering Item</option>
                                                                @foreach($kerupukKeringItems as $item)
                                                                <option value="{{ $item->id }}" {{ $step3->kerupuk_kering_item_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @if($actualStep3)
                                                                <input type="hidden" name="step3[{{ $index }}][kerupuk_kering_item_id]" value="{{ $step3->kerupuk_kering_item_id }}">
                                                            @endif
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step3->qty_gl1_gelondongan, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step3[{{ $index }}][actual_qty_gl1_gelondongan]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep3->actual_qty_gl1_gelondongan ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step3->qty_gl1_kg, 2) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step3[{{ $index }}][actual_qty_gl1_kg]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep3->actual_qty_gl1_kg ?? 0 }}" 
                                                                   step="0.01" min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step3->qty_gl2_gelondongan, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step3[{{ $index }}][actual_qty_gl2_gelondongan]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep3->actual_qty_gl2_gelondongan ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step3->qty_gl2_kg, 2) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step3[{{ $index }}][actual_qty_gl2_kg]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep3->actual_qty_gl2_kg ?? 0 }}" 
                                                                   step="0.01" min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step3->qty_ta_gelondongan, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step3[{{ $index }}][actual_qty_ta_gelondongan]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep3->actual_qty_ta_gelondongan ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step3->qty_ta_kg, 2) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step3[{{ $index }}][actual_qty_ta_kg]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep3->actual_qty_ta_kg ?? 0 }}" 
                                                                   step="0.01" min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step3->qty_bl_gelondongan, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step3[{{ $index }}][actual_qty_bl_gelondongan]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep3->actual_qty_bl_gelondongan ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step3->qty_bl_kg, 2) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step3[{{ $index }}][actual_qty_bl_kg]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep3->actual_qty_bl_kg ?? 0 }}" 
                                                                   step="0.01" min="0" required>
                                                        </td>
                                                        <td>
                                                            @if($actualStep3)
                                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteStep3Record({{ $actualStep3->id }}, this)">
                                                                    <i class="far fa-trash"></i>
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStep3Row(this)">
                                                                    <i class="far fa-trash"></i>
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr id="step3-empty-row">
                                                        <td colspan="19" class="text-center text-muted">No Step 3 data available. Click "Add Row" to add items.</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="far fa-save me-2"></i>Save Step 3
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="far fa-lock me-2"></i>You do not have access to Step 3. Please complete Step 2 first or contact your administrator.
                                    </div>
                                @endif
                            </div>

                            <!-- Step 4 Tab -->
                            <div class="tab-pane" id="step4">
                                @if($stepAccess[4] ?? false)
                                    <form method="POST" action="{{ route('manufacturing.production-plans.actuals.step4', $productionPlan) }}" id="step4-form">
                                        @csrf
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h4 class="mb-0">Step 4: Packing Production - Actual Quantities</h4>
                                            @if($stepAccess[4])
                                                <button type="button" class="btn btn-sm btn-primary" onclick="addStep4Row()">
                                                    <i class="far fa-plus"></i>&nbsp;Add Row
                                                </button>
                                            @endif
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped" id="step4-table">
                                                <thead>
                                                    <tr>
                                                        <th>Kerupuk Kering Item</th>
                                                        <th>Packing Item</th>
                                                        <th class="text-end">Planned GL1 Kg</th>
                                                        <th class="text-end">Actual GL1 Kg</th>
                                                        <th class="text-end">Planned GL1 Pack</th>
                                                        <th class="text-end">Actual GL1 Pack</th>
                                                        <th class="text-end">Planned GL2 Kg</th>
                                                        <th class="text-end">Actual GL2 Kg</th>
                                                        <th class="text-end">Planned GL2 Pack</th>
                                                        <th class="text-end">Actual GL2 Pack</th>
                                                        <th class="text-end">Planned TA Kg</th>
                                                        <th class="text-end">Actual TA Kg</th>
                                                        <th class="text-end">Planned TA Pack</th>
                                                        <th class="text-end">Actual TA Pack</th>
                                                        <th class="text-end">Planned BL Kg</th>
                                                        <th class="text-end">Actual BL Kg</th>
                                                        <th class="text-end">Planned BL Pack</th>
                                                        <th class="text-end">Actual BL Pack</th>
                                                        <th class="w-1">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="step4-tbody">
                                                    @forelse($productionPlan->step4 as $index => $step4)
                                                    @php
                                                        $actualStep4 = $step4->actualStep4;
                                                    @endphp
                                                    <tr class="step4-row" data-row-index="{{ $index }}">
                                                        <td>
                                                            <select name="step4[{{ $index }}][kerupuk_kering_item_id]" 
                                                                    class="form-select form-select-sm kerupuk-kering-item-select" 
                                                                    id="kerupuk-kering-item-select-step4-{{ $index }}" 
                                                                    data-row-index="{{ $index }}"
                                                                    {{ $actualStep4 ? 'disabled' : '' }}>
                                                                <option value="">Select Kerupuk Kering Item</option>
                                                                @foreach($kerupukKeringItems as $item)
                                                                <option value="{{ $item->id }}" {{ $step4->kerupuk_kering_item_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @if($actualStep4)
                                                                <input type="hidden" name="step4[{{ $index }}][kerupuk_kering_item_id]" value="{{ $step4->kerupuk_kering_item_id }}">
                                                            @endif
                                                            <input type="hidden" name="step4[{{ $index }}][production_plan_step4_id]" value="{{ $step4->id }}">
                                                        </td>
                                                        <td>
                                                            <select name="step4[{{ $index }}][kerupuk_packing_item_id]" 
                                                                    class="form-select form-select-sm packing-item-select" 
                                                                    id="packing-item-select-{{ $index }}" 
                                                                    data-row-index="{{ $index }}"
                                                                    {{ $actualStep4 ? 'disabled' : '' }}>
                                                                <option value="">Select Packing Item</option>
                                                                @foreach($packingItems as $item)
                                                                <option value="{{ $item->id }}" {{ $step4->kerupuk_packing_item_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @if($actualStep4)
                                                                <input type="hidden" name="step4[{{ $index }}][kerupuk_packing_item_id]" value="{{ $step4->kerupuk_packing_item_id }}">
                                                            @endif
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step4->qty_gl1_kg, 2) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step4[{{ $index }}][actual_qty_gl1_kg]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep4->actual_qty_gl1_kg ?? 0 }}" 
                                                                   step="0.01" min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step4->qty_gl1_packing, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step4[{{ $index }}][actual_qty_gl1_packing]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep4->actual_qty_gl1_packing ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step4->qty_gl2_kg, 2) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step4[{{ $index }}][actual_qty_gl2_kg]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep4->actual_qty_gl2_kg ?? 0 }}" 
                                                                   step="0.01" min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step4->qty_gl2_packing, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step4[{{ $index }}][actual_qty_gl2_packing]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep4->actual_qty_gl2_packing ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step4->qty_ta_kg, 2) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step4[{{ $index }}][actual_qty_ta_kg]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep4->actual_qty_ta_kg ?? 0 }}" 
                                                                   step="0.01" min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step4->qty_ta_packing, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step4[{{ $index }}][actual_qty_ta_packing]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep4->actual_qty_ta_packing ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step4->qty_bl_kg, 2) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step4[{{ $index }}][actual_qty_bl_kg]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep4->actual_qty_bl_kg ?? 0 }}" 
                                                                   step="0.01" min="0" required>
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step4->qty_bl_packing, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step4[{{ $index }}][actual_qty_bl_packing]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep4->actual_qty_bl_packing ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td>
                                                            @if($actualStep4)
                                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteStep4Record({{ $actualStep4->id }}, this)">
                                                                    <i class="far fa-trash"></i>
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStep4Row(this)">
                                                                    <i class="far fa-trash"></i>
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr id="step4-empty-row">
                                                        <td colspan="19" class="text-center text-muted">No Step 4 data available. Click "Add Row" to add items.</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="far fa-save me-2"></i>Save Step 4
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="far fa-lock me-2"></i>You do not have access to Step 4. Please complete Step 3 first or contact your administrator.
                                    </div>
                                @endif
                            </div>

                            <!-- Step 5 Tab -->
                            <div class="tab-pane" id="step5">
                                @if($stepAccess[5] ?? false)
                                    <form method="POST" action="{{ route('manufacturing.production-plans.actuals.step5', $productionPlan) }}" id="step5-form">
                                        @csrf
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h4 class="mb-0">Step 5: Packing Materials Usage - Actual Quantities</h4>
                                            @if($stepAccess[5])
                                                <button type="button" class="btn btn-sm btn-primary" onclick="addStep5Row()">
                                                    <i class="far fa-plus"></i>&nbsp;Add Row
                                                </button>
                                            @endif
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped" id="step5-table">
                                                <thead>
                                                    <tr>
                                                        <th>Pack SKU</th>
                                                        <th>Packing Material Item</th>
                                                        <th class="text-end">Planned Total</th>
                                                        <th class="text-end">Actual Total</th>
                                                        <th class="w-1">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="step5-tbody">
                                                    @forelse($productionPlan->step5 as $index => $step5)
                                                    @php
                                                        $actualStep5 = $step5->actualStep5;
                                                    @endphp
                                                    <tr class="step5-row" data-row-index="{{ $index }}">
                                                        <td>
                                                            <select name="step5[{{ $index }}][pack_sku_id]" 
                                                                    class="form-select form-select-sm pack-sku-select" 
                                                                    id="pack-sku-select-{{ $index }}" 
                                                                    data-row-index="{{ $index }}"
                                                                    {{ $actualStep5 ? 'disabled' : '' }}>
                                                                <option value="">Select Pack SKU</option>
                                                                @foreach($packingItems as $item)
                                                                <option value="{{ $item->id }}" {{ $step5->pack_sku_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @if($actualStep5)
                                                                <input type="hidden" name="step5[{{ $index }}][pack_sku_id]" value="{{ $step5->pack_sku_id }}">
                                                            @endif
                                                            <input type="hidden" name="step5[{{ $index }}][production_plan_step5_id]" value="{{ $step5->id }}">
                                                        </td>
                                                        <td>
                                                            <select name="step5[{{ $index }}][packing_material_item_id]" 
                                                                    class="form-select form-select-sm packing-material-item-select" 
                                                                    id="packing-material-item-select-{{ $index }}" 
                                                                    data-row-index="{{ $index }}"
                                                                    {{ $actualStep5 ? 'disabled' : '' }}>
                                                                <option value="">Select Packing Material Item</option>
                                                                @foreach($packingMaterialItems as $item)
                                                                <option value="{{ $item->id }}" {{ $step5->packing_material_item_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @if($actualStep5)
                                                                <input type="hidden" name="step5[{{ $index }}][packing_material_item_id]" value="{{ $step5->packing_material_item_id }}">
                                                            @endif
                                                        </td>
                                                        <td class="text-end text-muted">{{ number_format($step5->quantity_total, 0) }}</td>
                                                        <td class="text-end">
                                                            <input type="number" name="step5[{{ $index }}][actual_quantity_total]" 
                                                                   class="form-control form-control-sm text-end" 
                                                                   value="{{ $actualStep5->actual_quantity_total ?? 0 }}" 
                                                                   min="0" required>
                                                        </td>
                                                        <td>
                                                            @if($actualStep5)
                                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteStep5Record({{ $actualStep5->id }}, this)">
                                                                    <i class="far fa-trash"></i>
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStep5Row(this)">
                                                                    <i class="far fa-trash"></i>
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr id="step5-empty-row">
                                                        <td colspan="5" class="text-center text-muted">No Step 5 data available. Click "Add Row" to add items.</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="far fa-save me-2"></i>Save Step 5
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="far fa-lock me-2"></i>You do not have access to Step 5. Please complete Step 4 first or contact your administrator.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($progress['overall_status'] === 'complete')
                    <div class="card-footer">
                        <form method="POST" action="{{ route('manufacturing.production-plans.complete', $productionPlan) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to mark this production as completed?')">
                                <i class="far fa-check-circle me-2"></i>Mark as Completed
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Global variables
const doughItems = @json($doughItems);
const ingredientItems = @json($ingredientItems);
const adonanItems = @json($adonanItems);
const gelondonganItems = @json($gelondonganItems);
const kerupukKeringItems = @json($kerupukKeringItems);
const packingItems = @json($packingItems);
const packingMaterialItems = @json($packingMaterialItems);
const productionPlanId = {{ $productionPlan->id }};

// Row counters
let step1RowIndex = {{ $productionPlan->step1->count() }};
let step2RowIndex = {{ $productionPlan->step2->count() }};
let step3RowIndex = {{ $productionPlan->step3->count() }};
let step4RowIndex = {{ $productionPlan->step4->count() }};
let step5RowIndex = {{ $productionPlan->step5->count() }};

// Ingredient index tracking for Step 1
const ingredientIndexes = {};
@foreach($productionPlan->step1 as $index => $step1)
ingredientIndexes[{{ $index }}] = {{ $step1->recipeIngredients->count() }};
@endforeach

// TomSelect instances
const tomSelectInstances = new Map();

// CSRF token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

// ==================== STEP 1 FUNCTIONS ====================

function addStep1Row() {
    const tbody = document.getElementById('step1-tbody');
    const emptyRow = document.getElementById('step1-empty-row');
    if (emptyRow) emptyRow.remove();
    
    const row = document.createElement('tr');
    row.className = 'step1-row';
    row.setAttribute('data-row-index', step1RowIndex);
    
    const doughSelect = doughItems.map(item => 
        `<option value="${item.id}">${item.name}</option>`
    ).join('');
    
    row.innerHTML = `
        <td>
            <select name="step1[${step1RowIndex}][dough_item_id]" 
                    class="form-select form-select-sm dough-item-select" 
                    id="dough-item-select-${step1RowIndex}" 
                    data-row-index="${step1RowIndex}" required>
                <option value="">Select Dough Item</option>
                ${doughSelect}
            </select>
        </td>
        <td>
            <select name="step1[${step1RowIndex}][recipe_id]" 
                    class="form-select form-select-sm recipe-select" 
                    id="recipe-select-${step1RowIndex}" 
                    data-row-index="${step1RowIndex}" required>
                <option value="">Select Recipe</option>
            </select>
        </td>
        <td>
            <div class="ingredient-table-wrapper" style="max-height: 150px; overflow-y: auto;">
                <table class="table table-sm table-bordered mb-0 ingredients-table" id="ingredients-table-${step1RowIndex}">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody class="ingredients-tbody" id="ingredients-tbody-${step1RowIndex}">
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary mt-1 add-ingredient-btn" onclick="addIngredientRow(${step1RowIndex})">
                <i class="far fa-plus"></i> Add Ingredient
            </button>
        </td>
        <td class="text-end text-muted">0</td>
        <td class="text-end">
            <input type="number" name="step1[${step1RowIndex}][actual_qty_gl1]" class="form-control form-control-sm text-end" value="0" min="0" required>
        </td>
        <td class="text-end text-muted">0</td>
        <td class="text-end">
            <input type="number" name="step1[${step1RowIndex}][actual_qty_gl2]" class="form-control form-control-sm text-end" value="0" min="0" required>
        </td>
        <td class="text-end text-muted">0</td>
        <td class="text-end">
            <input type="number" name="step1[${step1RowIndex}][actual_qty_ta]" class="form-control form-control-sm text-end" value="0" min="0" required>
        </td>
        <td class="text-end text-muted">0</td>
        <td class="text-end">
            <input type="number" name="step1[${step1RowIndex}][actual_qty_bl]" class="form-control form-control-sm text-end" value="0" min="0" required>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStep1Row(this)">
                <i class="far fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    ingredientIndexes[step1RowIndex] = 0;
    initializeStep1TomSelects(step1RowIndex);
    step1RowIndex++;
}

function removeStep1Row(button) {
    const row = button.closest('tr');
    const rowIndex = row.getAttribute('data-row-index');
    
    // Clean up TomSelect instances
    ['dough', 'recipe'].forEach(type => {
        const instance = tomSelectInstances.get(`${type}-${rowIndex}`);
        if (instance) {
            instance.destroy();
            tomSelectInstances.delete(`${type}-${rowIndex}`);
        }
    });
    
    row.remove();
    
    // Check if table is empty
    const tbody = document.getElementById('step1-tbody');
    if (tbody.children.length === 0) {
        tbody.innerHTML = '<tr id="step1-empty-row"><td colspan="12" class="text-center text-muted">No Step 1 data available. Click "Add Row" to add items.</td></tr>';
    }
}

function deleteStep1Record(actualStep1Id, button) {
    if (!confirm('Are you sure you want to delete this record?')) return;
    
    fetch(`{{ route('manufacturing.production-plans.actuals.step1.delete', ['productionPlan' => $productionPlan->id, 'actualStep1' => '__ID__']) }}`.replace('__ID__', actualStep1Id), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.closest('tr').remove();
            location.reload();
        } else {
            alert(data.message || 'Error deleting record');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting record');
    });
}

function loadRecipes(doughItemId, rowIndex) {
    if (!doughItemId) {
        const recipeSelect = document.getElementById(`recipe-select-${rowIndex}`);
        const recipeTomSelect = tomSelectInstances.get(`recipe-${rowIndex}`);
        if (recipeTomSelect) {
            recipeTomSelect.clear();
            recipeTomSelect.clearOptions();
            recipeTomSelect.addOption({ value: '', text: 'Select Recipe' });
            recipeTomSelect.refreshOptions(false);
        } else if (recipeSelect) {
            recipeSelect.innerHTML = '<option value="">Select Recipe</option>';
        }
        return;
    }
    
    fetch(`{{ route('manufacturing.production-plans.recipes') }}?dough_item_id=${doughItemId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const recipeSelect = document.getElementById(`recipe-select-${rowIndex}`);
        const recipeTomSelect = tomSelectInstances.get(`recipe-${rowIndex}`);
        
        if (recipeTomSelect) {
            recipeTomSelect.clear();
            recipeTomSelect.clearOptions();
            recipeTomSelect.addOption({ value: '', text: 'Select Recipe' });
            if (Array.isArray(data) && data.length > 0) {
                data.forEach(recipe => {
                    recipeTomSelect.addOption({
                        value: String(recipe.id),
                        text: `${recipe.name} (${recipe.recipe_date})`
                    });
                });
            }
            recipeTomSelect.refreshOptions(false);
        } else if (recipeSelect) {
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
    });
}

function loadRecipeIngredients(recipeId, rowIndex) {
    if (!recipeId) return;
    
    fetch(`{{ route('manufacturing.production-plans.recipe-ingredients') }}?recipe_id=${recipeId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const tbody = document.getElementById(`ingredients-tbody-${rowIndex}`);
        if (!tbody) return;
        
        tbody.innerHTML = '';
        ingredientIndexes[rowIndex] = 0;
        
        if (Array.isArray(data) && data.length > 0) {
            data.forEach(ingredient => {
                addIngredientRow(rowIndex, ingredient);
            });
        }
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
    
    const ingredientOptions = ingredientItems.map(item => 
        `<option value="${item.id}" data-unit="${item.unit || ''}" ${selectedIngredientId == item.id ? 'selected' : ''}>${item.name}</option>`
    ).join('');
    
    row.innerHTML = `
        <td>
            <select name="step1[${rowIndex}][ingredients][${ingIndex}][ingredient_item_id]" class="form-select form-select-sm ingredient-item-select" required>
                <option value="">Select Item</option>
                ${ingredientOptions}
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
    
    // Auto-fill unit when ingredient is selected
    const selectElement = row.querySelector('.ingredient-item-select');
    const unitInput = row.querySelector('.ingredient-unit-input');
    selectElement.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const unit = selectedOption.getAttribute('data-unit');
        if (unit && !unitInput.value) {
            unitInput.value = unit;
        }
    });
}

function removeIngredientRow(button) {
    button.closest('tr').remove();
}

function initializeStep1TomSelects(rowIndex) {
    const doughSelect = document.getElementById(`dough-item-select-${rowIndex}`);
    const recipeSelect = document.getElementById(`recipe-select-${rowIndex}`);
    
    if (doughSelect && !doughSelect.closest('.ts-wrapper')) {
        const doughTomSelect = new TomSelect(`#dough-item-select-${rowIndex}`, {
            allowEmptyOption: true,
            placeholder: 'Select Dough Item',
            sortField: { field: 'text', direction: 'asc' },
            dropdownParent: 'body',
            onChange: function(value) {
                loadRecipes(value, rowIndex);
            }
        });
        tomSelectInstances.set(`dough-${rowIndex}`, doughTomSelect);
    }
    
    if (recipeSelect && !recipeSelect.closest('.ts-wrapper')) {
        const recipeTomSelect = new TomSelect(`#recipe-select-${rowIndex}`, {
            allowEmptyOption: true,
            placeholder: 'Select Recipe',
            sortField: { field: 'text', direction: 'asc' },
            dropdownParent: 'body',
            onChange: function(value) {
                loadRecipeIngredients(value, rowIndex);
            }
        });
        tomSelectInstances.set(`recipe-${rowIndex}`, recipeTomSelect);
    }
}

// ==================== STEP 2-5 FUNCTIONS ====================

// Step 2
function addStep2Row() {
    const tbody = document.getElementById('step2-tbody');
    const emptyRow = document.getElementById('step2-empty-row');
    if (emptyRow) emptyRow.remove();
    
    const row = document.createElement('tr');
    row.className = 'step2-row';
    row.setAttribute('data-row-index', step2RowIndex);
    
    const adonanSelect = adonanItems.map(item => `<option value="${item.id}">${item.name}</option>`).join('');
    const gelondonganSelect = gelondonganItems.map(item => `<option value="${item.id}">${item.name}</option>`).join('');
    
    row.innerHTML = `
        <td>
            <select name="step2[${step2RowIndex}][adonan_item_id]" class="form-select form-select-sm adonan-item-select" id="adonan-item-select-${step2RowIndex}" data-row-index="${step2RowIndex}" required>
                <option value="">Select Adonan Item</option>
                ${adonanSelect}
            </select>
        </td>
        <td>
            <select name="step2[${step2RowIndex}][gelondongan_item_id]" class="form-select form-select-sm gelondongan-item-select" id="gelondongan-item-select-${step2RowIndex}" data-row-index="${step2RowIndex}" required>
                <option value="">Select Gelondongan Item</option>
                ${gelondonganSelect}
            </select>
        </td>
        <td class="text-end text-muted">0</td>
        <td class="text-end"><input type="number" name="step2[${step2RowIndex}][actual_qty_gl1_adonan]" class="form-control form-control-sm text-end" value="0" min="0" required></td>
        <td class="text-end text-muted">0</td>
        <td class="text-end"><input type="number" name="step2[${step2RowIndex}][actual_qty_gl1_gelondongan]" class="form-control form-control-sm text-end" value="0" min="0" required></td>
        <td class="text-end text-muted">0</td>
        <td class="text-end"><input type="number" name="step2[${step2RowIndex}][actual_qty_gl2_adonan]" class="form-control form-control-sm text-end" value="0" min="0" required></td>
        <td class="text-end text-muted">0</td>
        <td class="text-end"><input type="number" name="step2[${step2RowIndex}][actual_qty_gl2_gelondongan]" class="form-control form-control-sm text-end" value="0" min="0" required></td>
        <td class="text-end text-muted">0</td>
        <td class="text-end"><input type="number" name="step2[${step2RowIndex}][actual_qty_ta_adonan]" class="form-control form-control-sm text-end" value="0" min="0" required></td>
        <td class="text-end text-muted">0</td>
        <td class="text-end"><input type="number" name="step2[${step2RowIndex}][actual_qty_ta_gelondongan]" class="form-control form-control-sm text-end" value="0" min="0" required></td>
        <td class="text-end text-muted">0</td>
        <td class="text-end"><input type="number" name="step2[${step2RowIndex}][actual_qty_bl_adonan]" class="form-control form-control-sm text-end" value="0" min="0" required></td>
        <td class="text-end text-muted">0</td>
        <td class="text-end"><input type="number" name="step2[${step2RowIndex}][actual_qty_bl_gelondongan]" class="form-control form-control-sm text-end" value="0" min="0" required></td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStep2Row(this)">
                <i class="far fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    initializeTomSelectForStep2(step2RowIndex);
    step2RowIndex++;
}

function removeStep2Row(button) {
    const row = button.closest('tr');
    const rowIndex = row.getAttribute('data-row-index');
    ['adonan', 'gelondongan'].forEach(type => {
        const instance = tomSelectInstances.get(`step2-${type}-${rowIndex}`);
        if (instance) { instance.destroy(); tomSelectInstances.delete(`step2-${type}-${rowIndex}`); }
    });
    row.remove();
    checkEmptyTable('step2-tbody', 'step2-empty-row', 19);
}

function deleteStep2Record(id, button) {
    deleteRecord(id, button, 'step2', `{{ route('manufacturing.production-plans.actuals.step2.delete', ['productionPlan' => $productionPlan->id, 'actualStep2' => '__ID__']) }}`);
}

function initializeTomSelectForStep2(rowIndex) {
    ['adonan', 'gelondongan'].forEach(type => {
        const select = document.getElementById(`${type}-item-select-${rowIndex}`);
        if (select && !select.closest('.ts-wrapper')) {
            const ts = new TomSelect(`#${type}-item-select-${rowIndex}`, {
                allowEmptyOption: true,
                placeholder: `Select ${type.charAt(0).toUpperCase() + type.slice(1)} Item`,
                sortField: { field: 'text', direction: 'asc' },
                dropdownParent: 'body'
            });
            tomSelectInstances.set(`step2-${type}-${rowIndex}`, ts);
        }
    });
}

// Similar functions for Steps 3, 4, 5 (abbreviated for space - full implementation follows same pattern)
function addStep3Row() {
    const tbody = document.getElementById('step3-tbody');
    const emptyRow = document.getElementById('step3-empty-row');
    if (emptyRow) emptyRow.remove();
    
    const row = document.createElement('tr');
    row.className = 'step3-row';
    row.setAttribute('data-row-index', step3RowIndex);
    
    const gelondonganSelect = gelondonganItems.map(item => `<option value="${item.id}">${item.name}</option>`).join('');
    const kerupukSelect = kerupukKeringItems.map(item => `<option value="${item.id}">${item.name}</option>`).join('');
    
    row.innerHTML = `
        <td>
            <select name="step3[${step3RowIndex}][gelondongan_item_id]" class="form-select form-select-sm gelondongan-item-select" id="gelondongan-item-select-step3-${step3RowIndex}" data-row-index="${step3RowIndex}" required>
                <option value="">Select Gelondongan Item</option>
                ${gelondonganSelect}
            </select>
        </td>
        <td>
            <select name="step3[${step3RowIndex}][kerupuk_kering_item_id]" class="form-select form-select-sm kerupuk-kering-item-select" id="kerupuk-kering-item-select-${step3RowIndex}" data-row-index="${step3RowIndex}" required>
                <option value="">Select Kerupuk Kering Item</option>
                ${kerupukSelect}
            </select>
        </td>
        <td class="text-end text-muted">0</td>
        <td class="text-end"><input type="number" name="step3[${step3RowIndex}][actual_qty_gl1_gelondongan]" class="form-control form-control-sm text-end" value="0" min="0" required></td>
        <td class="text-end text-muted">0.00</td>
        <td class="text-end"><input type="number" name="step3[${step3RowIndex}][actual_qty_gl1_kg]" class="form-control form-control-sm text-end" value="0" step="0.01" min="0" required></td>
        <td class="text-end text-muted">0</td>
        <td class="text-end"><input type="number" name="step3[${step3RowIndex}][actual_qty_gl2_gelondongan]" class="form-control form-control-sm text-end" value="0" min="0" required></td>
        <td class="text-end text-muted">0.00</td>
        <td class="text-end"><input type="number" name="step3[${step3RowIndex}][actual_qty_gl2_kg]" class="form-control form-control-sm text-end" value="0" step="0.01" min="0" required></td>
        <td class="text-end text-muted">0</td>
        <td class="text-end"><input type="number" name="step3[${step3RowIndex}][actual_qty_ta_gelondongan]" class="form-control form-control-sm text-end" value="0" min="0" required></td>
        <td class="text-end text-muted">0.00</td>
        <td class="text-end"><input type="number" name="step3[${step3RowIndex}][actual_qty_ta_kg]" class="form-control form-control-sm text-end" value="0" step="0.01" min="0" required></td>
        <td class="text-end text-muted">0</td>
        <td class="text-end"><input type="number" name="step3[${step3RowIndex}][actual_qty_bl_gelondongan]" class="form-control form-control-sm text-end" value="0" min="0" required></td>
        <td class="text-end text-muted">0.00</td>
        <td class="text-end"><input type="number" name="step3[${step3RowIndex}][actual_qty_bl_kg]" class="form-control form-control-sm text-end" value="0" step="0.01" min="0" required></td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStep3Row(this)">
                <i class="far fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    initializeTomSelectForStep3(step3RowIndex);
    step3RowIndex++;
}

function removeStep3Row(button) {
    const row = button.closest('tr');
    const rowIndex = row.getAttribute('data-row-index');
    ['gelondongan-step3', 'kerupuk-kering'].forEach(type => {
        const instance = tomSelectInstances.get(`step3-${type}-${rowIndex}`);
        if (instance) { instance.destroy(); tomSelectInstances.delete(`step3-${type}-${rowIndex}`); }
    });
    row.remove();
    checkEmptyTable('step3-tbody', 'step3-empty-row', 19);
}

function deleteStep3Record(id, button) {
    deleteRecord(id, button, 'step3', `{{ route('manufacturing.production-plans.actuals.step3.delete', ['productionPlan' => $productionPlan->id, 'actualStep3' => '__ID__']) }}`);
}

function initializeTomSelectForStep3(rowIndex) {
    const gelondonganSelect = document.getElementById(`gelondongan-item-select-step3-${rowIndex}`);
    const kerupukSelect = document.getElementById(`kerupuk-kering-item-select-${rowIndex}`);
    
    if (gelondonganSelect && !gelondonganSelect.closest('.ts-wrapper')) {
        const ts = new TomSelect(`#gelondongan-item-select-step3-${rowIndex}`, {
            allowEmptyOption: true,
            placeholder: 'Select Gelondongan Item',
            sortField: { field: 'text', direction: 'asc' },
            dropdownParent: 'body'
        });
        tomSelectInstances.set(`step3-gelondongan-step3-${rowIndex}`, ts);
    }
    
    if (kerupukSelect && !kerupukSelect.closest('.ts-wrapper')) {
        const ts = new TomSelect(`#kerupuk-kering-item-select-${rowIndex}`, {
            allowEmptyOption: true,
            placeholder: 'Select Kerupuk Kering Item',
            sortField: { field: 'text', direction: 'asc' },
            dropdownParent: 'body'
        });
        tomSelectInstances.set(`step3-kerupuk-kering-${rowIndex}`, ts);
    }
}

// Step 4
function addStep4Row() {
    const tbody = document.getElementById('step4-tbody');
    const emptyRow = document.getElementById('step4-empty-row');
    if (emptyRow) emptyRow.remove();
    
    const row = document.createElement('tr');
    row.className = 'step4-row';
    row.setAttribute('data-row-index', step4RowIndex);
    
    const kerupukSelect = kerupukKeringItems.map(item => `<option value="${item.id}">${item.name}</option>`).join('');
    const packingSelect = packingItems.map(item => `<option value="${item.id}">${item.name}</option>`).join('');
    
    row.innerHTML = `
        <td>
            <select name="step4[${step4RowIndex}][kerupuk_kering_item_id]" class="form-select form-select-sm kerupuk-kering-item-select" id="kerupuk-kering-item-select-step4-${step4RowIndex}" data-row-index="${step4RowIndex}" required>
                <option value="">Select Kerupuk Kering Item</option>
                ${kerupukSelect}
            </select>
        </td>
        <td>
            <select name="step4[${step4RowIndex}][kerupuk_packing_item_id]" class="form-select form-select-sm packing-item-select" id="packing-item-select-${step4RowIndex}" data-row-index="${step4RowIndex}" required>
                <option value="">Select Packing Item</option>
                ${packingSelect}
            </select>
        </td>
        <td class="text-end text-muted">0.00</td>
        <td class="text-end"><input type="number" name="step4[${step4RowIndex}][actual_qty_gl1_kg]" class="form-control form-control-sm text-end" value="0" step="0.01" min="0" required></td>
        <td class="text-end text-muted">0</td>
        <td class="text-end"><input type="number" name="step4[${step4RowIndex}][actual_qty_gl1_packing]" class="form-control form-control-sm text-end" value="0" min="0" required></td>
        <td class="text-end text-muted">0.00</td>
        <td class="text-end"><input type="number" name="step4[${step4RowIndex}][actual_qty_gl2_kg]" class="form-control form-control-sm text-end" value="0" step="0.01" min="0" required></td>
        <td class="text-end text-muted">0</td>
        <td class="text-end"><input type="number" name="step4[${step4RowIndex}][actual_qty_gl2_packing]" class="form-control form-control-sm text-end" value="0" min="0" required></td>
        <td class="text-end text-muted">0.00</td>
        <td class="text-end"><input type="number" name="step4[${step4RowIndex}][actual_qty_ta_kg]" class="form-control form-control-sm text-end" value="0" step="0.01" min="0" required></td>
        <td class="text-end text-muted">0</td>
        <td class="text-end"><input type="number" name="step4[${step4RowIndex}][actual_qty_ta_packing]" class="form-control form-control-sm text-end" value="0" min="0" required></td>
        <td class="text-end text-muted">0.00</td>
        <td class="text-end"><input type="number" name="step4[${step4RowIndex}][actual_qty_bl_kg]" class="form-control form-control-sm text-end" value="0" step="0.01" min="0" required></td>
        <td class="text-end text-muted">0</td>
        <td class="text-end"><input type="number" name="step4[${step4RowIndex}][actual_qty_bl_packing]" class="form-control form-control-sm text-end" value="0" min="0" required></td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStep4Row(this)">
                <i class="far fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    initializeTomSelectForStep4(step4RowIndex);
    step4RowIndex++;
}

function removeStep4Row(button) {
    const row = button.closest('tr');
    const rowIndex = row.getAttribute('data-row-index');
    ['kerupuk-kering-step4', 'packing'].forEach(type => {
        const instance = tomSelectInstances.get(`step4-${type}-${rowIndex}`);
        if (instance) { instance.destroy(); tomSelectInstances.delete(`step4-${type}-${rowIndex}`); }
    });
    row.remove();
    checkEmptyTable('step4-tbody', 'step4-empty-row', 19);
}

function deleteStep4Record(id, button) {
    deleteRecord(id, button, 'step4', `{{ route('manufacturing.production-plans.actuals.step4.delete', ['productionPlan' => $productionPlan->id, 'actualStep4' => '__ID__']) }}`);
}

function initializeTomSelectForStep4(rowIndex) {
    const kerupukSelect = document.getElementById(`kerupuk-kering-item-select-step4-${rowIndex}`);
    const packingSelect = document.getElementById(`packing-item-select-${rowIndex}`);
    
    if (kerupukSelect && !kerupukSelect.closest('.ts-wrapper')) {
        const ts = new TomSelect(`#kerupuk-kering-item-select-step4-${rowIndex}`, {
            allowEmptyOption: true,
            placeholder: 'Select Kerupuk Kering Item',
            sortField: { field: 'text', direction: 'asc' },
            dropdownParent: 'body'
        });
        tomSelectInstances.set(`step4-kerupuk-kering-step4-${rowIndex}`, ts);
    }
    
    if (packingSelect && !packingSelect.closest('.ts-wrapper')) {
        const ts = new TomSelect(`#packing-item-select-${rowIndex}`, {
            allowEmptyOption: true,
            placeholder: 'Select Packing Item',
            sortField: { field: 'text', direction: 'asc' },
            dropdownParent: 'body'
        });
        tomSelectInstances.set(`step4-packing-${rowIndex}`, ts);
    }
}

// Step 5
function addStep5Row() {
    const tbody = document.getElementById('step5-tbody');
    const emptyRow = document.getElementById('step5-empty-row');
    if (emptyRow) emptyRow.remove();
    
    const row = document.createElement('tr');
    row.className = 'step5-row';
    row.setAttribute('data-row-index', step5RowIndex);
    
    const packSkuSelect = packingItems.map(item => `<option value="${item.id}">${item.name}</option>`).join('');
    const packingMaterialSelect = packingMaterialItems.map(item => `<option value="${item.id}">${item.name}</option>`).join('');
    
    row.innerHTML = `
        <td>
            <select name="step5[${step5RowIndex}][pack_sku_id]" class="form-select form-select-sm pack-sku-select" id="pack-sku-select-${step5RowIndex}" data-row-index="${step5RowIndex}" required>
                <option value="">Select Pack SKU</option>
                ${packSkuSelect}
            </select>
        </td>
        <td>
            <select name="step5[${step5RowIndex}][packing_material_item_id]" class="form-select form-select-sm packing-material-item-select" id="packing-material-item-select-${step5RowIndex}" data-row-index="${step5RowIndex}" required>
                <option value="">Select Packing Material Item</option>
                ${packingMaterialSelect}
            </select>
        </td>
        <td class="text-end text-muted">0</td>
        <td class="text-end"><input type="number" name="step5[${step5RowIndex}][actual_quantity_total]" class="form-control form-control-sm text-end" value="0" min="0" required></td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStep5Row(this)">
                <i class="far fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    initializeTomSelectForStep5(step5RowIndex);
    step5RowIndex++;
}

function removeStep5Row(button) {
    const row = button.closest('tr');
    const rowIndex = row.getAttribute('data-row-index');
    ['pack-sku', 'packing-material-item'].forEach(type => {
        const instance = tomSelectInstances.get(`step5-${type}-${rowIndex}`);
        if (instance) { instance.destroy(); tomSelectInstances.delete(`step5-${type}-${rowIndex}`); }
    });
    row.remove();
    checkEmptyTable('step5-tbody', 'step5-empty-row', 5);
}

function deleteStep5Record(id, button) {
    deleteRecord(id, button, 'step5', `{{ route('manufacturing.production-plans.actuals.step5.delete', ['productionPlan' => $productionPlan->id, 'actualStep5' => '__ID__']) }}`);
}

function initializeTomSelectForStep5(rowIndex) {
    const packSkuSelect = document.getElementById(`pack-sku-select-${rowIndex}`);
    const packingMaterialSelect = document.getElementById(`packing-material-item-select-${rowIndex}`);
    
    if (packSkuSelect && !packSkuSelect.closest('.ts-wrapper')) {
        const ts = new TomSelect(`#pack-sku-select-${rowIndex}`, {
            allowEmptyOption: true,
            placeholder: 'Select Pack SKU',
            sortField: { field: 'text', direction: 'asc' },
            dropdownParent: 'body'
        });
        tomSelectInstances.set(`step5-pack-sku-${rowIndex}`, ts);
    }
    
    if (packingMaterialSelect && !packingMaterialSelect.closest('.ts-wrapper')) {
        const ts = new TomSelect(`#packing-material-item-select-${rowIndex}`, {
            allowEmptyOption: true,
            placeholder: 'Select Packing Material Item',
            sortField: { field: 'text', direction: 'asc' },
            dropdownParent: 'body'
        });
        tomSelectInstances.set(`step5-packing-material-item-${rowIndex}`, ts);
    }
}

// ==================== COMMON FUNCTIONS ====================

function deleteRecord(id, button, stepName, routeTemplate) {
    if (!confirm('Are you sure you want to delete this record?')) return;
    
    const url = routeTemplate.replace('__ID__', id);
    
    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.closest('tr').remove();
            location.reload();
        } else {
            alert(data.message || 'Error deleting record');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting record');
    });
}

function checkEmptyTable(tbodyId, emptyRowId, colspan) {
    const tbody = document.getElementById(tbodyId);
    if (tbody && tbody.children.length === 0) {
        tbody.innerHTML = `<tr id="${emptyRowId}"><td colspan="${colspan}" class="text-center text-muted">No data available. Click "Add Row" to add items.</td></tr>`;
    }
}

// Initialize TomSelect for existing rows on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Step 1 TomSelects for existing rows
    @foreach($productionPlan->step1 as $index => $step1)
        @if(!$step1->actualStep1)
            initializeStep1TomSelects({{ $index }});
            // Load recipes if dough item is selected
            @if($step1->dough_item_id)
                loadRecipes({{ $step1->dough_item_id }}, {{ $index }});
                @if($step1->recipe_id)
                    setTimeout(() => {
                        const recipeTomSelect = tomSelectInstances.get(`recipe-{{ $index }}`);
                        if (recipeTomSelect) {
                            recipeTomSelect.setValue('{{ $step1->recipe_id }}', true);
                        }
                    }, 500);
                @endif
            @endif
        @endif
    @endforeach
    
    // Initialize Step 2-5 TomSelects for existing rows
    @foreach($productionPlan->step2 as $index => $step2)
        @if(!$step2->actualStep2)
            initializeTomSelectForStep2({{ $index }});
        @endif
    @endforeach
    
    @foreach($productionPlan->step3 as $index => $step3)
        @if(!$step3->actualStep3)
            initializeTomSelectForStep3({{ $index }});
        @endif
    @endforeach
    
    @foreach($productionPlan->step4 as $index => $step4)
        @if(!$step4->actualStep4)
            initializeTomSelectForStep4({{ $index }});
        @endif
    @endforeach
    
    @foreach($productionPlan->step5 as $index => $step5)
        @if(!$step5->actualStep5)
            initializeTomSelectForStep5({{ $index }});
        @endif
    @endforeach
});
</script>
@endpush
@endsection


