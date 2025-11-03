@extends('layouts.app')

@section('title', 'Production Plan: ' . $productionPlan->plan_date->format('M d, Y'))

@section('content')
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
                    @if($productionPlan->canBeEdited())
                    <a href="{{ route('manufacturing.production-plans.edit', $productionPlan) }}" class="btn btn-primary">
                        <i class="far fa-edit me-2"></i>&nbsp;
                        Edit Plan
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
                                <a class="nav-link {{ !request('step') || request('step') == '1' ? 'active' : '' }}" href="#step1" data-bs-toggle="tab">
                                    Step 1: Dough Planning
                                    @if($productionPlan->step1->count() > 0)
                                        <span class="badge bg-success ms-1">{{ $productionPlan->step1->count() }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request('step') == '2' ? 'active' : '' }} {{ !$productionPlan->step1()->exists() ? 'disabled' : '' }}" 
                                   href="#step2" data-bs-toggle="tab" {{ !$productionPlan->step1()->exists() ? 'onclick="return false;"' : '' }}>
                                    Step 2: Gelondongan Planning
                                    @if($productionPlan->step2->count() > 0)
                                        <span class="badge bg-success ms-1">{{ $productionPlan->step2->count() }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request('step') == '3' ? 'active' : '' }} {{ !$productionPlan->step2()->exists() ? 'disabled' : '' }}" 
                                   href="#step3" data-bs-toggle="tab" {{ !$productionPlan->step2()->exists() ? 'onclick="return false;"' : '' }}>
                                    Step 3: Kerupuk Kering Planning
                                    @if($productionPlan->step3->count() > 0)
                                        <span class="badge bg-success ms-1">{{ $productionPlan->step3->count() }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request('step') == '4' ? 'active' : '' }} {{ !$productionPlan->step3()->exists() ? 'disabled' : '' }}" 
                                   href="#step4" data-bs-toggle="tab" {{ !$productionPlan->step3()->exists() ? 'onclick="return false;"' : '' }}>
                                    Step 4: Packing Planning
                                    @if($productionPlan->step4->count() > 0)
                                        <span class="badge bg-success ms-1">{{ $productionPlan->step4->count() }}</span>
                                    @endif
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Step 1 Tab -->
                            <div class="tab-pane {{ !request('step') || request('step') == '1' ? 'active' : '' }}" id="step1">
                                <h4 class="mb-3">Step 1: Dough Production Planning (Adonan)</h4>
                                @if($productionPlan->step1->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>Dough Item</th>
                                                <th>Recipe</th>
                                                <th>Recipe Date</th>
                                                <th>Qty GL1</th>
                                                <th>Qty GL2</th>
                                                <th>Qty TA</th>
                                                <th>Qty BL</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($productionPlan->step1 as $step1)
                                            <tr>
                                                <td>{{ $step1->doughItem->name ?? 'N/A' }}</td>
                                                <td>
                                                    {{ $step1->recipe_name }}
                                                    @if($step1->is_custom_recipe)
                                                        <span class="badge bg-info">Custom</span>
                                                    @endif
                                                </td>
                                                <td>{{ $step1->recipe_date->format('M d, Y') }}</td>
                                                <td>{{ number_format($step1->qty_gl1, 3) }}</td>
                                                <td>{{ number_format($step1->qty_gl2, 3) }}</td>
                                                <td>{{ number_format($step1->qty_ta, 3) }}</td>
                                                <td>{{ number_format($step1->qty_bl, 3) }}</td>
                                                <td><strong>{{ number_format($step1->total_quantity, 3) }}</strong></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3">Totals:</th>
                                                <th>{{ number_format($totals['step1']['qty_gl1'], 3) }}</th>
                                                <th>{{ number_format($totals['step1']['qty_gl2'], 3) }}</th>
                                                <th>{{ number_format($totals['step1']['qty_ta'], 3) }}</th>
                                                <th>{{ number_format($totals['step1']['qty_bl'], 3) }}</th>
                                                <th><strong>{{ number_format(array_sum($totals['step1']), 3) }}</strong></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @else
                                <div class="alert alert-info">
                                    No Step 1 data. <a href="{{ route('manufacturing.production-plans.edit', $productionPlan) }}">Edit the plan to add Step 1 data.</a>
                                </div>
                                @endif
                            </div>

                            <!-- Step 2 Tab -->
                            <div class="tab-pane {{ request('step') == '2' ? 'active' : '' }}" id="step2">
                                <h4 class="mb-3">Step 2: Gelondongan Production Planning</h4>
                                @if($productionPlan->step2->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>Adonan Item</th>
                                                <th>Gelondongan Item</th>
                                                <th colspan="2">GL1</th>
                                                <th colspan="2">GL2</th>
                                                <th colspan="2">TA</th>
                                                <th colspan="2">BL</th>
                                            </tr>
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th>Adonan</th>
                                                <th>Gelondongan</th>
                                                <th>Adonan</th>
                                                <th>Gelondongan</th>
                                                <th>Adonan</th>
                                                <th>Gelondongan</th>
                                                <th>Adonan</th>
                                                <th>Gelondongan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($productionPlan->step2 as $step2)
                                            <tr>
                                                <td>{{ $step2->adonanItem->name ?? 'N/A' }}</td>
                                                <td>{{ $step2->gelondonganItem->name ?? 'N/A' }}</td>
                                                <td>{{ number_format($step2->qty_gl1_adonan, 3) }}</td>
                                                <td>{{ number_format($step2->qty_gl1_gelondongan, 3) }}</td>
                                                <td>{{ number_format($step2->qty_gl2_adonan, 3) }}</td>
                                                <td>{{ number_format($step2->qty_gl2_gelondongan, 3) }}</td>
                                                <td>{{ number_format($step2->qty_ta_adonan, 3) }}</td>
                                                <td>{{ number_format($step2->qty_ta_gelondongan, 3) }}</td>
                                                <td>{{ number_format($step2->qty_bl_adonan, 3) }}</td>
                                                <td>{{ number_format($step2->qty_bl_gelondongan, 3) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2">Totals:</th>
                                                <th>{{ number_format($totals['step2']['qty_gl1_adonan'], 3) }}</th>
                                                <th>{{ number_format($totals['step2']['qty_gl1_gelondongan'], 3) }}</th>
                                                <th>{{ number_format($totals['step2']['qty_gl2_adonan'], 3) }}</th>
                                                <th>{{ number_format($totals['step2']['qty_gl2_gelondongan'], 3) }}</th>
                                                <th>{{ number_format($totals['step2']['qty_ta_adonan'], 3) }}</th>
                                                <th>{{ number_format($totals['step2']['qty_ta_gelondongan'], 3) }}</th>
                                                <th>{{ number_format($totals['step2']['qty_bl_adonan'], 3) }}</th>
                                                <th>{{ number_format($totals['step2']['qty_bl_gelondongan'], 3) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @if($productionPlan->canBeEdited())
                                <div class="mt-3">
                                    <a href="{{ route('manufacturing.production-plans.step2', $productionPlan) }}" class="btn btn-primary">
                                        <i class="far fa-edit me-2"></i>&nbsp;Edit Step 2
                                    </a>
                                </div>
                                @endif
                                @else
                                <div class="alert alert-warning">
                                    Step 2 not yet created. <a href="{{ route('manufacturing.production-plans.step2', $productionPlan) }}">Create Step 2 planning.</a>
                                </div>
                                @endif
                            </div>

                            <!-- Step 3 Tab -->
                            <div class="tab-pane {{ request('step') == '3' ? 'active' : '' }}" id="step3">
                                <h4 class="mb-3">Step 3: Kerupuk Kering Production Planning</h4>
                                @if($productionPlan->step3->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>Gelondongan Item</th>
                                                <th>Kerupuk Kering Item</th>
                                                <th colspan="2">GL1</th>
                                                <th colspan="2">GL2</th>
                                                <th colspan="2">TA</th>
                                                <th colspan="2">BL</th>
                                            </tr>
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th>Gelondongan</th>
                                                <th>Kg</th>
                                                <th>Gelondongan</th>
                                                <th>Kg</th>
                                                <th>Gelondongan</th>
                                                <th>Kg</th>
                                                <th>Gelondongan</th>
                                                <th>Kg</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($productionPlan->step3 as $step3)
                                            <tr>
                                                <td>{{ $step3->gelondonganItem->name ?? 'N/A' }}</td>
                                                <td>{{ $step3->kerupukKeringItem->name ?? 'N/A' }}</td>
                                                <td>{{ number_format($step3->qty_gl1_gelondongan, 3) }}</td>
                                                <td>{{ number_format($step3->qty_gl1_kg, 3) }}</td>
                                                <td>{{ number_format($step3->qty_gl2_gelondongan, 3) }}</td>
                                                <td>{{ number_format($step3->qty_gl2_kg, 3) }}</td>
                                                <td>{{ number_format($step3->qty_ta_gelondongan, 3) }}</td>
                                                <td>{{ number_format($step3->qty_ta_kg, 3) }}</td>
                                                <td>{{ number_format($step3->qty_bl_gelondongan, 3) }}</td>
                                                <td>{{ number_format($step3->qty_bl_kg, 3) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2">Totals:</th>
                                                <th>{{ number_format($totals['step3']['qty_gl1_gelondongan'], 3) }}</th>
                                                <th>{{ number_format($totals['step3']['qty_gl1_kg'], 3) }}</th>
                                                <th>{{ number_format($totals['step3']['qty_gl2_gelondongan'], 3) }}</th>
                                                <th>{{ number_format($totals['step3']['qty_gl2_kg'], 3) }}</th>
                                                <th>{{ number_format($totals['step3']['qty_ta_gelondongan'], 3) }}</th>
                                                <th>{{ number_format($totals['step3']['qty_ta_kg'], 3) }}</th>
                                                <th>{{ number_format($totals['step3']['qty_bl_gelondongan'], 3) }}</th>
                                                <th>{{ number_format($totals['step3']['qty_bl_kg'], 3) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @if($productionPlan->canBeEdited())
                                <div class="mt-3">
                                    <a href="{{ route('manufacturing.production-plans.step3', $productionPlan) }}" class="btn btn-primary">
                                        <i class="far fa-edit me-2"></i>&nbsp;Edit Step 3
                                    </a>
                                </div>
                                @endif
                                @else
                                <div class="alert alert-warning">
                                    Step 3 not yet created. <a href="{{ route('manufacturing.production-plans.step3', $productionPlan) }}">Create Step 3 planning.</a>
                                </div>
                                @endif
                            </div>

                            <!-- Step 4 Tab -->
                            <div class="tab-pane {{ request('step') == '4' ? 'active' : '' }}" id="step4">
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
                                            </tr>
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th>Kg</th>
                                                <th>Packing</th>
                                                <th>Kg</th>
                                                <th>Packing</th>
                                                <th>Kg</th>
                                                <th>Packing</th>
                                                <th>Kg</th>
                                                <th>Packing</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($productionPlan->step4 as $step4)
                                            <tr>
                                                <td>{{ $step4->kerupukKeringItem->name ?? 'N/A' }}</td>
                                                <td>{{ $step4->kerupukPackingItem->name ?? 'N/A' }}</td>
                                                <td>{{ number_format($step4->weight_per_unit, 3) }} kg</td>
                                                <td>{{ number_format($step4->qty_gl1_kg, 3) }}</td>
                                                <td>{{ number_format($step4->qty_gl1_packing, 3) }}</td>
                                                <td>{{ number_format($step4->qty_gl2_kg, 3) }}</td>
                                                <td>{{ number_format($step4->qty_gl2_packing, 3) }}</td>
                                                <td>{{ number_format($step4->qty_ta_kg, 3) }}</td>
                                                <td>{{ number_format($step4->qty_ta_packing, 3) }}</td>
                                                <td>{{ number_format($step4->qty_bl_kg, 3) }}</td>
                                                <td>{{ number_format($step4->qty_bl_packing, 3) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3">Totals:</th>
                                                <th>{{ number_format($totals['step4']['qty_gl1_kg'], 3) }}</th>
                                                <th>{{ number_format($totals['step4']['qty_gl1_packing'], 3) }}</th>
                                                <th>{{ number_format($totals['step4']['qty_gl2_kg'], 3) }}</th>
                                                <th>{{ number_format($totals['step4']['qty_gl2_packing'], 3) }}</th>
                                                <th>{{ number_format($totals['step4']['qty_ta_kg'], 3) }}</th>
                                                <th>{{ number_format($totals['step4']['qty_ta_packing'], 3) }}</th>
                                                <th>{{ number_format($totals['step4']['qty_bl_kg'], 3) }}</th>
                                                <th>{{ number_format($totals['step4']['qty_bl_packing'], 3) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @if($productionPlan->canBeEdited())
                                <div class="mt-3">
                                    <a href="{{ route('manufacturing.production-plans.step4', $productionPlan) }}" class="btn btn-primary">
                                        <i class="far fa-edit me-2"></i>&nbsp;Edit Step 4
                                    </a>
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

