@extends('layouts.app')

@section('title', 'Asset Lifetime Report - ' . $asset->code)

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    <a href="{{ route('options.assets.show', $asset) }}">Asset Details</a> / Lifetime Report
                </div>
                <h2 class="page-title">
                    {{ $asset->name }} ({{ $asset->code }})
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Lifetime Status</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Lifetime Unit</label>
                            <div class="form-control-plaintext">
                                @if($asset->lifetime_unit)
                                    <span class="badge bg-blue-lt">{{ $asset->lifetime_unit->label() }}</span>
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Expected Lifetime</label>
                                    <div class="form-control-plaintext fw-bold">
                                        {{ $expectedLifetime ? number_format($expectedLifetime, 1) . ' ' . ($asset->lifetime_unit ? $asset->lifetime_unit->label() : '') : '-' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Actual / Current Lifetime</label>
                                    <div class="form-control-plaintext fw-bold">
                                        {{ $actualLifetime ? number_format($actualLifetime, 1) . ' ' . ($asset->lifetime_unit ? $asset->lifetime_unit->label() : '') : '-' }}
                                    </div>
                                    <small class="form-hint">
                                        @if($asset->disposed_date)
                                            Final lifetime at disposal.
                                        @elseif($asset->lifetime_unit && $asset->lifetime_unit->isUsageBased())
                                            Only available after disposal for usage-based units.
                                        @else
                                            Current age since installation/purchase.
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>

                        @if($lifetimePercentage !== null)
                            <div class="mb-3">
                                <label class="form-label">Usage Percentage</label>
                                <div class="progress mb-2">
                                    <div class="progress-bar {{ $lifetimePercentage > 100 ? 'bg-danger' : ($lifetimePercentage > 80 ? 'bg-warning' : 'bg-success') }}" 
                                         style="width: {{ min(100, $lifetimePercentage) }}%"
                                         role="progressbar" 
                                         aria-valuenow="{{ $lifetimePercentage }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <span class="visually-hidden">{{ number_format($lifetimePercentage, 1) }}%</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between text-muted small">
                                    <span>0%</span>
                                    <span>{{ number_format($lifetimePercentage, 1) }}% used</span>
                                    <span>100%</span>
                                </div>
                            </div>
                        @endif

                        @if($remainingLifetime !== null)
                            <div class="mb-3">
                                <label class="form-label">Remaining Lifetime</label>
                                <div class="form-control-plaintext {{ $remainingLifetime <= 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($remainingLifetime, 1) }} {{ $asset->lifetime_unit ? $asset->lifetime_unit->label() : '' }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Category Benchmarks</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Comparison with other assets in the <strong>{{ $asset->assetCategory->name }}</strong> category.
                        </p>

                        <div class="mb-3">
                            <label class="form-label">Category Average</label>
                            <div class="form-control-plaintext">
                                @if($suggestedLifetime)
                                    {{ number_format($suggestedLifetime, 1) }}
                                @else
                                    <span class="text-muted">Not enough data</span>
                                @endif
                            </div>
                        </div>

                        @if($expectedLifetime && $suggestedLifetime)
                            <div class="mb-3">
                                <label class="form-label">Performance vs Average</label>
                                @php
                                    $diff = $expectedLifetime - $suggestedLifetime;
                                    $percentDiff = ($diff / $suggestedLifetime) * 100;
                                @endphp
                                <div class="form-control-plaintext">
                                    <span class="{{ $diff >= 0 ? 'text-success' : 'text-warning' }}">
                                        {{ $diff >= 0 ? '+' : '' }}{{ number_format($percentDiff, 1) }}%
                                    </span>
                                    <span class="text-muted small">
                                        ({{ $diff >= 0 ? 'Better' : 'Worse' }} than average)
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection






