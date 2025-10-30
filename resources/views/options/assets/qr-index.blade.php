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
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="operational" {{ request('status') === 'operational' ? 'selected' : '' }}>Operational</option>
                                <option value="down" {{ request('status') === 'down' ? 'selected' : '' }}>Down</option>
                                <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
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

        <!-- QR Codes Grid -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">QR Codes ({{ $assets->total() }})</h3>
            </div>
            <div class="card-body">
                @if($assets->count() > 0)
                    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
                        @foreach($assets as $asset)
                            <div class="col">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <!-- QR Code -->
                                        <img src="{{ asset($asset->qr_code_path) }}" 
                                                alt="QR Code for {{ $asset->code }}" 
                                                class="img-fluid mb-3"
                                                style="max-width: 200px;">
                                        
                                        <!-- Asset Info -->
                                        <h4 class="card-title mb-2">
                                            <a href="{{ route('options.assets.show', $asset) }}">
                                                {{ $asset->code }}
                                            </a>
                                        </h4>
                                        <p class="text-muted mb-2">{{ $asset->name }}</p>
                                        
                                        <div class="mb-2">
                                            <span class="badge bg-{{ $asset->status === 'operational' ? 'success' : ($asset->status === 'down' ? 'danger' : 'warning') }} text-white">
                                                {{ ucfirst($asset->status) }}
                                            </span>
                                        </div>
                                        
                                        @if($asset->assetCategory)
                                            <small class="text-muted d-block mb-2">{{ $asset->assetCategory->name }}</small>
                                        @endif
                                        
                                        @if($asset->location)
                                            <small class="text-muted d-block mb-3">
                                                <i class="far fa-map-marker-alt"></i> {{ $asset->location->name }}
                                            </small>
                                        @endif
                                        
                                        <!-- Actions -->
                                        <div class="btn-list justify-content-center">
                                            <a href="{{ route('options.assets.qr-code', $asset) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="far fa-eye"></i> View
                                            </a>
                                            <a href="{{ asset($asset->qr_code_path) }}" 
                                               download="qr-{{ $asset->code }}.png"
                                               class="btn btn-sm btn-primary">
                                                <i class="far fa-download"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $assets->links() }}
                    </div>
                @else
                    <div class="empty">
                        <div class="empty-img"><img src="{{ asset('assets/tabler/img/undraw_printing_invoices_-5-r4r.svg') }}" height="128" alt="">
                        </div>
                        <p class="empty-title">No QR codes found</p>
                        <p class="empty-subtitle text-muted">
                            No assets with QR codes match your filters.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transition: box-shadow 0.3s ease-in-out;
}
</style>
@endpush

