@extends('layouts.app')

@section('title', $category->name . ' - Lifetime Report')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    <a href="{{ route('reports.asset-lifetime.index') }}">Reports</a> / Asset Lifetime
                </div>
                <h2 class="page-title">
                    {{ $category->name }}
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Disposed Assets History</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Asset Code</th>
                            <th>Name</th>
                            <th>Installed Date</th>
                            <th>Disposed Date</th>
                            <th>Lifetime Unit</th>
                            <th>Actual Lifetime</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($disposedAssets as $asset)
                            <tr>
                                <td>
                                    <a href="{{ route('options.assets.show', $asset) }}" class="text-reset">
                                        {{ $asset->code }}
                                    </a>
                                </td>
                                <td>
                                    {{ $asset->name }}
                                    @if($asset->parentAsset)
                                        <div class="text-muted small">
                                            Part of: <a href="{{ route('options.assets.show', $asset->parentAsset) }}">{{ $asset->parentAsset->code }}</a>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    {{ $asset->installed_date ? $asset->installed_date->format('Y-m-d') : '-' }}
                                    @if($asset->installed_usage_value)
                                        <div class="text-muted small">
                                            Start: {{ number_format($asset->installed_usage_value) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    {{ $asset->disposed_date ? $asset->disposed_date->format('Y-m-d') : '-' }}
                                    @if($asset->disposed_usage_value)
                                        <div class="text-muted small">
                                            End: {{ number_format($asset->disposed_usage_value) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($asset->lifetime_unit)
                                        <span class="badge bg-blue-lt">{{ $asset->lifetime_unit->label() }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ number_format($asset->actual_lifetime_value, 1) }}</strong>
                                </td>
                                <td class="text-muted">
                                    {{ Str::limit($asset->installation_notes, 50) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No disposed assets with calculated lifetime found in this category.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex align-items-center">
                {{ $disposedAssets->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
