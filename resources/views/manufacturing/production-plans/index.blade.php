@extends('layouts.app')

@section('title', 'Production Plans')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Manufacturing
                </div>
                <h2 class="page-title">
                    Production Plans
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    @can('manufacturing.production-plans.create')
                    <a href="{{ route('manufacturing.production-plans.create') }}" class="btn btn-primary d-none d-sm-inline-block">
                        <i class="far fa-plus me-2"></i>&nbsp;
                        New Production Plan
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')
        
        <div class="row row-deck row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Production Plans</h3>
                        <div class="card-actions">
                            <form method="GET" action="{{ route('manufacturing.production-plans.index') }}" class="d-flex gap-2">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Status</option>
                                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="in_production" {{ request('status') === 'in_production' ? 'selected' : '' }}>In Production</option>
                                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                                <input type="date" name="date_from" class="form-control form-control-sm" 
                                       value="{{ request('date_from') }}" placeholder="From Date">
                                <input type="date" name="date_to" class="form-control form-control-sm" 
                                       value="{{ request('date_to') }}" placeholder="To Date">
                                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                                @if(request()->anyFilled(['status', 'date_from', 'date_to']))
                                <a href="{{ route('manufacturing.production-plans.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                                @endif
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Plan Date</th>
                                    <th>Production Start</th>
                                    <th>Ready Date</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Approved By</th>
                                    <th class="w-1">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($plans as $plan)
                                <tr>
                                    <td>
                                        <div class="font-weight-medium">{{ $plan->plan_date->format('M d, Y') }}</div>
                                        @if($plan->notes)
                                        <div class="text-muted text-xs">{{ Str::limit($plan->notes, 50) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $plan->production_start_date->format('M d, Y') }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $plan->ready_date->format('M d, Y') }}</span>
                                    </td>
                                    <td>
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
                                        <span class="badge {{ $statusColors[$plan->status] ?? 'bg-muted-lt' }}">
                                            {{ $statusLabels[$plan->status] ?? ucfirst($plan->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $plan->createdBy->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            {{ $plan->approvedBy->name ?? 'N/A' }}
                                            @if($plan->approved_at)
                                                <br><small>{{ $plan->approved_at->format('M d, Y') }}</small>
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <a href="{{ route('manufacturing.production-plans.show', $plan) }}" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                            @if($plan->canBeEdited())
                                            <a href="{{ route('manufacturing.production-plans.edit', $plan) }}" class="btn btn-sm btn-outline-secondary">
                                                Edit
                                            </a>
                                            @can('manufacturing.production-plans.delete')
                                            <form method="POST" action="{{ route('manufacturing.production-plans.destroy', $plan) }}" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this production plan?')">
                                                    Delete
                                                </button>
                                            </form>
                                            @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <div class="empty">
                                            <div class="empty-img"><img src="https://via.placeholder.com/128x128/e9ecef/6c757d?text=No+Data" height="128" alt=""></div>
                                            <p class="empty-title">No production plans found</p>
                                            <p class="empty-subtitle text-muted">
                                                Create your first production plan to get started.
                                            </p>
                                            @can('manufacturing.production-plans.create')
                                            <div class="empty-action">
                                                <a href="{{ route('manufacturing.production-plans.create') }}" class="btn btn-primary">
                                                    <i class="far fa-plus me-2"></i>&nbsp;
                                                    Create Production Plan
                                                </a>
                                            </div>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($plans->hasPages())
                    <div class="card-footer d-flex align-items-center">
                        {{ $plans->links('layouts.pagination') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


















