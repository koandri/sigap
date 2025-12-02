@section('title', 'View User')

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
                                            <input type="text" name="name" class="form-control" value="{{ $user->name }}" disabled />
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="name" class="col-sm-2 col-form-label">Email</label>
                                        <div class="col-sm-10">
                                            <input type="email" name="email" class="form-control" value="{{ $user->email }}" disabled />
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="name" class="col-sm-2 col-form-label">Mobile Phone No</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="mobilephone_no" class="form-control" value="{{ $user->mobilephone_no }}" disabled />
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="name" class="col-sm-2 col-form-label">Manager</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="manager_id" class="form-control" value="{{ !is_null($user->manager_id) ? $user->manager->name : '' }}" disabled />
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="name" class="col-sm-2 col-form-label">Locations</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="locations" class="form-control" value="{{ $user->locations ? implode(', ', $user->locations) : '—' }}" disabled />
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="name" class="col-sm-2 col-form-label">Departments</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="locations" class="form-control" value="{{ $user->getDepartmentShortNames() }}" disabled />
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="name" class="col-sm-2 col-form-label">Roles</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="locations" class="form-control" value="{{ $user->getRoleNames()->implode(', ') }}" disabled />
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="roles" class="col-sm-2 col-form-label">All Permissions</label>
                                        <div class="col-sm-10">
                                            <div class="alert alert-info mb-3">
                                                <i class="far fa-info-circle me-2"></i>
                                                <strong>Permission Summary:</strong> 
                                                Total: <strong>{{ $allPermissions->count() }}</strong> | 
                                                From Roles: <strong>{{ $rolePermissions->count() }}</strong> | 
                                                Direct: <strong>{{ $directPermissions->count() }}</strong>
                                            </div>
                                            
                                            @if($rolePermissions->count() > 0)
                                            <div class="mb-4">
                                                <h5 class="mb-3">
                                                    <i class="far fa-users me-2 text-primary"></i>
                                                    Permissions Inherited from Roles ({{ $rolePermissions->count() }})
                                                </h5>
                                                @foreach ($groupedRolePermissions as $prefix => $group)
                                                <div class="mb-3">
                                                    <h6 class="mb-2 border-bottom pb-1">
                                                        <i class="far fa-folder me-2 text-primary"></i>
                                                        {{ $group['name'] }}
                                                    </h6>
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
                                                                    <small class="text-primary d-block mt-1">
                                                                        <i class="far fa-users me-1"></i>
                                                                        Via: {{ $user->roles->filter(fn($role) => $role->hasPermissionTo($permission))->pluck('name')->implode(', ') }}
                                                                    </small>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                            @endif

                                            @if($directPermissions->count() > 0)
                                            <div class="mb-4">
                                                <h5 class="mb-3">
                                                    <i class="far fa-exclamation-triangle me-2 text-warning"></i>
                                                    Direct Permissions ({{ $directPermissions->count() }})
                                                    <small class="text-muted">- Bypass role-based access</small>
                                                </h5>
                                                <div class="alert alert-warning mb-3">
                                                    <i class="far fa-exclamation-triangle me-2"></i>
                                                    These permissions are assigned directly to the user, bypassing role-based access control. 
                                                    Consider moving these to appropriate roles for better maintainability.
                                                </div>
                                                @foreach ($groupedDirectPermissions as $prefix => $group)
                                                <div class="mb-3">
                                                    <h6 class="mb-2 border-bottom pb-1">
                                                        <i class="far fa-folder me-2 text-warning"></i>
                                                        {{ $group['name'] }}
                                                    </h6>
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
                                                                    <small class="text-warning d-block mt-1">
                                                                        <i class="far fa-user me-1"></i>
                                                                        Direct assignment
                                                                    </small>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                            @endif

                                            @if($allPermissions->count() === 0)
                                            <div class="alert alert-secondary">
                                                <i class="far fa-info-circle me-2"></i>
                                                This user has no permissions assigned (neither via roles nor directly).
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="active" class="col-sm-2 col-form-label">Active?</label>
                                        <div class="col-sm-10">
                                            <select class="form-select" name="active" disabled>
                                                <option value="1" @selected($user->active == 1)>Yes</option>
                                                <option value="0" @selected($user->active == 0)>No</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer clearfix">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">Edit</a>
                                    <a href="{{ route('users.index') }}" class="btn float-end">Back to Index</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection