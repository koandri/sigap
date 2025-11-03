@extends('layouts.app')

@section('title', $title)

@section('content')
<!-- BEGIN PAGE HEADER -->
<div class="page-header d-print-none" aria-label="Page header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('guides.index') }}">User Guides</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
                    </ol>
                </nav>
                <h2 class="page-title">{{ $title }}</h2>
            </div>
            <div class="col-auto ms-auto">
                <div class="btn-list">
                    <a href="{{ route('guides.download-pdf', $filename) }}" class="btn btn-primary">
                        <i class="far fa-file-pdf"></i>&nbsp;Download PDF
                    </a>
                    <a href="{{ route('guides.index') }}" class="btn btn-outline-secondary">
                        <i class="far fa-arrow-left"></i>&nbsp;Back to Guides
                    </a>
                </div>
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

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="guide-content">
                            {!! $content !!}
                        </div>
                    </div>
                    <div class="card-footer d-print-none">
                        <div class="btn-list">
                            <a href="{{ route('guides.download-pdf', $filename) }}" class="btn btn-primary">
                                <i class="far fa-file-pdf"></i>&nbsp;Download as PDF
                            </a>
                            <a href="{{ route('guides.index') }}" class="btn btn-outline-secondary">
                                <i class="far fa-arrow-left"></i>&nbsp;Back to Guides
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE BODY -->

@push('css')
<style>
.guide-content {
    font-size: 1rem;
    line-height: 1.7;
}

.guide-content h1 {
    font-size: 2rem;
    font-weight: 600;
    margin-top: 2rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
}

.guide-content h2 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}

.guide-content h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-top: 1.25rem;
    margin-bottom: 0.5rem;
}

.guide-content h4 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}

.guide-content p {
    margin-bottom: 1rem;
}

.guide-content ul,
.guide-content ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.guide-content li {
    margin-bottom: 0.5rem;
}

.guide-content code {
    background-color: #f1f3f5;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
    font-size: 0.9em;
}

.guide-content pre {
    background-color: #f1f3f5;
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin-bottom: 1rem;
}

.guide-content pre code {
    background-color: transparent;
    padding: 0;
}

.guide-content table {
    width: 100%;
    margin-bottom: 1rem;
    border-collapse: collapse;
}

.guide-content table th,
.guide-content table td {
    padding: 0.75rem;
    border: 1px solid #dee2e6;
}

.guide-content table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.guide-content blockquote {
    border-left: 4px solid #206bc4;
    padding-left: 1rem;
    margin-left: 0;
    margin-bottom: 1rem;
    color: #6c757d;
}

.guide-content img {
    max-width: 100%;
    height: auto;
    border-radius: 0.25rem;
    margin: 1rem 0;
}

.guide-content a {
    color: #206bc4;
    text-decoration: none;
}

.guide-content a:hover {
    text-decoration: underline;
}
</style>
@endpush
@endsection


