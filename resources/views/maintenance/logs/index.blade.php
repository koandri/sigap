@extends('layouts.app')

@section('title', 'Maintenance Logs')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    Maintenance Logs
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="far fa-filter me-2"></i>&nbsp;Filters
                </h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('maintenance.logs.index') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Asset</label>
                            <select name="asset" class="form-select" id="asset-select">
                                <option value="">All Assets</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}" {{ request('asset') == $asset->id ? 'selected' : '' }}>
                                        {{ $asset->code }} - {{ $asset->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="far fa-search me-1"></i>&nbsp; Filter
                                </button>
                                <a href="{{ route('maintenance.logs.index') }}" class="btn btn-secondary">
                                    <i class="far fa-times"></i>&nbsp;
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Maintenance Logs Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="far fa-list me-2"></i>&nbsp;All Maintenance Logs
                </h3>
                <div class="card-actions">
                    <span class="text-muted">{{ $logs->total() }} total log(s)</span>
                </div>
            </div>
            @if($logs->count() > 0)
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-hover">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Asset</th>
                                <th>Work Order</th>
                                <th>Performed By</th>
                                <th>Action Taken</th>
                                <th>Findings</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                            <tr>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold">{{ $log->performed_at->format('M d, Y') }}</span>
                                        <small class="text-muted">{{ $log->performed_at->format('H:i') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-bold">{{ $log->asset->name }}</div>
                                        <div class="text-muted small">{{ $log->asset->code }}</div>
                                    </div>
                                </td>
                                <td>
                                    @if($log->work_order_id)
                                        <a href="{{ route('maintenance.work-orders.show', $log->workOrder) }}" class="text-decoration-none">
                                            <span class="badge bg-info text-white">
                                                WO-{{ str_pad((string)$log->work_order_id, 4, '0', STR_PAD_LEFT) }}
                                            </span>
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->performedBy)
                                        <div class="d-flex align-items-center">
                                            <span>{{ $log->performedBy->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $log->action_taken }}">
                                        {{ $log->action_taken }}
                                    </div>
                                </td>
                                <td>
                                    @if($log->findings)
                                        <div class="text-truncate" style="max-width: 200px;" title="{{ $log->findings }}">
                                            {{ $log->findings }}
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $logs->links() }}
            </div>
            @else
            <div class="card-body">
                <div class="empty">
                    <div class="empty-icon">
                        <i class="far fa-clipboard-list icon"></i>&nbsp;
                    </div>
                    <p class="empty-title">No maintenance logs found</p>
                    <p class="empty-subtitle text-muted">
                        There are no maintenance logs matching your filters.
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/tom-select.base.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize TomSelect for asset dropdown
        if (document.getElementById('asset-select')) {
            new TomSelect('#asset-select', {
                placeholder: 'All Assets',
                allowEmptyOption: true,
                maxOptions: null
            });
        }
    });
</script>
@endpush

