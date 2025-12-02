@section('title', 'Create a new role')

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
                            <form class="card" action="{{ route('roles.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="guard_name" value="web" />
                                <div class="card-header">
                                    <h3 class="card-title">@yield('title')</h3>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <div class="row mb-3">
                                        <label for="name" class="col-sm-2 col-form-label required">Name</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="name" class="form-control" max="50" maxlength="50" required />
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="permissions" class="col-sm-2 col-form-label">Permissions</label>
                                        <div class="col-sm-10">
                                            @if($permissions->isEmpty())
                                            <div class="alert alert-secondary">
                                                <i class="far fa-info-circle me-2"></i>
                                                No permissions available. Please create permissions first.
                                            </div>
                                            @else
                                            @foreach ($groupedPermissions as $prefix => $group)
                                            <div class="mb-4">
                                                <h5 class="mb-3 border-bottom pb-2">
                                                    <i class="far fa-folder me-2 text-primary"></i>
                                                    {{ $group['name'] }}
                                                </h5>
                                                <div class="row">
                                                    @foreach ($group['permissions'] as $permission)
                                                    <div class="col-md-4 mb-2">
                                                        <label class="form-check">
                                                            <input class="form-check-input" name="permissions[]" value="{{ $permission->id }}" type="checkbox">
                                                            <span class="form-check-label">
                                                                {{ $permission->name }}
                                                                @if($permission->description)
                                                                    <small class="text-muted d-block">{{ $permission->description }}</small>
                                                                @endif
                                                            </span>
                                                        </label>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer clearfix">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                    <a href="{{ route('roles.index') }}" class="btn float-end">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection