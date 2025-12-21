@extends('layouts.app')

@section('title', 'Asset Lifetime Report')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Asset Lifetime Report
                </h2>
                <div class="text-muted mt-1">Average lifetime of assets by category</div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Lifetime Unit</th>
                                <th>Average Lifetime</th>
                                <th>Min / Max</th>
                                <th>Sample Size</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                @if(isset($categoryMetrics[$category->id]))
                                    @foreach($categoryMetrics[$category->id] as $unitValue => $metrics)
                                        <tr>
                                            <td>
                                                @if($loop->first)
                                                    <a href="{{ route('reports.asset-lifetime.category', $category) }}" class="fw-bold text-reset">
                                                        {{ $category->name }}
                                                    </a>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-blue-lt">
                                                    {{ \App\Enums\UsageUnit::tryFrom($unitValue)?->label() ?? $unitValue }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ number_format($metrics['average_lifetime'], 1) }}</div>
                                            </td>
                                            <td>
                                                <div class="text-muted">
                                                    {{ number_format($metrics['min_lifetime'], 1) }} - {{ number_format($metrics['max_lifetime'], 1) }}
                                                </div>
                                            </td>
                                            <td>
                                                {{ $metrics['sample_size'] }} assets
                                            </td>
                                            <td>
                                                @if($loop->first)
                                                    <a href="{{ route('reports.asset-lifetime.category', $category) }}" class="btn btn-sm btn-outline-primary">
                                                        View Details
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No categories found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
















