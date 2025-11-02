@extends('layouts.app')

@section('title', 'Asset Maintenance History - ' . $asset->name)

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    Asset Maintenance History
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('options.assets.show', $asset) }}" class="btn btn-secondary">
                        <i class="far fa-arrow-left me-1"></i>&nbsp; Back to Asset
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <!-- Asset Information -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="far fa-info-circle me-2"></i>&nbsp;Asset Information
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label text-muted">Asset Code</label>
                        <div class="fw-bold">{{ $asset->code }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted">Asset Name</label>
                        <div class="fw-bold">{{ $asset->name }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted">Category</label>
                        <div>{{ $asset->category->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted">Location</label>
                        <div>{{ $asset->location?->name ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance History -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="far fa-history me-2"></i>&nbsp;Maintenance History
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
                                <th>Work Order</th>
                                <th>Performed By</th>
                                <th>Action Taken</th>
                                <th>Findings</th>
                                <th>Recommendations</th>
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
                                    @if($log->work_order_id)
                                        <a href="{{ route('maintenance.work-orders.show', $log->workOrder) }}" class="text-decoration-none">
                                            <span class="badge bg-info">
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
                                            <span class="avatar avatar-sm me-2">{{ substr($log->performedBy->name, 0, 2) }}</span>
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
                                <td>
                                    @if($log->recommendations)
                                        <div class="text-truncate" style="max-width: 200px;" title="{{ $log->recommendations }}">
                                            {{ $log->recommendations }}
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
                        <i class="far fa-clipboard-list fa-3x text-muted"></i>&nbsp;
                    </div>
                    <p class="empty-title">No maintenance history</p>
                    <p class="empty-subtitle text-muted">
                        This asset has no maintenance logs yet.
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

