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
                                @endphp
                                <li class="nav-item">
                                    <a class="nav-link {{ $i === 1 ? 'active' : '' }} {{ !$stepExists ? 'disabled' : '' }}" 
                                       href="#step{{ $i }}" data-bs-toggle="tab" {{ !$stepExists ? 'onclick="return false;"' : '' }}>
                                        Step {{ $i }}
                                        @if($stepComplete)
                                            <i class="far fa-check-circle text-success ms-1"></i>
                                        @elseif($stepExists)
                                            <i class="far fa-clock text-warning ms-1"></i>
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
                                @if($productionPlan->step1->count() > 0)
                                <form method="POST" action="{{ route('manufacturing.production-plans.actuals.step1', $productionPlan) }}">
                                    @csrf
                                    <h4 class="mb-3">Step 1: Dough Production - Actual Quantities</h4>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Dough Item</th>
                                                    <th class="text-end">Planned GL1</th>
                                                    <th class="text-end">Actual GL1</th>
                                                    <th class="text-end">Planned GL2</th>
                                                    <th class="text-end">Actual GL2</th>
                                                    <th class="text-end">Planned TA</th>
                                                    <th class="text-end">Actual TA</th>
                                                    <th class="text-end">Planned BL</th>
                                                    <th class="text-end">Actual BL</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($productionPlan->step1 as $index => $step1)
                                                @php
                                                    $actualStep1 = $step1->actualStep1;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        {{ $step1->doughItem->name ?? 'N/A' }}
                                                        <input type="hidden" name="step1[{{ $index }}][production_plan_step1_id]" value="{{ $step1->id }}">
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
                                                </tr>
                                                @endforeach
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
                                <div class="alert alert-info">No Step 1 data available.</div>
                                @endif
                            </div>

                            <!-- Step 2 Tab -->
                            <div class="tab-pane" id="step2">
                                @if($productionPlan->step2->count() > 0)
                                <form method="POST" action="{{ route('manufacturing.production-plans.actuals.step2', $productionPlan) }}">
                                    @csrf
                                    <h4 class="mb-3">Step 2: Gelondongan Production - Actual Quantities</h4>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
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
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($productionPlan->step2 as $index => $step2)
                                                @php
                                                    $actualStep2 = $step2->actualStep2;
                                                @endphp
                                                <tr>
                                                    <td>{{ $step2->adonanItem->name ?? 'N/A' }}</td>
                                                    <td>{{ $step2->gelondonganItem->name ?? 'N/A' }}</td>
                                                    <input type="hidden" name="step2[{{ $index }}][production_plan_step2_id]" value="{{ $step2->id }}">
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
                                                </tr>
                                                @endforeach
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
                                <div class="alert alert-info">No Step 2 data available.</div>
                                @endif
                            </div>

                            <!-- Step 3 Tab -->
                            <div class="tab-pane" id="step3">
                                @if($productionPlan->step3->count() > 0)
                                <form method="POST" action="{{ route('manufacturing.production-plans.actuals.step3', $productionPlan) }}">
                                    @csrf
                                    <h4 class="mb-3">Step 3: Kerupuk Kering Production - Actual Quantities</h4>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
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
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($productionPlan->step3 as $index => $step3)
                                                @php
                                                    $actualStep3 = $step3->actualStep3;
                                                @endphp
                                                <tr>
                                                    <td>{{ $step3->gelondonganItem->name ?? 'N/A' }}</td>
                                                    <td>{{ $step3->kerupukKeringItem->name ?? 'N/A' }}</td>
                                                    <input type="hidden" name="step3[{{ $index }}][production_plan_step3_id]" value="{{ $step3->id }}">
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
                                                </tr>
                                                @endforeach
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
                                <div class="alert alert-info">No Step 3 data available.</div>
                                @endif
                            </div>

                            <!-- Step 4 Tab -->
                            <div class="tab-pane" id="step4">
                                @if($productionPlan->step4->count() > 0)
                                <form method="POST" action="{{ route('manufacturing.production-plans.actuals.step4', $productionPlan) }}">
                                    @csrf
                                    <h4 class="mb-3">Step 4: Packing Production - Actual Quantities</h4>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
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
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($productionPlan->step4 as $index => $step4)
                                                @php
                                                    $actualStep4 = $step4->actualStep4;
                                                @endphp
                                                <tr>
                                                    <td>{{ $step4->kerupukKeringItem->name ?? 'N/A' }}</td>
                                                    <td>{{ $step4->kerupukPackingItem->name ?? 'N/A' }}</td>
                                                    <input type="hidden" name="step4[{{ $index }}][production_plan_step4_id]" value="{{ $step4->id }}">
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
                                                </tr>
                                                @endforeach
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
                                <div class="alert alert-info">No Step 4 data available.</div>
                                @endif
                            </div>

                            <!-- Step 5 Tab -->
                            <div class="tab-pane" id="step5">
                                @if($productionPlan->step5->count() > 0)
                                <form method="POST" action="{{ route('manufacturing.production-plans.actuals.step5', $productionPlan) }}">
                                    @csrf
                                    <h4 class="mb-3">Step 5: Packing Materials Usage - Actual Quantities</h4>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Pack SKU</th>
                                                    <th>Packing Material Item</th>
                                                    <th class="text-end">Planned Total</th>
                                                    <th class="text-end">Actual Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($productionPlan->step5 as $index => $step5)
                                                @php
                                                    $actualStep5 = $step5->actualStep5;
                                                @endphp
                                                <tr>
                                                    <td>{{ $step5->packSku->name ?? 'N/A' }}</td>
                                                    <td>{{ $step5->packingMaterialItem->name ?? 'N/A' }}</td>
                                                    <input type="hidden" name="step5[{{ $index }}][production_plan_step5_id]" value="{{ $step5->id }}">
                                                    <td class="text-end text-muted">{{ number_format($step5->quantity_total, 0) }}</td>
                                                    <td class="text-end">
                                                        <input type="number" name="step5[{{ $index }}][actual_quantity_total]" 
                                                               class="form-control form-control-sm text-end" 
                                                               value="{{ $actualStep5->actual_quantity_total ?? 0 }}" 
                                                               min="0" required>
                                                    </td>
                                                </tr>
                                                @endforeach
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
                                <div class="alert alert-info">No Step 5 data available.</div>
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
@endsection


