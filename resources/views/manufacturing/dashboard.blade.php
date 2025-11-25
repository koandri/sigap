@extends('layouts.app')

@section('title', 'Manufacturing Dashboard')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Overview
                </div>
                <h2 class="page-title">
                    Manufacturing Dashboard
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        
        <!-- Statistics Cards -->
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Plans</div>
                        </div>
                        <div class="h1 mb-0">{{ $stats['total_plans'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">In Production</div>
                        </div>
                        <div class="h1 mb-0">{{ $stats['in_production_plans'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Active Recipes</div>
                        </div>
                        <div class="h1 mb-0">{{ $stats['total_recipes'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Yield Guidelines</div>
                        </div>
                        <div class="h1 mb-0">{{ $stats['total_yield_guidelines'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Active Production Plans -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Active Production Plans</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Plan Date</th>
                                    <th>Status</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeProductionPlans as $plan)
                                <tr>
                                    <td>{{ $plan->plan_date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $plan->status)) }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('manufacturing.production-plans.show', $plan) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No active production plans</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Production Plans -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Production Plans</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Plan Date</th>
                                    <th>Status</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentProductionPlans as $plan)
                                <tr>
                                    <td>{{ $plan->plan_date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge @if($plan->status === 'draft') bg-secondary @elseif($plan->status === 'approved') bg-success @elseif($plan->status === 'in_production') bg-info @elseif($plan->status === 'completed') bg-primary @endif">
                                            {{ ucfirst(str_replace('_', ' ', $plan->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('manufacturing.production-plans.show', $plan) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Recipes -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Recipes</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Recipe Name</th>
                                    <th>Dough Item</th>
                                    <th>Recipe Date</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentRecipes as $recipe)
                                <tr>
                                    <td>{{ $recipe->name }}</td>
                                    <td>{{ $recipe->doughItem->name ?? 'N/A' }}</td>
                                    <td>{{ $recipe->recipe_date->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('manufacturing.recipes.show', $recipe) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('manufacturing.production-plans.index') }}" class="btn btn-outline-primary w-100">
                                    <i class="far fa-calendar-check icon mb-2"></i>&nbsp;
                                    <br>Production Planning
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('manufacturing.recipes.index') }}" class="btn btn-outline-primary w-100">
                                    <i class="far fa-book icon mb-2"></i>&nbsp;
                                    <br>Recipes
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('manufacturing.yield-guidelines.index') }}" class="btn btn-outline-primary w-100">
                                    <i class="far fa-chart-line icon mb-2"></i>&nbsp;
                                    <br>Yield Guidelines
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('manufacturing.production-plans.create') }}" class="btn btn-outline-success w-100">
                                    <i class="far fa-plus icon mb-2"></i>&nbsp;
                                    <br>New Production Plan
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

