@section('title', 'View Permission')

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
                                    <h3 class="card-title">@yield('title')</h3>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <div class="row mb-3">
                                        <label for="name" class="col-sm-2 col-form-label">Name</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="name" class="form-control" value="{{ $permission->name }}" disabled />
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer clearfix">
                                    <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-primary">Edit</a>
                                    <a href="{{ route('permissions.index') }}" class="btn float-end">Back to Index</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection