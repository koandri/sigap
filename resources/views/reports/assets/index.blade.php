@extends('layouts.app')

@section('title', 'Asset Reports')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Reports
                </div>
                <h2 class="page-title">
                    Asset Reports
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <!-- Asset Reports Quick Links -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Available Asset Reports</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card card-link">
                            <a href="{{ route('reports.assets.by-location') }}" class="text-decoration-none text-reset">
                                <div class="card-body text-center">
                                    <div class="text-primary mb-3">
                                        <i class="fa fa-map-marker-alt fa-3x"></i>
                                    </div>
                                    <h3 class="card-title mb-2">Assets by Location</h3>
                                    <p class="text-secondary mb-0">View active and inactive assets grouped by location</p>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-link">
                            <a href="{{ route('reports.assets.by-category') }}" class="text-decoration-none text-reset">
                                <div class="card-body text-center">
                                    <div class="text-success mb-3">
                                        <i class="fa fa-tags fa-3x"></i>
                                    </div>
                                    <h3 class="card-title mb-2">Assets by Category</h3>
                                    <p class="text-secondary mb-0">View all assets filtered by category</p>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-link">
                            <a href="{{ route('reports.assets.by-category-location') }}" class="text-decoration-none text-reset">
                                <div class="card-body text-center">
                                    <div class="text-info mb-3">
                                        <i class="fa fa-layer-group fa-3x"></i>
                                    </div>
                                    <h3 class="card-title mb-2">Assets by Category & Location</h3>
                                    <p class="text-secondary mb-0">View assets by category across multiple locations</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <div class="card card-link">
                            <a href="{{ route('reports.assets.by-department') }}" class="text-decoration-none text-reset">
                                <div class="card-body text-center">
                                    <div class="text-purple mb-3">
                                        <i class="fa fa-building fa-3x"></i>
                                    </div>
                                    <h3 class="card-title mb-2">Assets by Department</h3>
                                    <p class="text-secondary mb-0">View all assets grouped by department</p>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-link">
                            <a href="{{ route('reports.assets.by-user') }}" class="text-decoration-none text-reset">
                                <div class="card-body text-center">
                                    <div class="text-orange mb-3">
                                        <i class="fa fa-user fa-3x"></i>
                                    </div>
                                    <h3 class="card-title mb-2">Assets by Assigned User</h3>
                                    <p class="text-secondary mb-0">View assets assigned to specific users</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

