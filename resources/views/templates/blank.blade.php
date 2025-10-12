@section('title', 'Blank')

@extends('layouts.app')

@section('content')            
            <!-- BEGIN PAGE HEADER -->
            <div class="page-header d-print-none" aria-label="Page header">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <h2 class="page-title">@yield('title')</h2>
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
                    
                    <div class="row row-deck row-cards">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Invoices</h3>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <div class="table-responsive">
                                    
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="row g-2 justify-content-center justify-content-sm-between">
                                        <div class="col-auto d-flex align-items-center">
                                            <p class="m-0 text-secondary">Showing <strong>1 to 8</strong> of <strong>16 entries</strong></p>
                                        </div>
                                        <div class="col-auto">
                                            <ul class="pagination m-0 ms-auto">
                                                <li class="page-item disabled">
                                                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                                                        <!-- Download SVG icon from http://tabler.io/icons/icon/chevron-left -->
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                                            <path d="M15 6l-6 6l6 6" />
                                                        </svg>
                                                    </a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#">1</a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#">2</a>
                                                </li>
                                                <li class="page-item active">
                                                    <a class="page-link" href="#">3</a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#">4</a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#">5</a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#">
                                                        <!-- Download SVG icon from http://tabler.io/icons/icon/chevron-right -->
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                                            <path d="M9 6l6 6l-6 6" />
                                                        </svg>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection