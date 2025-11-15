@extends('layouts.app')

@section('title', 'Yield Guideline: ' . ($yieldGuideline->fromItem->name ?? 'N/A') . ' → ' . ($yieldGuideline->toItem->name ?? 'N/A'))

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.dashboard') }}">Manufacturing</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.yield-guidelines.index') }}">Yield Guidelines</a></li>
                        <li class="breadcrumb-item active">Guideline Details</li>
                    </ol>
                </nav>
                <h2 class="page-title">
                    Yield Guideline
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('manufacturing.yield-guidelines.edit', $yieldGuideline) }}" class="btn btn-primary">
                        <i class="far fa-edit me-2"></i>&nbsp;
                        Edit Guideline
                    </a>
                    <a href="{{ route('manufacturing.yield-guidelines.index') }}" class="btn btn-outline-secondary">
                        <i class="far fa-arrow-left me-2"></i>&nbsp;
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
        
        <!-- Yield Guideline Information -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Guideline Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dt>From Stage:</dt>
                                <dd>
                                    <span class="badge bg-blue-lt">{{ ucfirst(str_replace('_', ' ', $yieldGuideline->from_stage)) }}</span>
                                </dd>
                            </div>
                            <div class="col-md-6">
                                <dt>To Stage:</dt>
                                <dd>
                                    <span class="badge bg-green-lt">{{ ucfirst(str_replace('_', ' ', $yieldGuideline->to_stage)) }}</span>
                                </dd>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <dt>From Item:</dt>
                                <dd>
                                    <div class="font-weight-medium">{{ $yieldGuideline->fromItem->name ?? 'N/A' }}</div>
                                    @if($yieldGuideline->fromItem)
                                    <div class="text-muted small">
                                        Category: {{ $yieldGuideline->fromItem->itemCategory->name ?? 'N/A' }}
                                    </div>
                                    @endif
                                </dd>
                            </div>
                            <div class="col-md-6">
                                <dt>To Item:</dt>
                                <dd>
                                    <div class="font-weight-medium">{{ $yieldGuideline->toItem->name ?? 'N/A' }}</div>
                                    @if($yieldGuideline->toItem)
                                    <div class="text-muted small">
                                        Category: {{ $yieldGuideline->toItem->itemCategory->name ?? 'N/A' }}
                                    </div>
                                    @endif
                                </dd>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6 col-lg-4">
                                <dt>Yield Quantity:</dt>
                                <dd>
                                    <strong class="text-primary" style="font-size: 1.2rem;">{{ number_format($yieldGuideline->yield_quantity, 3) }}</strong>
                                </dd>
                                <small class="text-muted">1 {{ optional($yieldGuideline->fromItem)->name ?? 'From Item' }} produces this many {{ optional($yieldGuideline->toItem)->name ?? 'To Item' }}</small>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <dt>Created:</dt>
                                <dd>{{ $yieldGuideline->created_at->format('M d, Y H:i') }}</dd>
                            </div>
                        </div>
                        @if($yieldGuideline->updated_at != $yieldGuideline->created_at)
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <dt>Last Updated:</dt>
                                <dd>{{ $yieldGuideline->updated_at->format('M d, Y H:i') }}</dd>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage Information -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Usage</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="far fa-info-circle me-2"></i>
                            This yield guideline is used in production planning calculations to convert quantities between production stages.
                        </div>
                        <div class="mb-3">
                            <strong>Conversion:</strong> 
                            <span class="text-muted">1 {{ optional($yieldGuideline->fromItem)->name ?? 'From Item' }} = {{ number_format($yieldGuideline->yield_quantity, 3) }} {{ optional($yieldGuideline->toItem)->name ?? 'To Item' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back Navigation -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('manufacturing.yield-guidelines.index') }}" class="btn btn-outline-secondary">
                                <i class="far fa-arrow-left me-2"></i>&nbsp;
                                Back to Guidelines
                            </a>
                            <div class="btn-list">
                                <a href="{{ route('manufacturing.yield-guidelines.edit', $yieldGuideline) }}" class="btn btn-primary">
                                    <i class="far fa-edit me-2"></i>&nbsp;
                                    Edit Guideline
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
















