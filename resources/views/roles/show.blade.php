@section('title', 'View Role')

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
                                            <input type="text" name="name" class="form-control" value="{{ $role->name }}" disabled />
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="users" class="col-sm-2 col-form-label">Users with this Role</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="users" class="form-control" value="{{ $usersCount }} user(s)" disabled />
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="permissions" class="col-sm-2 col-form-label">Permissions</label>
                                        <div class="col-sm-10">
                                            <div class="alert alert-info mb-3">
                                                <i class="far fa-info-circle me-2"></i>
                                                <strong>Permission Summary:</strong> This role has <strong>{{ $permissions->count() }}</strong> permission(s) assigned.
                                                All users with this role will inherit these permissions.
                                            </div>
                                            @if($permissions->count() > 0)
                                            @foreach ($groupedPermissions as $prefix => $group)
                                            <div class="mb-4">
                                                <h5 class="mb-3 border-bottom pb-2">
                                                    <i class="far fa-folder me-2 text-primary"></i>
                                                    {{ $group['name'] }}
                                                </h5>
                                                <div class="row">
                                                    @foreach ($group['permissions'] as $permission)
                                                    <div class="col-md-4 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" checked disabled>
                                                            <label class="form-check-label">
                                                                <strong>{{ $permission->name }}</strong>
                                                                @if($permission->description)
                                                                    <small class="text-muted d-block">{{ $permission->description }}</small>
                                                                @endif
                                                            </label>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endforeach
                                            @else
                                            <div class="alert alert-warning">
                                                <i class="far fa-exclamation-triangle me-2"></i>
                                                This role has no permissions assigned. Users with this role will not have any permissions.
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer clearfix">
                                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-primary">Edit</a>
                                    <a href="{{ route('roles.index') }}" class="btn float-end">Back to Index</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection