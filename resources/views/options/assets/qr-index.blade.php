@extends('layouts.app')

@section('title', 'Asset QR Codes')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    Asset QR Codes
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('options.assets.index') }}" class="btn btn-outline-secondary">
                        Back to Assets
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('options.assets.qr-index') }}">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Asset name or code" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Asset Location</label>
                            <select name="location" class="form-select">
                                <option value="">All Locations</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ request('location') == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="{{ route('options.assets.qr-index') }}" class="btn btn-outline-secondary">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- QR Codes Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">QR Codes ({{ $assets->total() }})</h3>
            </div>
            @if($assets->count() > 0)
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Asset Code</th>
                                <th>Asset Name</th>
                                <th>Asset Category</th>
                                <th>Asset Location</th>
                                <th class="text-center">QR Code</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assets as $asset)
                                <tr>
                                    <td>
                                        <a href="{{ route('options.assets.show', $asset) }}">
                                            {{ $asset->code }}
                                        </a>
                                    </td>
                                    <td>{{ $asset->name }}</td>
                                    <td>
                                        @if($asset->assetCategory)
                                            {{ $asset->assetCategory->name }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($asset->location)
                                            <i class="far fa-map-marker-alt"></i>&nbsp; {{ $asset->location->name }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <img src="{{ $asset->qr_code_url }}" 
                                                 alt="QR Code for {{ $asset->code }}" 
                                                 class="img-fluid"
                                                 style="max-width: 100px; height: auto;">
                                            <div class="btn-list">
                                                <a href="{{ route('options.assets.qr-code', $asset) }}" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="View QR Code">
                                                    <i class="far fa-eye"></i>
                                                </a>
                                                <a href="{{ $asset->qr_code_url }}" 
                                                   download="qr-{{ $asset->code }}.png"
                                                   class="btn btn-sm btn-primary"
                                                   title="Download QR Code">
                                                    <i class="far fa-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="card-footer d-flex align-items-center">
                    {{ $assets->links() }}
                </div>
            @else
                <div class="card-body">
                    <div class="empty">
                        <div class="empty-img"><img src="{{ asset('assets/tabler/img/undraw_printing_invoices_-5-r4r.svg') }}" height="128" alt="">
                        </div>
                        <p class="empty-title">No QR codes found</p>
                        <p class="empty-subtitle text-muted">
                            No assets with QR codes match your filters.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


