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
                                            <input type="text" name="locations" class="form-control" value="{{ implode(', ', $user->locations) }}" disabled />
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
                                        <label for="roles" class="col-sm-2 col-form-label">Permissions</label>
                                        <div class="col-sm-10">
                                            @foreach ($user->permissions()->orderBy('name')->get() as $user_permission)
                                            <div class="col-3">
                                                <label class="form-check">
                                                    <input class="form-check-input" name="permissions[]" type="checkbox" value="{{ $user_permission->id }}" checked disabled>
                                                    <span class="form-check-label">{{ $user_permission->name }}</span>
                                                </label>
                                            </div>
                                            @endforeach
                                            @foreach ($permissions as $permission)
                                            <div class="col-3">
                                                <label class="form-check">
                                                    <input class="form-check-input" name="permissions[]" value="{{ $permission->id }}" type="checkbox" disabled>
                                                    <span class="form-check-label">{{ $permission->name }}</span>
                                                </label>
                                            </div>
                                            @endforeach
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