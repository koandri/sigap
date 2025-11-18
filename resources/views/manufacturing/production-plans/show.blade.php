@extends('layouts.app')

@section('title', 'Production Plan: ' . $productionPlan->plan_date->format('M d, Y'))

@section('content')
@php
    $createStepNumber = null;
    $createStepRoute = null;

    if ($productionPlan->canBeEdited()) {
        $nextStepCandidate = $highestStep < 4 ? max(1, $highestStep + 1) : null;

        if ($nextStepCandidate) {
            $canCreateStep = match ($nextStepCandidate) {
                1 => $productionPlan->canEditStep(1),
                2 => $productionPlan->canEditStep(2),
                3 => $productionPlan->canEditStep(3),
                4 => $productionPlan->canEditStep(4),
                default => false,
            };

            if ($canCreateStep) {
                $createStepNumber = $nextStepCandidate;
                $createStepRoute = match ($nextStepCandidate) {
                    1 => route('manufacturing.production-plans.edit', $productionPlan),
                    2 => route('manufacturing.production-plans.step2', $productionPlan),
                    3 => route('manufacturing.production-plans.step3', $productionPlan),
                    4 => route('manufacturing.production-plans.step4', $productionPlan),
                    default => null,
                };
            }
        }
    }
@endphp
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.dashboard') }}">Manufacturing</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.production-plans.index') }}">Production Plans</a></li>
                        <li class="breadcrumb-item active">{{ $productionPlan->plan_date->format('M d, Y') }}</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    Production Plan: {{ $productionPlan->plan_date->format('M d, Y') }}
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    @if($productionPlan->canBeEdited() && $productionPlan->canEditStep(1))
                    <a href="{{ route('manufacturing.production-plans.edit', $productionPlan) }}" class="btn btn-primary">
                        <i class="far fa-edit me-2"></i>&nbsp;
                        Edit Plan
                    </a>
                    @endif
                    @if($createStepNumber && $createStepRoute)
                    <a href="{{ $createStepRoute }}" class="btn btn-success">
                        <i class="far fa-plus me-2"></i>&nbsp;
                        Create Step {{ $createStepNumber }}
                    </a>
                    @endif
                    @if($productionPlan->isDraft() && $isComplete)
                    @can('manufacturing.production-plans.approve')
                    <form method="POST" action="{{ route('manufacturing.production-plans.approve', $productionPlan) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this production plan?')">
                            <i class="far fa-check me-2"></i>&nbsp;
                            Approve Plan
                        </button>
                    </form>
                    @endcan
                    @endif
                    <a href="{{ route('manufacturing.production-plans.index') }}" class="btn btn-outline-secondary">
                        <i class="far fa-arrow-left me-2"></i>&nbsp;
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
        
        <!-- Plan Information -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Plan Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <dt>Plan Date:</dt>
                                <dd>{{ $productionPlan->plan_date->format('M d, Y') }}</dd>
                            </div>
                            <div class="col-md-3">
                                <dt>Production Start:</dt>
                                <dd>{{ $productionPlan->production_start_date->format('M d, Y') }}</dd>
                            </div>
                            <div class="col-md-3">
                                <dt>Ready Date:</dt>
                                <dd>{{ $productionPlan->ready_date->format('M d, Y') }}</dd>
                            </div>
                            <div class="col-md-3">
                                <dt>Status:</dt>
                                <dd>
                                    @php
                                        $statusColors = [
                                            'draft' => 'bg-yellow-lt',
                                            'approved' => 'bg-blue-lt',
                                            'in_production' => 'bg-green-lt',
                                            'completed' => 'bg-muted-lt',
                                        ];
                                        $statusLabels = [
                                            'draft' => 'Draft',
                                            'approved' => 'Approved',
                                            'in_production' => 'In Production',
                                            'completed' => 'Completed',
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusColors[$productionPlan->status] ?? 'bg-muted-lt' }}">
                                        {{ $statusLabels[$productionPlan->status] ?? ucfirst($productionPlan->status) }}
                                    </span>
                                </dd>
                            </div>
                        </div>
                        @if($productionPlan->notes)
                        <div class="row mt-3">
                            <div class="col-12">
                                <dt>Notes:</dt>
                                <dd>{{ $productionPlan->notes }}</dd>
                            </div>
                        </div>
                        @endif
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <dt>Created By:</dt>
                                <dd>{{ $productionPlan->createdBy->name ?? 'N/A' }}</dd>
                            </div>
                            @if($productionPlan->approvedBy)
                            <div class="col-md-3">
                                <dt>Approved By:</dt>
                                <dd>{{ $productionPlan->approvedBy->name }}</dd>
                            </div>
                            <div class="col-md-3">
                                <dt>Approved At:</dt>
                                <dd>{{ $productionPlan->approved_at->format('M d, Y H:i') }}</dd>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Production Plan Steps with Tabs -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link {{ $activeStep === 1 ? 'active' : '' }}" href="#step1" data-bs-toggle="tab">
                                    Step 1: Dough Planning
                                    @if($productionPlan->step1->count() > 0)
                                        <span class="badge bg-success text-white ms-1">{{ $productionPlan->step1->count() }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeStep === 2 ? 'active' : '' }} {{ !$productionPlan->step1()->exists() ? 'disabled' : '' }}" 
                                   href="#step2" data-bs-toggle="tab" {{ !$productionPlan->step1()->exists() ? 'onclick="return false;"' : '' }}>
                                    Step 2: Gld Planning
                                    @if($productionPlan->step2->count() > 0)
                                        <span class="badge bg-success text-white ms-1">{{ $productionPlan->step2->count() }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeStep === 3 ? 'active' : '' }} {{ !$productionPlan->step2()->exists() ? 'disabled' : '' }}" 
                                   href="#step3" data-bs-toggle="tab" {{ !$productionPlan->step2()->exists() ? 'onclick="return false;"' : '' }}>
                                    Step 3: Kerupuk Kering Planning
                                    @if($productionPlan->step3->count() > 0)
                                        <span class="badge bg-success text-white ms-1">{{ $productionPlan->step3->count() }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeStep === 4 ? 'active' : '' }} {{ !$productionPlan->step3()->exists() ? 'disabled' : '' }}" 
                                   href="#step4" data-bs-toggle="tab" {{ !$productionPlan->step3()->exists() ? 'onclick="return false;"' : '' }}>
                                    Step 4: Packing Planning
                                    @if($productionPlan->step4->count() > 0)
                                        <span class="badge bg-success text-white ms-1">{{ $productionPlan->step4->count() }}</span>
                                    @endif
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Step 1 Tab -->
                            <div class="tab-pane {{ $activeStep === 1 ? 'active' : '' }}" id="step1">
                                <h4 class="mb-3">Step 1: Dough Production Planning (Adn)</h4>
                                @if($productionPlan->step1->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th class="align-top">Dough Item</th>
                                                <th class="align-top">Recipe Details</th>
                                                <th class="align-top text-end">Qty GL1</th>
                                                <th class="align-top text-end">Qty GL2</th>
                                                <th class="align-top text-end">Qty TA</th>
                                                <th class="align-top text-end">Qty BL</th>
                                                <th class="align-top text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($productionPlan->step1 as $index => $step1)
                                            @php
                                                $recipeName = $step1->recipe_name ?? $step1->recipe->name ?? 'N/A';
                                                $recipeDate = $step1->recipe_date
                                                    ? $step1->recipe_date->format('M d, Y')
                                                    : ($step1->recipe && $step1->recipe->recipe_date
                                                        ? $step1->recipe->recipe_date->format('M d, Y')
                                                        : null);
                                                $ingredients = $step1->recipeIngredients->count() > 0
                                                    ? $step1->recipeIngredients
                                                    : ($step1->recipe?->ingredients ?? collect());
                                            @endphp
                                            <tr class="align-top">
                                                <td class="align-top">{{ $step1->doughItem->name ?? 'N/A' }}</td>
                                                <td class="align-top">
                                                    <div><strong>Recipe Name:</strong> {{ $recipeName }}</div>
                                                    @if($recipeDate)
                                                        <div><strong>Recipe Date:</strong> {{ $recipeDate }}</div>
                                                    @endif
                                                    <div class="mt-2 d-flex flex-column gap-2">
                                                        <div>
                                                            <strong>Ingredients</strong>
                                                            <small class="text-muted">(per batch vs total required)</small>
                                                        </div>
                                                        @if($ingredients->count() > 0)
                                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#ingredients-{{ $index }}" aria-expanded="false" aria-controls="ingredients-{{ $index }}">
                                                                <i class="far fa-list me-1"></i> View Ingredients
                                                            </button>
                                                            <div class="collapse mt-2" id="ingredients-{{ $index }}">
                                                                <div class="card card-sm">
                                                                    <div class="card-body p-0">
                                                                        <div class="table-responsive">
                                                                            <table class="table table-sm table-bordered mb-0">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th>Ingredient</th>
                                                                                        <th class="text-end">Per Batch</th>
                                                                                        <th class="text-end">Total Required</th>
                                                                                        <th>Unit</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    @foreach($ingredients as $ingredient)
                                                                                        @php
                                                                                            $perBatchQty = (float) $ingredient->quantity;
                                                                                            $totalAdonan = (float) $step1->total_quantity;
                                                                                            $totalRequired = $perBatchQty * $totalAdonan;
                                                                                            $unit = $ingredient->unit ?? $ingredient->ingredientItem?->unit ?? '-';
                                                                                        @endphp
                                                                                        <tr>
                                                                                            <td>{{ $ingredient->ingredientItem->name ?? 'N/A' }}</td>
                                                                                            <td class="text-end">{{ number_format($perBatchQty, 3) }}</td>
                                                                                            <td class="text-end"><strong>{{ number_format($totalRequired, 3) }}</strong></td>
                                                                                            <td>{{ $unit }}</td>
                                                                                        </tr>
                                                                                    @endforeach
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <span class="text-muted small">No ingredients recorded.</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="align-top text-end">{{ number_format($step1->qty_gl1, 0) }}</td>
                                                <td class="align-top text-end">{{ number_format($step1->qty_gl2, 0) }}</td>
                                                <td class="align-top text-end">{{ number_format($step1->qty_ta, 0) }}</td>
                                                <td class="align-top text-end">{{ number_format($step1->qty_bl, 0) }}</td>
                                                <td class="align-top text-end"><strong>{{ number_format($step1->total_quantity, 0) }}</strong></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2">Totals:</th>
                                                <th class="text-end">{{ number_format($totals['step1']['qty_gl1'], 0) }}</th>
                                                <th class="text-end">{{ number_format($totals['step1']['qty_gl2'], 0) }}</th>
                                                <th class="text-end">{{ number_format($totals['step1']['qty_ta'], 0) }}</th>
                                                <th class="text-end">{{ number_format($totals['step1']['qty_bl'], 0) }}</th>
                                                <th class="text-end"><strong>{{ number_format(array_sum($totals['step1']), 0) }}</strong></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @else
                                <div class="alert alert-info">
                                    No Step 1 data. 
                                    @if($productionPlan->canEditStep(1))
                                    <a href="{{ route('manufacturing.production-plans.edit', $productionPlan) }}">Edit the plan to add Step 1 data.</a>
                                    @else
                                    <span class="text-muted">Step 1 is locked. Please delete Step 2 first.</span>
                                    @endif
                                </div>
                                @endif
                            </div>

                            <!-- Step 2 Tab -->
                            <div class="tab-pane {{ $activeStep === 2 ? 'active' : '' }}" id="step2">
                                <h4 class="mb-3">Step 2: Gld Production Planning</h4>
                                @if($productionPlan->step2->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>Adn Item</th>
                                                <th>Gld Item</th>
                                                <th colspan="2">GL1</th>
                                                <th colspan="2">GL2</th>
                                                <th colspan="2">TA</th>
                                                <th colspan="2">BL</th>
                                            </tr>
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th>Adn</th>
                                                <th>Gld</th>
                                                <th>Adn</th>
                                                <th>Gld</th>
                                                <th>Adn</th>
                                                <th>Gld</th>
                                                <th>Adn</th>
                                                <th>Gld</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($productionPlan->step2 as $step2)
                                            <tr>
                                                <td>{{ $step2->adonanItem->name ?? 'N/A' }}</td>
                                                <td>{{ $step2->gelondonganItem->name ?? 'N/A' }}</td>
                                                <td>{{ number_format($step2->qty_gl1_adonan, 0) }}</td>
                                                <td>{{ number_format($step2->qty_gl1_gelondongan, 0) }}</td>
                                                <td>{{ number_format($step2->qty_gl2_adonan, 0) }}</td>
                                                <td>{{ number_format($step2->qty_gl2_gelondongan, 0) }}</td>
                                                <td>{{ number_format($step2->qty_ta_adonan, 0) }}</td>
                                                <td>{{ number_format($step2->qty_ta_gelondongan, 0) }}</td>
                                                <td>{{ number_format($step2->qty_bl_adonan, 0) }}</td>
                                                <td>{{ number_format($step2->qty_bl_gelondongan, 0) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2">Totals:</th>
                                                <th>{{ number_format($totals['step2']['qty_gl1_adonan'], 0) }}</th>
                                                <th>{{ number_format($totals['step2']['qty_gl1_gelondongan'], 0) }}</th>
                                                <th>{{ number_format($totals['step2']['qty_gl2_adonan'], 0) }}</th>
                                                <th>{{ number_format($totals['step2']['qty_gl2_gelondongan'], 0) }}</th>
                                                <th>{{ number_format($totals['step2']['qty_ta_adonan'], 0) }}</th>
                                                <th>{{ number_format($totals['step2']['qty_ta_gelondongan'], 0) }}</th>
                                                <th>{{ number_format($totals['step2']['qty_bl_adonan'], 0) }}</th>
                                                <th>{{ number_format($totals['step2']['qty_bl_gelondongan'], 0) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @if($productionPlan->canBeEdited())
                                <div class="mt-3 d-flex flex-wrap gap-2 align-items-start">
                                    @if($productionPlan->canEditStep(2))
                                    <a href="{{ route('manufacturing.production-plans.step2', $productionPlan) }}" class="btn btn-primary">
                                        <i class="far fa-edit me-2"></i>&nbsp;Edit Step 2
                                    </a>
                                    @else
                                    <div class="alert alert-warning mb-2">
                                        <i class="far fa-lock me-2"></i>Step 2 is locked. Delete Step 3 first.
                                    </div>
                                    @endif
                                    @if($highestStep === 2)
                                    <form method="POST" action="{{ route('manufacturing.production-plans.step2.delete', $productionPlan) }}" onsubmit="return confirm('Are you sure you want to delete Step 2?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="far fa-trash me-2"></i>&nbsp;Delete Step 2
                                        </button>
                                    </form>
                                    @endif
                                </div>
                                @endif
                                @else
                                <div class="alert alert-warning">
                                    Step 2 not yet created. <a href="{{ route('manufacturing.production-plans.step2', $productionPlan) }}">Create Step 2 planning.</a>
                                </div>
                                @endif
                            </div>

                            <!-- Step 3 Tab -->
                            <div class="tab-pane {{ $activeStep === 3 ? 'active' : '' }}" id="step3">
                                <h4 class="mb-3">Step 3: Kerupuk Kering Production Planning</h4>
                                @if($productionPlan->step3->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>Gld Item</th>
                                                <th>Kerupuk Kering Item</th>
                                                <th colspan="2">GL1</th>
                                                <th colspan="2">GL2</th>
                                                <th colspan="2">TA</th>
                                                <th colspan="2">BL</th>
                                            </tr>
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th>Gld</th>
                                                <th>Kg</th>
                                                <th>Gld</th>
                                                <th>Kg</th>
                                                <th>Gld</th>
                                                <th>Kg</th>
                                                <th>Gld</th>
                                                <th>Kg</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($productionPlan->step3 as $step3)
                                            <tr>
                                                <td>{{ $step3->gelondonganItem->name ?? 'N/A' }}</td>
                                                <td>{{ $step3->kerupukKeringItem->name ?? 'N/A' }}</td>
                                                <td>{{ number_format($step3->qty_gl1_gelondongan, 0) }}</td>
                                                <td>{{ number_format($step3->qty_gl1_kg, 2) }}</td>
                                                <td>{{ number_format($step3->qty_gl2_gelondongan, 0) }}</td>
                                                <td>{{ number_format($step3->qty_gl2_kg, 2) }}</td>
                                                <td>{{ number_format($step3->qty_ta_gelondongan, 0) }}</td>
                                                <td>{{ number_format($step3->qty_ta_kg, 2) }}</td>
                                                <td>{{ number_format($step3->qty_bl_gelondongan, 0) }}</td>
                                                <td>{{ number_format($step3->qty_bl_kg, 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2">Totals:</th>
                                                <th>{{ number_format($totals['step3']['qty_gl1_gelondongan'], 0) }}</th>
                                                <th>{{ number_format($totals['step3']['qty_gl1_kg'], 2) }}</th>
                                                <th>{{ number_format($totals['step3']['qty_gl2_gelondongan'], 0) }}</th>
                                                <th>{{ number_format($totals['step3']['qty_gl2_kg'], 2) }}</th>
                                                <th>{{ number_format($totals['step3']['qty_ta_gelondongan'], 0) }}</th>
                                                <th>{{ number_format($totals['step3']['qty_ta_kg'], 2) }}</th>
                                                <th>{{ number_format($totals['step3']['qty_bl_gelondongan'], 0) }}</th>
                                                <th>{{ number_format($totals['step3']['qty_bl_kg'], 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @if($productionPlan->canBeEdited())
                                <div class="mt-3 d-flex flex-wrap gap-2 align-items-start">
                                    @if($productionPlan->canEditStep(3))
                                    <a href="{{ route('manufacturing.production-plans.step3', $productionPlan) }}" class="btn btn-primary">
                                        <i class="far fa-edit me-2"></i>&nbsp;Edit Step 3
                                    </a>
                                    @else
                                    <div class="alert alert-warning mb-2">
                                        <i class="far fa-lock me-2"></i>Step 3 is locked. Delete Step 4 first.
                                    </div>
                                    @endif
                                    @if($highestStep === 3)
                                    <form method="POST" action="{{ route('manufacturing.production-plans.step3.delete', $productionPlan) }}" onsubmit="return confirm('Are you sure you want to delete Step 3?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="far fa-trash me-2"></i>&nbsp;Delete Step 3
                                        </button>
                                    </form>
                                    @endif
                                </div>
                                @endif
                                @else
                                <div class="alert alert-warning">
                                    Step 3 not yet created. <a href="{{ route('manufacturing.production-plans.step3', $productionPlan) }}">Create Step 3 planning.</a>
                                </div>
                                @endif
                            </div>

                            <!-- Step 4 Tab -->
                            <div class="tab-pane {{ $activeStep === 4 ? 'active' : '' }}" id="step4">
                                <h4 class="mb-3">Step 4: Packing Planning</h4>
                                @if($productionPlan->step4->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>Kerupuk Kering Item</th>
                                                <th>Packing Item</th>
                                                <th>Weight/Unit</th>
                                                <th colspan="2">GL1</th>
                                                <th colspan="2">GL2</th>
                                                <th colspan="2">TA</th>
                                                <th colspan="2">BL</th>
                                                <th colspan="2">Totals</th>
                                            </tr>
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th>Kg</th>
                                                <th>Packs</th>
                                                <th>Kg</th>
                                                <th>Packs</th>
                                                <th>Kg</th>
                                                <th>Packs</th>
                                                <th>Kg</th>
                                                <th>Packs</th>
                                                <th>Kg</th>
                                                <th>Packs</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($productionPlan->step4 as $step4)
                                            @php
                                                $rowTotalPacks = $step4->total_packing;
                                                $rowTotalKg = $step4->total_kg;
                                            @endphp
                                            <tr>
                                                <td>{{ $step4->kerupukKeringItem->name ?? 'N/A' }}</td>
                                                <td>{{ $step4->kerupukPackingItem->name ?? 'N/A' }}</td>
                                                <td>{{ number_format($step4->weight_per_unit, 2) }} kg</td>
                                                <td>{{ number_format($step4->qty_gl1_kg, 2) }}</td>
                                                <td>{{ number_format($step4->qty_gl1_packing, 0) }}</td>
                                                <td>{{ number_format($step4->qty_gl2_kg, 2) }}</td>
                                                <td>{{ number_format($step4->qty_gl2_packing, 0) }}</td>
                                                <td>{{ number_format($step4->qty_ta_kg, 2) }}</td>
                                                <td>{{ number_format($step4->qty_ta_packing, 0) }}</td>
                                                <td>{{ number_format($step4->qty_bl_kg, 2) }}</td>
                                                <td>{{ number_format($step4->qty_bl_packing, 0) }}</td>
                                                <td>{{ number_format($rowTotalKg, 2) }}</td>
                                                <td>{{ number_format($rowTotalPacks, 0) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            @php
                                                $totalPacksAll = $totals['step4']['qty_gl1_packing']
                                                    + $totals['step4']['qty_gl2_packing']
                                                    + $totals['step4']['qty_ta_packing']
                                                    + $totals['step4']['qty_bl_packing'];
                                                $totalKgAll = $totals['step4']['qty_gl1_kg']
                                                    + $totals['step4']['qty_gl2_kg']
                                                    + $totals['step4']['qty_ta_kg']
                                                    + $totals['step4']['qty_bl_kg'];
                                            @endphp
                                            <tr>
                                                <th colspan="3">Totals:</th>
                                                <th>{{ number_format($totals['step4']['qty_gl1_kg'], 2) }}</th>
                                                <th>{{ number_format($totals['step4']['qty_gl1_packing'], 0) }}</th>
                                                <th>{{ number_format($totals['step4']['qty_gl2_kg'], 2) }}</th>
                                                <th>{{ number_format($totals['step4']['qty_gl2_packing'], 0) }}</th>
                                                <th>{{ number_format($totals['step4']['qty_ta_kg'], 2) }}</th>
                                                <th>{{ number_format($totals['step4']['qty_ta_packing'], 0) }}</th>
                                                <th>{{ number_format($totals['step4']['qty_bl_kg'], 2) }}</th>
                                                <th>{{ number_format($totals['step4']['qty_bl_packing'], 0) }}</th>
                                                <th>{{ number_format($totalKgAll, 2) }}</th>
                                                <th>{{ number_format($totalPacksAll, 0) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="mt-3">
                                    <h4 class="mb-3">Packing Material Usage</h4>
                                    @if($packingMaterialsByRow->isNotEmpty() && $packingMaterialsByRow->some(fn($row) => $row['materials']->isNotEmpty()))
                                        @foreach($packingMaterialsByRow as $rowData)
                                            @if($rowData['materials']->isNotEmpty())
                                                <div class="card mb-3">
                                                    <div class="card-header">
                                                        <h5 class="card-title mb-0">
                                                            <i class="far fa-box me-2"></i>{{ $rowData['pack_sku_name'] }}
                                                            <span class="text-muted ms-2">({{ number_format($rowData['total_packs'], 0) }} packs)</span>
                                                        </h5>
                                                    </div>
                                                    <div class="card-body p-0">
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-vcenter mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Packing Material</th>
                                                                        <th class="text-end">Total Quantity</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($rowData['materials'] as $material)
                                                                        <tr>
                                                                            <td>{{ $material['item']->name ?? 'N/A' }}</td>
                                                                            <td class="text-end">{{ number_format($material['quantity_total'], 0) }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <div class="alert alert-warning mb-0">
                                            <i class="far fa-exclamation-triangle me-2"></i>No packing material blueprints detected for the selected SKUs.
                                        </div>
                                    @endif
                                </div>
                                @if($productionPlan->canBeEdited())
                                <div class="mt-3 d-flex flex-wrap gap-2 align-items-start">
                                    @if($productionPlan->canEditStep(4))
                                    <a href="{{ route('manufacturing.production-plans.step4', $productionPlan) }}" class="btn btn-primary">
                                        <i class="far fa-edit me-2"></i>&nbsp;Edit Step 4
                                    </a>
                                    @endif
                                    @if($highestStep === 4)
                                    <form method="POST" action="{{ route('manufacturing.production-plans.step4.delete', $productionPlan) }}" onsubmit="return confirm('Are you sure you want to delete Step 4?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="far fa-trash me-2"></i>&nbsp;Delete Step 4
                                        </button>
                                    </form>
                                    @endif
                                </div>
                                @endif
                                @else
                                <div class="alert alert-warning">
                                    Step 4 not yet created. <a href="{{ route('manufacturing.production-plans.step4', $productionPlan) }}">Create Step 4 planning.</a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection















