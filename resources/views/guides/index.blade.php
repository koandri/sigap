@extends('layouts.app')

@section('title', 'User Guides')

@section('content')
<!-- BEGIN PAGE HEADER -->
<div class="page-header d-print-none" aria-label="Page header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">User Guides</h2>
                <div class="text-muted mt-1">Browse documentation and download guides as PDF</div>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('guides.download-combined') }}" class="btn btn-primary">
                    <i class="far fa-file-pdf"></i>&nbsp;Download Complete Handbook (PDF)
                </a>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE HEADER -->

<!-- BEGIN PAGE BODY -->
<div class="page-body">
    <div class="container-xl">
        <div class="row">
            @include('layouts.alerts')
        </div>

        @if($guides->isEmpty())
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="far fa-book-open fa-3x text-muted mb-3"></i>
                        <h3 class="text-muted">No guides available</h3>
                        <p class="text-muted">User guides will appear here once they are added to the system.</p>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="row row-cards">
            @foreach($guides as $guide)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="card-title">
                            <a href="{{ route('guides.show', $guide['filename']) }}" class="text-decoration-none">
                                {{ $guide['title'] }}
                            </a>
                        </h3>
                        @if(!empty($guide['description']))
                        <p class="text-muted">{{ Str::limit($guide['description'], 120) }}</p>
                        @endif
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="far fa-clock"></i>&nbsp;
                                Updated: {{ \Carbon\Carbon::createFromTimestamp($guide['modified'])->format('M d, Y') }}
                            </small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="btn-list">
                            <a href="{{ route('guides.show', $guide['filename']) }}" class="btn btn-sm btn-primary">
                                <i class="far fa-eye"></i>&nbsp;View
                            </a>
                            <a href="{{ route('guides.download-pdf', $guide['filename']) }}" class="btn btn-sm btn-outline-danger">
                                <i class="far fa-file-pdf"></i>&nbsp;PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="row mt-4">
            <div class="col-12">
                <div class="card bg-primary-lt">
                    <div class="card-body">
                        <h3 class="card-title">
                            <i class="far fa-file-pdf"></i>&nbsp;Complete User Handbook
                        </h3>
                        <p class="text-muted mb-3">
                            Download a single PDF file containing all user guides. Perfect for printing or offline reference.
                        </p>
                        <a href="{{ route('guides.download-combined') }}" class="btn btn-primary">
                            <i class="far fa-download"></i>&nbsp;Download Complete Handbook
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE BODY -->
@endsection





















