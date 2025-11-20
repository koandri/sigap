@extends('layouts.app')

@section('title', 'Production Actuals: ' . $productionPlan->plan_date->format('M d, Y'))

@section('content')
@php
    function getVarianceClass($status) {
        return match($status) {
            'on_target' => 'table-success',
            'minor_variance' => 'table-warning',
            'major_variance' => 'table-danger',
            default => '',
        };
    }
    
    function getVarianceBadge($status) {
        return match($status) {
            'on_target' => '<span class="badge bg-success">On Target</span>',
            'minor_variance' => '<span class="badge bg-warning">Minor Variance</span>',
            'major_variance' => '<span class="badge bg-danger">Major Variance</span>',
            default => '',
        };
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
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.production-plans.show', $productionPlan) }}">Plan Details</a></li>
                        <li class="breadcrumb-item active">Production Actuals</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    Production Actuals: {{ $productionPlan->plan_date->format('M d, Y') }}
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    @if($productionPlan->isInProduction())
                    @can('manufacturing.production-plans.record-actuals')
                    <a href="{{ route('manufacturing.production-plans.execute', $productionPlan) }}" class="btn btn-warning">
                        <i class="far fa-tasks me-2"></i>&nbsp;
                        Continue Production
                    </a>
                    @endcan
                    @endif
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
        
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="h3 mb-0">{{ number_format($progress['completion_percentage'], 1) }}%</div>
                        <div class="text-muted">Completion</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="h3 mb-0">{{ count($progress['steps_complete']) }}/5</div>
                        <div class="text-muted">Steps Complete</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="h3 mb-0">{{ $actual->production_date->format('M d, Y') }}</div>
                        <div class="text-muted">Production Date</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="h3 mb-0">{{ $actual->recordedBy->name ?? 'N/A' }}</div>
                        <div class="text-muted">Recorded By</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comparison Tables -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            @for($i = 1; $i <= 5; $i++)
                                @php
                                    $hasData = count($variances["step{$i}"]) > 0;
                                @endphp
                                <li class="nav-item">
                                    <a class="nav-link {{ $i === 1 ? 'active' : '' }} {{ !$hasData ? 'disabled' : '' }}" 
                                       href="#step{{ $i }}" data-bs-toggle="tab" {{ !$hasData ? 'onclick="return false;"' : '' }}>
                                        Step {{ $i }}
                                        @if($hasData)
                                            <span class="badge bg-primary ms-1">{{ count($variances["step{$i}"]) }}</span>
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
                                @if(count($variances['step1']) > 0)
                                <h4 class="mb-3">Step 1: Dough Production - Planned vs Actual</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Dough Item</th>
                                                <th>Channel</th>
                                                <th class="text-end">Planned</th>
                                                <th class="text-end">Actual</th>
                                                <th class="text-end">Variance</th>
                                                <th class="text-end">Variance %</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($variances['step1'] as $variance)
                                                @foreach(['gl1', 'gl2', 'ta', 'bl'] as $channel)
                                                    @php
                                                        $channelData = $variance['channels'][$channel];
                                                        $varianceClass = getVarianceClass($channelData['status']);
                                                    @endphp
                                                    <tr class="{{ $varianceClass }}">
                                                        <td>{{ $variance['dough_item_name'] }}</td>
                                                        <td><strong>{{ strtoupper($channel) }}</strong></td>
                                                        <td class="text-end">{{ number_format($channelData['planned'], 0) }}</td>
                                                        <td class="text-end">{{ number_format($channelData['actual'], 0) }}</td>
                                                        <td class="text-end {{ $channelData['variance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                            {{ $channelData['variance'] >= 0 ? '+' : '' }}{{ number_format($channelData['variance'], 0) }}
                                                        </td>
                                                        <td class="text-end {{ $channelData['variance_percent'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                            {{ $channelData['variance_percent'] >= 0 ? '+' : '' }}{{ number_format($channelData['variance_percent'], 2) }}%
                                                        </td>
                                                        <td>{!! getVarianceBadge($channelData['status']) !!}</td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="alert alert-info">No Step 1 actual data recorded yet.</div>
                                @endif
                            </div>

                            <!-- Step 2 Tab -->
                            <div class="tab-pane" id="step2">
                                @if(count($variances['step2']) > 0)
                                <h4 class="mb-3">Step 2: Gelondongan Production - Planned vs Actual</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Adonan Item</th>
                                                <th>Gelondongan Item</th>
                                                <th>Channel</th>
                                                <th>Type</th>
                                                <th class="text-end">Planned</th>
                                                <th class="text-end">Actual</th>
                                                <th class="text-end">Variance</th>
                                                <th class="text-end">Variance %</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($variances['step2'] as $variance)
                                                @foreach(['gl1', 'gl2', 'ta', 'bl'] as $channel)
                                                    @foreach(['adonan', 'gelondongan'] as $type)
                                                        @php
                                                            $typeData = $variance['channels'][$channel][$type];
                                                            $varianceClass = getVarianceClass($typeData['status']);
                                                        @endphp
                                                        <tr class="{{ $varianceClass }}">
                                                            <td>{{ $variance['adonan_item_name'] }}</td>
                                                            <td>{{ $variance['gelondongan_item_name'] }}</td>
                                                            <td><strong>{{ strtoupper($channel) }}</strong></td>
                                                            <td><strong>{{ ucfirst($type) }}</strong></td>
                                                            <td class="text-end">{{ number_format($typeData['planned'], 0) }}</td>
                                                            <td class="text-end">{{ number_format($typeData['actual'], 0) }}</td>
                                                            <td class="text-end {{ $typeData['variance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                                {{ $typeData['variance'] >= 0 ? '+' : '' }}{{ number_format($typeData['variance'], 0) }}
                                                            </td>
                                                            <td class="text-end {{ $typeData['variance_percent'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                                {{ $typeData['variance_percent'] >= 0 ? '+' : '' }}{{ number_format($typeData['variance_percent'], 2) }}%
                                                            </td>
                                                            <td>{!! getVarianceBadge($typeData['status']) !!}</td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="alert alert-info">No Step 2 actual data recorded yet.</div>
                                @endif
                            </div>

                            <!-- Step 3 Tab -->
                            <div class="tab-pane" id="step3">
                                @if(count($variances['step3']) > 0)
                                <h4 class="mb-3">Step 3: Kerupuk Kering Production - Planned vs Actual</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Gelondongan Item</th>
                                                <th>Kerupuk Kering Item</th>
                                                <th>Channel</th>
                                                <th>Type</th>
                                                <th class="text-end">Planned</th>
                                                <th class="text-end">Actual</th>
                                                <th class="text-end">Variance</th>
                                                <th class="text-end">Variance %</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($variances['step3'] as $variance)
                                                @foreach(['gl1', 'gl2', 'ta', 'bl'] as $channel)
                                                    @foreach(['gelondongan', 'kg'] as $type)
                                                        @php
                                                            $typeData = $variance['channels'][$channel][$type];
                                                            $varianceClass = getVarianceClass($typeData['status']);
                                                            $isDecimal = $type === 'kg';
                                                        @endphp
                                                        <tr class="{{ $varianceClass }}">
                                                            <td>{{ $variance['gelondongan_item_name'] }}</td>
                                                            <td>{{ $variance['kerupuk_kering_item_name'] }}</td>
                                                            <td><strong>{{ strtoupper($channel) }}</strong></td>
                                                            <td><strong>{{ strtoupper($type) }}</strong></td>
                                                            <td class="text-end">{{ number_format($typeData['planned'], $isDecimal ? 2 : 0) }}</td>
                                                            <td class="text-end">{{ number_format($typeData['actual'], $isDecimal ? 2 : 0) }}</td>
                                                            <td class="text-end {{ $typeData['variance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                                {{ $typeData['variance'] >= 0 ? '+' : '' }}{{ number_format($typeData['variance'], $isDecimal ? 2 : 0) }}
                                                            </td>
                                                            <td class="text-end {{ $typeData['variance_percent'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                                {{ $typeData['variance_percent'] >= 0 ? '+' : '' }}{{ number_format($typeData['variance_percent'], 2) }}%
                                                            </td>
                                                            <td>{!! getVarianceBadge($typeData['status']) !!}</td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="alert alert-info">No Step 3 actual data recorded yet.</div>
                                @endif
                            </div>

                            <!-- Step 4 Tab -->
                            <div class="tab-pane" id="step4">
                                @if(count($variances['step4']) > 0)
                                <h4 class="mb-3">Step 4: Packing Production - Planned vs Actual</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Kerupuk Kering Item</th>
                                                <th>Packing Item</th>
                                                <th>Channel</th>
                                                <th>Type</th>
                                                <th class="text-end">Planned</th>
                                                <th class="text-end">Actual</th>
                                                <th class="text-end">Variance</th>
                                                <th class="text-end">Variance %</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($variances['step4'] as $variance)
                                                @foreach(['gl1', 'gl2', 'ta', 'bl'] as $channel)
                                                    @foreach(['kg', 'packing'] as $type)
                                                        @php
                                                            $typeData = $variance['channels'][$channel][$type];
                                                            $varianceClass = getVarianceClass($typeData['status']);
                                                            $isDecimal = $type === 'kg';
                                                        @endphp
                                                        <tr class="{{ $varianceClass }}">
                                                            <td>{{ $variance['kerupuk_kering_item_name'] }}</td>
                                                            <td>{{ $variance['kerupuk_packing_item_name'] }}</td>
                                                            <td><strong>{{ strtoupper($channel) }}</strong></td>
                                                            <td><strong>{{ ucfirst($type) }}</strong></td>
                                                            <td class="text-end">{{ number_format($typeData['planned'], $isDecimal ? 2 : 0) }}</td>
                                                            <td class="text-end">{{ number_format($typeData['actual'], $isDecimal ? 2 : 0) }}</td>
                                                            <td class="text-end {{ $typeData['variance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                                {{ $typeData['variance'] >= 0 ? '+' : '' }}{{ number_format($typeData['variance'], $isDecimal ? 2 : 0) }}
                                                            </td>
                                                            <td class="text-end {{ $typeData['variance_percent'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                                {{ $typeData['variance_percent'] >= 0 ? '+' : '' }}{{ number_format($typeData['variance_percent'], 2) }}%
                                                            </td>
                                                            <td>{!! getVarianceBadge($typeData['status']) !!}</td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="alert alert-info">No Step 4 actual data recorded yet.</div>
                                @endif
                            </div>

                            <!-- Step 5 Tab -->
                            <div class="tab-pane" id="step5">
                                @if(count($variances['step5']) > 0)
                                <h4 class="mb-3">Step 5: Packing Materials Usage - Planned vs Actual</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Pack SKU</th>
                                                <th>Packing Material Item</th>
                                                <th class="text-end">Planned</th>
                                                <th class="text-end">Actual</th>
                                                <th class="text-end">Variance</th>
                                                <th class="text-end">Variance %</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($variances['step5'] as $variance)
                                                @php
                                                    $varianceClass = getVarianceClass($variance['status']);
                                                @endphp
                                                <tr class="{{ $varianceClass }}">
                                                    <td>{{ $variance['pack_sku_name'] }}</td>
                                                    <td>{{ $variance['packing_material_item_name'] }}</td>
                                                    <td class="text-end">{{ number_format($variance['planned'], 0) }}</td>
                                                    <td class="text-end">{{ number_format($variance['actual'], 0) }}</td>
                                                    <td class="text-end {{ $variance['variance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ $variance['variance'] >= 0 ? '+' : '' }}{{ number_format($variance['variance'], 0) }}
                                                    </td>
                                                    <td class="text-end {{ $variance['variance_percent'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ $variance['variance_percent'] >= 0 ? '+' : '' }}{{ number_format($variance['variance_percent'], 2) }}%
                                                    </td>
                                                    <td>{!! getVarianceBadge($variance['status']) !!}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="alert alert-info">No Step 5 actual data recorded yet.</div>
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


