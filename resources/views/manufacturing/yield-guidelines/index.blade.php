@extends('layouts.app')

@section('title', 'Yield Guidelines')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Manufacturing
                </div>
                <h2 class="page-title">
                    Yield Guidelines
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    @can('manufacturing.yield-guidelines.create')
                    <a href="{{ route('manufacturing.yield-guidelines.create') }}" class="btn btn-primary">
                        <i class="far fa-plus me-2"></i>&nbsp;
                        <span class="d-none d-sm-inline">New Yield Guideline</span>
                        <span class="d-sm-none">New</span>
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
                        <h3 class="card-title">All Yield Guidelines</h3>
                        <div class="card-actions">
                            <form method="GET" action="{{ route('manufacturing.yield-guidelines.index') }}" class="d-flex gap-2">
                                <select name="from_stage" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All From Stages</option>
                                    <option value="adonan" {{ request('from_stage') === 'adonan' ? 'selected' : '' }}>Adonan</option>
                                    <option value="gelondongan" {{ request('from_stage') === 'gelondongan' ? 'selected' : '' }}>Gelondongan</option>
                                    <option value="kerupuk_kg" {{ request('from_stage') === 'kerupuk_kg' ? 'selected' : '' }}>Kerupuk Kg</option>
                                </select>
                                <select name="to_stage" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All To Stages</option>
                                    <option value="gelondongan" {{ request('to_stage') === 'gelondongan' ? 'selected' : '' }}>Gelondongan</option>
                                    <option value="kerupuk_kg" {{ request('to_stage') === 'kerupuk_kg' ? 'selected' : '' }}>Kerupuk Kg</option>
                                    <option value="packing" {{ request('to_stage') === 'packing' ? 'selected' : '' }}>Packing</option>
                                </select>
                                <input type="text" name="search" class="form-control form-control-sm" 
                                       value="{{ request('search') }}" placeholder="Search items...">
                                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                                @if(request()->anyFilled(['from_stage', 'to_stage', 'search']))
                                <a href="{{ route('manufacturing.yield-guidelines.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                                @endif
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>From Item</th>
                                    <th>From Stage</th>
                                    <th>To Item</th>
                                    <th>To Stage</th>
                                    <th>Yield Quantity</th>
                                    <th class="w-1">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($guidelines as $guideline)
                                <tr>
                                    <td>
                                        <div class="font-weight-medium">{{ $guideline->fromItem->name ?? 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-blue-lt">{{ ucfirst(str_replace('_', ' ', $guideline->from_stage)) }}</span>
                                    </td>
                                    <td>
                                        <div class="font-weight-medium">{{ $guideline->toItem->name ?? 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-green-lt">{{ ucfirst(str_replace('_', ' ', $guideline->to_stage)) }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ number_format($guideline->yield_quantity, 3) }}</strong>
                                    </td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <a href="{{ route('manufacturing.yield-guidelines.show', $guideline) }}" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                            <a href="{{ route('manufacturing.yield-guidelines.edit', $guideline) }}" class="btn btn-sm btn-outline-secondary">
                                                Edit
                                            </a>
                                            @can('manufacturing.yield-guidelines.delete')
                                            <form method="POST" action="{{ route('manufacturing.yield-guidelines.destroy', $guideline) }}" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this yield guideline?')">
                                                    Delete
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <div class="empty">
                                            <div class="empty-img"><img src="https://via.placeholder.com/128x128/e9ecef/6c757d?text=No+Data" height="128" alt=""></div>
                                            <p class="empty-title">No yield guidelines found</p>
                                            <p class="empty-subtitle text-muted">
                                                Create your first yield guideline to get started.
                                            </p>
                                            @can('manufacturing.yield-guidelines.create')
                                            <div class="empty-action">
                                                <a href="{{ route('manufacturing.yield-guidelines.create') }}" class="btn btn-primary">
                                                    <i class="far fa-plus me-2"></i>&nbsp;
                                                    Create Yield Guideline
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
                    @if($guidelines->hasPages())
                    <div class="card-footer d-flex align-items-center">
                        {{ $guidelines->links('layouts.pagination') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
















